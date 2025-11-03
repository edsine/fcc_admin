<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/7/2017
 * Time: 4:29 PM
 */

namespace AppBundle\Controller\MisHead;


use AppBundle\AppException\AppException;
use AppBundle\Model\SearchCriteria\NominalRoleSearchCriteria;
use AppBundle\Model\SearchCriteria\NominalRollSubmissionSearchCriteria;
use AppBundle\Model\Security\SecurityUser;
use AppBundle\Model\Submission\NominalRoll;
use AppBundle\Model\Submission\NominalRollSubmission;
use AppBundle\Model\Users\UserProfile;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MisHeadNominalRollController extends Controller
{

    /**
     * @Route("/secure_area/federal/mis_head/nominal_roll/pending_approval/list"
     * , name="mis_head_nominal_roll_pending_approval_list")
     */
    public function listPendingSubmissionApprovalAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        $searchFilter = new NominalRollSubmissionSearchCriteria();
        $searchFilter->setOrganizationId($request->request->get('searchOrganizationId'));
        $searchFilter->setValidationStatus(AppConstants::PASSED);
        $searchFilter->setFccDeskOfficerConfirmationStatus(AppConstants::CONFIRMED);
        $searchFilter->setFccMisHeadApprovalStatus(AppConstants::PENDING);

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

        $misHeadNominalRoleService = $this->get('app.mis_head_submission_service');

        $records = array();
        $organizations = array();
        $submissionSummary = array();

        $totalPending = '';
        $totalStillProcessing = '';

        try {
            $records = $misHeadNominalRoleService->searchNominalRollUploads($searchFilter, $paginator, $pageDirection);
            $organizations = $this->get('app.shared_data_manager')->getOrganizations();
            $submissionSummary = $misHeadNominalRoleService->getMisHeadSubmissionSummary();

            $totalPending = $submissionSummary['totalPending'];
            $totalStillProcessing = $submissionSummary['totalStillProcessing'];
        } catch (AppException $e) {
            $this->get('logger')->info($e->getMessage());
        }


        return $this->render('secure_area/federal/nominal_roll/mis_head/mis_head_nominal_roll_pending_approval_list.html.twig',
            array(
                'records' => $records,
                'searchFilter' => $searchFilter,
                'paginator' => $paginator,
                'organizations' => $organizations,
                'totalPending' => $totalPending,
                'totalStillProcessing' => $totalStillProcessing
            )
        );
    }

    /**
     * @Route("/secure_area/federal/mis_head/nominal_roll/processing/list"
     * , name="mis_head_nominal_roll_processing_list")
     */
    public function listProcessingSubmissionAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        $searchFilter = new NominalRollSubmissionSearchCriteria();
        $searchFilter->setOrganizationId($request->request->get('searchOrganizationId'));
        $searchFilter->setValidationStatus(AppConstants::PASSED);
        $searchFilter->setFccDeskOfficerConfirmationStatus(AppConstants::CONFIRMED);
        $searchFilter->setFccMisHeadApprovalStatus(AppConstants::APPROVED);
        $searchFilter->setProcessingStatus(AppConstants::PENDING);

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

        $misHeadNominalRoleService = $this->get('app.mis_head_submission_service');

        $records = array();
        $organizations = array();
        $submissionSummary = array();

        $totalPending = '';
        $totalStillProcessing = '';

        try {
            $records = $misHeadNominalRoleService->searchNominalRollUploads($searchFilter, $paginator, $pageDirection);
            $organizations = $this->get('app.shared_data_manager')->getOrganizations();
            $submissionSummary = $misHeadNominalRoleService->getMisHeadSubmissionSummary();

            $totalPending = $submissionSummary['totalPending'];
            $totalStillProcessing = $submissionSummary['totalStillProcessing'];
        } catch (AppException $e) {
            $this->get('logger')->info($e->getMessage());
        }


        return $this->render('secure_area/federal/nominal_roll/mis_head/mis_head_nominal_roll_processing_list.html.twig',
            array(
                'records' => $records,
                'searchFilter' => $searchFilter,
                'paginator' => $paginator,
                'organizations' => $organizations,
                'totalPending' => $totalPending,
                'totalStillProcessing' => $totalStillProcessing
            )
        );
    }

    /**
     * @Route("/secure_area/federal/mis_head/nominal_roll/processed/list"
     * , name="mis_head_nominal_roll_processed_list")
     */
    public function listProcessedSubmissionAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        $searchFilter = new NominalRollSubmissionSearchCriteria();
        $searchFilter->setOrganizationId($request->request->get('searchOrganizationId'));
        $searchFilter->setValidationStatus(AppConstants::PASSED);
        $searchFilter->setFccDeskOfficerConfirmationStatus(AppConstants::CONFIRMED);
        $searchFilter->setFccMisHeadApprovalStatus(AppConstants::APPROVED);
        $searchFilter->setProcessingStatus(AppConstants::COMPLETED);

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

        $misHeadNominalRoleService = $this->get('app.mis_head_submission_service');

        $records = array();
        $organizations = array();
        $submissionSummary = array();

        $totalPending = '';
        $totalStillProcessing = '';

        try {
            $records = $misHeadNominalRoleService->searchNominalRollUploads($searchFilter, $paginator, $pageDirection);
            $organizations = $this->get('app.shared_data_manager')->getOrganizations();
            $submissionSummary = $misHeadNominalRoleService->getMisHeadSubmissionSummary();

            $totalPending = $submissionSummary['totalPending'];
            $totalStillProcessing = $submissionSummary['totalStillProcessing'];
        } catch (AppException $e) {
            $this->get('logger')->info($e->getMessage());
        }


        return $this->render('secure_area/federal/nominal_roll/mis_head/mis_head_nominal_roll_processed_list.html.twig',
            array(
                'records' => $records,
                'searchFilter' => $searchFilter,
                'paginator' => $paginator,
                'organizations' => $organizations,
                'totalPending' => $totalPending,
                'totalStillProcessing' => $totalStillProcessing
            )
        );
    }

    /**
     * @Route("/secure_area/federal/mis_head/nominal_roll/approve"
     * , name="mis_head_nominal_roll_approve")
     */
    public function approveSelectedSubmissionAction(Request $request)
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

        $selectedSubmissionIds = $request->request->get('selectedSubmissionIds');
        if($selectedSubmissionIds){
            try{
                $outcome = $this->get('app.mis_head_submission_service')->approveNominalRollSubmission($selectedSubmissionIds, $loggedInUser->getId());
                if($outcome){
                    $this->addFlash('success', 'Selected Submissions have been approved and sent for processing successfully');
                }else{
                    $this->addFlash('danger', 'Approvals could not be processed at the moment, Please try again later.');
                }

            }catch(AppException $appExc){
                $this->addFlash('danger', 'Approvals could not be processed at the moment, Please try again later.');
                $this->get('logger')->info($appExc->getMessage());
            }

        }

        return $this->redirectToRoute('mis_head_nominal_roll_pending_approval_list');
    }


    /**
     * @Route("/secure_area/federal/mis_head/nominal_roll/{submissionId}/show"
     * , name="mis_head_nominal_roll_show")
     */
    public function showSubmissionAction(Request $request, $submissionId)
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
        $nominalRoleSubmission = null;
        try {
            $nominalRoleSubmission = $mdaSubmissionService->getSubmission($submissionId);

            if($nominalRoleSubmission){
                if($nominalRoleSubmission->getFccDeskOfficerConfirmationStatus() === AppConstants::CONFIRMED){
                    $records = $mdaSubmissionService->searchApprovedNominalRoleDetail($searchFilter, $paginator, $pageDirection);
                }else if($nominalRoleSubmission->getValidationStatus() === 'PASSED'){
                    $records = $mdaSubmissionService->searchPassedValidationDetail($searchFilter, $paginator, $pageDirection);
                }else{
                    $records = array();
                }
            }
        } catch (AppException $e) {
            $this->get('logger')->alert($e->getMessage());
        }

        if(!$nominalRoleSubmission){
            $nominalRoleSubmission = new NominalRollSubmission();
        }

        return $this->render('secure_area/federal/nominal_roll/mis_head/mis_head_nominal_roll_show.html.twig',
            array(
                'nominalRoleSubmission' => $nominalRoleSubmission,
                'records' => $records,
                'paginator' => $paginator
            )
        );
    }

}