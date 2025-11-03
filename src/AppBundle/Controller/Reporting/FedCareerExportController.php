<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 4/20/2017
 * Time: 11:26 AM
 */

namespace AppBundle\Controller\Reporting;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use \Throwable;

class FedCareerExportController extends Controller
{

    /**
     * @Route("/reporting/export/federal_level_single/{organization}/{submissionYear}", name="export_fed_level_career_dist_single_establishment")
     */
    public function careerDistributionExportAction($organization,$submissionYear)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        //use a search filter later
        $searchOrganization = $organization;
        $searchSubmissionYear = $submissionYear;

        $report = null;

        if ($searchOrganization && $searchSubmissionYear) {
            try {
                $report = $this->get('app.career_distribution_service')->getFederalLevelPostDistributionReport('SINGLE_ORGANIZATION', $searchOrganization, $searchSubmissionYear);
            } catch (Throwable $t) {
                $this->get('logger')->info('CAREER DIST: ERROR: ' . $t->getMessage());
            }
        }

        $html = $this->renderView('reporting/exports/pdf_fed_level_career_dist_single_establishment.html.twig',
            array(
                'report' => $report,
                'searchOrganization' => $searchOrganization,
                'searchSubmissionYear' => $searchSubmissionYear
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
                'Content-Disposition'   => 'attachment; filename="fed_mda_career_dist.pdf"'
            )
        );

    }

    /**
     * @Route("/reporting/export/fed_level_career_dist_min_consolidated/{submissionYear}", name="export_fed_level_career_dist_min_consolidated")
     */
    public function federalMinistryConsolidated($submissionYear)
    {

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

        if ($searchSubmissionYear) {
            try {
                $report = $this->get('app.career_distribution_service')->getFederalLevelPostDistributionReport('CONSOLIDATED_MINISTRY', null, $searchSubmissionYear);
            } catch (Throwable $t) {
                $this->get('logger')->info('CAREER DIST: ERROR: ' . $t->getMessage());
            }
        }

        $html = $this->renderView('reporting/exports/pdf_fed_level_career_dist_min_consolidated.html.twig',
            array(
                'report' => $report,
                'searchSubmissionYear' => $searchSubmissionYear
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
                'Content-Disposition'   => 'attachment; filename="fed_ministry_consolidated.pdf"'
            )
        );

    }

    /**
     * @Route("/reporting/export/fed_level_career_dist_parastatal_consolidated/{submissionYear}", name="export_fed_level_career_dist_parastatal_consolidated")
     */
    public function federalParastatalConsolidated($submissionYear)
    {

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

        if ($searchSubmissionYear) {
            try {
                $report = $this->get('app.career_distribution_service')->getFederalLevelPostDistributionReport('CONSOLIDATED_PARASTATAL', null, $searchSubmissionYear);
            } catch (Throwable $t) {
                $this->get('logger')->info('CAREER DIST: ERROR: ' . $t->getMessage());
            }
        }


        $html = $this->renderView('reporting/exports/pdf_fed_level_career_dist_parastatal_consolidated.html.twig',
            array(
                'report' => $report,
                'searchSubmissionYear' => $searchSubmissionYear
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
                'Content-Disposition'   => 'attachment; filename="fed_parastatal_consolidated.pdf"'
            )
        );

    }

    /**
     * @Route("/reporting/export/fed_level_comparative_data/{startYear}/{endYear}", name="export_fed_level_comparative_data")
     */
    public function exportFederalLevelComparativeDataAction($startYear,$endYear)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        //use a search filter later
        $searchStartYear = $startYear;
        $searchEndYear = $endYear;

        $report = null;

        //include search state here
        if ($searchStartYear && $searchEndYear && ($searchStartYear < $searchEndYear)) {
            try {
                $report = $this->get('app.comparative_data_report')->getReport(null, null, $searchStartYear, $searchEndYear);
            } catch (Throwable $t) {
                $this->get('logger')->info('Comparative Dist Error: ' . $t->getMessage());
            }
        }


        $html = $this->renderView('reporting/exports/pdf_fed_level_comparative_manpower_report.html.twig',
            array(
                'report' => $report,
                'searchStartYear' => $searchStartYear,
                'searchEndYear' => $searchEndYear
            ));

        $exportPdfOptions = [
            'page-size' => 'A4',
            'orientation' => 'Portrait'
        ];

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html, $exportPdfOptions),
            200,
            array(
                'Content-Type'          => 'application/pdf',
                'Content-Disposition'   => 'attachment; filename="fed_comparative_data.pdf"'
            )
        );

    }


    /**
     * @Route("/reporting/export/federal_level_character_balancing_index/{numberToBeRecruited}/{gradeLevelCategory}/{organization}"
     * , name="export_federal_level_character_balancing_index")
     */
    public function characterBalancingIndexAction($numberToBeRecruited,$gradeLevelCategory,$organization)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->getUser();

        $report = null;

        $searchNumberToBeRecruited = $numberToBeRecruited;
        $searchOrganization = $organization;
        $searchGradeLevelCategory = $gradeLevelCategory;

        if ($searchNumberToBeRecruited && $searchOrganization && $searchGradeLevelCategory) {
            try {
                $report = $this->get('app.character_balancing_index')->getCBIndexDistribution($searchNumberToBeRecruited, $searchGradeLevelCategory, $searchOrganization);
            } catch (Throwable $t) {
                $this->get('logger')->info('EXPORT CHARACTER BALANCING INDEX: ERROR: ' . $t->getMessage());
            }
        }

        $html = $this->renderView('reporting/exports/pdf_fed_level_character_balancing_index.html.twig',
            array(
                'report' => $report,
                'searchNumberToBeRecruited' => $searchNumberToBeRecruited,
                'searchOrganization' => $searchOrganization,
                'searchGradeLevelCategory' => $searchGradeLevelCategory
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
                'Content-Disposition'   => 'attachment; filename="fed_cb_index.pdf"'
            )
        );
    }

}