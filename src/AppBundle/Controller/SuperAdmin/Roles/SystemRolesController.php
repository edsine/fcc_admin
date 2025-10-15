<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 8:47 PM
 */

namespace AppBundle\Controller\SuperAdmin\Roles;


use AppBundle\AppException\AppException;
use AppBundle\Model\Notification\AlertNotification;
use AppBundle\Model\SearchCriteria\SystemRoleSearchCriteria;
use AppBundle\Model\Security\SecurityUser;
use AppBundle\Model\Security\SystemRole;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\GUIDHelper;
use AppBundle\Utils\Paginator;
use AppBundle\Validation\Validator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;

class SystemRolesController extends Controller
{

    /**
     * @Route("/super_admin/system_role/list", name="system_role_list")
     */
    public function listAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $searchFilter = new SystemRoleSearchCriteria();
        $searchFilter->setRoleName($request->request->get('search_name'));
        $searchFilter->setCategory($request->request->get('search_category'));
        $searchFilter->setStatus($request->request->get('search_status', AppConstants::ACTIVE));

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
        $systemRoleService = $this->get('app.system_roles_service');
        try {
            $records = $systemRoleService->searchRecords($searchFilter, $paginator, $pageDirection);
        } catch (AppException $e) {
            $this->get('logger')->info($e->getMessage());
        }

        $sharedDataManager = $this->get('app.shared_data_manager');
        $systemRoleCategories = $sharedDataManager->getSystemRoleCategories();

        return $this->render('super_admin/security/system_role_list.html.twig',
            array('records' => $records,
                'searchFilter' => $searchFilter,
                'paginator' => $paginator,
                'systemRoleCategories' => $systemRoleCategories
            )
        );
    }

    /**
     * @Route("/super_admin/system_role/new",name="system_role_new")
     */
    public function newAction(Request $request)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var SecurityUser $loggedInUser
         */

        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        $systemRole = new SystemRole();

        $validator = new Validator();
        $this->initializeValidationFields($validator, AppConstants::NEW);

        $alertNotification = new AlertNotification();
        $outcome = false;

        //check if the form was submitted and process else render empty form
        if ($request->request->has("btn_submit")) {

            $systemRole = $this->fillModelFromRequest($request);
            $this->validateForm($validator, $systemRole, AppConstants::NEW);

            if (!$validator->getFields()->hasErrors()) {

                $systemRole->setRecordStatus(AppConstants::ACTIVE);

                $today = date("Y-m-d H:i:s");
                $systemRole->setLastModified($today);
                $systemRole->setLastModifiedByUserId($loggedInUser->getId());

                $guidHelper = new GUIDHelper();
                $systemRole->setGuid($guidHelper->getGUIDUsingMD5($loggedInUser->getUsername()));

                $systemRoleService = $this->get('app.system_roles_service');
                try {
                    $outcome = $systemRoleService->addSystemRole($systemRole);
                    if ($outcome) {
                        $alertNotification->addSuccess('System Role created successfully');
                        $systemRole = new SystemRole();

                        $this->get('app.shared_data_manager')->removeCacheItem(AppConstants::KEY_CACHED_ROLES);
                    }
                } catch (AppException $app_exc) {
                    switch ($app_exc->getMessage()) {
                        case AppConstants::DUPLICATE_NAME:
                            $errorMessage = "Name is already in use.";
                            break;

                        default:
                            $errorMessage = "An error occured, Try again.";
                            break;
                    }
                    $alertNotification->addError($errorMessage);
                    $this->get('logger')->info($app_exc->getMessage());
                }
            }

        }

        $sharedDataManager = $this->get('app.shared_data_manager');
        $systemPrivileges = $sharedDataManager->getSystemPrivileges();
        $systemRoleCategories = $sharedDataManager->getSystemRoleCategories();

        return $this->render('super_admin/security/system_role_new.html.twig',
            array(
                'systemRole' => $systemRole,
                'validator' => $validator,
                'outcome' => $outcome,
                'alertNotification' => $alertNotification,
                'systemPrivileges' => $systemPrivileges,
                'systemRoleCategories' => $systemRoleCategories
            )
        );

    }

    /**
     * @Route("/super_admin/system_role/{guid}/edit",name="system_role_edit")
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

        $systemRole = new SystemRole();

        $validator = new Validator();
        $this->initializeValidationFields($validator, AppConstants::EDIT);

        $alertNotification = new AlertNotification();
        $outcome = false;

        $systemRoleService = $this->get('app.system_roles_service');

        if (!$request->request->has("btn_submit")) {//first page load

            try {
                $systemRole = $systemRoleService->getSystemRole($guid);
            } catch (AppException $app_ex) {
                $this->get('logger')->info('GET ERROR: ' . $app_ex->getMessage());
            }

            if (!$systemRole) {
                $systemRole = new SystemRole();
                $alertNotification->addError('Role could not be loaded, go back and try again.');
            }

        } else if ($request->request->has("btn_submit")) {

            $systemRole = $this->fillModelFromRequest($request);
            $this->validateForm($validator, $systemRole, AppConstants::EDIT);

            if (!$validator->getFields()->hasErrors()) {

                $today = date("Y-m-d H:i:s");
                $systemRole->setLastModified($today);
                $systemRole->setLastModifiedByUserId($loggedInUser->getId());

                try {
                    $outcome = $systemRoleService->editSystemRole($systemRole);
                    if ($outcome) {
                        $alertNotification->addSuccess('System Role updated successfully');
                        $this->get('app.shared_data_manager')->removeCacheItem(AppConstants::KEY_CACHED_ROLES);
                    }
                } catch (AppException $app_exc) {

                    $this->get('logger')->info('GET ERROR: ' . $app_exc->getMessage());

                    switch ($app_exc->getMessage()) {
                        case AppConstants::DUPLICATE_NAME:
                            $errorMessage = "Name is already in use.";
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
        $systemPrivileges = $sharedDataManager->getSystemPrivileges();
        $systemRoleCategories = $sharedDataManager->getSystemRoleCategories();

        return $this->render('super_admin/security/system_role_edit.html.twig',
            array(
                'systemRole' => $systemRole,
                'validator' => $validator,
                'outcome' => $outcome,
                'alertNotification' => $alertNotification,
                'systemPrivileges' => $systemPrivileges,
                'systemRoleCategories' => $systemRoleCategories
            )
        );
    }

    /**
     * @Route("/super_admin/system_role/{guid}/delete",name="system_role_delete")
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

        $systemRole = new SystemRole();

        $validator = new Validator();
        $this->initializeValidationFields($validator, AppConstants::DELETE);

        $alertNotification = new AlertNotification();
        $outcome = false;

        $systemRoleService = $this->get('app.system_roles_service');

        if (!$request->request->has("btn_submit")) {//first page load

            $this->get('logger')->info('DELETE ROLE: new page load');

            try {
                $systemRole = $systemRoleService->getSystemRole($guid);
            } catch (AppException $app_ex) {

            }

            if (!$systemRole) {
                $systemRole = new SystemRole();
                $alertNotification->addError('Role could not be loaded, go back and try again.');
            }

        } else if ($request->request->has("btn_submit")) {

            $this->get('logger')->info('DELETE ROLE: button click page load');

            $systemRole = $this->fillModelFromRequest($request);
            $this->validateForm($validator, $systemRole, AppConstants::DELETE);

            if (!$validator->getFields()->hasErrors()) {

                $systemRole->setRecordStatus(AppConstants::INACTIVE);

                $today = date("Y-m-d H:i:s");
                $systemRole->setLastModified($today);
                $systemRole->setLastModifiedByUserId($loggedInUser->getId());

                try {
                    $outcome = $systemRoleService->deleteSystemRole($systemRole);
                    if ($outcome) {
                        //$notificationMessage = "SystemRole updated successfully";
                        //$this->addFlash('success', 'committee.deleted_successfully');
                        $this->get('app.shared_data_manager')->removeCacheItem(AppConstants::KEY_CACHED_ROLES);
                        return $this->redirectToRoute('system_role_list');

                    }
                } catch (AppException $app_exc) {
                    $errorMessage = "An error occured, Try again.";
                    $alertNotification->addError($errorMessage);
                } catch (Exception $e) {

                }
            }

        }

        $sharedDataManager = $this->get('app.shared_data_manager');
        $systemPrivileges = $sharedDataManager->getSystemPrivileges();
        $systemRoleCategories = $sharedDataManager->getSystemRoleCategories();

        return $this->render('super_admin/security/system_role_delete.html.twig',
            array(
                'systemRole' => $systemRole,
                'validator' => $validator,
                'outcome' => $outcome,
                'alertNotification' => $alertNotification,
                'systemPrivileges' => $systemPrivileges,
                'systemRoleCategories' => $systemRoleCategories
            )
        );
    }

    //helper methods
    private function initializeValidationFields(Validator $validator, string $which)
    {
        $validator->getFields()->addField('role_name', "Name is required");
        $validator->getFields()->addField('role_category', "Role user category is required");
        $validator->getFields()->addField('privileges', "Role privileges are required");

        switch ($which) {
            case AppConstants::EDIT:
                $validator->getFields()->addField('guid', "Invalid record identifier");
                break;

            case AppConstants::DELETE:
                $validator->getFields()->addField('guid', "Invalid record identifier");
                break;
        }
    }

    private function validateForm(Validator $validator, SystemRole $systemRole, string $which)
    {
        $validator->textRequiredMax('role_name', $systemRole->getRoleName(), true, 1, 45);
        $validator->textRequiredMax('role_category', $systemRole->getCategoryId());

        switch ($which) {
            case AppConstants::NEW:
                $validator->required('privileges', implode(",", $systemRole->getPrivileges()));
                break;

            case AppConstants::EDIT:
                $validator->required('guid', $systemRole->getGuid());
                $validator->required('privileges', implode(",", $systemRole->getPrivileges()));
                break;

            case AppConstants::DELETE:
                $validator->required('guid', $systemRole->getGuid());
                break;
        }
    }

    private function fillModelFromRequest(Request $request)
    {
        $systemRole = new SystemRole();
        $systemRole->setRoleName($request->request->get("role_name", ""));
        $systemRole->setCategoryId($request->request->get("role_category"));
        $systemRole->setPrivileges($request->request->get('privilegeIds', array()));
        $systemRole->setGuid($request->request->get("guid", ""));

        return $systemRole;
    }

}