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
use AppBundle\Validation\Validator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class MisHeadSubmissionPermissionRequestController extends Controller
{

    /**
     * @Route("/secure_area/mis_head/approval_requests/submission_permission_request/pending_approval/list"
     * , name="mis_head_submission_permission_request_pending_approval_list")
     */
    public function pendingApprovalListAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var SecurityUser $loggedInUser
         */
        $loggedInUser = $this->getUser();

        $searchFilter = new SubmissionPermissionSearchCriteria();
        $searchFilter->setApprovalStatus(AppConstants::PENDING);
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
            $records = $submissionPermissionRequestService->searchRecords($searchFilter, $paginator, $pageDirection, false);
        } catch (AppException $e) {
            $this->get('logger')->info($e->getMessage());
        }

        return $this->render('secure_area/approval_requests/mis_head/mis_head_submission_permission_request_pending_approval_list.html.twig',
            array('records' => $records,
                'searchFilter' => $searchFilter,
                'paginator' => $paginator
            )
        );
    }

    /**
     * @Route("/secure_area/mis_head/approval_requests/submission_permission_request/approved/list"
     * , name="mis_head_submission_permission_request_approved_list")
     */
    public function approvedListAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var SecurityUser $loggedInUser
         */
        $loggedInUser = $this->getUser();

        $searchFilter = new SubmissionPermissionSearchCriteria();
        $searchFilter->setApprovalStatus(AppConstants::APPROVED);
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
            $records = $submissionPermissionRequestService->searchRecords($searchFilter, $paginator, $pageDirection, false);
        } catch (AppException $e) {
            $this->get('logger')->info($e->getMessage());
        }

        return $this->render('secure_area/approval_requests/mis_head/mis_head_submission_permission_request_approved_list.html.twig',
            array('records' => $records,
                'searchFilter' => $searchFilter,
                'paginator' => $paginator
            )
        );
    }

    /**
     * @Route("/secure_area/mis_head/approval_requests/submission_permission_request/declined/list"
     * , name="mis_head_submission_permission_request_declined_list")
     */
    public function declinedListAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var SecurityUser $loggedInUser
         */
        $loggedInUser = $this->getUser();

        $searchFilter = new SubmissionPermissionSearchCriteria();
        $searchFilter->setApprovalStatus(AppConstants::DECLINED);
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
            $records = $submissionPermissionRequestService->searchRecords($searchFilter, $paginator, $pageDirection, false);
        } catch (AppException $e) {
            $this->get('logger')->info($e->getMessage());
        }

        return $this->render('secure_area/approval_requests/mis_head/mis_head_submission_permission_request_declined_list.html.twig',
            array('records' => $records,
                'searchFilter' => $searchFilter,
                'paginator' => $paginator
            )
        );
    }

    /**
     * @Route("/secure_area/mis_head/approval_requests/submission_permission_request/{selector}/approve"
     * , name="mis_head_submission_permission_request_approve")
     */
    public function approveAction(Request $request, $selector)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        $submissionPermissionRequest = new SubmissionPermissionRequest();
        $submissionPermissionRequestSummary = new SubmissionPermissionRequest();

        $validator = new Validator();
        $this->initializeValidationFields($validator, AppConstants::EDIT);

        $alertNotification =  new AlertNotification();
        $outcome = false;

        $submissionPermissionRequestService = $this->get('app.submission_permission_request');

        try {
            $submissionPermissionRequestSummary = $submissionPermissionRequestService->getSubmissionPermissionRequest($selector);
        } catch (AppException $app_ex) {

        }

        if (!$submissionPermissionRequestSummary) {
            $submissionPermissionRequestSummary = new SubmissionPermissionRequest();
            $alertNotification->addError('Request could not be loaded, go back and try again.');
        }

        if ($request->request->has("btnSubmit")) {

            $submissionPermissionRequest = $this->fillModelFromRequest($request);
            $this->validateForm($validator, $submissionPermissionRequest, AppConstants::NEW);

            if (!$validator->getFields()->hasErrors()) {

                $today = date("Y-m-d H:i:s");
                $submissionPermissionRequest->setDateOfApproval($today);
                $submissionPermissionRequest->setApprovedByUserId($loggedInUser->getId());
                $submissionPermissionRequest->setLastModified($today);
                $submissionPermissionRequest->setLastModifiedByUserId($loggedInUser->getId());

                try {
                    $outcome = $submissionPermissionRequestService->approveSubmissionPermissionRequest($submissionPermissionRequest);
                    if ($outcome) {
                        $alertNotification->addSuccess('Request updated successfully');
                        $submissionPermissionRequest = $submissionPermissionRequestService->getSubmissionPermissionRequest($selector);
                        $submissionPermissionRequestSummary = $submissionPermissionRequest;
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

        $approvalStatus = $this->get('app.shared_data_manager')->getApprovalStatus();

        return $this->render('secure_area/approval_requests/mis_head/mis_head_submission_permission_request_approve.html.twig',
            array(
                'submissionPermissionRequestSummary' => $submissionPermissionRequestSummary,
                'submissionPermissionRequest' => $submissionPermissionRequest,
                'validator' => $validator,
                'outcome' => $outcome,
                'alertNotification' => $alertNotification,
                'approvalStatus' => $approvalStatus
            )
        );
    }

    //helper methods
    private function initializeValidationFields(Validator $validator, string $which)
    {
        $validator->getFields()->addField('submission_year', "Submission year is required");
        $validator->getFields()->addField('remark', "Remark is required");
        $validator->getFields()->addField('approval_status', "Approval status is required");

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
        $validator->required('approval_status', $submissionPermissionRequest->getApprovalStatus());

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
        $submissionPermissionRequest->setApprovalStatus($request->request->get("approval_status"));
        $submissionPermissionRequest->setSelector($request->request->get("selector"));

        return $submissionPermissionRequest;
    }

}