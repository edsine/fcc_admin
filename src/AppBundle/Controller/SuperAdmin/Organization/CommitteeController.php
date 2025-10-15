<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 8:47 PM
 */

namespace AppBundle\Controller\SuperAdmin\Organization;


use AppBundle\AppException\AppException;
use AppBundle\Model\Notification\AlertNotification;
use AppBundle\Model\Organizations\Committee;
use AppBundle\Model\SearchCriteria\CommitteeSearchCriteria;
use AppBundle\Model\Security\SecurityUser;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\GUIDHelper;
use AppBundle\Utils\Paginator;
use AppBundle\Validation\Validator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;

class CommitteeController extends Controller
{

    /**
     * @Route("/super_admin/committee/list", name="committee_list")
     */
    public function listAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $searchFilter = new CommitteeSearchCriteria();
        $searchFilter->setName($request->request->get('searchName'));
        $searchFilter->setStatus($request->request->get('searchStatus', AppConstants::ACTIVE));

        $paginator = new Paginator();
        $paginator->setStartRow($request->request->get('startRow', 0));

        $pageDirection = '';
        if ($request->request->has('btnSearch')) {
            $pageDirection = "FIRST";
        } else if ($request->request->has('btnPageFirst')) {
            $pageDirection = "FIRST";
        } else if ($request->request->has('btnPagePrev')) {
            $pageDirection = "PREVIOUS";
        } else if ($request->request->has('btnPageNext')) {
            $pageDirection = "NEXT";
        } else if ($request->request->has('btnPageLast')) {
            $pageDirection = "LAST";
        }

        $records = array();
        $committeeService = $this->get('app.committee_service');
        try {
            $records = $committeeService->searchRecords($searchFilter, $paginator, $pageDirection);
        } catch (AppException $e) {
            $this->get('logger')->info($e->getMessage());
        }

        return $this->render('super_admin/organization/committee_list.html.twig',
            array('records' => $records,
                'searchFilter' => $searchFilter,
                'paginator' => $paginator
            )
        );
    }

    /**
     * @Route("/super_admin/committee/new",name="committee_new")
     */
    public function newAction(Request $request)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();

        $committee = new Committee();

        $validator = new Validator();
        $this->initializeValidationFields($validator, AppConstants::NEW);

        $alertNotification = new AlertNotification();
        $outcome = false;

        //check if the form was submitted and process else render empty form
        if ($request->request->has("btnSubmit")) {

            $committee = $this->fillModelFromRequest($request);
            $this->validateForm($validator, $committee, AppConstants::NEW);

            if (!$validator->getFields()->hasErrors()) {

                $committee->setStatus(AppConstants::ACTIVE);

                $today = date("Y-m-d H:i:s");
                $committee->setLastModified($today);
                $committee->setLastModifiedByUserId($loggedInUser->getId());

                $guidHelper = new GUIDHelper();
                $committee->setGuid($guidHelper->getGUIDUsingMD5($loggedInUser->getUsername()));

                $committeeService = $this->get('app.committee_service');
                try {
                    $outcome = $committeeService->addCommittee($committee);
                    if ($outcome) {
                        $alertNotification->addSuccess('Committee created successfully');
                        $committee = new Committee();

                        $this->get('app.shared_data_manager')->removeCacheItem(AppConstants::KEY_CACHED_COMMITTEES);
                    }
                } catch (AppException $app_exc) {
                    switch ($app_exc->getMessage()) {
                        case AppConstants::DUPLICATE_CODE:
                            $errorMessage = "Code is already in use.";
                            break;

                        case AppConstants::DUPLICATE_DESC:
                            $errorMessage = "Description is already in use.";
                            break;

                        case AppConstants::DUPLICATE_GUID:
                            $errorMessage = "Duplicate identifier, Please try again.";
                            break;

                        default:
                            $errorMessage = "An error occured, Try again.";
                            break;
                    }
                    $alertNotification->addError($errorMessage);
                } catch (Exception $e) {

                }
            }

        }

        $sharedDataManager = $this->get('app.shared_data_manager');

        $fccCommissioners = $sharedDataManager->getFCCUserByRole(AppConstants::ROLE_FCC_COMMISSIONER);
        $fccCommitteeSecretaries = $sharedDataManager->getFCCUserByRole(AppConstants::ROLE_FCC_COMMITTEE_SECRETARY);
        $fccDeskOfficers = $sharedDataManager->getFCCUserByRole(AppConstants::ROLE_FCC_DESK_OFFICER_FEDERAL);
        $organizations = $sharedDataManager->getOrganizations();

        return $this->render('super_admin/organization/committee_new.html.twig',
            array(
                'committee' => $committee,
                'fccCommissioners' => $fccCommissioners,
                'fccCommitteeSecretaries' => $fccCommitteeSecretaries,
                'fccDeskOfficers' => $fccDeskOfficers,
                'organizations' => $organizations,
                'validator' => $validator,
                'outcome' => $outcome,
                'alertNotification' => $alertNotification
            )
        );

    }

    /**
     * @Route("/super_admin/committee/{guid}/edit",name="committee_edit")
     */
    public function editAction($guid, Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();

        $committee = new Committee();

        $validator = new Validator();
        $this->initializeValidationFields($validator, AppConstants::NEW);

        $alertNotification =  new AlertNotification();
        $outcome = false;

        $committeeService = $this->get('app.committee_service');

        if (!$request->request->has("btnSubmit")) {//first page load

            try {
                $committee = $committeeService->getCommittee($guid);
            } catch (AppException $app_ex) {
                $this->get('logger')->info($app_ex->getMessage());
            }

            if (!$committee) {
                $committee = new Committee();
                $alertNotification->addError('Committee could not be loaded, go back and try again.');
            }

        } else if ($request->request->has("btnSubmit")) {

            $committee = $this->fillModelFromRequest($request);
            $this->validateForm($validator, $committee, AppConstants::NEW);

            if (!$validator->getFields()->hasErrors()) {

                $today = date("Y-m-d H:i:s");
                $committee->setLastModified($today);
                $committee->setLastModifiedByUserId($loggedInUser->getId());

                try {
                    $outcome = $committeeService->editCommittee($committee);
                    if ($outcome) {
                        $alertNotification->addSuccess('Committee updated successfully');
                        $this->get('app.shared_data_manager')->removeCacheItem(AppConstants::KEY_CACHED_COMMITTEES);
                    }
                } catch (AppException $app_exc) {
                    switch ($app_exc->getMessage()) {
                        case AppConstants::DUPLICATE_CODE:
                            $errorMessage = "Code is already in use.";
                            break;

                        case AppConstants::DUPLICATE_DESC:
                            $errorMessage = "Description is already in use.";
                            break;

                        default:
                            $errorMessage = "An error occured, Try again.";
                            break;
                    }
                    $alertNotification->addError($errorMessage);
                } catch (Exception $e) {

                }
            }

        }

        $sharedDataManager = $this->get('app.shared_data_manager');
        $fccCommissioners = $sharedDataManager->getFCCUserByRole(AppConstants::ROLE_FCC_COMMISSIONER);
        $fccCommitteeSecretaries = $sharedDataManager->getFCCUserByRole(AppConstants::ROLE_FCC_COMMITTEE_SECRETARY);
        $fccDeskOfficers = $sharedDataManager->getFCCUserByRole(AppConstants::ROLE_FCC_DESK_OFFICER_FEDERAL);
        $organizations = $sharedDataManager->getOrganizations();

        return $this->render('super_admin/organization/committee_edit.html.twig',
            array(
                'committee' => $committee,
                'fccCommissioners' => $fccCommissioners,
                'fccCommitteeSecretaries' => $fccCommitteeSecretaries,
                'fccDeskOfficers' => $fccDeskOfficers,
                'organizations' => $organizations,
                'validator' => $validator,
                'outcome' => $outcome,
                'alertNotification' => $alertNotification
            )
        );
    }

    /**
     * @Route("/super_admin/committee/{guid}/delete",name="committee_delete")
     */
    public function deleteAction($guid, Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();

        $committee = new Committee();

        $validator = new Validator();
        $this->initializeValidationFields($validator, AppConstants::DELETE);

        $alertNotification = new AlertNotification();
        $outcome = false;

        $committeeService = $this->get('app.committee_service');

        try {
            $committee = $committeeService->getCommittee($guid);
        } catch (AppException $app_ex) {

        }

        if (!$committee) {
            $committee = new Committee();
            $alertNotification->addError('Committee could not be loaded, go back and try again.');
        }

        if ($request->request->has("btnSubmit")) {

            $committee = $this->fillModelFromRequest($request);
            $this->validateForm($validator, $committee, AppConstants::DELETE);

            if (!$validator->getFields()->hasErrors()) {

                $committee->setStatus(AppConstants::INACTIVE);

                $today = date("Y-m-d H:i:s");
                $committee->setLastModified($today);
                $committee->setLastModifiedByUserId($loggedInUser->getId());

                try {
                    $outcome = $committeeService->deleteCommittee($committee);
                    if ($outcome) {
                        //$notificationMessage = "Committee updated successfully";
                        //$this->addFlash('success', 'committee.deleted_successfully');
                        $this->get('app.shared_data_manager')->removeCacheItem(AppConstants::KEY_CACHED_COMMITTEES);
                        return $this->redirectToRoute('committee_list');

                    }
                } catch (AppException $app_exc) {
                    $errorMessage = "An error occured, Try again.";
                    $alertNotification->addError($errorMessage);
                } catch (Exception $e) {

                }
            }

        }

        $sharedDataManager = $this->get('app.shared_data_manager');
        $fccCommissioners = $sharedDataManager->getFCCUserByRole(AppConstants::ROLE_FCC_COMMISSIONER);
        $fccCommitteeSecretaries = $sharedDataManager->getFCCUserByRole(AppConstants::ROLE_FCC_COMMITTEE_SECRETARY);
        $fccDeskOfficers = $sharedDataManager->getFCCUserByRole(AppConstants::ROLE_FCC_DESK_OFFICER_FEDERAL);
        $organizations = $sharedDataManager->getOrganizations();

        return $this->render('super_admin/organization/committee_delete.html.twig',
            array(
                'committee' => $committee,
                'fccCommissioners' => $fccCommissioners,
                'fccCommitteeSecretaries' => $fccCommitteeSecretaries,
                'fccDeskOfficers' => $fccDeskOfficers,
                'organizations' => $organizations,
                'validator' => $validator,
                'outcome' => $outcome,
                'alertNotification' => $alertNotification
            )
        );
    }

    //helper methods
    private function initializeValidationFields(Validator $validator, string $which)
    {
        $validator->getFields()->addField('committee_name', "Committee name is required");
        $validator->getFields()->addField('committee_chairman', "Chairman is required");
        $validator->getFields()->addField('committee_secretary', "Secretary is required");
        $validator->getFields()->addField('committee_members', "Committee Members are required");
        $validator->getFields()->addField('committee_mdas', "Committee MDAs are required");

        switch ($which) {
            case AppConstants::EDIT:
                $validator->getFields()->addField('guid', "Invalid record identifier");
                break;

            case AppConstants::DELETE:
                $validator->getFields()->addField('guid', "Invalid record identifier");
                break;
        }
    }

    private function validateForm(Validator $validator, Committee $committee, string $which)
    {
        $validator->textRequiredMax('committee_name', $committee->getName());

        if($which == AppConstants::NEW || $which == AppConstants::EDIT){
            $validator->required('committee_chairman', $committee->getChairmanUserId());
            $validator->required('committee_secretary', $committee->getSecretaryUserId());
            $validator->required('committee_members', implode('', $committee->getCommitteeMemberIds()));
            $validator->required('committee_mdas', implode('', $committee->getCommitteeMdaIds()));
        }

        switch ($which) {
            case AppConstants::EDIT:
                $validator->required('guid', $committee->getGuid());
                break;

            case AppConstants::DELETE:
                $validator->required('guid', $committee->getGuid());
                break;
        }
    }

    private function fillModelFromRequest(Request $request)
    {
        $committee = new Committee();
        $committee->setName($request->request->get("committee_name"));
        $committee->setChairmanUserId($request->request->get("committee_chairman"));
        $committee->setSecretaryUserId($request->request->get("committee_secretary"));
        $committee->setCommitteeMemberIds($request->request->get("committee_members", array()));
        $committee->setCommitteeMdaIds($request->request->get("committee_mdas", array()));
        $committee->setGuid($request->request->get("guid", ""));

        return $committee;
    }

}