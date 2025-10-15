<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/27/2017
 * Time: 3:01 PM
 */

namespace AppBundle\Controller\Reporting;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PrintableViewController extends Controller
{

    /**
     * @Route("/reporting/print_fed_level_career_dist_single_establishment/{organization}/{submissionYear}", name="print_fed_level_career_dist_single_establishment")
     */
    public function printFedLevelCareerDistSingleEst(Request $request, $organization, $submissionYear){

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->getUser();

        //use a search filter later
        $searchOrganization = $organization;
        $searchSubmissionYear = $submissionYear;

        $report = null;

        if($searchOrganization && $searchSubmissionYear){
            try{
                $report = $this->get('app.career_distribution_service')->getFederalLevelPostDistributionReport('SINGLE_ORGANIZATION',$searchOrganization, $searchSubmissionYear);
            }catch(\Throwable $t){
                $this->get('logger')->info('CAREER DIST: ERROR: ' . $t->getMessage());
            }
        }

        return $this->render('reporting/print_fed_level_career_dist_single_establishment.html.twig',
            array(
                'report' => $report
            ));

    }

    /**
     * @Route("/reporting/print_fed_level_career_dist_ministry_consolidated/{submissionYear}", name="print_fed_level_career_dist_ministry_consolidated")
     */
    public function printFedLevelCareerDistMinistryConsolidated($submissionYear){

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->getUser();

        $searchSubmissionYear = $submissionYear;

        $report = null;

        if($searchSubmissionYear){
            try{
                $report = $this->get('app.career_distribution_service')->getFederalLevelPostDistributionReport('CONSOLIDATED_MINISTRY',null, $searchSubmissionYear);
            }catch(\Throwable $t){
                $this->get('logger')->info('CAREER DIST: ERROR: ' . $t->getMessage());
            }
        }

        return $this->render('reporting/print_fed_level_career_dist_min_consolidated.html.twig',
            array(
                'report' => $report
            ));

    }

    /**
     * @Route("/reporting/print_fed_level_career_dist_parastatal_consolidated/{submissionYear}", name="print_fed_level_career_dist_parastatal_consolidated")
     */
    public function printFedLevelCareerDistParastatalConsolidated($submissionYear){

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->getUser();

        $searchSubmissionYear = $submissionYear;

        $report = null;

        if($searchSubmissionYear){
            try{
                $report = $this->get('app.career_distribution_service')->getFederalLevelPostDistributionReport('CONSOLIDATED_PARASTATAL',null, $searchSubmissionYear);
            }catch(\Throwable $t){
                $this->get('logger')->info('CAREER DIST: ERROR: ' . $t->getMessage());
            }
        }

        return $this->render('reporting/print_fed_level_career_dist_parastatal_consolidated.html.twig',
            array(
                'report' => $report
            ));

    }

}