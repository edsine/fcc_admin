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
use AppBundle\Model\Organizations\Organization;
use AppBundle\Model\SearchCriteria\OrganizationSearchCriteria;
use AppBundle\Model\Security\SecurityUser;
use AppBundle\Model\Users\UserProfile;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\GUIDHelper;
use AppBundle\Utils\Paginator;
use AppBundle\Validation\Validator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;

class FederalMinistryController extends Controller
{

    /**
     * @Route("/super_admin/federal_ministry/list", name="federal_ministry_list")
     */
    public function listAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $searchFilter = new OrganizationSearchCriteria(AppConstants::FEDERAL_MINISTRY_ESTABLISHMENT);
        $searchFilter->setOrganizationName($request->request->get('searchName'));
        $searchFilter->setEstablishmentCode($request->request->get('searchCode'));
        $searchFilter->setEstablishmentMnemonic($request->request->get('searchMnemonic'));
        $searchFilter->setFccCommittee($request->request->get('searchCommittee'));
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
        $organizationService = $this->get('app.organization_service');
        try {
            $records = $organizationService->searchRecords($searchFilter, $paginator, $pageDirection);
        } catch (AppException $app_exc) {

        }

        return $this->render('super_admin/organization/federal_ministry_list.html.twig',
            array('records' => $records,
                'searchFilter' => $searchFilter,
                'paginator' => $paginator
            )
        );
    }

    /**
     * @Route("/super_admin/federal_ministry/new",name="federal_ministry_new")
     */
    public function newAction(Request $request)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var UserProfile $loggedInUser
         */
        $loggedInUser = $this->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        $organization = new Organization();

        $validator = new Validator();
        $this->initializeValidationFields($validator, AppConstants::NEW);

        $alertNotification = new AlertNotification();
        $outcome = false;

        //check if the form was submitted and process else render empty form
        if ($request->request->has("btnSubmit")) {

            $organization = $this->fillModelFromRequest($request);
            $this->validateForm($validator, $organization, AppConstants::NEW);

            if (!$validator->getFields()->hasErrors()) {

                $organization->setStatus(AppConstants::ACTIVE);
                $organization->setLevelOfGovernment(AppConstants::FEDERAL);
                $organization->setEstablishmentTypeId(AppConstants::FEDERAL_MINISTRY_ESTABLISHMENT);

                $today = date("Y-m-d H:i:s");
                $organization->setLastModified($today);
                $organization->setLastModifiedByUserId($loggedInUser->getId());

                $guidHelper = new GUIDHelper();
                $organization->setGuid($guidHelper->getGUIDUsingMD5($loggedInUser->getUsername()));

                //SETUP THE MDA ADMIN
                $userProfile = new UserProfile();
                $userProfile->setProfileType(AppConstants::MDA_USER_PROFILE);
                $userProfile->setUsername($organization->getEstablishmentCode());
                $userProfile->setPlainPassword($userProfile->getUsername());
                $userProfile->setPrimaryRole(AppConstants::ROLE_MDA_ADMIN);
                $userProfile->setStatus(AppConstants::ACTIVE);
                $userProfile->setFirstLogin(AppConstants::N);
                $userProfile->setLastModified($today);
                $userProfile->setLastModifiedByUserId($loggedInUser->getId());
                $userProfile->setGuid($guidHelper->getGUIDUsingMD5($userProfile->getUsername()));

                $encoder = $this->get('security.password_encoder');
                $encryptedPassword = $encoder->encodePassword($userProfile, $userProfile->getPlainPassword());
                $userProfile->setPassword($encryptedPassword);
                //END SETUP MDA ADMIN

                //no insert record
                $organizationService = $this->get('app.organization_service');
                try {
                    $outcome = $organizationService->addOrganization($organization, $userProfile);
                    if ($outcome) {
                        $alertNotification->addSuccess('Organization created successfully');
                        $organization = new Organization();

                        $this->get('app.shared_data_manager')->removeCacheItem(AppConstants::KEY_CACHED_ORGANIZATIONS);
                    }
                } catch (AppException $app_exc) {
                    switch ($app_exc->getMessage()) {
                        case AppConstants::DUPLICATE_CODE:
                            $errorMessage = "Establishment code is already in use.";
                            break;

                        case AppConstants::DUPLICATE_DESC:
                            $errorMessage = "Organization name is already in use.";
                            break;

                        case AppConstants::DUPLICATE_EMAIL:
                            $errorMessage = "Email is already in use.";
                            break;

                        case AppConstants::DUPLICATE_PRIMARY_PHONE:
                            $errorMessage = "Primary phone number is already in use.";
                            break;

                        case AppConstants::DUPLICATE_SECONDARY_PHONE:
                            $errorMessage = "Secondary phone number is already in use.";
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
        $organizationCategoryTypes = $sharedDataManager->getOrganizationCategoryTypes();

        $this->get('logger')->info( "FCC USERS: " . print_r($sharedDataManager->getFCCUsers(), true));

        $fccDeskOfficers = $sharedDataManager->getFCCUserByPrivilege(AppConstants::PRIV_CONFIRM_FED_MDA_NOMINAL_ROLL_UPLOAD);


        return $this->render('super_admin/organization/federal_ministry_new.html.twig',
            array(
                'organization' => $organization,
                'validator' => $validator,
                'outcome' => $outcome,
                'alertNotification' => $alertNotification,
                'fccDeskOfficers' => $fccDeskOfficers,
                'organizationCategoryTypes' => $organizationCategoryTypes
            )
        );

    }

    /**
     * @Route("/super_admin/federal_ministry/{guid}/edit",name="federal_ministry_edit")
     */
    public function editAction(Request $request, $guid)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        $organization = new Organization();

        $validator = new Validator();
        $this->initializeValidationFields($validator, AppConstants::EDIT);

        $alertNotification =  new AlertNotification();
        $outcome = false;

        $organizationService = $this->get('app.organization_service');

        if (!$request->request->has("btnSubmit")) {//first page load

            try {
                $organization = $organizationService->getOrganization($guid);
            } catch (AppException $app_ex) {
                $this->get('logger')->error('EXCE HERERERER: ' . $app_ex->getMessage());
            }

            if (!$organization) {
                $organization = new Organization();
                $alertNotification->addError('Organization could not be loaded, go back and try again.');
            }

        } else if ($request->request->has("btnSubmit")) {

            $organization = $this->fillModelFromRequest($request);
            $this->validateForm($validator, $organization, AppConstants::EDIT);

            if (!$validator->getFields()->hasErrors()) {

                $today = date("Y-m-d H:i:s");
                $organization->setLastModified($today);
                $organization->setLastModifiedByUserId($loggedInUser->getId());

                try {
                    $outcome = $organizationService->editOrganization($organization);
                    if ($outcome) {
                        $alertNotification->addSuccess('Organization updated successfully');
                        $this->get('app.shared_data_manager')->removeCacheItem(AppConstants::KEY_CACHED_ORGANIZATIONS);
                    }
                } catch (AppException $app_exc) {
                    $this->get('logger')->error($app_exc->getMessage());
                    switch ($app_exc->getMessage()) {
                        case AppConstants::DUPLICATE_CODE:
                            $errorMessage = "Establishment code is already in use.";
                            break;

                        case AppConstants::DUPLICATE_DESC:
                            $errorMessage = "Organization name is already in use.";
                            break;

                        case AppConstants::DUPLICATE_EMAIL:
                            $errorMessage = "Email is already in use.";
                            break;

                        case AppConstants::DUPLICATE_PRIMARY_PHONE:
                            $errorMessage = "Primary phone number is already in use.";
                            break;

                        case AppConstants::DUPLICATE_SECONDARY_PHONE:
                            $errorMessage = "Secondary phone number is already in use.";
                            break;

                        default:
                            $errorMessage = "An error occured, Try again.";
                            break;
                    }
                    $alertNotification->addError($errorMessage);
                }
            }

        }

        $sharedDataManager = $this->get('app.shared_data_manager');
        //$fccCommittees = $sharedDataManager->getCommittees();
        $organizationCategoryTypes = $sharedDataManager->getOrganizationCategoryTypes();
        $fccDeskOfficers = $sharedDataManager->getFCCUserByPrivilege(AppConstants::PRIV_CONFIRM_FED_MDA_NOMINAL_ROLL_UPLOAD);

        return $this->render('super_admin/organization/federal_ministry_edit.html.twig',
            array(
                'organization' => $organization,
                'validator' => $validator,
                'outcome' => $outcome,
                'alertNotification' => $alertNotification,
                'fccDeskOfficers' => $fccDeskOfficers,
                'organizationCategoryTypes' => $organizationCategoryTypes
            )
        );
    }

    /**
     * @Route("/super_admin/federal_ministry/{guid}/delete",name="federal_ministry_delete")
     */
    public function deleteAction($guid, Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        $organization = new Organization();

        $validator = new Validator();
        $this->initializeValidationFields($validator, AppConstants::DELETE);

        $alertNotification = new AlertNotification();
        $outcome = false;

        $organizationService = $this->get('app.organization_service');

        if (!$request->request->has("btnSubmit")) {//first page load

            try {
                $organization = $organizationService->getOrganization($guid);
            } catch (AppException $app_ex) {
            }

            if (!$organization) {
                $organization = new Organization();
                $alertNotification->addError('Organization could not be loaded, go back and try again.');
            }

        } else if ($request->request->has("btnSubmit")) {

            $organization = $this->fillModelFromRequest($request);
            $this->validateForm($validator, $organization, AppConstants::DELETE);

            if (!$validator->getFields()->hasErrors()) {

                $organization->setStatus(AppConstants::INACTIVE);

                $today = date("Y-m-d H:i:s");
                $organization->setLastModified($today);
                $organization->setLastModifiedByUserId($loggedInUser->getId());

                try {
                    $outcome = $organizationService->deleteOrganization($organization);
                    if ($outcome) {
                        //$notificationMessage = "Committee updated successfully";
                        //$this->addFlash('success', 'committee.deleted_successfully');
                        $this->get('app.shared_data_manager')->removeCacheItem(AppConstants::KEY_CACHED_ORGANIZATIONS);
                        return $this->redirectToRoute('federal_ministry_list');

                    }
                } catch (AppException $app_exc) {
                    $errorMessage = "An error occured, Try again.";
                    $alertNotification->addError($errorMessage);
                } catch (Exception $e) {

                }
            }

        }

        $sharedDataManager = $this->get('app.shared_data_manager');
        //$fccCommittees = $sharedDataManager->getCommittees();
        $organizationCategoryTypes = $sharedDataManager->getOrganizationCategoryTypes();
        $fccDeskOfficers = $sharedDataManager->getFCCUserByPrivilege(AppConstants::PRIV_CONFIRM_FED_MDA_NOMINAL_ROLL_UPLOAD);

        return $this->render('super_admin/organization/federal_ministry_delete.html.twig',
            array(
                'organization' => $organization,
                'validator' => $validator,
                'outcome' => $outcome,
                'alertNotification' => $alertNotification,
                'fccDeskOfficers' => $fccDeskOfficers,
                'organizationCategoryTypes' => $organizationCategoryTypes
            )
        );
    }

    //helper methods
    private function initializeValidationFields(Validator $validator, string $which)
    {
        $validator->getFields()->addField('organization_name', "Organization name is required");
        $validator->getFields()->addField('establishment_code', "Establishment code is required");
        $validator->getFields()->addField('establishment_mnemonic', "Organization Mnemonic is required");
        $validator->getFields()->addField('establishment_categories', "Organization category is required");
        //$validator->getFields()->addField('establishment_type', "Type od establishment is required");
        //$validator->getFields()->addField('state_owned_establishment_state', "State owner is required");
        //$validator->getFields()->addField('state_of_location', "State of location is required");
        //$validator->getFields()->addField('contact_address', "Contact address is required");
        //$validator->getFields()->addField('website_address', "Invaalid website address");
        //$validator->getFields()->addField('email_address', "Invalid email address");
        //$validator->getFields()->addField('primary_phone', "Primary Phone number is required");
        //$validator->getFields()->addField('parent_organization', "Parent organization is required");
        //$validator->getFields()->addField('fcc_committee', "Supervising Committee is required");
        //$validator->getFields()->addField('fcc_desk_officer', "FCC Desk officer is required");

        switch ($which) {
            case AppConstants::NEW:
                break;

            case AppConstants::EDIT:
                $validator->getFields()->addField('guid', "Invalid record identifier");
                break;

            case AppConstants::DELETE:
                $validator->getFields()->addField('guid', "Invalid record identifier");
                break;
        }
    }

    private function validateForm(Validator $validator, Organization $organization, string $which)
    {
        $validator->textRequiredMax('establishment_code', $organization->getEstablishmentCode(), true, 1, 15);
        $validator->textRequiredMax('establishment_mnemonic', $organization->getEstablishmentMnemonic(), true, 1, 10);
        $validator->textRequiredMax('organization_name', $organization->getOrganizationName());
        //$validator->required('establishment_categories', $organization->getEstablishmentCategoryIds());
        /*$validator->textRequired('establishment_type', $organization->getEstablishmentTypeId());
        $validator->textRequired('state_owned_establishment_state', $organization->getStateOwnedEstablishmentStateId());
        $validator->textRequired('state_of_location', $organization->getStateOfLocationId());
        $validator->textRequired('contact_address', $organization->getContactAddress());
        $validator->textRequired('website_address', $organization->getWebsiteAddress());
        $validator->textRequired('email_address', $organization->getEmailAddress());
        $validator->textRequired('primary_phone', $organization->getPrimaryPhone());*/
        //$validator->textRequired('parent_organization', $organization->getParentOrganizationId());
        //$validator->required('fcc_committee', $organization->getFccCommitteeId());
        //$validator->required('fcc_desk_officer', $organization->getFccDeskOfficerId());

        switch ($which) {
            case AppConstants::NEW:
                break;

            case AppConstants::EDIT:
                $validator->required('guid', $organization->getGuid());
                break;

            case AppConstants::DELETE:
                $validator->required('guid', $organization->getGuid());
                break;
        }
    }

    private function fillModelFromRequest(Request $request)
    {
        $organization = new Organization();
        $organization->setEstablishmentCode(strtoupper($request->request->get("establishment_code")));
        $organization->setEstablishmentMnemonic(strtoupper($request->request->get("establishment_mnemonic")));
        $organization->setOrganizationName(strtoupper($request->request->get("organization_name")));
        $organization->setEstablishmentTypeId($request->request->get("establishment_type"));
        $organization->setEstablishmentCategoryIds($request->request->get("establishment_categories", array()));
        $organization->setStateOwnedEstablishmentStateId($request->request->get("state_owned_establishment_state"));
        $organization->setStateOfLocationId($request->request->get("state_of_location"));
        $organization->setContactAddress($request->request->get("contact_address"));
        $organization->setWebsiteAddress($request->request->get("website_address"));
        $organization->setEmailAddress($request->request->get("email_address"));
        $organization->setPrimaryPhone($request->request->get("primary_phone"));
        $organization->setParentOrganizationId($request->request->get("parent_organization"));
        //$organization->setFccCommitteeId($request->request->get("fcc_committee"));
        $organization->setFccDeskOfficerId($request->request->get("fcc_desk_officer"));
        $organization->setGuid($request->request->get("guid"));

        return $organization;
    }

}