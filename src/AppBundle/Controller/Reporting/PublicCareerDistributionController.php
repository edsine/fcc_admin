<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:17 PM
 */

namespace AppBundle\Controller\Reporting;


use AppBundle\Model\Reporting\FederalLevelPostDistributionAnalysis;
use AppBundle\Utils\AppConstants;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use \Throwable;

class PublicCareerDistributionController extends Controller
{
    /**
     * @Route("/public_reporting/federal_level_single", name="public_fed_level_career_dist_single_establishment")
     */
    public function publicCareerDistributionAction(Request $request){

        //use a search filter later
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

        /*$totalStage2 = -1;
        if($report){
            $totalStage2 = count($report->getStage2()->getStage2Data());
        }*/

        $organizations = $this->get('app.shared_data_manager')->getOrganizationByType2("FEDERAL");
        $submissionYears = $this->get('app.shared_data_manager')->getSubmissionYears();



        return $this->render('reporting/public_fed_level_career_dist_single_establishment.html.twig',
            array(
                'report' => $report,
                'organizations' => $organizations,
                'submissionYears' => $submissionYears,
                'searchOrganization' => $searchOrganization,
                'searchSubmissionYear' => $searchSubmissionYear
            ));

    }

}