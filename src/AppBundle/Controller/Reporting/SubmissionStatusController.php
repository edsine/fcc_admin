<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/6/2017
 * Time: 4:45 PM
 */

namespace AppBundle\Controller\Reporting;


use AppBundle\AppException\AppException;
use AppBundle\Model\SearchCriteria\NominalRoleSearchCriteria;
use AppBundle\Model\SearchCriteria\SubmissionStatusSearchCriteria;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SubmissionStatusController extends Controller
{
    /**
     * @Route("/secure_area/reporting/submission_status", name="submission_status")
     */
    public function listAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        $searchFilter = new SubmissionStatusSearchCriteria();
        $searchFilter->setOrganizationId($request->request->get('search_organization'));
        $searchFilter->setSubmissionYear($request->request->get('search_submission_year'));

        //$searchFilter->setSubmissionStatus($request->request->get('search_submission_status'));

        $searchFilter->setValidationStatus($request->request->get('search_submission_status'));
        $searchFilter->setFccDeskOfficerConfirmationStatus($request->request->get('search_confirmation_status'));
        $searchFilter->setMisHeadApprovalStatus($request->request->get('search_mis_approval_status'));
        $searchFilter->setProcessingStatus($request->request->get('search_processing_status'));

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

        $submissionStatusReportService = $this->get('app.submission_status_service');

        $records = array();
        try {
            $records = $submissionStatusReportService->searchRecords($searchFilter, $paginator, $pageDirection);
        } catch (AppException $e) {
            $this->get('logger')->info($e->getMessage());
        }

        $sharedDataManager = $this->get('app.shared_data_manager');

        $organizations = $sharedDataManager->getOrganizations();
        $submissionYears = $sharedDataManager->getSubmissionYears();
        $submissionStatus = $sharedDataManager->getSubmissionStatus();


        return $this->render('reporting/submission_status.html.twig',
            array(
                'records' => $records,
                'searchFilter' => $searchFilter,
                'paginator' => $paginator,
                'organizations' => $organizations,
                'submissionYears' => $submissionYears,
                'submissionStatus' => $submissionStatus
            )
        );
    }

    /**
     * @Route("/secure_area/mis_head/submission_status_preview/{submissionId}/show", name="submission_status_preview_show")
     */
    public function showSubmissionStatusPreviewAction(Request $request, $submissionId)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $searchFilter = new NominalRoleSearchCriteria();
        $searchFilter->setSubmissionId($submissionId);
        $searchFilter->setEmployeeNumber($request->request->get('searchEmployeeNumber'));
        $searchFilter->setName($request->request->get('searchName'));
        $searchFilter->setNationality($request->request->get('searchNationality'));
        $searchFilter->setStateOfOrigin($request->request->get('searchStateOfOrigin'));
        $searchFilter->setGradeLevel($request->request->get('searchGradeLevel'));
        $searchFilter->setGeoPoliticalZone($request->request->get('searchGeoPoliticalZone'));

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

        $mdaSubmissionService = $this->get('app.federal_nominal_roll_service');

        $records = array();
        $nominalRoleSubmission = $mdaSubmissionService->getSubmission($submissionId);
        try {
            if($nominalRoleSubmission->getValidationStatus() == AppConstants::FAILED){
                $records = $mdaSubmissionService->searchFailedValidationDetail($submissionId, $paginator, $pageDirection);
            }else if($nominalRoleSubmission->getValidationStatus() == AppConstants::PASSED){
                $records = $mdaSubmissionService->searchPassedValidationDetail($searchFilter, $paginator, $pageDirection);
            }
        } catch (AppException $e) {
        }

        if($nominalRoleSubmission->getValidationStatus() == AppConstants::FAILED){
            return $this->render('reporting/submission_status_failed_preview.html.twig',
                array(
                    'nominalRoleSubmission' => $nominalRoleSubmission,
                    'records' => $records,
                    'paginator' => $paginator
                )
            );
        }else if($nominalRoleSubmission->getValidationStatus() == AppConstants::PASSED){
            return $this->render('reporting/submission_status_passed_preview.html.twig',
                array(
                    'nominalRoleSubmission' => $nominalRoleSubmission,
                    'records' => $records,
                    'paginator' => $paginator
                )
            );
        }


    }

}