<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 8:47 PM
 */

namespace AppBundle\Controller\SuperAdmin\Users;


use AppBundle\AppException\AppException;
use AppBundle\Model\Notification\AlertNotification;
use AppBundle\Model\SearchCriteria\UserProfileSearchCriteria;
use AppBundle\Model\Security\SecurityUser;
use AppBundle\Model\Users\UserProfile;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\GUIDHelper;
use AppBundle\Utils\Paginator;
use AppBundle\Validation\Validator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FCCUserController extends Controller
{

    /**
     * @Route("/super_admin/fcc_user/list", name="fcc_user_list")
     */
    public function listAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $searchFilter = new UserProfileSearchCriteria(AppConstants::FCC_USER_PROFILE);
        $searchFilter->setUsername($request->request->get('searchUsername'));
        $searchFilter->setFirstName($request->request->get('searchFirstName'));
        $searchFilter->setLastName($request->request->get('searchLastName'));
        $searchFilter->setEmailAddress($request->request->get('searchEmailAddress'));
        $searchFilter->setPhoneNumber($request->request->get('searchPhoneNumber'));
        $searchFilter->setPrimaryRole($request->request->get('searchPrimaryRole'));
        $searchFilter->setFccLocation($request->request->get('searchFccLocation'));
        $searchFilter->setFccDepartment($request->request->get('searchFccDepartment'));
        $searchFilter->setFccCommittee($request->request->get('searchFccCommittee'));
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
        $userProfileService = $this->get('app.user_profile_service');
        try {
            $records = $userProfileService->searchRecords($searchFilter, $paginator, $pageDirection);
        } catch (AppException $app_exc) {
            $this->get('logger')->alert($app_exc->getMessage());
        }

        return $this->render('super_admin/fcc_users/fcc_user_list.html.twig',
            array('records' => $records,
                'searchFilter' => $searchFilter,
                'paginator' => $paginator
            )
        );
    }

    /**
     * @Route("/super_admin/fcc_user/new",name="fcc_user_new")
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

        $userProfile = new UserProfile();

        $validator = new Validator();
        $this->initializeValidationFields($validator, AppConstants::NEW);

        $alertNotification = new AlertNotification();
        $outcome = false;

        $sharedDataManager = $this->get('app.shared_data_manager');

        //check if the form was submitted and process else render empty form
        if ($request->request->has("btnSubmit")) {

            $userProfile = $this->fillModelFromRequest($request);
            $this->validateForm($validator, $userProfile, AppConstants::NEW);

            if (!$validator->getFields()->hasErrors()) {

                //$userProfile->setOrganizationId($loggedInUser->getOrganizationId());

                $userProfile->setStatus(AppConstants::ACTIVE);
                $userProfile->setProfileType(AppConstants::FCC_USER_PROFILE);

                $today = date("Y-m-d H:i:s");
                $userProfile->setLastModified($today);
                $userProfile->setLastModifiedByUserId($loggedInUser->getId());

                $guidHelper = new GUIDHelper();
                $userProfile->setGuid($guidHelper->getGUIDUsingMD5($loggedInUser->getUsername()));

                $userProfile->setFirstLogin('N');

                $encoder = $this->get('security.password_encoder');
                //$encoded = $encoder->encodePassword($user, $plainPassword);
                $encryptedPassword = $encoder->encodePassword($userProfile, $userProfile->getPlainPassword());

                $userProfile->setPassword($encryptedPassword);

                $clientOrganization = $sharedDataManager->getClientOrganization();
                $userProfile->setOrganizationId($clientOrganization->getId());

                $userProfileService = $this->get('app.user_profile_service');
                try {
                    $outcome = $userProfileService->addUserProfile($userProfile);
                    if ($outcome) {
                        $alertNotification->addSuccess('User profile created successfully');
                        $userProfile = new UserProfile();

                        $this->get('app.shared_data_manager')->removeCacheItem(AppConstants::KEY_CACHED_FCC_USERS);
                    }
                } catch (AppException $app_exc) {

                    $this->get('logger')->info($app_exc->getMessage());

                    switch ($app_exc->getMessage()) {
                        case AppConstants::DUPLICATE_USERNAME:
                            $errorMessage = "Username is already in use.";
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

                        case AppConstants::DUPLICATE_FCC_DESK_OFFICER_COMMITTEE:
                            $errorMessage = "Committee is already in use";
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

        $fccDepartments = $sharedDataManager->getDepartments();
        $fccRoles = $sharedDataManager->getFCCRoles();
        $fccCommittees = $sharedDataManager->getCommittees();
        $fccSupervisors = $sharedDataManager->getFCCUserByPrivilege('SUPERVISOR');
        $nigerianStates = $sharedDataManager->getNigerianStates();

        return $this->render('super_admin/fcc_users/fcc_user_new.html.twig',
            array(
                'userProfile' => $userProfile,
                'validator' => $validator,
                'outcome' => $outcome,
                'alertNotification' => $alertNotification,
                'fccDepartments' => $fccDepartments,
                'fccRoles' => $fccRoles,
                'fccCommittees' => $fccCommittees,
                'fccSupervisors' => $fccSupervisors,
                'nigerianStates' => $nigerianStates
            )
        );

    }

    /**
     * @Route("/super_admin/fcc_user/{guid}/edit",name="fcc_user_edit")
     */
    public function editAction($guid, Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$loggedInUser) {
            $loggedInUser = new UserProfile();
        }

        $userProfile = new UserProfile();

        $validator = new Validator();
        $this->initializeValidationFields($validator, AppConstants::EDIT);

        $alertNotification = new AlertNotification();
        $outcome = false;

        $userProfileService = $this->get('app.user_profile_service');

        if (!$request->request->has("btnSubmit")) {//first page load

            try {
                $userProfile = $userProfileService->getUserProfile($guid);
            } catch (AppException $app_ex) {

            }

            if (!$userProfile) {
                $userProfile = new UserProfile();
                $alertNotification->addError('User profile could not be loaded, go back and try again.');
            }

        } else if ($request->request->has("btnSubmit")) {

            $userProfile = $this->fillModelFromRequest($request);
            $this->validateForm($validator, $userProfile, AppConstants::EDIT);

            if (!$validator->getFields()->hasErrors()) {

                $today = date("Y-m-d H:i:s");
                $userProfile->setLastModified($today);
                $userProfile->setLastModifiedByUserId($loggedInUser->getId());

                try {
                    $outcome = $userProfileService->editUserProfile($userProfile);
                    if ($outcome) {
                        $alertNotification->addSuccess('User profile updated successfully');
                        $this->get('app.shared_data_manager')->removeCacheItem(AppConstants::KEY_CACHED_FCC_USERS);
                    }
                } catch (AppException $app_exc) {
                    switch ($app_exc->getMessage()) {
                        case AppConstants::DUPLICATE_USERNAME:
                            $errorMessage = "Username is already in use.";
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

                        case AppConstants::DUPLICATE_FCC_DESK_OFFICER_COMMITTEE:
                            $errorMessage = "Committee is already in use";
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
        $departments = $sharedDataManager->getDepartments();
        $fccRoles = $sharedDataManager->getFCCRoles();
        $fccCommittees = $sharedDataManager->getCommittees();
        $fccSupervisors = $sharedDataManager->getFCCUserByPrivilege('SUPERVISOR');
        $nigerianStates = $sharedDataManager->getNigerianStates();

        return $this->render('super_admin/fcc_users/fcc_user_edit.html.twig',
            array(
                'userProfile' => $userProfile,
                'validator' => $validator,
                'outcome' => $outcome,
                'alertNotification' => $alertNotification,
                'departments' => $departments,
                'fccRoles' => $fccRoles,
                'fccCommittees' => $fccCommittees,
                'fccSupervisors' => $fccSupervisors,
                'nigerianStates' => $nigerianStates
            )
        );
    }

    /**
     * @Route("/super_admin/fcc_user/{guid}/delete",name="fcc_user_delete")
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

        $userProfile = new UserProfile();

        $validator = new Validator();
        $this->initializeValidationFields($validator, AppConstants::DELETE);

        $alertNotification = new AlertNotification();
        $outcome = false;

        $userProfileService = $this->get('app.user_profile_service');

        if (!$request->request->has("btnSubmit")) {//first page load

            try {
                $userProfile = $userProfileService->getUserProfile($guid);
            } catch (AppException $app_ex) {
            }

            if (!$userProfile) {
                $userProfile = new UserProfile();
                $alertNotification->addError('User profile could not be loaded, go back and try again.');
            }

        } else if ($request->request->has("btnSubmit")) {

            $userProfile = $this->fillModelFromRequest($request);
            $this->validateForm($validator, $userProfile, AppConstants::DELETE);

            if (!$validator->getFields()->hasErrors()) {

                $userProfile->setStatus(AppConstants::INACTIVE);

                $today = date("Y-m-d H:i:s");
                $userProfile->setLastModified($today);
                $userProfile->setLastModifiedByUserId($loggedInUser->getId());

                try {
                    $outcome = $userProfileService->deleteUserProfile($userProfile);
                    if ($outcome) {
                        //$notificationMessage = "Committee updated successfully";
                        //$this->addFlash('success', 'committee.deleted_successfully');
                        $this->get('app.shared_data_manager')->removeCacheItem(AppConstants::KEY_CACHED_FCC_USERS);
                        return $this->redirectToRoute('fcc_user_list');

                    }
                } catch (AppException $app_exc) {
                    $errorMessage = "An error occured, Try again.";
                    $alertNotification->addError($errorMessage);

                    $this->get('logger')->error($app_exc->getMessage());
                } catch (Exception $e) {

                }
            }

        }

        return $this->render('super_admin/fcc_users/fcc_user_delete.html.twig',
            array(
                'userProfile' => $userProfile,
                'validator' => $validator,
                'outcome' => $outcome,
                'alertNotification' => $alertNotification
            )
        );
    }

    //helper methods
    private function initializeValidationFields(Validator $validator, string $which)
    {
        $validator->getFields()->addField('username', "Username is required");
        $validator->getFields()->addField('first_name', "First Name is required");
        $validator->getFields()->addField('last_name', "Last Name is required");
        $validator->getFields()->addField('middle_name', "Middle Name is required");
        $validator->getFields()->addField('email', "Invalid Email address");
        $validator->getFields()->addField('primary_phone', "Invalid primary phone number");
        $validator->getFields()->addField('secondary_phone', "Invalid secondary phone");
        $validator->getFields()->addField('state_of_posting', "State of posting is required");
        $validator->getFields()->addField('fcc_department', "Department is required");
        $validator->getFields()->addField('primary_role', "Primary role is required");
        $validator->getFields()->addField('fcc_committee', "Committee is required");
        $validator->getFields()->addField('fcc_supervisor', "Supervisor is required");

        switch ($which) {
            case AppConstants::NEW:
                $validator->getFields()->addField('plain_pass', "Password is required");
                break;

            case AppConstants::EDIT:
                $validator->getFields()->addField('guid', "Invalid record identifier");
                break;

            case AppConstants::DELETE:
                $validator->getFields()->addField('guid', "Invalid record identifier");
                break;
        }
    }

    private function validateForm(Validator $validator, UserProfile $userProfile, string $which)
    {
        if($which != AppConstants::DELETE){
            $validator->textRequiredMax('username', $userProfile->getUsername(),true, 2, 60);
            $validator->textRequiredMax('first_name', $userProfile->getFirstName(),true, 3, 60);
            $validator->textRequiredMax('last_name', $userProfile->getLastName(),true, 3, 60);
            //$validator->textRequired('middle_name', $userProfile->getMiddleName());
            $validator->email('email', $userProfile->getEmailAddress());
            $validator->phone('primary_phone', $userProfile->getPrimaryPhone());
            //$validator->textRequired('secondary_phone', $userProfile->getSecondaryPhone());
            $validator->required('state_of_posting', $userProfile->getStateOfPostingId());
            $validator->required('fcc_department', $userProfile->getFccDepartmentId());
            $validator->required('primary_role', $userProfile->getPrimaryRole());

            if($userProfile->getPrimaryRole() == AppConstants::ROLE_FCC_DESK_OFFICER_FEDERAL){
                $validator->required('fcc_committee', $userProfile->getFccCommitteeId());
            }

            /*switch ($userProfile->getPrimaryRole()) {
                case AppConstants::ROLE_FCC_DESK_OFFICER_FEDERAL:
                    $validator->textRequired('fcc_supervisor', $userProfile->getFccSupervisorId());
                    break;

                case AppConstants::ROLE_FCC_COMMITTEE_SECRETARY:
                    $validator->textRequired('fcc_committee', $userProfile->getFccCommitteeId());
                    break;
            }*/
        }

        switch ($which) {
            case AppConstants::NEW:
                $validator->required('plain_pass', $userProfile->getPlainPassword());
                break;

            case AppConstants::EDIT:
                $validator->required('guid', $userProfile->getGuid());
                break;

            case AppConstants::DELETE:
                $validator->required('guid', $userProfile->getGuid());
                break;
        }
    }

    private function fillModelFromRequest(Request $request)
    {
        $userProfile = new UserProfile();
        $userProfile->setUsername($request->request->get("username"));
        $userProfile->setPlainPassword($request->request->get("plain_pass"));
        $userProfile->setFirstName($request->request->get("first_name"));
        $userProfile->setLastName($request->request->get("last_name"));
        $userProfile->setMiddleName($request->request->get("middle_name"));
        $userProfile->setEmailAddress($request->request->get("email"));
        $userProfile->setPrimaryPhone($request->request->get("primary_phone"));
        $userProfile->setSecondaryPhone($request->request->get("secondary_phone"));
        $userProfile->setStateOfPostingId($request->request->get("state_of_posting"));
        $userProfile->setFccDepartmentId($request->request->get("fcc_department"));
        $userProfile->setPrimaryRole($request->request->get("primary_role"));
        $userProfile->setFccCommitteeId($request->request->get("fcc_committee"));
        $userProfile->setFccSupervisorId($request->request->get("fcc_supervisor"));
        $userProfile->setGuid($request->request->get("guid"));

        return $userProfile;
    }

}