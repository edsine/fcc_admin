<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:17 PM
 */

namespace AppBundle\Controller\Reporting;


use AppBundle\Model\Reporting\FederalLevelPostDistributionAnalysis;
use AppBundle\Model\Security\SecurityUser;
use AppBundle\Utils\AppConstants;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use \Throwable;

class CareerDistributionController extends Controller
{
    /**
     * @Route("/reporting/federal_level_single", name="fed_level_career_dist_single_establishment")
     */
    public function careerDistributionAction(Request $request)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var SecurityUser $loggedInUser
         */
        $loggedInUser = $this->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        //use a search filter later
        $searchOrganization = $request->request->get('searchOrganization');
        $searchSubmissionYear = $request->request->get('searchSubmissionYear');

        $report = null;

        if ($searchOrganization && $searchSubmissionYear) {
            try {
                $report = $this->get('app.career_distribution_service')->getFederalLevelPostDistributionReport('SINGLE_ORGANIZATION', $searchOrganization, $searchSubmissionYear);
            } catch (Throwable $t) {
                $this->get('logger')->info('CAREER DIST: ERROR: ' . $t->getMessage());
            }
        }

        /*$totalStage2 = -1;
        if($report){
            $totalStage2 = count($report->getStage2()->getStage2Data());
        }*/

        $organizations = array();
        if ($loggedInUser->getPrivilegeChecker()->isCanSelectAllMdas()
            || $loggedInUser->getPrivilegeChecker()->isSuperAdmin()
            || $loggedInUser->getPrivilegeChecker()->isMisHead()
        ) {
            $organizations = $this->get('app.shared_data_manager')->getOrganizationByType2("FEDERAL");
        } else if ($loggedInUser->getPrivilegeChecker()->isCanSelectOnlyCommitteeMdas()) {
            $organizations = $this->get('app.shared_data_manager')->getOrganizationByType2AndCommittee("FEDERAL", $loggedInUser->getFccCommitteeId());
        } else if ($loggedInUser->getPrivilegeChecker()->isCanSelectOnlyAssignedMdas()) {
            $organizations = $this->get('app.shared_data_manager')->getOrganizationByType2AndDeskOfficer("FEDERAL", $loggedInUser->getId());
        } else if ($loggedInUser->getPrivilegeChecker()->isCanSelectOnlyUserMda()) {
            $organizations = $this->get('app.shared_data_manager')->getOrganizationById($loggedInUser->getOrganizationId());
        }


        $submissionYears = $this->get('app.shared_data_manager')->getSubmissionYears();


        return $this->render('reporting/fed_level_career_dist_single_establishment.html.twig',
            array(
                'report' => $report,
                'organizations' => $organizations,
                'submissionYears' => $submissionYears,
                'searchOrganization' => $searchOrganization,
                'searchSubmissionYear' => $searchSubmissionYear
            ));

    }

    /**
     * @Route("/reporting/fed_level_career_dist_min_consolidated", name="fed_level_career_dist_min_consolidated")
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
                $report = $this->get('app.career_distribution_service')->getFederalLevelPostDistributionReport('CONSOLIDATED_MINISTRY', null, $searchSubmissionYear);
            } catch (Throwable $t) {
                $this->get('logger')->info('CAREER DIST: ERROR: ' . $t->getMessage());
            }
        }

        $submissionYears = $this->get('app.shared_data_manager')->getSubmissionYears();


        return $this->render('reporting/fed_level_career_dist_min_consolidated.html.twig',
            array(
                'report' => $report,
                'submissionYears' => $submissionYears,
                'searchSubmissionYear' => $searchSubmissionYear
            ));

    }

    /**
     * @Route("/reporting/fed_level_career_dist_parastatal_consolidated", name="fed_level_career_dist_parastatal_consolidated")
     */
    public function federalParastatalConsolidated(Request $request)
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
                $report = $this->get('app.career_distribution_service')->getFederalLevelPostDistributionReport('CONSOLIDATED_PARASTATAL', null, $searchSubmissionYear);
            } catch (Throwable $t) {
                $this->get('logger')->info('CAREER DIST: ERROR: ' . $t->getMessage());
            }
        }

        $submissionYears = $this->get('app.shared_data_manager')->getSubmissionYears();


        return $this->render('reporting/fed_level_career_dist_parastatal_consolidated.html.twig',
            array(
                'report' => $report,
                'submissionYears' => $submissionYears,
                'searchSubmissionYear' => $searchSubmissionYear
            ));

    }


    /**
     * @Route("/reporting/state_level_career_dist_single_establishment", name="state_level_career_dist_single_establishment")
     */
    public function stateLevelCareerDistributionAction(Request $request)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        //use a search filter later
        $searchState = $request->request->get('searchState');
        $searchOrganization = $request->request->get('searchOrganization');
        $searchSubmissionYear = $request->request->get('searchSubmissionYear');

        $report = null;

        //include search state here
        if ($searchOrganization && $searchSubmissionYear) {
            try {
                $report = $this->get('app.state_level_career_distribution_service')->getStateLevelPostDistributionReport($searchOrganization, $searchSubmissionYear);
            } catch (Throwable $t) {
                $this->get('logger')->info('CAREER DIST: ERROR: ' . $t->getMessage());
            }
        }

        /*$totalStage2 = -1;
        if($report){
            $totalStage2 = count($report->getStage2()->getStage2Data());
        }*/

        $nigerianStates = $this->get('app.shared_data_manager')->getNigerianStates();
        $organizations = $this->get('app.shared_data_manager')->getOrganizationByType2("STATE");
        $submissionYears = $this->get('app.shared_data_manager')->getSubmissionYears();


        return $this->render('reporting/state_level_career_distribution.html.twig',
            array(
                'report' => $report,
                'nigerianStates' => $nigerianStates,
                'organizations' => $organizations,
                'submissionYears' => $submissionYears,
                'searchState' => $searchState,
                'searchOrganization' => $searchOrganization,
                'searchSubmissionYear' => $searchSubmissionYear
            ));

    }

    /**
     * @Route("/reporting/fed_level_comparative_data", name="fed_level_comparative_data")
     */
    public function federalLevelComparativeData(Request $request)
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
        if ($searchStartYear && $searchEndYear && ($searchStartYear < $searchEndYear)) {
            try {
                $report = $this->get('app.comparative_data_report')->getReport(null, null, $searchStartYear, $searchEndYear);
            } catch (Throwable $t) {
                $this->get('logger')->info('Comparative Dist Error: ' . $t->getMessage());
            }
        }

        /*$totalStage2 = -1;
        if($report){
            $totalStage2 = count($report->getStage2()->getStage2Data());
        }*/

        $submissionYears = $this->get('app.shared_data_manager')->getSubmissionYears();


        return $this->render('reporting/fed_level_comparative_manpower_report.html.twig',
            array(
                'report' => $report,
                'submissionYears' => $submissionYears,
                'searchStartYear' => $searchStartYear,
                'searchEndYear' => $searchEndYear
            ));

    }


    /**
     * @Route("/reporting/affected_fed_level_comparative_mda_data/{startYear}/{endYear}/{organizationCategory}", name="affected_fed_level_comparative_mda_data", defaults={"organizationCategory":""})
     */
    public function affectedFederalLevelComparativeMDAData(Request $request, $startYear, $endYear, $organizationCategory)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }


        $report = null;

        //include search state here
        if ($startYear && $endYear && ($startYear < $endYear)) {
            try {
                $report = $this->get('app.affected_mda_report')->getAffectedFedMdaComparativeReport($startYear, $endYear, $organizationCategory);
            } catch (Throwable $t) {
                $this->get('logger')->info('Affected FED MDA Comparative Dist Error: ' . $t->getMessage());
            }
        }


        return $this->render('reporting/affected_fed_level_comparative_mda_manpower_report.html.twig',
            array(
                'report' => $report,
                'startYear' => $startYear,
                'endYear' => $endYear
            ));

    }


    /**
     * @Route("/reporting/federal_level_character_balancing_index", name="federal_level_character_balancing_index")
     */
    public function characterBalancingIndexAction(Request $request)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var SecurityUser $loggedInUser
         */
        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        //use a search filter later
        $searchNumberToBeRecruited = $request->request->get('searchRecruitmentValue');
        $searchGradeLevelCategory = $request->request->get('searchGradeLevelCategory');
        $searchOrganization = $request->request->get('searchOrganization');

        $report = null;

        if ($searchNumberToBeRecruited && $searchOrganization && $searchGradeLevelCategory) {
            try {
                $report = $this->get('app.character_balancing_index')->getCBIndexDistribution($searchNumberToBeRecruited, $searchGradeLevelCategory, $searchOrganization);
            } catch (Throwable $t) {
                $this->get('logger')->info('CHARACTER BALANCING INDEX: ERROR: ' . $t->getMessage());
            }
        }

        $organizations = array();
        if ($loggedInUser->getPrivilegeChecker()->isCanSelectAllMdas()
            || $loggedInUser->getPrivilegeChecker()->isSuperAdmin()
            || $loggedInUser->getPrivilegeChecker()->isMisHead()
        ) {
            $organizations = $this->get('app.shared_data_manager')->getOrganizationByType2("FEDERAL");
        } else if ($loggedInUser->getPrivilegeChecker()->isCanSelectOnlyCommitteeMdas()) {
            $organizations = $this->get('app.shared_data_manager')->getOrganizationByType2AndCommittee("FEDERAL", $loggedInUser->getFccCommitteeId());
        } else if ($loggedInUser->getPrivilegeChecker()->isCanSelectOnlyAssignedMdas()) {
            $organizations = $this->get('app.shared_data_manager')->getOrganizationByType2AndDeskOfficer("FEDERAL", $loggedInUser->getId());
        } else if ($loggedInUser->getPrivilegeChecker()->isCanSelectOnlyUserMda()) {
            $organizations = $this->get('app.shared_data_manager')->getOrganizationById($loggedInUser->getOrganizationId());
        }

        $sharedDataManager = $this->get('app.shared_data_manager');
        $gradeLevelCategories = $sharedDataManager->getCharacterBalancingReportGradeLevelCategories();

        return $this->render('reporting/fed_level_character_balancing_index.html.twig',
            array(
                'report' => $report,
                'organizations' => $organizations,
                'gradeLevelCategories' => $gradeLevelCategories,
                'searchNumberToBeRecruited' => $searchNumberToBeRecruited,
                'searchOrganization' => $searchOrganization,
                'searchGradeLevelCategory' => $searchGradeLevelCategory
            ));

    }


    /**
     * @Route("/reporting/federal/cbi/request/view/{cbiRequestSelector}", name="federal_cbi_request_report_show")
     */
    public function viewCharacterBalancingIndexRequestReportAction($cbiRequestSelector)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var SecurityUser $loggedInUser
         */
        $loggedInUser = $this->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        $report = null;
        $cbiReportRequest = null;
        try {
            $cbiReportRequest = $this->get('app.cbi_report_request')->getCbiReportRequest($cbiRequestSelector);

            if ($cbiReportRequest) {
                $report = $this->get('app.character_balancing_index')->getCBIndexDistribution($cbiReportRequest->getRecruitmentValue()
                    , $cbiReportRequest->getCbiGradeLevelCategory()
                    , $cbiReportRequest->getOrganizationId());
            }
        } catch (Throwable $t) {
            $this->get('logger')->alert('CBI REQUEST VIEW: ERROR: ' . $t->getMessage());
        }

        return $this->render('reporting/federal_cbi_request_report_show.html.twig',
            array(
                'cbiReportRequest' => $cbiReportRequest,
                'report' => $report
            ));

    }

}