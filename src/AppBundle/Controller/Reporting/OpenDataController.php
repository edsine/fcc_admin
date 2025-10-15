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
use \Throwable;

class OpenDataController extends Controller
{

    /**
     * @Route("/open_data", name="open_data")
     */
    public function openDataIndexAction(Request $request)
    {

        $zonalFedPolohConsolidatedReport = array();

        try {

            $reportService = $this->get('app.fed_level_poloh_distribution_service');

            $fetchParam = new ReportStagesFetchParam();
            $fetchParam->setFetchStage3(true);

            $searchSubmissionYear = $request->request->get('searchSubmissionYear');

            if (!$searchSubmissionYear) {
                $searchSubmissionYear = date('Y') - 1;
            }

            $zonalFedPolohConsolidatedReport = $reportService->getExternalFederalLevelPOLOHDistributionReport();
        } catch (Throwable $t) {
            $this->get('logger')->info('CONS POLIT DIST: ERROR: ' . $t->getMessage());
        }

        $organizations = $this->get('app.shared_data_manager')->getFederalOrganizationsForOpenData("FEDERAL", false);
        //$submissionYears = $this->get('app.shared_data_manager')->getSubmissionYears();
        $submissionYears = array();

        return $this->render('reporting/open_data/open_data_index.html.twig',
            array(
                'organizations' => $organizations,
                'submissionYears' => $submissionYears,
                'zonalFedPolohConsolidatedReport' => $zonalFedPolohConsolidatedReport
            ));

    }

    /**
     * @Route("/open_data/federal_level_single", name="open_data_fed_level_career_dist_single_establishment")
     */
    public function openDataFedMdaCareerDistributionAction(Request $request){

        $searchOrganization = $request->request->get('searchOrganization');
        $searchSubmissionYear = $request->request->get('searchSubmissionYear');

        $report = null;

        if($searchOrganization && $searchSubmissionYear){
            try{
                $report = $this->get('app.career_distribution_service')->getFederalLevelPostDistributionReport('SINGLE_ORGANIZATION',$searchOrganization, $searchSubmissionYear);
            }catch(Throwable $t){
                $this->get('logger')->info('CAREER DIST: ERROR: ' . $t->getMessage());
            }
        }

        $organizations = $this->get('app.shared_data_manager')->getFederalOrganizationsForOpenData("FEDERAL", false);
        //$submissionYears = $this->get('app.shared_data_manager')->getSubmissionYears();
        $submissionYears = $this->get('app.shared_data_service')->getSubmissionYearsProcessedForOrganization($searchOrganization);

        return $this->render('reporting/open_data/open_data_fed_level_career_dist_single_establishment.html.twig',
            array(
                'report' => $report,
                'organizations' => $organizations,
                'submissionYears' => $submissionYears,
                'searchOrganization' => $searchOrganization,
                'searchSubmissionYear' => $searchSubmissionYear
            ));
    }

}