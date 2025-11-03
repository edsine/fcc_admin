<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/7/2017
 * Time: 4:29 PM
 */

namespace AppBundle\Controller\MisHead;


use AppBundle\AppException\AppException;
use Doctrine\DBAL\DBALException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class FixSubmissionIssuesController extends Controller
{
    /**
     * @Route("/secure_area/federal/mis_head/fix/submission/issues"
     * , name="mis_head_fix_submission_issues")
     */
    public function showFixIssuesAction()
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('mis_head/fix_submission_issues.html.twig',[]);
    }

    /**
     * @Route("/secure_area/mis_head/clear_fatal_errors"
     * , name="mis_head_clear_fatal_errors")
     */
    public function clearFatalErrorsSubmissionAction()
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $mdaSubmissionService = $this->get('app.federal_nominal_roll_service');
        try {
            $outcome = $mdaSubmissionService->clearFatalErrorSubmissions();
            if($outcome){
                $this->addFlash('success', "Fatal error submissions have been cleared successfully");
            }
        } catch (AppException | DBALException $e) {
            $this->addFlash('danger', "An error occurred while clearing fatal error submissions");
            $this->get('logger')->alert($e->getMessage());
        }

        return $this->redirectToRoute('mis_head_fix_submission_issues', []);
    }

    /**
     * @Route("/secure_area/mis_head/reset_hanging_report_analysis"
     * , name="mis_head_reset_hanging_report_analysis")
     */
    public function resetHangingReportAnalysisAction()
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $misHeadNominalRollService = $this->get('app.mis_head_submission_service');
        try {
            $outcome = $misHeadNominalRollService->resetHangingReportAnalysis();
            if($outcome){
                $this->addFlash('success', "Hanging report processing reset successfully");
            }
        } catch (AppException | DBALException $e) {
            $this->addFlash('danger', "An error occurred while resetting hanging report analysis");
            $this->get('logger')->alert($e->getMessage());
        }

        return $this->redirectToRoute('mis_head_fix_submission_issues', []);
    }

}