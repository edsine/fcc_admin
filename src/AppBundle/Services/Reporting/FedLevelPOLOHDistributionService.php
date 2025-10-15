<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:43 PM
 */

namespace AppBundle\Services\Reporting;


use AppBundle\AppException\AppException;
use AppBundle\Model\Reporting\FederalLevel\POLOHDistributionAnalysis;
use AppBundle\Model\Reporting\FederalLevel\POLOHDistStage1Analysis;
use AppBundle\Model\Reporting\FederalLevel\POLOHDistStage1Entry;
use AppBundle\Model\Reporting\FederalLevel\POLOHDistStage2Analysis;
use AppBundle\Model\Reporting\FederalLevel\POLOHDistStage2Entry;
use AppBundle\Model\Reporting\FederalLevel\POLOHDistStage3Analysis;
use AppBundle\Model\Reporting\FederalLevel\POLOHDistStage3Entry;
use AppBundle\Model\Reporting\ReportStagesFetchParam;
use AppBundle\Utils\AppConstants;
use Doctrine\DBAL\Connection;
use \Throwable;

class FedLevelPOLOHDistributionService
{
    private $connection;
    private $rows_per_page;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->rows_per_page = AppConstants::ROWS_PER_PAGE;
    }

    /**
     * @param $whichMode
     * @param $organizationId
     * @param $submissionYear
     * @return POLOHDistributionAnalysis
     * @throws AppException
     */
    public function getFederalLevelPOLOHStage1Distribution($whichMode, $organizationId, $submissionYear): POLOHDistributionAnalysis
    {
        $report = null;

        $statement = null;

        try {

            $exists = null;

            switch ($whichMode) {
                case 'SINGLE_ORGANIZATION':

                    $query = "SELECT * FROM federal_level_nominal_roll_submissions "
                        . "WHERE organization_id=:organization_id AND submission_year=:submission_year AND processing_status=:processing_status LIMIT 1";
                    $statement = $this->connection->prepare($query);
                    $statement->bindValue(':organization_id', $organizationId);
                    $statement->bindValue(':submission_year', $submissionYear);
                    $statement->bindValue(':processing_status', AppConstants::COMPLETED);
                    $statement->execute();
                    $exists = $statement->fetch();

                    break;

                case 'CONSOLIDATED_MINISTRY':
                    $query = "SELECT d.submission_year FROM federal_level_nominal_roll_submissions d "
                        . " JOIN organization o ON d.organization_id = o.id "
                        . "WHERE o.establishment_type_id=:establishment_type_id AND submission_year=:submission_year AND processing_status=:processing_status LIMIT 1";
                    $statement = $this->connection->prepare($query);
                    $statement->bindValue(':establishment_type_id', AppConstants::FEDERAL_MINISTRY_ESTABLISHMENT);
                    $statement->bindValue(':submission_year', $submissionYear);
                    $statement->bindValue(':processing_status', AppConstants::COMPLETED);
                    $statement->execute();
                    $exists = $statement->fetch();
                    break;

                case 'CONSOLIDATED_PARASTATAL':
                    $query = "SELECT d.submission_year FROM federal_level_nominal_roll_submissions d "
                        . " JOIN organization o ON d.organization_id = o.id "
                        . "WHERE o.establishment_type_id=:establishment_type_id AND submission_year=:submission_year AND processing_status=:processing_status LIMIT 1";
                    $statement = $this->connection->prepare($query);
                    $statement->bindValue(':establishment_type_id', AppConstants::FEDERAL_PARASTATAL_ESTABLISHMENT);
                    $statement->bindValue(':submission_year', $submissionYear);
                    $statement->bindValue(':processing_status', AppConstants::COMPLETED);
                    $statement->execute();
                    $exists = $statement->fetch();
                    break;

                case 'CONSOLIDATED_MINISTRY_PARASTATAL':
                    $query = "SELECT d.submission_year FROM federal_level_nominal_roll_submissions d "
                        . " JOIN organization o ON d.organization_id = o.id "
                        . "WHERE (o.establishment_type_id=:establishment_type_id OR o.establishment_type_id=:establishment_type_id2)  "
                        . " AND submission_year=:submission_year AND processing_status=:processing_status LIMIT 1";
                    $statement = $this->connection->prepare($query);
                    $statement->bindValue(':establishment_type_id', AppConstants::FEDERAL_MINISTRY_ESTABLISHMENT);
                    $statement->bindValue(':establishment_type_id2', AppConstants::FEDERAL_PARASTATAL_ESTABLISHMENT);
                    $statement->bindValue(':submission_year', $submissionYear);
                    $statement->bindValue(':processing_status', AppConstants::COMPLETED);
                    $statement->execute();
                    $exists = $statement->fetch();
                    break;
            }

            if ($exists) { //if so then continue

                //get the report details, if consolidated, get total organizations involved
                $report = new POLOHDistributionAnalysis();

                switch ($whichMode) {
                    case 'SINGLE_ORGANIZATION':

                        //get the organization name
                        $query = "SELECT organization_name FROM organization WHERE id=:id";
                        $statement = $this->connection->prepare($query);
                        $statement->bindValue(':id', $organizationId);
                        $statement->execute();

                        $organizationName = $statement->fetchColumn(0);
                        $report->setOrganizationName($organizationName);
                        $report->setSubmissionYear($submissionYear);

                        break;

                    case 'CONSOLIDATED_MINISTRY':
                        $query = "SELECT count(DISTINCT(d.organization_id)) FROM federal_level_nominal_roll_submissions d "
                            . " JOIN organization o ON d.organization_id = o.id "
                            . "WHERE o.establishment_type_id=:establishment_type_id AND submission_year=:submission_year AND processing_status=:processing_status";
                        $statement = $this->connection->prepare($query);
                        $statement->bindValue(':establishment_type_id', AppConstants::FEDERAL_MINISTRY_ESTABLISHMENT);
                        $statement->bindValue(':submission_year', $submissionYear);
                        $statement->bindValue(':processing_status', AppConstants::COMPLETED);
                        $statement->execute();
                        $totalEstablishments = $statement->fetchColumn(0);

                        $report->setTotalEstablishments($totalEstablishments);
                        $report->setSubmissionYear($submissionYear);
                        break;

                    case 'CONSOLIDATED_PARASTATAL':
                        $query = "SELECT count(DISTINCT(d.organization_id)) FROM federal_level_nominal_roll_submissions d "
                            . " JOIN organization o ON d.organization_id = o.id "
                            . "WHERE o.establishment_type_id=:establishment_type_id AND submission_year=:submission_year AND processing_status=:processing_status";
                        $statement = $this->connection->prepare($query);
                        $statement->bindValue(':establishment_type_id', AppConstants::FEDERAL_PARASTATAL_ESTABLISHMENT);
                        $statement->bindValue(':submission_year', $submissionYear);
                        $statement->bindValue(':processing_status', AppConstants::COMPLETED);
                        $statement->execute();
                        $totalEstablishments = $statement->fetchColumn(0);

                        $report->setTotalEstablishments($totalEstablishments);
                        $report->setSubmissionYear($submissionYear);
                        break;

                    case 'CONSOLIDATED_MINISTRY_PARASTATAL':
                        $query = "SELECT count(DISTINCT(d.organization_id)) FROM federal_level_nominal_roll_submissions d "
                            . " JOIN organization o ON d.organization_id = o.id "
                            . "WHERE (o.establishment_type_id=:establishment_type_id OR o.establishment_type_id=:establishment_type_id2) "
                            . " AND submission_year=:submission_year AND processing_status=:processing_status";
                        $statement = $this->connection->prepare($query);
                        $statement->bindValue(':establishment_type_id', AppConstants::FEDERAL_MINISTRY_ESTABLISHMENT);
                        $statement->bindValue(':establishment_type_id2', AppConstants::FEDERAL_PARASTATAL_ESTABLISHMENT);
                        $statement->bindValue(':submission_year', $submissionYear);
                        $statement->bindValue(':processing_status', AppConstants::COMPLETED);
                        $statement->execute();
                        $totalEstablishments = $statement->fetchColumn(0);

                        $report->setTotalEstablishments($totalEstablishments);
                        $report->setSubmissionYear($submissionYear);
                        break;
                }


                //STAGE 1 ANALYSIS
                $stage1Analysis = new POLOHDistStage1Analysis();

                $stage1Data = array(); //array of stage1 entries representing each state, totals and percentage

                switch ($whichMode) {
                    case 'SINGLE_ORGANIZATION':

                        $query = "SELECT d.organization_id,d.submission_year,d.state_of_origin_id "
                            . ",d.total_gl_90 as _total_gl_90,d.total_gl_91 as _total_gl_91,d.total_gl_92 as _total_gl_92"
                            . ",d.total_gl_93 as _total_gl_93,d.total_gl_94 as _total_gl_94,d.total_gl_95 as _total_gl_95,d.total_gl_96 as _total_gl_96 "
                            . ",d.total_gl_97 as _total_gl_97,d.total_gl_98 as _total_gl_98,d.total_gl_99 as _total_gl_99 "
                            . ",s.state_code,s.state_name, g.id as _zone_id, g.zone_name "
                            . "FROM federal_level_nominal_roll_career_post_analysis d "
                            . "JOIN states s on d.state_of_origin_id = s.id "
                            . "JOIN geo_political_zone g on s.geo_political_zone_id = g.id "
                            . "WHERE d.organization_id=:organization_id AND d.submission_year=:submission_year order by s.state_name ";
                        $statement = $this->connection->prepare($query);
                        $statement->bindValue(':organization_id', $organizationId);
                        $statement->bindValue(':submission_year', $submissionYear);

                        $statement->execute();

                        break;

                    case 'CONSOLIDATED_MINISTRY':
                        $query = "SELECT d.state_of_origin_id "
                            . ",sum(d.total_gl_90) as _total_gl_90,sum(d.total_gl_91) as _total_gl_91,sum(d.total_gl_92) as _total_gl_92 "
                            . ",sum(d.total_gl_93) as _total_gl_93,sum(d.total_gl_94) as _total_gl_94,sum(d.total_gl_95) as _total_gl_95,sum(d.total_gl_96) as _total_gl_96 "
                            . ",sum(d.total_gl_97) as _total_gl_97,sum(d.total_gl_98) as _total_gl_98,sum(d.total_gl_99) as _total_gl_99 "
                            . ",s.state_code,s.state_name, g.id as _zone_id, g.zone_name "
                            . "FROM federal_level_nominal_roll_career_post_analysis d "
                            . "JOIN organization o on d.organization_id = o.id  "
                            . "JOIN states s on d.state_of_origin_id = s.id "
                            . "JOIN geo_political_zone g on s.geo_political_zone_id = g.id "
                            . "WHERE o.establishment_type_id=:establishment_type_id AND d.submission_year=:submission_year "
                            . "GROUP BY d.state_of_origin_id "
                            . "ORDER BY s.state_name ";
                        $statement = $this->connection->prepare($query);
                        $statement->bindValue(':establishment_type_id', AppConstants::FEDERAL_MINISTRY_ESTABLISHMENT);
                        $statement->bindValue(':submission_year', $submissionYear);

                        $statement->execute();
                        break;

                    case 'CONSOLIDATED_PARASTATAL':
                        $query = "SELECT d.state_of_origin_id "
                            . ",sum(d.total_gl_90) as _total_gl_90,sum(d.total_gl_91) as _total_gl_91,sum(d.total_gl_92) as _total_gl_92 "
                            . ",sum(d.total_gl_93) as _total_gl_93,sum(d.total_gl_94) as _total_gl_94,sum(d.total_gl_95) as _total_gl_95,sum(d.total_gl_96) as _total_gl_96 "
                            . ",sum(d.total_gl_97) as _total_gl_97,sum(d.total_gl_98) as _total_gl_98,sum(d.total_gl_99) as _total_gl_99 "
                            . ",s.state_code,s.state_name, g.id as _zone_id, g.zone_name "
                            . "FROM federal_level_nominal_roll_career_post_analysis d "
                            . "JOIN organization o on d.organization_id = o.id  "
                            . "JOIN states s on d.state_of_origin_id = s.id "
                            . "JOIN geo_political_zone g on s.geo_political_zone_id = g.id "
                            . "WHERE o.establishment_type_id=:establishment_type_id AND d.submission_year=:submission_year "
                            . "GROUP BY d.state_of_origin_id "
                            . "ORDER BY s.state_name ";
                        $statement = $this->connection->prepare($query);
                        $statement->bindValue(':establishment_type_id', AppConstants::FEDERAL_PARASTATAL_ESTABLISHMENT);
                        $statement->bindValue(':submission_year', $submissionYear);
                        break;

                    case 'CONSOLIDATED_MINISTRY_PARASTATAL':
                        $query = "SELECT d.state_of_origin_id "
                            . ",sum(d.total_gl_90) as _total_gl_90,sum(d.total_gl_91) as _total_gl_91,sum(d.total_gl_92) as _total_gl_92 "
                            . ",sum(d.total_gl_93) as _total_gl_93,sum(d.total_gl_94) as _total_gl_94,sum(d.total_gl_95) as _total_gl_95,sum(d.total_gl_96) as _total_gl_96 "
                            . ",sum(d.total_gl_97) as _total_gl_97,sum(d.total_gl_98) as _total_gl_98,sum(d.total_gl_99) as _total_gl_99 "
                            . ",s.state_code,s.state_name, g.id as _zone_id, g.zone_name "
                            . "FROM federal_level_nominal_roll_career_post_analysis d "
                            . "JOIN organization o on d.organization_id = o.id  "
                            . "JOIN states s on d.state_of_origin_id = s.id "
                            . "JOIN geo_political_zone g on s.geo_political_zone_id = g.id "
                            . "WHERE (o.establishment_type_id=:establishment_type_id or o.establishment_type_id=:establishment_type_id2) "
                            . " AND d.submission_year=:submission_year "
                            . "GROUP BY d.state_of_origin_id "
                            . "ORDER BY s.state_name ";
                        $statement = $this->connection->prepare($query);
                        $statement->bindValue(':establishment_type_id', AppConstants::FEDERAL_MINISTRY_ESTABLISHMENT);
                        $statement->bindValue(':establishment_type_id2', AppConstants::FEDERAL_PARASTATAL_ESTABLISHMENT);
                        $statement->bindValue(':submission_year', $submissionYear);

                        $statement->execute();
                        break;
                }


                $analysisRecords = $statement->fetchAll();
                if ($analysisRecords) {
                    //fill the stage 1 entries array

                    //calculate the overall totals and overall percentage
                    $stage1OverallCEOs = 0;
                    $stage1OverallSAssistToVP = 0;
                    $stage1OverallSAssistToPresident = 0;
                    $stage1OverallSAdviserToPresident = 0;
                    $stage1OverallAmbassadors = 0;
                    $stage1OverallMembers = 0;
                    $stage1OverallChairmen = 0;
                    $stage1OverallPermSecs = 0;
                    $stage1OverallMinOfStates = 0;
                    $stage1OverallMinisters = 0;

                    $overallTotal = 0;

                    foreach ($analysisRecords as $stateAnalysis) {
                        $stage1Entry = new POLOHDistStage1Entry();
                        $stage1Entry->setStateCode($stateAnalysis['state_code']);
                        $stage1Entry->setStateName($stateAnalysis['state_name']);
                        $stage1Entry->setTotalChiefExecutives($stateAnalysis['_total_gl_90']);
                        $stage1Entry->setTotalSpecialAssistantsToTheVP($stateAnalysis['_total_gl_91']);
                        $stage1Entry->setTotalSpecialAssistantsToThePresident($stateAnalysis['_total_gl_92']);
                        $stage1Entry->setTotalSpecialAdviserToPresident($stateAnalysis['_total_gl_93']);
                        $stage1Entry->setTotalAmbassadors($stateAnalysis['_total_gl_94']);
                        $stage1Entry->setTotalMembers($stateAnalysis['_total_gl_95']);
                        $stage1Entry->setTotalChairmen($stateAnalysis['_total_gl_96']);
                        $stage1Entry->setTotalPermSecs($stateAnalysis['_total_gl_97']);
                        $stage1Entry->setTotalMinistersOfState($stateAnalysis['_total_gl_98']);
                        $stage1Entry->setTotalMinisters($stateAnalysis['_total_gl_99']);

                        $stage1Entry->setGeoPoliticalZoneId($stateAnalysis['_zone_id']);
                        $stage1Entry->setGeoPoliticalZoneName($stateAnalysis['zone_name']);

                        $stage1Data[] = $stage1Entry;

                        //calculate totals
                        $stage1Entry->calculateTotal();

                        $stage1OverallCEOs += $stage1Entry->getTotalChiefExecutives();
                        $stage1OverallSAssistToVP += $stage1Entry->getTotalSpecialAssistantsToTheVP();
                        $stage1OverallSAssistToPresident += $stage1Entry->getTotalSpecialAssistantsToThePresident();
                        $stage1OverallSAdviserToPresident += $stage1Entry->getTotalSpecialAdviserToPresident();
                        $stage1OverallAmbassadors += $stage1Entry->getTotalAmbassadors();
                        $stage1OverallMembers += $stage1Entry->getTotalMembers();
                        $stage1OverallChairmen += $stage1Entry->getTotalChairmen();
                        $stage1OverallPermSecs += $stage1Entry->getTotalPermSecs();
                        $stage1OverallMinOfStates += $stage1Entry->getTotalMinistersOfState();
                        $stage1OverallMinisters += $stage1Entry->getTotalMinisters();

                        $overallTotal += $stage1Entry->getTotal();

                    }

                    //calculate the percentages for each stage 1 entry and the overall precentage
                    $overallPercentage = 0;

                    foreach ($stage1Data as $stage1Entry) {
                        $stage1Entry->calculatePercentage($overallTotal);
                        $overallPercentage += $stage1Entry->getPercentage();
                    }

                    $stage1Analysis->setStage1Data($stage1Data);

                    //set overall totals
                    $stage1Analysis->setOverallChiefExecutives($stage1OverallCEOs);
                    $stage1Analysis->setOverallSpecialAssistantToTheVP($stage1OverallSAssistToVP);
                    $stage1Analysis->setOverallSpecialAssistantToThePresident($stage1OverallSAssistToPresident);
                    $stage1Analysis->setOverallSpecialAdviserToThePresident($stage1OverallSAdviserToPresident);
                    $stage1Analysis->setOverallAmbassadors($stage1OverallAmbassadors);
                    $stage1Analysis->setOverallMembers($stage1OverallMembers);
                    $stage1Analysis->setOverallChairmen($stage1OverallChairmen);
                    $stage1Analysis->setOverallPermSecs($stage1OverallPermSecs);
                    $stage1Analysis->setOverallMinistersOfState($stage1OverallMinOfStates);
                    $stage1Analysis->setOverallMinisters($stage1OverallMinisters);

                    $stage1Analysis->setOverallTotal($overallTotal);
                    $stage1Analysis->setOverallPercentage(number_format($overallPercentage));

                    //
                    $report->setStage1($stage1Analysis);
                }

            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $report;
    }

    /**
     * @param $whichMode
     * @param $organizationId
     * @param $submissionYear
     * @param ReportStagesFetchParam $fetchParam
     * @return POLOHDistributionAnalysis
     * @throws AppException
     */
    public function getFederalLevelPOLOHDistributionReport($whichMode, $organizationId, $submissionYear, ReportStagesFetchParam $fetchParam): POLOHDistributionAnalysis
    {
        $report = null;

        $statement = null;

        try {

            $report = $this->getFederalLevelPOLOHStage1Distribution($whichMode, $organizationId, $submissionYear);

            if ($report) { //if so then continue

                //STAGE 1 ANALYSIS
                $stage1Analysis = $report->getStage1();

                if ($stage1Analysis) {

                    if ($fetchParam->isFetchStage2() || $fetchParam->isFetchAll()) {

                        //STAGE 2 ANALYSIS
                        $stage2Analysis = new POLOHDistStage2Analysis();
                        $stage2Data = array();

                        $stage2OverallMinisters = 0;
                        $stage2OverallPercentageMinisters = 0;
                        $stage2OverallMinistersOfState = 0;
                        $stage2OverallPercentageMinistersOfState = 0;
                        $stage2OverallMinistersCategory = 0;
                        $stage2OverallPercentageMinistersCategory = 0;
                        $stage2OverallSpecialAdvisers = 0;
                        $stage2OverallPercentageSpecialAdvisers = 0;
                        $stage2OverallSpecialAssistants = 0;
                        $stage2OverallPercentageSpecialAssistants = 0;

                        $stage2OverallSpecialAdvAssistCategory = 0;
                        $stage2OverallPercentageSpecialAdvAssistCategory = 0;


                        $stage2OverallPermSecs = 0;
                        $stage2OverallPercentagePermSecs = 0;
                        $stage2OverallAmbassadors = 0;
                        $stage2OverallPercentageAmbassadors = 0;

                        $stage2OverallTotal = 0;
                        $stage2OverallPercentage = 0;


                        /**
                         * @var POLOHDistStage1Entry $stage1StateData
                         */
                        foreach ($stage1Analysis->getStage1Data() as $stage1StateData) {
                            $stage2Entry = new POLOHDistStage2Entry();

                            $stage2Entry->setStateCode($stage1StateData->getStateCode());
                            $stage2Entry->setStateName($stage1StateData->getStateName());

                            $stage2Entry->setTotalMinisters($stage1StateData->getTotalMinisters());
                            $stage2Entry->setTotalMinistersOfState($stage1StateData->getTotalMinistersOfState());
                            $stage2Entry->setTotalMinistersCategory($stage1StateData->calculateMinistersCategorySubTotal());

                            $stage2Entry->setTotalSpecialAdvisers($stage1StateData->calculateSpecAdviserCategory());
                            $stage2Entry->setTotalSpecialAssistants($stage1StateData->calculateSpecAssistantsCategory());
                            $stage2Entry->setTotalSpecialAdvAssistCategory(
                                $stage1StateData->calculateSpecAdviserCategory() + $stage1StateData->calculateSpecAssistantsCategory()
                            );

                            $stage2Entry->setTotalPermSecs($stage1StateData->getTotalPermSecs());
                            $stage2Entry->setTotalAmbassadors($stage1StateData->getTotalAmbassadors());

                            $stage2Data[] = $stage2Entry;

                            //do other totals
                            $stage2OverallMinisters += $stage2Entry->getTotalMinisters();
                            $stage2OverallMinistersOfState += $stage2Entry->getTotalMinistersOfState();
                            $stage2OverallMinistersCategory += $stage2Entry->getTotalMinistersCategory();
                            $stage2OverallSpecialAdvisers += $stage2Entry->getTotalSpecialAdvisers();
                            $stage2OverallSpecialAssistants += $stage2Entry->getTotalSpecialAssistants();
                            $stage2OverallSpecialAdvAssistCategory += $stage2Entry->getTotalSpecialAdvAssistCategory();
                            $stage2OverallPermSecs += $stage2Entry->getTotalPermSecs();
                            $stage2OverallAmbassadors += $stage2Entry->getTotalAmbassadors();

                            $stage2Entry->calculateTotal();
                            $stage2OverallTotal += $stage2Entry->getTotal();

                        }

                        $stage2CategoryTotals = array(
                            'overall_ministers' => $stage2OverallMinisters,
                            'overall_ministers_of_state' => $stage2OverallMinistersOfState,
                            'overall_ministers_category' => $stage2OverallMinistersCategory,
                            'overall_special_advisers' => $stage2OverallSpecialAdvisers,
                            'overall_special_assistants' => $stage2OverallSpecialAssistants,
                            'overall_special_adv_assist_category' => $stage2OverallSpecialAdvAssistCategory,
                            'overall_perm_secs' => $stage2OverallPermSecs,
                            'overall_ambassadors' => $stage2OverallAmbassadors,
                        );

                        foreach ($stage2Data as $stage2_data) {

                            //$stage2_data = new POLOHDistStage2Entry();

                            $stage2_data->calculatePercentage($stage2OverallTotal);
                            $stage2_data->calculateCategoryPercentages($stage2CategoryTotals);

                            $stage2OverallPercentageMinisters += $stage2_data->getPercentageMinisters();
                            $stage2OverallPercentageMinistersOfState += $stage2_data->getPercentageMinistersOfState();
                            $stage2OverallPercentageMinistersCategory += $stage2_data->getPercentageMinistersCategory();
                            $stage2OverallPercentageSpecialAdvisers += $stage2_data->getPercentageSpecialAdvisers();
                            $stage2OverallPercentageSpecialAssistants += $stage2_data->getPercentageSpecialAssistants();
                            $stage2OverallPercentageSpecialAdvAssistCategory += $stage2_data->getPercentageSpecialAdvAssistCategory();
                            $stage2OverallPercentagePermSecs += $stage2_data->getPercentagePermSecs();
                            $stage2OverallPercentageAmbassadors += $stage2_data->getPercentageAmbassadors();

                            $stage2OverallPercentage += $stage2_data->getPercentage();
                        }

                        $stage2Analysis->setStage2Data($stage2Data);

                        $stage2Analysis->setOverallMinisters($stage2OverallMinisters);
                        $stage2Analysis->setOverallPercentageMinisters(number_format($stage2OverallPercentageMinisters));

                        $stage2Analysis->setOverallMinistersOfState($stage2OverallMinistersOfState);
                        $stage2Analysis->setOverallPercentageMinistersOfState(number_format($stage2OverallPercentageMinistersOfState));

                        $stage2Analysis->setOverallMinistersCategory($stage2OverallMinistersCategory);
                        $stage2Analysis->setOverallPercentageMinistersCategory(number_format($stage2OverallPercentageMinistersCategory));

                        $stage2Analysis->setOverallSpecialAdvisers($stage2OverallSpecialAdvisers);
                        $stage2Analysis->setOverallPercentageSpecialAdvisers(number_format($stage2OverallPercentageSpecialAdvisers));

                        $stage2Analysis->setOverallSpecialAssistants($stage2OverallSpecialAssistants);
                        $stage2Analysis->setOverallPercentageSpecialAssistants(number_format($stage2OverallPercentageSpecialAssistants));

                        $stage2Analysis->setOverallSpecialAdvAssistCategory($stage2OverallSpecialAdvAssistCategory);
                        $stage2Analysis->setOverallPercentageSpecialAdvAssistCategory(number_format($stage2OverallPercentageSpecialAdvAssistCategory));

                        $stage2Analysis->setOverallPermSecs($stage2OverallPermSecs);
                        $stage2Analysis->setOverallPercentagePermSecs(number_format($stage2OverallPercentagePermSecs));

                        $stage2Analysis->setOverallAmbassadors($stage2OverallAmbassadors);
                        $stage2Analysis->setOverallPercentageAmbassadors(number_format($stage2OverallPercentageAmbassadors));

                        $stage2Analysis->setOverallTotal($stage2OverallTotal);
                        $stage2Analysis->setOverallPercentage(number_format($stage2OverallPercentage));

                        $report->setStage2($stage2Analysis);
                    }

                    if ($fetchParam->isFetchStage3() || $fetchParam->isFetchAll()) {

                        //prepare a map of zone_id -> states array

                        $stage3Analysis = new POLOHDistStage3Analysis();

                        $stage3OverallCEOs = 0;
                        $stage3OverallSAssistToVP = 0;
                        $stage3OverallSAssistToPresident = 0;
                        $stage3OverallSAdviserToPresident = 0;
                        $stage3OverallAmbassadors = 0;
                        $stage3OverallMembers = 0;
                        $stage3OverallChairmen = 0;
                        $stage3OverallPermSecs = 0;
                        $stage3OverallMinOfStates = 0;
                        $stage3OverallMinisters = 0;

                        $stage3OverallTotal = 0;
                        $stage3OverallPercentage = 0;

                        //select the geo-political zone ids and name
                        $query = "SELECT id, zone_code, zone_name FROM geo_political_zone WHERE zone_code<>:zone_code AND record_status=:record_status";
                        $statement = $this->connection->prepare($query);
                        $statement->bindValue(':zone_code', 'NON');
                        $statement->bindValue(':record_status', AppConstants::ACTIVE);

                        $statement->execute();

                        $geoPoliticalZones = $statement->fetchAll();

                        $zoneStatesMap = array();
                        $stage3TempData = array();

                        //setup the zonestatesmap and a temp map of zone analysis
                        foreach ($geoPoliticalZones as $geoPoliticalZone) {
                            $zoneStatesMap[$geoPoliticalZone['id']] = array();

                            $stage3Entry = new POLOHDistStage3Entry();
                            $stage3Entry->setZoneId($geoPoliticalZone['id']);
                            $stage3Entry->setZoneCode($geoPoliticalZone['zone_code']);
                            $stage3Entry->setZoneName($geoPoliticalZone['zone_name']);

                            $stage3TempData[$geoPoliticalZone['id']] = $stage3Entry;
                        }

                        //fill the zone states map
                        /**
                         * @var $stage_1_data POLOHDistStage1Entry
                         */
                        foreach ($stage1Analysis->getStage1Data() as $stage_1_data) {
                            $zoneStatesMap[$stage_1_data->getGeoPoliticalZoneId()][] = $stage_1_data;
                        }

                        /**
                         * @var $stage3Entry POLOHDistStage3Entry
                         */
                        foreach ($stage3TempData as $k => $stage3Entry) {

                            $totalCEOs = 0;
                            $totalSAssistToVP = 0;
                            $totalSAssistToPresident = 0;
                            $totalSAdviserToPresident = 0;
                            $totalAmbassadors = 0;
                            $totalMembers = 0;
                            $totalChairmen = 0;
                            $totalPermSecs = 0;
                            $totalMinOfStates = 0;
                            $totalMinisters = 0;

                            /**
                             * @var $stage3StateEntry POLOHDistStage1Entry
                             */
                            foreach ($zoneStatesMap[$k] as $stage3StateEntry) {
                                $totalCEOs += $stage3StateEntry->getTotalChiefExecutives();
                                $totalSAssistToVP += $stage3StateEntry->getTotalSpecialAssistantsToTheVP();
                                $totalSAssistToPresident += $stage3StateEntry->getTotalSpecialAssistantsToThePresident();
                                $totalSAdviserToPresident += $stage3StateEntry->getTotalSpecialAdviserToPresident();
                                $totalAmbassadors += $stage3StateEntry->getTotalAmbassadors();
                                $totalMembers += $stage3StateEntry->getTotalMembers();
                                $totalChairmen += $stage3StateEntry->getTotalChairmen();
                                $totalPermSecs += $stage3StateEntry->getTotalPermSecs();
                                $totalMinOfStates += $stage3StateEntry->getTotalMinistersOfState();
                                $totalMinisters += $stage3StateEntry->getTotalMinisters();
                            }

                            $stage3Entry->setTotalChiefExecutives($totalCEOs);
                            $stage3Entry->setTotalSpecialAssistantsToTheVP($totalSAssistToVP);
                            $stage3Entry->setTotalSpecialAssistantsToThePresident($totalSAssistToPresident);
                            $stage3Entry->setTotalSpecialAdviserToPresident($totalSAdviserToPresident);
                            $stage3Entry->setTotalAmbassadors($totalAmbassadors);
                            $stage3Entry->setTotalMembers($totalMembers);
                            $stage3Entry->setTotalChairmen($totalChairmen);
                            $stage3Entry->setTotalPermSecs($totalPermSecs);
                            $stage3Entry->setTotalMinistersOfState($totalMinOfStates);
                            $stage3Entry->setTotalMinisters($totalMinisters);

                            //calculate overall totals
                            $stage3OverallCEOs += $stage3Entry->getTotalChiefExecutives();
                            $stage3OverallSAssistToVP += $stage3Entry->getTotalSpecialAssistantsToTheVP();
                            $stage3OverallSAssistToPresident += $stage3Entry->getTotalSpecialAssistantsToThePresident();
                            $stage3OverallSAdviserToPresident += $stage3Entry->getTotalSpecialAdviserToPresident();
                            $stage3OverallAmbassadors += $stage3Entry->getTotalAmbassadors();
                            $stage3OverallMembers += $stage3Entry->getTotalMembers();
                            $stage3OverallChairmen += $stage3Entry->getTotalChairmen();
                            $stage3OverallPermSecs += $stage3Entry->getTotalPermSecs();
                            $stage3OverallMinOfStates += $stage3Entry->getTotalMinistersOfState();
                            $stage3OverallMinisters += $stage3Entry->getTotalMinisters();

                            $stage3Entry->calculateTotal();
                            $stage3OverallTotal += $stage3Entry->getTotal();
                        }

                        //calculate percentages
                        /**
                         * @var $stage3Entry POLOHDistStage3Entry
                         */
                        foreach ($stage3TempData as $stage3Entry) {
                            $stage3Entry->calculatePercentage($stage3OverallTotal);
                            $stage3OverallPercentage += $stage3Entry->getPercentage();
                        }

                        $stage3Analysis->setOverallChiefExecutives($stage3OverallCEOs);
                        $stage3Analysis->setOverallSpecialAssistantToTheVP($stage3OverallSAssistToVP);
                        $stage3Analysis->setOverallSpecialAssistantToThePresident($stage3OverallSAssistToPresident);
                        $stage3Analysis->setOverallSpecialAdviserToThePresident($stage3OverallSAdviserToPresident);
                        $stage3Analysis->setOverallAmbassadors($stage3OverallAmbassadors);
                        $stage3Analysis->setOverallMembers($stage3OverallMembers);
                        $stage3Analysis->setOverallChairmen($stage3OverallChairmen);
                        $stage3Analysis->setOverallPermSecs($stage3OverallPermSecs);
                        $stage3Analysis->setOverallMinistersOfState($stage3OverallMinOfStates);
                        $stage3Analysis->setOverallMinisters($stage3OverallMinisters);
                        $stage3Analysis->setOverallTotal($stage3OverallTotal);
                        $stage3Analysis->setOverallPercentage(number_format($stage3OverallPercentage));

                        $stage3Data = array_values($stage3TempData);

                        $stage3Analysis->setStage3Data($stage3Data);

                        $report->setStage3($stage3Analysis);

                    }


                }

            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $report;
    }

    /**
     * @return POLOHDistributionAnalysis
     * @throws AppException
     */
    public function getExternalFederalLevelPOLOHDistributionReport(): POLOHDistributionAnalysis
    {
        $report = null;

        $statement = null;

        try {

            $report = new POLOHDistributionAnalysis();
            $stage1Analysis = new POLOHDistStage1Analysis();

            /**
             * @var POLOHDistStage1Entry[] $stage1Data
             */
            $stage1Data = array();


            $query = "SELECT s.id,s.state_code,s.state_name, g.id AS _zone_id, g.zone_name
        ,(SELECT count(p.state_of_origin_id) FROM _political_off_holders_2018 p WHERE p.state_of_origin_id = s.id AND p.portfolio_code=90) AS _total_gl_90
        ,(SELECT count(p.state_of_origin_id) FROM _political_off_holders_2018 p WHERE p.state_of_origin_id = s.id AND p.portfolio_code=91) AS _total_gl_91
        ,(SELECT count(p.state_of_origin_id) FROM _political_off_holders_2018 p WHERE p.state_of_origin_id = s.id AND p.portfolio_code=92) AS _total_gl_92 
        ,(SELECT count(p.state_of_origin_id) FROM _political_off_holders_2018 p WHERE p.state_of_origin_id = s.id AND p.portfolio_code=93) AS _total_gl_93
        ,(SELECT count(p.state_of_origin_id) FROM _political_off_holders_2018 p WHERE p.state_of_origin_id = s.id AND p.portfolio_code=94) AS _total_gl_94
        ,(SELECT count(p.state_of_origin_id) FROM _political_off_holders_2018 p WHERE p.state_of_origin_id = s.id AND p.portfolio_code=95) AS _total_gl_95
        ,(SELECT count(p.state_of_origin_id) FROM _political_off_holders_2018 p WHERE p.state_of_origin_id = s.id AND p.portfolio_code=96) AS _total_gl_96 
        ,(SELECT count(p.state_of_origin_id) FROM _political_off_holders_2018 p WHERE p.state_of_origin_id = s.id AND p.portfolio_code=97) AS _total_gl_97
        ,(SELECT count(p.state_of_origin_id) FROM _political_off_holders_2018 p WHERE p.state_of_origin_id = s.id AND p.portfolio_code=98) AS _total_gl_98
        ,(SELECT count(p.state_of_origin_id) FROM _political_off_holders_2018 p WHERE p.state_of_origin_id = s.id AND p.portfolio_code=99) AS _total_gl_99 
        FROM states s
        JOIN geo_political_zone g ON s.geo_political_zone_id = g.id 
        where s.state_code<>:state_code
        ORDER BY s.state_name ";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':state_code', 'NON');
            $statement->execute();

            $analysisRecords = $statement->fetchAll();
            if ($analysisRecords) {
                //fill the stage 1 entries array

                //calculate the overall totals and overall percentage
                $stage1OverallCEOs = 0;
                $stage1OverallSAssistToVP = 0;
                $stage1OverallSAssistToPresident = 0;
                $stage1OverallSAdviserToPresident = 0;
                $stage1OverallAmbassadors = 0;
                $stage1OverallMembers = 0;
                $stage1OverallChairmen = 0;
                $stage1OverallPermSecs = 0;
                $stage1OverallMinOfStates = 0;
                $stage1OverallMinisters = 0;

                $overallTotal = 0;

                foreach ($analysisRecords as $stateAnalysis) {

                    $stage1Entry = new POLOHDistStage1Entry();
                    $stage1Entry->setStateCode($stateAnalysis['state_code']);
                    $stage1Entry->setStateName($stateAnalysis['state_name']);
                    $stage1Entry->setTotalChiefExecutives($stateAnalysis['_total_gl_90']);
                    $stage1Entry->setTotalSpecialAssistantsToTheVP($stateAnalysis['_total_gl_91']);
                    $stage1Entry->setTotalSpecialAssistantsToThePresident($stateAnalysis['_total_gl_92']);
                    $stage1Entry->setTotalSpecialAdviserToPresident($stateAnalysis['_total_gl_93']);
                    $stage1Entry->setTotalAmbassadors($stateAnalysis['_total_gl_94']);
                    $stage1Entry->setTotalMembers($stateAnalysis['_total_gl_95']);
                    $stage1Entry->setTotalChairmen($stateAnalysis['_total_gl_96']);
                    $stage1Entry->setTotalPermSecs($stateAnalysis['_total_gl_97']);
                    $stage1Entry->setTotalMinistersOfState($stateAnalysis['_total_gl_98']);
                    $stage1Entry->setTotalMinisters($stateAnalysis['_total_gl_99']);

                    $stage1Entry->setGeoPoliticalZoneId($stateAnalysis['_zone_id']);
                    $stage1Entry->setGeoPoliticalZoneName($stateAnalysis['zone_name']);

                    $stage1Data[] = $stage1Entry;

                    //calculate totals
                    $stage1Entry->calculateTotal();

                    $stage1OverallCEOs += $stage1Entry->getTotalChiefExecutives();
                    $stage1OverallSAssistToVP += $stage1Entry->getTotalSpecialAssistantsToTheVP();
                    $stage1OverallSAssistToPresident += $stage1Entry->getTotalSpecialAssistantsToThePresident();
                    $stage1OverallSAdviserToPresident += $stage1Entry->getTotalSpecialAdviserToPresident();
                    $stage1OverallAmbassadors += $stage1Entry->getTotalAmbassadors();
                    $stage1OverallMembers += $stage1Entry->getTotalMembers();
                    $stage1OverallChairmen += $stage1Entry->getTotalChairmen();
                    $stage1OverallPermSecs += $stage1Entry->getTotalPermSecs();
                    $stage1OverallMinOfStates += $stage1Entry->getTotalMinistersOfState();
                    $stage1OverallMinisters += $stage1Entry->getTotalMinisters();

                    $overallTotal += $stage1Entry->getTotal();

                }

                //calculate the percentages for each stage 1 entry and the overall precentage
                $overallPercentage = 0;

                /**
                 * @var
                 */
                foreach ($stage1Data as $stage1Entry) {
                    $stage1Entry->calculatePercentage($overallTotal);
                    $overallPercentage += $stage1Entry->getPercentage();
                }

                $stage1Analysis->setStage1Data($stage1Data);

                //set overall totals
                $stage1Analysis->setOverallChiefExecutives($stage1OverallCEOs);
                $stage1Analysis->setOverallSpecialAssistantToTheVP($stage1OverallSAssistToVP);
                $stage1Analysis->setOverallSpecialAssistantToThePresident($stage1OverallSAssistToPresident);
                $stage1Analysis->setOverallSpecialAdviserToThePresident($stage1OverallSAdviserToPresident);
                $stage1Analysis->setOverallAmbassadors($stage1OverallAmbassadors);
                $stage1Analysis->setOverallMembers($stage1OverallMembers);
                $stage1Analysis->setOverallChairmen($stage1OverallChairmen);
                $stage1Analysis->setOverallPermSecs($stage1OverallPermSecs);
                $stage1Analysis->setOverallMinistersOfState($stage1OverallMinOfStates);
                $stage1Analysis->setOverallMinisters($stage1OverallMinisters);

                $stage1Analysis->setOverallTotal($overallTotal);
                $stage1Analysis->setOverallPercentage(number_format($overallPercentage));

                //
                $report->setStage1($stage1Analysis);
            }


            if ($report) { //if so then continue

                //STAGE 1 ANALYSIS
                $stage1Analysis = $report->getStage1();

                if ($stage1Analysis) {

                    //prepare a map of zone_id -> states array
                    $stage3Analysis = new POLOHDistStage3Analysis();

                    $stage3OverallCEOs = 0;
                    $stage3OverallSAssistToVP = 0;
                    $stage3OverallSAssistToPresident = 0;
                    $stage3OverallSAdviserToPresident = 0;
                    $stage3OverallAmbassadors = 0;
                    $stage3OverallMembers = 0;
                    $stage3OverallChairmen = 0;
                    $stage3OverallPermSecs = 0;
                    $stage3OverallMinOfStates = 0;
                    $stage3OverallMinisters = 0;

                    $stage3OverallTotal = 0;
                    $stage3OverallPercentage = 0;

                    //select the geo-political zone ids and name
                    $query = "SELECT id, zone_code, zone_name FROM geo_political_zone WHERE zone_code<>:zone_code AND record_status=:record_status";
                    $statement = $this->connection->prepare($query);
                    $statement->bindValue(':zone_code', 'NON');
                    $statement->bindValue(':record_status', AppConstants::ACTIVE);

                    $statement->execute();

                    $geoPoliticalZones = $statement->fetchAll();

                    $zoneStatesMap = array();
                    $stage3TempData = array();

                    //setup the zonestatesmap and a temp map of zone analysis
                    foreach ($geoPoliticalZones as $geoPoliticalZone) {
                        $zoneStatesMap[$geoPoliticalZone['id']] = array();

                        $stage3Entry = new POLOHDistStage3Entry();
                        $stage3Entry->setZoneId($geoPoliticalZone['id']);
                        $stage3Entry->setZoneCode($geoPoliticalZone['zone_code']);
                        $stage3Entry->setZoneName($geoPoliticalZone['zone_name']);

                        $stage3TempData[$geoPoliticalZone['id']] = $stage3Entry;
                    }

                    //fill the zone states map
                    /**
                     * @var $stage_1_data POLOHDistStage1Entry
                     */
                    foreach ($stage1Analysis->getStage1Data() as $stage_1_data) {
                        $zoneStatesMap[$stage_1_data->getGeoPoliticalZoneId()][] = $stage_1_data;
                    }

                    /**
                     * @var $stage3Entry POLOHDistStage3Entry
                     */
                    foreach ($stage3TempData as $k => $stage3Entry) {

                        $totalCEOs = 0;
                        $totalSAssistToVP = 0;
                        $totalSAssistToPresident = 0;
                        $totalSAdviserToPresident = 0;
                        $totalAmbassadors = 0;
                        $totalMembers = 0;
                        $totalChairmen = 0;
                        $totalPermSecs = 0;
                        $totalMinOfStates = 0;
                        $totalMinisters = 0;

                        /**
                         * @var $stage3StateEntry POLOHDistStage1Entry
                         */
                        foreach ($zoneStatesMap[$k] as $stage3StateEntry) {
                            $totalCEOs += $stage3StateEntry->getTotalChiefExecutives();
                            $totalSAssistToVP += $stage3StateEntry->getTotalSpecialAssistantsToTheVP();
                            $totalSAssistToPresident += $stage3StateEntry->getTotalSpecialAssistantsToThePresident();
                            $totalSAdviserToPresident += $stage3StateEntry->getTotalSpecialAdviserToPresident();
                            $totalAmbassadors += $stage3StateEntry->getTotalAmbassadors();
                            $totalMembers += $stage3StateEntry->getTotalMembers();
                            $totalChairmen += $stage3StateEntry->getTotalChairmen();
                            $totalPermSecs += $stage3StateEntry->getTotalPermSecs();
                            $totalMinOfStates += $stage3StateEntry->getTotalMinistersOfState();
                            $totalMinisters += $stage3StateEntry->getTotalMinisters();
                        }

                        $stage3Entry->setTotalChiefExecutives($totalCEOs);
                        $stage3Entry->setTotalSpecialAssistantsToTheVP($totalSAssistToVP);
                        $stage3Entry->setTotalSpecialAssistantsToThePresident($totalSAssistToPresident);
                        $stage3Entry->setTotalSpecialAdviserToPresident($totalSAdviserToPresident);
                        $stage3Entry->setTotalAmbassadors($totalAmbassadors);
                        $stage3Entry->setTotalMembers($totalMembers);
                        $stage3Entry->setTotalChairmen($totalChairmen);
                        $stage3Entry->setTotalPermSecs($totalPermSecs);
                        $stage3Entry->setTotalMinistersOfState($totalMinOfStates);
                        $stage3Entry->setTotalMinisters($totalMinisters);

                        //calculate overall totals
                        $stage3OverallCEOs += $stage3Entry->getTotalChiefExecutives();
                        $stage3OverallSAssistToVP += $stage3Entry->getTotalSpecialAssistantsToTheVP();
                        $stage3OverallSAssistToPresident += $stage3Entry->getTotalSpecialAssistantsToThePresident();
                        $stage3OverallSAdviserToPresident += $stage3Entry->getTotalSpecialAdviserToPresident();
                        $stage3OverallAmbassadors += $stage3Entry->getTotalAmbassadors();
                        $stage3OverallMembers += $stage3Entry->getTotalMembers();
                        $stage3OverallChairmen += $stage3Entry->getTotalChairmen();
                        $stage3OverallPermSecs += $stage3Entry->getTotalPermSecs();
                        $stage3OverallMinOfStates += $stage3Entry->getTotalMinistersOfState();
                        $stage3OverallMinisters += $stage3Entry->getTotalMinisters();

                        $stage3Entry->calculateTotal();
                        $stage3OverallTotal += $stage3Entry->getTotal();
                    }

                    //calculate percentages
                    /**
                     * @var $stage3Entry POLOHDistStage3Entry
                     */
                    foreach ($stage3TempData as $stage3Entry) {
                        $stage3Entry->calculatePercentage($stage3OverallTotal);
                        $stage3OverallPercentage += $stage3Entry->getPercentage();
                    }

                    $stage3Analysis->setOverallChiefExecutives($stage3OverallCEOs);
                    $stage3Analysis->setOverallSpecialAssistantToTheVP($stage3OverallSAssistToVP);
                    $stage3Analysis->setOverallSpecialAssistantToThePresident($stage3OverallSAssistToPresident);
                    $stage3Analysis->setOverallSpecialAdviserToThePresident($stage3OverallSAdviserToPresident);
                    $stage3Analysis->setOverallAmbassadors($stage3OverallAmbassadors);
                    $stage3Analysis->setOverallMembers($stage3OverallMembers);
                    $stage3Analysis->setOverallChairmen($stage3OverallChairmen);
                    $stage3Analysis->setOverallPermSecs($stage3OverallPermSecs);
                    $stage3Analysis->setOverallMinistersOfState($stage3OverallMinOfStates);
                    $stage3Analysis->setOverallMinisters($stage3OverallMinisters);
                    $stage3Analysis->setOverallTotal($stage3OverallTotal);
                    $stage3Analysis->setOverallPercentage(number_format($stage3OverallPercentage));

                    $stage3Data = array_values($stage3TempData);

                    $stage3Analysis->setStage3Data($stage3Data);

                    $report->setStage3($stage3Analysis);

                }

            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $report;
    }

    /**
     * @return bool|null|string
     * @throws AppException
     */
    public function getTopProcessedYear()
    {
        $mostRecentActiveProcessedYear = null;
        $statement = null;

        try {
            $query = "SELECT max(submission_year) AS most_recent_active_processed_year
                      FROM federal_level_nominal_roll_submissions
                      WHERE is_active=:is_active
                      AND processing_status=:processing_status";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':is_active', AppConstants::Y);
            $statement->bindValue(':processing_status', AppConstants::COMPLETED);
            $statement->execute();

            $mostRecentActiveProcessedYear = $statement->fetchColumn(0);

        } catch (\Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $mostRecentActiveProcessedYear;
    }

}