<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:17 PM
 */

namespace AppBundle\Controller\Reporting;


use AppBundle\AppException\AppException;
use AppBundle\Model\SearchCriteria\NominalRoleSearchCriteria;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use \Throwable;

class FederalLevelCriteriaSearchReportController extends Controller
{
    /**
     * @Route("/reporting/federal_level_criteria_search_report", name="federal_level_criteria_search_report")
     */
    public function showSubmissionAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $searchCriteria = new NominalRoleSearchCriteria();
        $searchCriteria->setSubmissionYear($request->request->get('searchSubmissionYear'));
        $searchCriteria->setOrganization($request->request->get('searchOrganization'));
        $searchCriteria->setEmployeeNumber($request->request->get('searchEmployeeNumber'));
        $searchCriteria->setName($request->request->get('searchName'));
        $searchCriteria->setNationality($request->request->get('searchNationality'));
        $searchCriteria->setStateOfOrigin($request->request->get('searchStateOfOrigin'));
        $searchCriteria->setGradeLevel($request->request->get('searchGradeLevel'));
        $searchCriteria->setDesignation($request->request->get('searchDesignation'));
        $searchCriteria->setGeoPoliticalZone($request->request->get('searchGeoPoliticalZone'));
        $searchCriteria->setGender($request->request->get('searchGender'));
        $searchCriteria->setMaritalStatus($request->request->get('searchMaritalStatus'));

        $canSearch = true;
        if($request->request->has('btnResetSearch')){
            $searchCriteria = new NominalRoleSearchCriteria();
            $canSearch = false;
        }

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

        $criteriaSearchReportService = $this->get('app.criteria_search_report');

        $records = array();
        if($request->request->has('startRow') && $canSearch){ //search only if action was clicked
            try {
                $records = $criteriaSearchReportService->searchApprovedFedLevelNominalRoll($searchCriteria, $paginator, $pageDirection);
            } catch (AppException $e) {
                $this->get('logger')->info($e->getMessage());
            }
        }

        $sharedDataManager = $this->get('app.shared_data_manager');
        $nigerianStates = $sharedDataManager->getNigerianStates();
        $organizations = $sharedDataManager->getOrganizationByType2("FEDERAL");
        $submissionYears = $sharedDataManager->getSubmissionYears();
        $gender = $sharedDataManager->getGender();
        $maritalStatus = $sharedDataManager->getMaritalStatus();
        $geoPoliticalZones = $sharedDataManager->getGeoPoliticalZones();
        $gradeLevels = array_merge($sharedDataManager->getCareerGradeLevels(),$sharedDataManager->getPoliticalGradeLevels());
        $nationalityCodes = $sharedDataManager->getNationalityCode();

        return $this->render('reporting/fed_level_criteria_search_report.html.twig',
            array(
                'records' => $records,
                'searchCriteria' => $searchCriteria,
                'paginator' => $paginator,
                'nigerianStates' => $nigerianStates,
                'organizations' => $organizations,
                'submissionYears' => $submissionYears,
                'gender' => $gender,
                'maritalStatus' => $maritalStatus,
                'geoPoliticalZones' => $geoPoliticalZones,
                'gradeLevels' => $gradeLevels,
                'nationalityCodes' => $nationalityCodes
            )
        );
    }


    /**
     * @Route("/reporting/print_federal_level_criteria_search_report", name="print_federal_level_criteria_search_report")
     */
    public function printSubmissionSearchAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $searchCriteria = new NominalRoleSearchCriteria();
        $searchCriteria->setSubmissionYear($request->request->get('searchSubmissionYear'));
        $searchCriteria->setOrganization($request->request->get('searchOrganization'));
        $searchCriteria->setEmployeeNumber($request->request->get('searchEmployeeNumber'));
        $searchCriteria->setName($request->request->get('searchName'));
        $searchCriteria->setNationality($request->request->get('searchNationality'));
        $searchCriteria->setStateOfOrigin($request->request->get('searchStateOfOrigin'));
        $searchCriteria->setGradeLevel($request->request->get('searchGradeLevel'));
        $searchCriteria->setGeoPoliticalZone($request->request->get('searchGeoPoliticalZone'));
        $searchCriteria->setGender($request->request->get('searchGender'));
        $searchCriteria->setMaritalStatus($request->request->get('searchMaritalStatus'));

        $canSearch = true;
        if($request->request->has('btnResetSearch')){
            $searchCriteria = new NominalRoleSearchCriteria();
            $canSearch = false;
        }

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

        $criteriaSearchReportService = $this->get('app.criteria_search_report');

        $records = array();
        if($request->request->has('startRow') && $canSearch){ //search only if action was clicked
            try {
                $records = $criteriaSearchReportService->searchApprovedFedLevelNominalRoll($searchCriteria, $paginator, $pageDirection, false);
            } catch (AppException $e) {
                $this->get('logger')->info($e->getMessage());
            }
        }

        $sharedDataManager = $this->get('app.shared_data_manager');
        $nigerianStates = $sharedDataManager->getNigerianStates();
        $organizations = $sharedDataManager->getOrganizationByType2("FEDERAL");
        $submissionYears = $sharedDataManager->getSubmissionYears();
        $gender = $sharedDataManager->getGender();
        $maritalStatus = $sharedDataManager->getMaritalStatus();
        $geoPoliticalZones = $sharedDataManager->getGeoPoliticalZones();
        $gradeLevels = array_merge($sharedDataManager->getCareerGradeLevels(),$sharedDataManager->getPoliticalGradeLevels());
        $nationalityCodes = $sharedDataManager->getNationalityCode();

        return $this->render('reporting/print_fed_level_criteria_search_report.html.twig',
            array(
                'records' => $records,
                'searchCriteria' => $searchCriteria,
                'paginator' => $paginator,
                'nigerianStates' => $nigerianStates,
                'organizations' => $organizations,
                'submissionYears' => $submissionYears,
                'gender' => $gender,
                'maritalStatus' => $maritalStatus,
                'geoPoliticalZones' => $geoPoliticalZones,
                'gradeLevels' => $gradeLevels,
                'nationalityCodes' => $nationalityCodes
            )
        );
    }

}