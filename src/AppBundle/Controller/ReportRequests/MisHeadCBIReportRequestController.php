<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 8:47 PM
 */

namespace AppBundle\Controller\ReportRequests;


use AppBundle\AppException\AppException;
use AppBundle\AppException\AppExceptionMessages;
use AppBundle\Model\ReportRequests\CBIReportRequest;
use AppBundle\Model\Notification\AlertNotification;
use AppBundle\Model\SearchCriteria\CBIReportRequestSearchCriteria;
use AppBundle\Model\Security\SecurityUser;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\Paginator;
use AppBundle\Validation\Validator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class MisHeadCBIReportRequestController extends Controller
{

    /**
     * @Route("/secure_area/mis_head/cbi_report_request/pending_approval/list", name="mis_head_cbi_report_request_pending_approval_list")
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

        $searchFilter = new CBIReportRequestSearchCriteria();
        $searchFilter->setApprovalStatus(AppConstants::PENDING);
        $searchFilter->setOrganizationId($request->request->get('search_organization'));

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
        $cbiReportRequestService = $this->get('app.cbi_report_request');
        try {
            $records = $cbiReportRequestService->searchRecords($searchFilter, $paginator, $pageDirection, false);
        } catch (AppException $e) {
            $this->get('logger')->info($e->getMessage());
        }

        $organizations = $this->get('app.shared_data_manager')->getOrganizationByLevelOfGovernment(AppConstants::FEDERAL);

        return $this->render('secure_area/report_requests/mis_head/mis_head_cbi_report_request_pending_approval_list.html.twig',
            array('records' => $records,
                'searchFilter' => $searchFilter,
                'organizations' => $organizations,
                'paginator' => $paginator
            )
        );
    }

    /**
     * @Route("/secure_area/mis_head/cbi_report_request/approved/list", name="mis_head_cbi_report_request_approved_list")
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

        $searchFilter = new CBIReportRequestSearchCriteria();
        $searchFilter->setApprovalStatus(AppConstants::APPROVED);
        $searchFilter->setOrganizationId($request->request->get('search_organization'));

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
        $cbiReportRequestService = $this->get('app.cbi_report_request');
        try {
            $records = $cbiReportRequestService->searchRecords($searchFilter, $paginator, $pageDirection, false);
        } catch (AppException $e) {
            $this->get('logger')->info($e->getMessage());
        }

        return $this->render('secure_area/report_requests/mis_head/mis_head_cbi_report_request_approved_list.html.twig',
            array('records' => $records,
                'searchFilter' => $searchFilter,
                'paginator' => $paginator
            )
        );
    }

    /**
     * @Route("/secure_area/mis_head/cbi_report_request/declined/list", name="mis_head_cbi_report_request_declined_list")
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

        $searchFilter = new CBIReportRequestSearchCriteria();
        $searchFilter->setApprovalStatus(AppConstants::DECLINED);
        $searchFilter->setOrganizationId($request->request->get('search_organization'));

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
        $cbiReportRequestService = $this->get('app.cbi_report_request');
        try {
            $records = $cbiReportRequestService->searchRecords($searchFilter, $paginator, $pageDirection, false);
        } catch (AppException $e) {
            $this->get('logger')->info($e->getMessage());
        }

        return $this->render('secure_area/report_requests/mis_head/mis_head_cbi_report_request_declined_list.html.twig',
            array('records' => $records,
                'searchFilter' => $searchFilter,
                'paginator' => $paginator
            )
        );
    }

    /**
     * @Route("/secure_area/mis_head/cbi_report_request/{selector}/approve", name="mis_head_cbi_report_request_approve")
     */
    public function approveAction(Request $request, $selector)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var SecurityUser $loggedInUser
         */
        $loggedInUser = $this->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        $cbiReportRequest = new CBIReportRequest();
        $cbiReportRequestSummary = new CBIReportRequest();

        $validator = new Validator();
        $this->initializeValidationFields($validator, AppConstants::EDIT);

        $alertNotification =  new AlertNotification();
        $outcome = false;

        $cbiReportRequestService = $this->get('app.cbi_report_request');

        try {
            $cbiReportRequestSummary = $cbiReportRequestService->getCbiReportRequest($selector);
        } catch (AppException $app_ex) {
            $this->get('logger')->alert($app_ex->getMessage());
        }

        if (!$cbiReportRequestSummary) {
            $cbiReportRequestSummary = new CBIReportRequest();
            $alertNotification->addError('Request could not be loaded, go back and try again.');
        }

        if ($request->request->has("btnSubmit")) {

            $cbiReportRequest = $this->fillModelFromRequest($request);
            $this->validateForm($validator, $cbiReportRequest, AppConstants::NEW);

            if (!$validator->getFields()->hasErrors()) {

                $today = date("Y-m-d H:i:s");
                $cbiReportRequest->setDateOfApproval($today);
                $cbiReportRequest->setApprovedByUserId($loggedInUser->getId());
                $cbiReportRequest->setLastModified($today);
                $cbiReportRequest->setLastModifiedByUserId($loggedInUser->getId());

                try {
                    $outcome = $cbiReportRequestService->approveCbiReportRequestRequest($cbiReportRequest);
                    if ($outcome) {
                        $alertNotification->addSuccess('Request updated successfully');
                        $cbiReportRequest = $cbiReportRequestService->getCbiReportRequest($selector);
                        $cbiReportRequestSummary = $cbiReportRequest;
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

        return $this->render('secure_area/report_requests/mis_head/mis_head_cbi_report_request_approve.html.twig',
            array(
                'cbiReportRequestSummary' => $cbiReportRequestSummary,
                'cbiReportRequest' => $cbiReportRequest,
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
        $validator->getFields()->addField('recruitment_value', "Invalid recruitment value");
        $validator->getFields()->addField('cbi_grade_level_category', "Grade level category is required");
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

    private function validateForm(Validator $validator, CBIReportRequest $cbiReportRequest, string $which)
    {
        $validator->integer('recruitment_value', $cbiReportRequest->getRecruitmentValue());
        $validator->required('cbi_grade_level_category', $cbiReportRequest->getCbiGradeLevelCategory());
        $validator->textRequiredMax('remark', $cbiReportRequest->getRemarks(), true, 1, 255);
        $validator->required('approval_status', $cbiReportRequest->getApprovalStatus());

        switch ($which) {
            case AppConstants::EDIT:
                $validator->required('selector', $cbiReportRequest->getSelector());
                break;

            case AppConstants::CANCEL:
                $validator->required('selector', $cbiReportRequest->getSelector());
                break;
        }
    }

    private function fillModelFromRequest(Request $request)
    {
        $cbiReportRequest = new CBIReportRequest();
        $cbiReportRequest->setRecruitmentValue($request->request->get("recruitment_value"));
        $cbiReportRequest->setCbiGradeLevelCategory($request->request->get("cbi_grade_level_category"));
        $cbiReportRequest->setRemarks($request->request->get("remark"));
        $cbiReportRequest->setApprovalStatus($request->request->get("approval_status"));
        $cbiReportRequest->setSelector($request->request->get("selector"));

        return $cbiReportRequest;
    }

}