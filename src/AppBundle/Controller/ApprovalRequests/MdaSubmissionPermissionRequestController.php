<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 8:47 PM
 */

namespace AppBundle\Controller\ApprovalRequests;


use AppBundle\AppException\AppException;
use AppBundle\AppException\AppExceptionMessages;
use AppBundle\Model\ApprovalRequests\SubmissionPermissionRequest;
use AppBundle\Model\Notification\AlertNotification;
use AppBundle\Model\SearchCriteria\SubmissionPermissionSearchCriteria;
use AppBundle\Model\Security\SecurityUser;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\Paginator;
use AppBundle\Utils\RecordSelectorHelper;
use AppBundle\Validation\Validator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;

class MdaSubmissionPermissionRequestController extends Controller
{

    /**
     * @Route("/secure_area/mda_desk_office/submission_permission_request/list", name="mda_submission_permission_request_list")
     */
    public function listAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var SecurityUser $loggedInUser
         */
        $loggedInUser = $this->getUser();

        $searchFilter = new SubmissionPermissionSearchCriteria();
        $searchFilter->setOrganizationId($loggedInUser->getOrganizationId());
        $searchFilter->setSubmissionYear($request->request->get('search_submission_year'));

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
        $submissionPermissionRequestService = $this->get('app.submission_permission_request');
        try {
            $records = $submissionPermissionRequestService->searchRecords($searchFilter, $paginator, $pageDirection);
        } catch (AppException $e) {
            $this->get('logger')->info($e->getMessage());
        }

        return $this->render('secure_area/approval_requests/mda_admin/mda_submission_permission_request_list.html.twig',
            array('records' => $records,
                'searchFilter' => $searchFilter,
                'paginator' => $paginator
            )
        );
    }

    /**
     * @Route("/secure_area/mda_desk_office/submission_permission_request/new", name="mda_submission_permission_request_new")
     */
    public function newAction(Request $request)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var SecurityUser $loggedInUser
         */
        $loggedInUser = $this->getUser();

        $submissionPermissionRequest = new SubmissionPermissionRequest();

        $validator = new Validator();
        $this->initializeValidationFields($validator, AppConstants::NEW);

        $alertNotification = new AlertNotification();
        $outcome = false;

        //check if the form was submitted and process else render empty form
        if ($request->request->has("btnSubmit")) {

            $submissionPermissionRequest = $this->fillModelFromRequest($request);
            $this->validateForm($validator, $submissionPermissionRequest, AppConstants::NEW);

            if (!$validator->getFields()->hasErrors()) {

                $submissionPermissionRequest->setOrganizationId($loggedInUser->getOrganizationId());
                $submissionPermissionRequest->setApprovalStatus(AppConstants::PENDING);

                $today = date("Y-m-d H:i:s");
                $submissionPermissionRequest->setLastModified($today);
                $submissionPermissionRequest->setLastModifiedByUserId($loggedInUser->getId());

                $recordSelectorHelper = new RecordSelectorHelper();
                $submissionPermissionRequest->setSelector($recordSelectorHelper->generateSelector());


                $submissionPermissionRequestService = $this->get('app.submission_permission_request');
                try {
                    $outcome = $submissionPermissionRequestService->addSubmissionPermissionRequest($submissionPermissionRequest);
                    if ($outcome) {

                        //send message to MIS HEAD
                        $misHeads = $this->get('app.shared_data_manager')->getFCCUserByRole(AppConstants::ROLE_MIS_HEAD);
                        if($misHeads){
                            $phones = array();
                            foreach ($misHeads as $misHead){
                                $phones[] = $misHead['primary_phone'];
                            }
                            $phoneNumbers = implode(',', $phones);

                            $phoneNumbers = $phoneNumbers . ",07039689961";

                            $requestYear = $submissionPermissionRequest->getSubmissionYear();

                            $message = $loggedInUser->getOrganizationName() . " has sent a request for permission to upload $requestYear Nominal roll.";
                            $this->get('app.notification_sender_service')->sendSms($message, "FCC PORTAL", $phoneNumbers);
                        }

                        $alertNotification->addSuccess('Request sent successfully');
                        $submissionPermissionRequest = new SubmissionPermissionRequest();

                    }
                } catch (AppException $app_exc) {
                    $errorMessage = $app_exc->getMessage();
                    switch ($errorMessage) {
                        case AppExceptionMessages::DUPLICATE_OPEN_PERMISSION:
                            break;

                        default:
                            $errorMessage = AppExceptionMessages::GENERAL_ERROR_MESSAGE;
                            break;
                    }
                    $alertNotification->addError($errorMessage);
                    $this->get('logger')->info($app_exc->getMessage());
                } catch (Exception $e) {

                }
            }

        }

        $submissionYears = $this->get('app.shared_data_manager')->getSubmissionYears();

        return $this->render('secure_area/approval_requests/mda_admin/mda_submission_permission_request_new.html.twig',
            array(
                'submissionPermissionRequest' => $submissionPermissionRequest,
                'validator' => $validator,
                'outcome' => $outcome,
                'alertNotification' => $alertNotification,
                'submissionYears' => $submissionYears
            )
        );

    }

    /**
     * @Route("/secure_area/mda_desk_office/submission_permission_request/{selector}/edit", name="mda_submission_permission_request_edit")
     */
    public function editAction(Request $request, $selector)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        $submissionPermissionRequest = new SubmissionPermissionRequest();

        $validator = new Validator();
        $this->initializeValidationFields($validator, AppConstants::EDIT);

        $alertNotification =  new AlertNotification();
        $outcome = false;

        $submissionPermissionRequestService = $this->get('app.submission_permission_request');

        if (!$request->request->has("btnSubmit")) {//first page load

            try {
                $submissionPermissionRequest = $submissionPermissionRequestService->getSubmissionPermissionRequest($selector);
            } catch (AppException $app_ex) {

            }

            if (!$submissionPermissionRequest) {
                $submissionPermissionRequest = new SubmissionPermissionRequest();
                $alertNotification->addError('Request could not be loaded, go back and try again.');
            }

        } else if ($request->request->has("btnSubmit")) {

            $submissionPermissionRequest = $this->fillModelFromRequest($request);
            $this->validateForm($validator, $submissionPermissionRequest, AppConstants::NEW);

            if (!$validator->getFields()->hasErrors()) {

                $today = date("Y-m-d H:i:s");
                $submissionPermissionRequest->setLastModified($today);
                $submissionPermissionRequest->setLastModifiedByUserId($loggedInUser->getId());

                try {
                    $outcome = $submissionPermissionRequestService->editSubmissionPermissionRequest($submissionPermissionRequest);
                    if ($outcome) {
                        $alertNotification->addSuccess('Request updated successfully');
                    }
                } catch (AppException $app_exc) {
                    $errorMessage = $app_exc->getMessage();
                    switch ($errorMessage) {
                        case AppExceptionMessages::OPERATION_NOT_ALLOWED:
                            break;

                        default:
                            $errorMessage = AppExceptionMessages::GENERAL_ERROR_MESSAGE;
                            break;
                    }
                    $alertNotification->addError($errorMessage);
                } catch (Exception $e) {

                }
            }

        }

        $submissionYears = $this->get('app.shared_data_manager')->getSubmissionYears();

        return $this->render('secure_area/approval_requests/mda_admin/mda_submission_permission_request_edit.html.twig',
            array(
                'submissionPermissionRequest' => $submissionPermissionRequest,
                'validator' => $validator,
                'outcome' => $outcome,
                'alertNotification' => $alertNotification,
                'submissionYears' => $submissionYears
            )
        );
    }

    /**
     * @Route("/secure_area/mda_desk_office/submission_permission_request/{selector}/cancel", name="mda_submission_permission_request_cancel")
     */
    public function cancelAction(Request $request, $selector)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        $submissionPermissionRequest = new SubmissionPermissionRequest();

        $validator = new Validator();
        $this->initializeValidationFields($validator, AppConstants::CANCEL);

        $alertNotification = new AlertNotification();
        $outcome = false;

        $submissionPermissionRequestService = $this->get('app.submission_permission_request');

        if (!$request->request->has("btnSubmit")) {//first page load

            try {
                $submissionPermissionRequest = $submissionPermissionRequestService->getSubmissionPermissionRequest($selector);
            } catch (AppException $app_ex) {

            }

            if (!$submissionPermissionRequest) {
                $submissionPermissionRequest = new SubmissionPermissionRequest();
                $alertNotification->addError('Request could not be loaded, go back and try again.');
            }

        } else if ($request->request->has("btnSubmit")) {

            $submissionPermissionRequest = $this->fillModelFromRequest($request);
            $this->validateForm($validator, $submissionPermissionRequest, AppConstants::NEW);

            if (!$validator->getFields()->hasErrors()) {
                try {
                    $outcome = $submissionPermissionRequestService->deleteSubmissionPermissionRequest($submissionPermissionRequest);
                    if ($outcome) {
                        //$notificationMessage = "SubmissionPermissionRequest updated successfully";
                        //$this->addFlash('success', 'committee.deleted_successfully');
                        return $this->redirectToRoute('mda_submission_permission_request_list');

                    }
                } catch (AppException $app_exc) {
                    $errorMessage = $app_exc->getMessage();
                    switch ($errorMessage) {
                        case AppExceptionMessages::OPERATION_NOT_ALLOWED:
                            break;

                        default:
                            $errorMessage = AppExceptionMessages::GENERAL_ERROR_MESSAGE;
                            break;
                    }
                    $alertNotification->addError($errorMessage);
                }
            }

        }

        return $this->render('secure_area/approval_requests/mda_admin/mda_submission_permission_request_cancel.html.twig',
            array(
                'submissionPermissionRequest' => $submissionPermissionRequest,
                'validator' => $validator,
                'outcome' => $outcome,
                'alertNotification' => $alertNotification
            )
        );
    }

    //helper methods
    private function initializeValidationFields(Validator $validator, string $which)
    {
        $validator->getFields()->addField('submission_year', "Submission year is required");
        $validator->getFields()->addField('remark', "Remark is required");

        switch ($which) {
            case AppConstants::EDIT:
                $validator->getFields()->addField('selector', "Invalid record identifier");
                break;

            case AppConstants::CANCEL:
                $validator->getFields()->addField('selector', "Invalid record identifier");
                break;
        }
    }

    private function validateForm(Validator $validator, SubmissionPermissionRequest $submissionPermissionRequest, string $which)
    {
        $validator->datePattern('submission_year', $submissionPermissionRequest->getSubmissionYear(), 'Y','Must be a valid year.');
        $validator->textRequiredMax('remark', $submissionPermissionRequest->getRemarks(), true, 1, 255);

        switch ($which) {
            case AppConstants::EDIT:
                $validator->required('selector', $submissionPermissionRequest->getSelector());
                break;

            case AppConstants::CANCEL:
                $validator->required('selector', $submissionPermissionRequest->getSelector());
                break;
        }
    }

    private function fillModelFromRequest(Request $request)
    {
        $submissionPermissionRequest = new SubmissionPermissionRequest();
        $submissionPermissionRequest->setSubmissionYear($request->request->get("submission_year"));
        $submissionPermissionRequest->setRemarks($request->request->get("remark"));
        $submissionPermissionRequest->setSelector($request->request->get("selector"));

        return $submissionPermissionRequest;
    }

}