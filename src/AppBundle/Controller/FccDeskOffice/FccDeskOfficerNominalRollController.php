<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/7/2017
 * Time: 4:29 PM
 */

namespace AppBundle\Controller\FccDeskOffice;


use AppBundle\AppException\AppException;
use AppBundle\Model\SearchCriteria\NominalRoleSearchCriteria;
use AppBundle\Model\SearchCriteria\NominalRollSubmissionSearchCriteria;
use AppBundle\Model\Security\SecurityUser;
use AppBundle\Model\Users\UserProfile;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FccDeskOfficerNominalRollController extends Controller
{

    /**
     * @Route("/secure_area/federal/fcc_desk_officer/nominal_roll/pending_confirmation/list"
     * , name="fcc_desk_officer_nominal_roll_pending_confirmation_list")
     */
    public function listPendingConfirmationAction(Request $request)
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

        $searchFilter = new NominalRollSubmissionSearchCriteria();
        $searchFilter->setOrganizationId($request->request->get('searchOrganizationId'));
        $searchFilter->setValidationStatus(AppConstants::PASSED);
        $searchFilter->setFccDeskOfficerConfirmationStatus(AppConstants::PENDING);
        $searchFilter->setActiveStatus(AppConstants::Y);

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

        $fccNominalRoleService = $this->get('app.fcc_desk_office_submission_service');

        $records = array();
        $deskOfficerOrganizations = array();
        $submissionSummary = array();

        $totalPending = '';
        $totalFailed = '';

        $fccDeskOfficerUserCommitteeId = $loggedInUser->getFccCommitteeId();
        try {
            $records = $fccNominalRoleService->searchNominalRollUploads($searchFilter, $fccDeskOfficerUserCommitteeId, $paginator, $pageDirection);
            $deskOfficerOrganizations = $fccNominalRoleService->getFccDeskOfficerOrganizations($fccDeskOfficerUserCommitteeId);
            $submissionSummary = $fccNominalRoleService->getFccDeskOfficerSubmissionSummary($fccDeskOfficerUserCommitteeId);

            $totalPending = $submissionSummary['totalPending'];
            $totalFailed = $submissionSummary['totalFailed'];
        } catch (AppException $e) {
            $this->get('logger')->info($e->getMessage());
        }


        return $this->render('secure_area/federal/nominal_roll/fcc_desk_officer/fcc_desk_officer_nominal_roll_pending_confirmation_list.html.twig',
            array(
                'records' => $records,
                'searchFilter' => $searchFilter,
                'paginator' => $paginator,
                'deskOfficerOrganizations' => $deskOfficerOrganizations,
                'totalPending' => $totalPending,
                'totalFailed' => $totalFailed
            )
        );
    }

    /**
     * @Route("/secure_area/federal/fcc_desk_officer/nominal_roll/failed_validation/list"
     * , name="fcc_desk_officer_nominal_roll_failed_validation_list")
     */
    public function listFailedSubmissionAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        $searchFilter = new NominalRollSubmissionSearchCriteria();
        $searchFilter->setOrganizationId($request->request->get('searchOrganizationId'));
        $searchFilter->setValidationStatus(AppConstants::FAILED);

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

        $fccNominalRoleService = $this->get('app.fcc_desk_office_submission_service');

        $records = array();
        $deskOfficerOrganizations = array();
        $submissionSummary = array();

        $totalPending = '';
        $totalFailed = '';

        $fccDeskOfficerUserCommitteeId = $loggedInUser->getFccCommitteeId();
        try {
            $records = $fccNominalRoleService->searchNominalRollUploads($searchFilter, $fccDeskOfficerUserCommitteeId, $paginator, $pageDirection);
            $deskOfficerOrganizations = $fccNominalRoleService->getFccDeskOfficerOrganizations($fccDeskOfficerUserCommitteeId);
            $submissionSummary = $fccNominalRoleService->getFccDeskOfficerSubmissionSummary($fccDeskOfficerUserCommitteeId);

            $totalPending = $submissionSummary['totalPending'];
            $totalFailed = $submissionSummary['totalFailed'];
        } catch (AppException $e) {
            $this->get('logger')->info($e->getMessage());
        }


        return $this->render('secure_area/federal/nominal_roll/fcc_desk_officer/fcc_desk_officer_nominal_roll_failed_validation_list.html.twig',
            array(
                'records' => $records,
                'searchFilter' => $searchFilter,
                'paginator' => $paginator,
                'deskOfficerOrganizations' => $deskOfficerOrganizations,
                'totalPending' => $totalPending,
                'totalFailed' => $totalFailed
            )
        );
    }

    /**
     * @Route("/secure_area/federal/fcc_desk_officer/nominal_roll/confirmed/list"
     * , name="fcc_desk_officer_nominal_roll_confirmed_list")
     */
    public function listConfirmedSubmissionAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        $searchFilter = new NominalRollSubmissionSearchCriteria();
        $searchFilter->setOrganizationId($request->request->get('searchOrganizationId'));
        $searchFilter->setValidationStatus(AppConstants::PASSED);
        $searchFilter->setFccDeskOfficerConfirmationStatus(AppConstants::CONFIRMED);

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

        $fccNominalRoleService = $this->get('app.fcc_desk_office_submission_service');

        $records = array();
        $deskOfficerOrganizations = array();
        $submissionSummary = array();

        $totalPending = '';
        $totalFailed = '';

        $fccDeskOfficerUserCommitteeId = $loggedInUser->getFccCommitteeId();
        try {
            $records = $fccNominalRoleService->searchNominalRollUploads($searchFilter, $fccDeskOfficerUserCommitteeId, $paginator, $pageDirection);
            $deskOfficerOrganizations = $fccNominalRoleService->getFccDeskOfficerOrganizations($fccDeskOfficerUserCommitteeId);
            $submissionSummary = $fccNominalRoleService->getFccDeskOfficerSubmissionSummary($fccDeskOfficerUserCommitteeId);

            $totalPending = $submissionSummary['totalPending'];
            $totalFailed = $submissionSummary['totalFailed'];
        } catch (AppException $e) {
            $this->get('logger')->info($e->getMessage());
        }


        return $this->render('secure_area/federal/nominal_roll/fcc_desk_officer/fcc_desk_officer_nominal_roll_confirmed_list.html.twig',
            array(
                'records' => $records,
                'searchFilter' => $searchFilter,
                'paginator' => $paginator,
                'deskOfficerOrganizations' => $deskOfficerOrganizations,
                'totalPending' => $totalPending,
                'totalFailed' => $totalFailed
            )
        );
    }

    /**
     * @Route("/secure_area/federal/fcc_desk_officer/nominal_roll/confirm"
     * , name="fcc_desk_officer_nominal_roll_confirm")
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

        $selectedSubmissionIds = $request->request->get('selectedSubmissionIds');
        if ($selectedSubmissionIds) {
            try {
                $this->get('app.fcc_desk_office_submission_service')->confirmNominalRollSubmission($selectedSubmissionIds
                    , $loggedInUser->getPrivilegeChecker()->isCanConfirmFedMdaNominalRollUpload()
                    , $loggedInUser->getPrivilegeChecker()->isCanConfirmStateMdaNominalRollUpload()
                    , $loggedInUser->getId());

                //send message to MIS HEAD
                $misHeads = $this->get('app.shared_data_manager')->getFCCUserByRole(AppConstants::ROLE_MIS_HEAD);
                if ($misHeads) {
                    $phones = array();
                    foreach ($misHeads as $misHead) {
                        $phones[] = $misHead['primary_phone'];
                    }
                    $phoneNumbers = implode(',', $phones);

                    $message = $loggedInUser->getDisplayName() . " has confirmed some submissions for your approval.";
                    $this->get('app.notification_sender_service')->sendSms($message, "FCC PORTAL", $phoneNumbers);
                }

                $this->addFlash('success', 'Selected Submissions have been confirmed successfully');

            } catch (AppException $appExc) {
                $this->addFlash('danger', 'Confirmations could not be processed at the moment, Please try again later.');
                $this->get('logger')->info($appExc->getMessage());
            }

        }

        return $this->redirectToRoute('fcc_desk_officer_nominal_roll_pending_confirmation_list');
    }

}