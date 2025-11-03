<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:17 PM
 */

namespace AppBundle\Controller\Reporting;


use AppBundle\Model\Reporting\FederalLevel\NumberOfPositionDataAnalysis;
use AppBundle\Model\Reporting\FederalLevelPostDistributionAnalysis;
use AppBundle\Model\Security\SecurityUser;
use AppBundle\Utils\AppConstants;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use \Throwable;

class ProportionDistributionController extends Controller
{

    /**
     * @Route("/reporting/proportion_of_position/fcsc", name="proportion_of_position_fcsc")
     */
    public function fcscReportAction(Request $request)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        //use a search filter later
        $searchStartYear = $request->request->get('searchStartYear');
        $searchEndYear = $request->request->get('searchEndYear');

        $report = null;

        //include search state here
        if ($searchStartYear && $searchEndYear && ($searchStartYear <= $searchEndYear)) {
            try {
                $report = $this->get('app.proportion_of_positions')->getFCSCReport($searchStartYear, $searchEndYear);
            } catch (Throwable $t) {
                $this->get('logger')->info('FCSC Report: ' . $t->getMessage());
            }
        }

        /*$totalStage2 = -1;
        if($report){
            $totalStage2 = count($report->getStage2()->getStage2Data());
        }*/

        $submissionYears = $this->get('app.shared_data_manager')->getSubmissionYears();


        return $this->render('reporting/proportion_of_position_fcsc.html.twig',
            array(
                'report' => $report,
                'submissionYears' => $submissionYears,
                'searchStartYear' => $searchStartYear,
                'searchEndYear' => $searchEndYear
            ));

    }





    /**
     * @Route("/reporting/proportion_of_position/lgsc", name="proportion_of_position_lgsc")
     */
    public function lgscReportAction(Request $request)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        //use a search filter later
        $searchStartYear = $request->request->get('searchStartYear');
        $searchEndYear = $request->request->get('searchEndYear');

        $report = null;

        //include search state here
        if ($searchStartYear && $searchEndYear && ($searchStartYear <= $searchEndYear)) {
            /*try {
                $report = $this->get('app.proportion_of_positions')->getLGSCReport($searchStartYear, $searchEndYear);
            } catch (Throwable $t) {
                $this->get('logger')->info('LGSC Report: ' . $t->getMessage());
            }*/
        }

        $submissionYears = $this->get('app.shared_data_manager')->getSubmissionYears();


        return $this->render('reporting/proportion_of_position_lgsc.html.twig',
            array(
                'report' => $report,
                'submissionYears' => $submissionYears,
                'searchStartYear' => $searchStartYear,
                'searchEndYear' => $searchEndYear
            ));
    }


    /**
     * @Route("/reporting/proportion_of_position/nuc", name="proportion_of_position_nuc")
     */
    public function nucReportAction(Request $request)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        //use a search filter later
        $searchStartYear = $request->request->get('searchStartYear');
        $searchEndYear = $request->request->get('searchEndYear');

        $report = null;

        //include search state here
        if ($searchStartYear && $searchEndYear && ($searchStartYear <= $searchEndYear)) {
            try {
                $report = $this->get('app.proportion_of_positions')->getNUCReport($searchStartYear, $searchEndYear);
            } catch (Throwable $t) {
                $this->get('logger')->info('NUC Report: ' . $t->getMessage());
            }
        }


        $submissionYears = $this->get('app.shared_data_manager')->getSubmissionYears();


        return $this->render('reporting/proportion_of_position_nuc.html.twig',
            array(
                'report' => $report,
                'submissionYears' => $submissionYears,
                'searchStartYear' => $searchStartYear,
                'searchEndYear' => $searchEndYear
            ));
    }

    /**
     * @Route("/reporting/submission/summary", name="submission_summary")
     */
    public function submissionSummaryAction(Request $request)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        //use a search filter later
        $searchStartYear = $request->request->get('searchStartYear');
        $searchEndYear = $request->request->get('searchEndYear');

        $report = null;

        //include search state here
        if ($searchStartYear && $searchEndYear && ($searchStartYear <= $searchEndYear)) {
            try {
                $report = $this->get('app.submission_summary')->getSubmissionSummaryReport($searchStartYear, $searchEndYear);
                $this->get('logger')->info('Submission Summary: EXECUTED');
            } catch (Throwable $t) {
                $this->get('logger')->info('Submission Summary: ' . $t->getMessage());
            }
        }

        $submissionYears = $this->get('app.shared_data_manager')->getSubmissionYears();


        return $this->render('reporting/submission_summary.html.twig',
            array(
                'report' => $report,
                'submissionYears' => $submissionYears,
                'searchStartYear' => $searchStartYear,
                'searchEndYear' => $searchEndYear
            ));
    }

}