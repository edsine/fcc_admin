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
use Symfony\Component\HttpFoundation\Response;
use \Throwable;

class FedPOLOHExportController extends Controller
{
    /**
     * @Route("/reporting/export/fed_level_poloh_dist_consolidated/{submissionYear}", name="export_fed_level_poloh_dist_consolidated")
     */
    public function federalMinistryConsolidated($submissionYear){

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        //use a search filter later
        $searchSubmissionYear = $submissionYear;

        $report = null;

        if($searchSubmissionYear){
            try{
                $fetchParam = new ReportStagesFetchParam();
                $fetchParam->setFetchStage2(true);
                $report = $this->get('app.fed_level_poloh_distribution_service')->getFederalLevelPOLOHDistributionReport('CONSOLIDATED_MINISTRY_PARASTATAL',null, $searchSubmissionYear, $fetchParam);
            }catch(Throwable $t){
                $this->get('logger')->info('CONS POLIT DIST: ERROR: ' . $t->getMessage());
            }
        }

        $html = $this->renderView('reporting/exports/pdf_fed_level_poloh_dist_consolidated.html.twig',
            array(
                'report' => $report,
                'searchSubmissionYear' => $searchSubmissionYear
            ));

        $exportPdfOptions = [
            'page-size' => 'A4',
            'no-stop-slow-scripts' => true,
            'orientation' => 'Landscape'
        ];

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html, $exportPdfOptions),
            200,
            array(
                'Content-Type'          => 'application/pdf',
                'Content-Disposition'   => 'attachment; filename="fed_poloh_dist_cons_list.pdf"'
            )
        );

    }

    /**
     * @Route("/reporting/export/fed_level_poloh_by_position_and_year_dist/{position}/{submissionYear}", name="export_fed_level_poloh_by_position_and_year_dist")
     */
    public function federalLevelPOLOHByPositionAndYear($position,$submissionYear){

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->getUser();

        //use a search filter later
        $searchPosition = $position;
        $searchSubmissionYear = $submissionYear;

        $report = null;

        if($searchPosition && $searchSubmissionYear){
            try{
                $report = $this->get('app.fed_level_poloh_by_position_and_year_distribution_service')
                    ->getFederalLevelPOLOHByPositionAndYearDist($searchPosition, $searchSubmissionYear);
            }catch(Throwable $t){
                $this->get('logger')->info('POL DIST BY POS AND YEAR: ERROR: ' . $t->getMessage());
            }
        }

        $html = $this->renderView('reporting/exports/pdf_fed_level_poloh_by_position_and_year_dist.html.twig',
            array(
                'report' => $report,
                'searchPosition' => $searchPosition,
                'searchSubmissionYear' => $searchSubmissionYear
            ));

        $exportPdfOptions = [
            'page-size' => 'A4',
            'no-stop-slow-scripts' => true,
            'orientation' => 'Landscape'
        ];

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html, $exportPdfOptions),
            200,
            array(
                'Content-Type'          => 'application/pdf',
                'Content-Disposition'   => 'attachment; filename="fed_level_poloh_by_position_and_year_dist.pdf"'
            )
        );

    }

    /**
     * @Route("/reporting/export/fed_level_ceo_list/{exportFormat}", name="export_fed_level_ceo_list_export")
     */
    public function federalCeoReportExportAction($exportFormat)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->getUser();

        $report = null;

        try {
            $report = $this->get('app.list_of_federal_ceo')->getFederalCeos($this->get('logger'));
        } catch (Throwable $t) {
            $this->get('logger')->info('LIST OF FED CEOs: ' . $t->getMessage());
        }

        $sharedDataManager = $this->get('app.shared_data_manager');
        $committees = $sharedDataManager->getCommittees();

        $html = $this->renderView('reporting/exports/pdf_fed_level_ceo_list.html.twig',
            array(
                'report' => $report,
                'committees' => $committees,
            ));

        $exportPdfOptions = [
            'page-size' => 'A4',
            'orientation' => 'Landscape'
        ];

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html, $exportPdfOptions),
            200,
            array(
                'Content-Type'          => 'application/pdf',
                'Content-Disposition'   => 'attachment; filename="ceo_list.pdf"'
            )
        );

    }

}