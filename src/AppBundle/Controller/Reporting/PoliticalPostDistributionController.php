<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:17 PM
 */

namespace AppBundle\Controller\Reporting;


use AppBundle\Model\Reporting\ReportStagesFetchParam;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use \Throwable;

class PoliticalPostDistributionController extends Controller
{

    /**
     * @Route("/reporting/fed_level_poloh_dist_consolidated", name="fed_level_poloh_dist_consolidated")
     */
    public function federalMinistryConsolidated(Request $request)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        //use a search filter later
        $searchSubmissionYear = $request->request->get('searchSubmissionYear');

        $report = null;

        if ($searchSubmissionYear) {
            try {
                $fetchParam = new ReportStagesFetchParam();
                $fetchParam->setFetchStage2(true);
                $report = $this->get('app.fed_level_poloh_distribution_service')->getFederalLevelPOLOHDistributionReport('CONSOLIDATED_MINISTRY_PARASTATAL', null, $searchSubmissionYear, $fetchParam);
            } catch (Throwable $t) {
                $this->get('logger')->info('CONS POLIT DIST: ERROR: ' . $t->getMessage());
            }
        }

        $submissionYears = $this->get('app.shared_data_manager')->getSubmissionYears();


        return $this->render('reporting/fed_level_poloh_dist_consolidated.html.twig',
            array(
                'report' => $report,
                'submissionYears' => $submissionYears,
                'searchSubmissionYear' => $searchSubmissionYear
            ));

    }

    /**
     * @Route("/reporting/fed_level_poloh_dist_zonal_consolidated", name="fed_level_poloh_dist_zonal_consolidated")
     */
    public function federalMinistryZonalConsolidated(Request $request)
    {


        $mode = $request->query->get('display_mode', 'default');

        if ($mode != 'embed') {
            if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
                throw $this->createAccessDeniedException();
            }

            $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
            if (!$loggedInUser) {
                throw $this->createAccessDeniedException();
            }
        }

        $zonalFedPolohConsolidatedReport = null;

        try {

            $reportService = $this->get('app.fed_level_poloh_distribution_service');

            $fetchParam = new ReportStagesFetchParam();
            $fetchParam->setFetchStage3(true);

            $searchSubmissionYear = $request->request->get('searchSubmissionYear');

            if ($mode == 'embed') {
                //$searchSubmissionYear = $reportService->getTopProcessedYear();
                $searchSubmissionYear = date('Y') - 1;
            }

            //$zonalFedPolohConsolidatedReport = $reportService->getFederalLevelPOLOHDistributionReport('CONSOLIDATED_MINISTRY_PARASTATAL', null, $searchSubmissionYear, $fetchParam);
            if($mode == 'embed'){
                $zonalFedPolohConsolidatedReport = $reportService->getExternalFederalLevelPOLOHDistributionReport();
            }
        } catch (Throwable $t) {
            $this->get('logger')->info('CONS POLIT DIST: ERROR: ' . $t->getMessage());
        }


        if ($mode == 'embed') {
            return $this->render('reporting/political_office/fed_level_poloh_dist_zonal_consolidated_embedded.html.twig',
                array(
                    'zonalFedPolohConsolidatedReport' => $zonalFedPolohConsolidatedReport
                ));
        } else {
            return new Response("REPORT COULD NOT BE DISPLAYED AT THE MOMENT");
        }

    }

    /**
     * @Route("/reporting/fed_level_poloh_by_position_and_year_dist", name="fed_level_poloh_by_position_and_year_dist")
     */
    public function federalLevelPOLOHByPositionAndYear(Request $request)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->getUser();

        //use a search filter later
        $searchPosition = $request->request->get('searchPosition');
        $searchSubmissionYear = $request->request->get('searchSubmissionYear');

        $report = null;

        if ($searchPosition && $searchSubmissionYear) {
            try {
                $report = $this->get('app.fed_level_poloh_by_position_and_year_distribution_service')
                    ->getFederalLevelPOLOHByPositionAndYearDist($searchPosition, $searchSubmissionYear);
            } catch (Throwable $t) {
                $this->get('logger')->info('POL DIST BY POS AND YEAR: ERROR: ' . $t->getMessage());
            }
        }

        $sharedDataManager = $this->get('app.shared_data_manager');
        $submissionYears = $sharedDataManager->getSubmissionYears();
        $politicalPositions = $sharedDataManager->getPoliticalGradeLevels();


        return $this->render('reporting/fed_level_poloh_by_position_and_year_dist.html.twig',
            array(
                'report' => $report,
                'politicalPositions' => $politicalPositions,
                'submissionYears' => $submissionYears,
                'searchPosition' => $searchPosition,
                'searchSubmissionYear' => $searchSubmissionYear
            ));

    }

    /**
     * @Route("/reporting/fed_level_ceo_list", name="fed_level_ceo_list")
     */
    public function federalCeoReportAction(Request $request)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->getUser();

        $report = null;

        if ($request->request->has('btnSearch')) {
            try {
                $report = $this->get('app.list_of_federal_ceo')->getFederalCeos($this->get('logger'));
            } catch (Throwable $t) {
                $this->get('logger')->info('LIST OF FED CEOs: ' . $t->getMessage());
            }
        }

        $sharedDataManager = $this->get('app.shared_data_manager');
        $committees = $sharedDataManager->getCommittees();


        return $this->render('reporting/fed_level_ceo_list.html.twig',
            array(
                'report' => $report,
                'committees' => $committees,
            ));

    }

}