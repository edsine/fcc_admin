<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:43 PM
 */

namespace AppBundle\Services\Reporting;


use AppBundle\AppException\AppException;
use AppBundle\Model\Reporting\FederalLevelPostDistributionAnalysis;
use AppBundle\Model\Reporting\FederalLevelStage1Analysis;
use AppBundle\Model\Reporting\FederalLevelStage1Entry;
use AppBundle\Model\Reporting\FederalLevelStage2Analysis;
use AppBundle\Model\Reporting\FederalLevelStage2Entry;
use AppBundle\Model\Reporting\FederalLevelStage3Analysis;
use AppBundle\Model\Reporting\FederalLevelStage4Analysis;
use AppBundle\Model\Reporting\FederalLevelStage4SubAnalysis;
use AppBundle\Model\Reporting\FederalLevelStage4SubAnalysisEntry;
use AppBundle\Model\Reporting\FederalLevelStage5Analysis;
use AppBundle\Model\Reporting\FederalLevelStage5SubAnalysis;
use AppBundle\Model\Reporting\FederalLevelStage5SubAnalysisEntry;
use AppBundle\Model\Reporting\FederalLevelStage6Analysis;
use AppBundle\Model\Reporting\FederalLevelStage6SubAnalysis;
use AppBundle\Model\Reporting\FederalLevelStage6SubAnalysisEntry;
use AppBundle\Model\Reporting\FederalLevelStage7Analysis;
use AppBundle\Model\Reporting\FederalLevelStage7SubAnalysis;
use AppBundle\Model\Reporting\FederalLevelStage7SubAnalysisEntry;
use AppBundle\Model\Reporting\FederalLevelStage8Analysis;
use AppBundle\Model\Reporting\FederalLevelStage8CategoryAnalysis;
use AppBundle\Model\Reporting\FederalLevelStage8SubAnalysis;
use AppBundle\Model\Reporting\FederalLevelStage8SubAnalysisEntry;
use AppBundle\Model\Reporting\FederalLevelStage9Analysis;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\Paginator;
use AppBundle\Utils\ServiceHelper;
use Doctrine\DBAL\Connection;
use \Throwable;
use \PDO;

class CareerDistributionService extends ServiceHelper
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
     * @return FederalLevelPostDistributionAnalysis|null
     * @throws AppException
     */
    public function getFederalLevelStage1Distribution($whichMode, $organizationId, $submissionYear):?FederalLevelPostDistributionAnalysis
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
            }

            if ($exists) { //if so then continue

                //get the report details, if consolidated, get total organizations involved
                $report = new FederalLevelPostDistributionAnalysis();

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
                }


                //STAGE 1 ANALYSIS
                $stage1Analysis = new FederalLevelStage1Analysis();

                $stage1Data = array(); //array of stage1 entries representing each state, totals and percentage

                switch ($whichMode) {
                    case 'SINGLE_ORGANIZATION':

                        $query = "SELECT d.organization_id,d.submission_year,d.state_of_origin_id "
                            . ",d.total_gl_1 as _total_gl_1,d.total_gl_2 as _total_gl_2,d.total_gl_3 as _total_gl_3"
                            . ",d.total_gl_4 as _total_gl_4,d.total_gl_5 as _total_gl_5,d.total_gl_6 as _total_gl_6,d.total_gl_7 as _total_gl_7 "
                            . ",d.total_gl_8 as _total_gl_8,d.total_gl_9 as _total_gl_9,d.total_gl_10 as _total_gl_10,d.total_gl_11 as _total_gl_11 "
                            . ",d.total_gl_12 as _total_gl_12,d.total_gl_13 as _total_gl_13,d.total_gl_14 as _total_gl_14 "
                            . ",d.total_gl_15 as _total_gl_15,d.total_gl_16 as _total_gl_16,d.total_gl_17 as _total_gl_17"
                            . ",d.total_consolidated as _total_consolidated "
                            . ",s.state_code,s.state_name, g.id as _zone_id, g.zone_name "
                            . "FROM federal_level_nominal_roll_career_post_analysis d "
                            . "JOIN states s on d.state_of_origin_id = s.id "
                            . "LEFT JOIN geo_political_zone g on s.geo_political_zone_id = g.id "
                            . "WHERE d.organization_id=:organization_id AND d.submission_year=:submission_year order by s.order_id ";
                        $statement = $this->connection->prepare($query);
                        $statement->bindValue(':organization_id', $organizationId);
                        $statement->bindValue(':submission_year', $submissionYear);

                        $statement->execute();

                        break;

                    case 'CONSOLIDATED_MINISTRY':
                        $query = "SELECT d.state_of_origin_id "
                            . ",sum(d.total_gl_1) as _total_gl_1,sum(d.total_gl_2) as _total_gl_2,sum(d.total_gl_3) as _total_gl_3 "
                            . ",sum(d.total_gl_4) as _total_gl_4,sum(d.total_gl_5) as _total_gl_5,sum(d.total_gl_6) as _total_gl_6,sum(d.total_gl_7) as _total_gl_7 "
                            . ",sum(d.total_gl_8) as _total_gl_8,sum(d.total_gl_9) as _total_gl_9,sum(d.total_gl_10) as _total_gl_10,sum(d.total_gl_11) as _total_gl_11 "
                            . ",sum(d.total_gl_12) as _total_gl_12,sum(d.total_gl_13) as _total_gl_13,sum(d.total_gl_14) as _total_gl_14 "
                            . ",sum(d.total_gl_15) as _total_gl_15,sum(d.total_gl_16) as _total_gl_16,sum(d.total_gl_17) as _total_gl_17 "
                            . ",sum(d.total_consolidated) as _total_consolidated "
                            . ",s.state_code,s.state_name, g.id as _zone_id, g.zone_name "
                            . "FROM federal_level_nominal_roll_career_post_analysis d "
                            . "JOIN organization o on d.organization_id = o.id  "
                            . "JOIN states s on d.state_of_origin_id = s.id "
                            . "LEFT JOIN geo_political_zone g on s.geo_political_zone_id = g.id "
                            . "WHERE o.establishment_type_id=:establishment_type_id AND d.submission_year=:submission_year "
                            . "GROUP BY d.state_of_origin_id "
                            . "ORDER BY s.order_id ";
                        $statement = $this->connection->prepare($query);
                        $statement->bindValue(':establishment_type_id', AppConstants::FEDERAL_MINISTRY_ESTABLISHMENT);
                        $statement->bindValue(':submission_year', $submissionYear);

                        $statement->execute();
                        break;

                    case 'CONSOLIDATED_PARASTATAL':
                        $query = "SELECT d.state_of_origin_id "
                            . ",sum(d.total_gl_1) as _total_gl_1,sum(d.total_gl_2) as _total_gl_2,sum(d.total_gl_3) as _total_gl_3 "
                            . ",sum(d.total_gl_4) as _total_gl_4,sum(d.total_gl_5) as _total_gl_5,sum(d.total_gl_6) as _total_gl_6,sum(d.total_gl_7) as _total_gl_7 "
                            . ",sum(d.total_gl_8) as _total_gl_8,sum(d.total_gl_9) as _total_gl_9,sum(d.total_gl_10) as _total_gl_10,sum(d.total_gl_11) as _total_gl_11 "
                            . ",sum(d.total_gl_12) as _total_gl_12,sum(d.total_gl_13) as _total_gl_13,sum(d.total_gl_14) as _total_gl_14 "
                            . ",sum(d.total_gl_15) as _total_gl_15,sum(d.total_gl_16) as _total_gl_16,sum(d.total_gl_17) as _total_gl_17 "
                            . ",sum(d.total_consolidated) as _total_consolidated "
                            . ",s.state_code,s.state_name, g.id as _zone_id, g.zone_name "
                            . "FROM federal_level_nominal_roll_career_post_analysis d "
                            . "JOIN organization o on d.organization_id = o.id  "
                            . "JOIN states s on d.state_of_origin_id = s.id "
                            . "LEFT JOIN geo_political_zone g on s.geo_political_zone_id = g.id "
                            . "WHERE o.establishment_type_id=:establishment_type_id AND d.submission_year=:submission_year "
                            . "GROUP BY d.state_of_origin_id "
                            . "ORDER BY s.order_id ";
                        $statement = $this->connection->prepare($query);
                        $statement->bindValue(':establishment_type_id', AppConstants::FEDERAL_PARASTATAL_ESTABLISHMENT);
                        $statement->bindValue(':submission_year', $submissionYear);

                        $statement->execute();
                        break;
                }


                $analysisRecords = $statement->fetchAll();
                if ($analysisRecords) {
                    //fill the stage 1 entries array

                    //calculate the overall totals and overall percentage
                    $stage1OverallGL1 = 0;
                    $stage1OverallGL2 = 0;
                    $stage1OverallGL3 = 0;
                    $stage1OverallGL4 = 0;
                    $stage1OverallGL5 = 0;
                    $stage1OverallGL6 = 0;
                    $stage1OverallGL7 = 0;
                    $stage1OverallGL8 = 0;
                    $stage1OverallGL9 = 0;
                    $stage1OverallGL10 = 0;
                    $stage1OverallGL11 = 0;
                    $stage1OverallGL12 = 0;
                    $stage1OverallGL13 = 0;
                    $stage1OverallGL14 = 0;
                    $stage1OverallGL15 = 0;
                    $stage1OverallGL16 = 0;
                    $stage1OverallGL17 = 0;
                    $stage1OverallConsolidated = 0;

                    $overallTotal = 0;

                    foreach ($analysisRecords as $stateAnalysis) {
                        $stage1Entry = new FederalLevelStage1Entry();
                        $stage1Entry->setStateCode($stateAnalysis['state_code']);
                        $stage1Entry->setStateName($stateAnalysis['state_name']);
                        $stage1Entry->setTotalGL1($stateAnalysis['_total_gl_1']);
                        $stage1Entry->setTotalGL2($stateAnalysis['_total_gl_2']);
                        $stage1Entry->setTotalGL3($stateAnalysis['_total_gl_3']);
                        $stage1Entry->setTotalGL4($stateAnalysis['_total_gl_4']);
                        $stage1Entry->setTotalGL5($stateAnalysis['_total_gl_5']);
                        $stage1Entry->setTotalGL6($stateAnalysis['_total_gl_6']);
                        $stage1Entry->setTotalGL7($stateAnalysis['_total_gl_7']);
                        $stage1Entry->setTotalGL8($stateAnalysis['_total_gl_8']);
                        $stage1Entry->setTotalGL9($stateAnalysis['_total_gl_9']);
                        $stage1Entry->setTotalGL10($stateAnalysis['_total_gl_10']);
                        $stage1Entry->setTotalGL11($stateAnalysis['_total_gl_11']);
                        $stage1Entry->setTotalGL12($stateAnalysis['_total_gl_12']);
                        $stage1Entry->setTotalGL13($stateAnalysis['_total_gl_13']);
                        $stage1Entry->setTotalGL14($stateAnalysis['_total_gl_14']);
                        $stage1Entry->setTotalGL15($stateAnalysis['_total_gl_15']);
                        $stage1Entry->setTotalGL16($stateAnalysis['_total_gl_16']);
                        $stage1Entry->setTotalGL17($stateAnalysis['_total_gl_17']);
                        $stage1Entry->setTotalConsolidated($stateAnalysis['_total_consolidated']);

                        $stage1Entry->setGeoPoliticalZoneId($stateAnalysis['_zone_id']);
                        $stage1Entry->setGeoPoliticalZoneName($stateAnalysis['zone_name']);

                        $stage1Data[] = $stage1Entry;

                        //calculate totals
                        $stage1Entry->calculateTotal();

                        $stage1OverallGL1 += $stage1Entry->getTotalGL1();
                        $stage1OverallGL2 += $stage1Entry->getTotalGL2();
                        $stage1OverallGL3 += $stage1Entry->getTotalGL3();
                        $stage1OverallGL4 += $stage1Entry->getTotalGL4();
                        $stage1OverallGL5 += $stage1Entry->getTotalGL5();
                        $stage1OverallGL6 += $stage1Entry->getTotalGL6();
                        $stage1OverallGL7 += $stage1Entry->getTotalGL7();
                        $stage1OverallGL8 += $stage1Entry->getTotalGL8();
                        $stage1OverallGL9 += $stage1Entry->getTotalGL9();
                        $stage1OverallGL10 += $stage1Entry->getTotalGL10();
                        $stage1OverallGL11 += $stage1Entry->getTotalGL11();
                        $stage1OverallGL12 += $stage1Entry->getTotalGL12();
                        $stage1OverallGL13 += $stage1Entry->getTotalGL13();
                        $stage1OverallGL14 += $stage1Entry->getTotalGL14();
                        $stage1OverallGL15 += $stage1Entry->getTotalGL15();
                        $stage1OverallGL16 += $stage1Entry->getTotalGL16();
                        $stage1OverallGL17 += $stage1Entry->getTotalGL17();
                        $stage1OverallConsolidated += $stage1Entry->getTotalConsolidated();

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
                    $stage1Analysis->setOverallGl1($stage1OverallGL1);
                    $stage1Analysis->setOverallGl2($stage1OverallGL2);
                    $stage1Analysis->setOverallGl3($stage1OverallGL3);
                    $stage1Analysis->setOverallGl4($stage1OverallGL4);
                    $stage1Analysis->setOverallGl5($stage1OverallGL5);
                    $stage1Analysis->setOverallGl6($stage1OverallGL6);
                    $stage1Analysis->setOverallGl7($stage1OverallGL7);
                    $stage1Analysis->setOverallGl8($stage1OverallGL8);
                    $stage1Analysis->setOverallGl9($stage1OverallGL9);
                    $stage1Analysis->setOverallGl10($stage1OverallGL10);
                    $stage1Analysis->setOverallGl11($stage1OverallGL11);
                    $stage1Analysis->setOverallGl12($stage1OverallGL12);
                    $stage1Analysis->setOverallGl13($stage1OverallGL13);
                    $stage1Analysis->setOverallGl14($stage1OverallGL14);
                    $stage1Analysis->setOverallGl15($stage1OverallGL15);
                    $stage1Analysis->setOverallGl16($stage1OverallGL16);
                    $stage1Analysis->setOverallGl17($stage1OverallGL17);
                    $stage1Analysis->setOverallConsolidated($stage1OverallConsolidated);

                    $stage1Analysis->setOverallTotal($overallTotal);
                    $stage1Analysis->setOverallPercentage(number_format($this->fix100PercentIssue($overallPercentage)));

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

    public function getFederalLevelPostDistributionReport($whichMode, $organizationId, $submissionYear):?FederalLevelPostDistributionAnalysis
    {
        $report = null;

        $statement = null;

        try {

            $report = $this->getFederalLevelStage1Distribution($whichMode, $organizationId, $submissionYear);

            if ($report) { //if so then continue

                //STAGE 1 ANALYSIS
                /**
                 * @var FederalLevelStage1Analysis $stage1Analysis
                 */
                $stage1Analysis = $report->getStage1();

                if ($stage1Analysis) {

                    //STAGE 2 ANALYSIS
                    $stage2Analysis = new FederalLevelStage2Analysis();
                    $stage2Data = array();

                    $stage2OverallGL_1_to_4 = 0;
                    $stage2OverallPercentageGL_1_to_4 = 0;
                    $stage2OverallGL_5_to_6 = 0;
                    $stage2OverallPercentageGL_5_to_6 = 0;
                    $stage2OverallGL_1_to_6 = 0;
                    $stage2OverallPercentageGL_1_to_6 = 0;
                    $stage2OverallGL_7_to_10 = 0;
                    $stage2OverallPercentageGL_7_to_10 = 0;
                    $stage2OverallGL_11_to_14 = 0;
                    $stage2OverallPercentageGL_11_to_14 = 0;
                    $stage2OverallGL_15_And_Above = 0;
                    $stage2OverallPercentageGL_15_And_Above = 0;
                    $stage2OverallGL_7_to_15_And_Above = 0;
                    $stage2OverallPercentageGL_7_to_15_And_Above = 0;

                    $stage2OverallTotal = 0;
                    $stage2OverallPercentage = 0;


                    foreach ($stage1Analysis->getStage1Data() as $stage1StateData) {
                        $stage2Entry = new FederalLevelStage2Entry();

                        //$stage1StateData = new FederalLevelStage1Entry(); //REMOVE THIS

                        $stage2Entry->setStateCode($stage1StateData->getStateCode());
                        $stage2Entry->setStateName($stage1StateData->getStateName());

                        $stage2Entry->setTotalGL1To4($stage1StateData->calculateGL_1_to_4());
                        $stage2Entry->setTotalGL5To6($stage1StateData->calculateGL_5_to_6());
                        $stage2Entry->setTotalGL1To6($stage1StateData->calculateGL_1_to_4() + $stage1StateData->calculateGL_5_to_6());

                        $stage2Entry->setTotalGL7To10($stage1StateData->calculateGL_7_to_10());
                        $stage2Entry->setTotalGL11To14($stage1StateData->calculateGL_11_to_14());
                        $stage2Entry->setTotalGL15AndAbove($stage1StateData->calculateGL_15_And_Above());
                        $stage2Entry->setTotalGL7To15AndAbove($stage1StateData->calculateGL_7_to_10() +
                            $stage1StateData->calculateGL_11_to_14() +
                            $stage1StateData->calculateGL_15_And_Above());

                        $stage2Data[] = $stage2Entry;

                        //do other totals
                        $stage2OverallGL_1_to_4 += $stage2Entry->getTotalGL1To4();
                        $stage2OverallGL_5_to_6 += $stage2Entry->getTotalGL5To6();
                        $stage2OverallGL_1_to_6 += $stage2Entry->getTotalGL1To6();
                        $stage2OverallGL_7_to_10 += $stage2Entry->getTotalGL7To10();
                        $stage2OverallGL_11_to_14 += $stage2Entry->getTotalGL11To14();
                        $stage2OverallGL_15_And_Above += $stage2Entry->getTotalGL15AndAbove();
                        $stage2OverallGL_7_to_15_And_Above += $stage2Entry->getTotalGL7To15AndAbove();

                        $stage2Entry->calculateTotal();
                        $stage2OverallTotal += $stage2Entry->getTotal();

                    }

                    $stage2CategoryTotals = array(
                        'overall_GL_1_to_4' => $stage2OverallGL_1_to_4,
                        'overall_GL_5_to_6' => $stage2OverallGL_5_to_6,
                        'overall_GL_1_to_6' => $stage2OverallGL_1_to_6,
                        'overall_GL_7_to_10' => $stage2OverallGL_7_to_10,
                        'overall_GL_11_to_14' => $stage2OverallGL_11_to_14,
                        'overall_GL_15_And_Above' => $stage2OverallGL_15_And_Above,
                        'overall_GL_7_to_15_And_Above' => $stage2OverallGL_7_to_15_And_Above,
                    );

                    foreach ($stage2Data as $stage2_data) {
                        $stage2_data->calculatePercentage($stage2OverallTotal);
                        $stage2_data->calculateCategoryPercentages($stage2CategoryTotals);

                        $stage2OverallPercentageGL_1_to_4 += $stage2_data->getPercentageGL1To4();
                        $stage2OverallPercentageGL_5_to_6 += $stage2_data->getPercentageGL5To6();
                        $stage2OverallPercentageGL_1_to_6 += $stage2_data->getPercentageGL1To6();
                        $stage2OverallPercentageGL_7_to_10 += $stage2_data->getPercentageGL7To10();
                        $stage2OverallPercentageGL_11_to_14 += $stage2_data->getPercentageGL11To14();
                        $stage2OverallPercentageGL_15_And_Above += $stage2_data->getPercentageGL15AndAbove();
                        $stage2OverallPercentageGL_7_to_15_And_Above += $stage2_data->getPercentageGL7To15AndAbove();

                        $stage2OverallPercentage += $stage2_data->getPercentage();
                    }

                    $stage2Analysis->setStage2Data($stage2Data);

                    $stage2Analysis->setOverallGL1To4($stage2OverallGL_1_to_4);
                    $stage2Analysis->setOverallPercentageGL1To4(number_format($this->fix100PercentIssue($stage2OverallPercentageGL_1_to_4)));

                    $stage2Analysis->setOverallGL5To6($stage2OverallGL_5_to_6);
                    $stage2Analysis->setOverallPercentageGL5To6(number_format($this->fix100PercentIssue($stage2OverallPercentageGL_5_to_6)));

                    $stage2Analysis->setOverallGL1To6($stage2OverallGL_1_to_6);
                    $stage2Analysis->setOverallPercentageGL1To6(number_format($this->fix100PercentIssue($stage2OverallPercentageGL_1_to_6)));

                    $stage2Analysis->setOverallGL7To10($stage2OverallGL_7_to_10);
                    $stage2Analysis->setOverallPercentageGL7To10(number_format($this->fix100PercentIssue($stage2OverallPercentageGL_7_to_10)));

                    $stage2Analysis->setOverallGL11To14($stage2OverallGL_11_to_14);
                    $stage2Analysis->setOverallPercentageGL11To14(number_format($this->fix100PercentIssue($stage2OverallPercentageGL_11_to_14)));

                    $stage2Analysis->setOverallGL15AndAbove($stage2OverallGL_15_And_Above);
                    $stage2Analysis->setOverallPercentageGL15AndAbove(number_format($this->fix100PercentIssue($stage2OverallPercentageGL_15_And_Above)));

                    $stage2Analysis->setOverallGL7To15AndAbove($stage2OverallGL_7_to_15_And_Above);
                    $stage2Analysis->setOverallPercentageGL7To15AndAbove(number_format($this->fix100PercentIssue($stage2OverallPercentageGL_7_to_15_And_Above)));

                    $stage2Analysis->setOverallTotal($stage2OverallTotal);
                    $stage2Analysis->setOverallPercentage(number_format($this->fix100PercentIssue($stage2OverallPercentage)));

                    $report->setStage2($stage2Analysis);

                    //STAGE 3 ANALYSIS
                    $stage3Analysis = new FederalLevelStage3Analysis();

                    $stage3Data = array();

                    $stage3GL1to4Series = array();
                    $stage3GL5to6Series = array();
                    $stage3GL7to10Series = array();
                    $stage3GL11to14Series = array();
                    $stage3GL15AndAbove = array();

                    $stage3XAxisLabels = array();

                    foreach ($stage2Analysis->getStage2Data() as $stage2_Data) {

                        //$stage2_Data = new FederalLevelStage2Entry();

                        $stage3XAxisLabels[] = $stage2_Data->getStateCode();

                        $stage3GL1to4Series[] = $stage2_Data->getTotalGL1To4();
                        $stage3GL5to6Series[] = $stage2_Data->getTotalGL5To6();
                        $stage3GL7to10Series[] = $stage2_Data->getTotalGL7To10();
                        $stage3GL11to14Series[] = $stage2_Data->getTotalGL11To14();
                        $stage3GL15AndAbove[] = $stage2_Data->getTotalGL15AndAbove();
                    }

                    $stage3Data[] = json_encode($stage3GL1to4Series);
                    $stage3Data[] = json_encode($stage3GL5to6Series);
                    $stage3Data[] = json_encode($stage3GL7to10Series);
                    $stage3Data[] = json_encode($stage3GL11to14Series);
                    $stage3Data[] = json_encode($stage3GL15AndAbove);

                    $stage3Analysis->setStage3Data($stage3Data);
                    $stage3Analysis->setXAxisLabels("['" . implode("','", $stage3XAxisLabels) . "']");

                    $report->setStage3($stage3Analysis);


                    //STAGE 4 ANALYSIS
                    //prepare a map of zone_id -> states array
                    $zoneStatesMap = array();

                    $stage4Analysis = new FederalLevelStage4Analysis();

                    //select the geo-political zone ids and name
                    $query = "SELECT id, zone_code, zone_name FROM geo_political_zone WHERE zone_code<>:zone_code AND record_status=:record_status";
                    $statement = $this->connection->prepare($query);
                    $statement->bindValue(':zone_code', 'NON');
                    $statement->bindValue(':record_status', AppConstants::ACTIVE);

                    $statement->execute();

                    $geoPoliticalZones = $statement->fetchAll();

                    $stage4TempData = array();

                    //setup the zonestatesmap and a temp map of subanalysis
                    foreach ($geoPoliticalZones as $geoPoliticalZone) {

                        $zoneStatesMap[$geoPoliticalZone['id']] = array();

                        $stage4SubAnalysis = new FederalLevelStage4SubAnalysis();
                        $stage4SubAnalysis->setZoneId($geoPoliticalZone['id']);
                        $stage4SubAnalysis->setZoneCode($geoPoliticalZone['zone_code']);
                        $stage4SubAnalysis->setZoneName($geoPoliticalZone['zone_name']);

                        $stage4TempData[$geoPoliticalZone['id']] = $stage4SubAnalysis;

                    }

                    //fill the zone states map
                    foreach ($stage1Analysis->getStage1Data() as $stage_1_data) {
                        $zoneStatesMap[$stage_1_data->getGeoPoliticalZoneId()][] = $stage_1_data;
                    }

                    //now do the sub analysis entries
                    foreach ($stage4TempData as $k => $subAnalysis) {

                        $stage4SubAnalysisData = array();

                        $stage4SubOverallGL_1_to_3 = 0;
                        $stage4SubOverallPercentageGL_1_to_3 = 0;
                        $stage4SubOverallGL_4_to_5 = 0;
                        $stage4SubOverallPercentageGL_4_to_5 = 0;
                        $stage4SubOverallGL6 = 0;
                        $stage4SubOverallPercentageGL6 = 0;
                        $stage4SubOverallTotal = 0;
                        $stage4SubOverallPercentage = 0;

                        foreach ($zoneStatesMap[$k] as $stage_1_zonestate) {
                            $stage4SubAnalysisEntry = new FederalLevelStage4SubAnalysisEntry();
                            $stage4SubAnalysisEntry->setStateCode($stage_1_zonestate->getStateCode());
                            $stage4SubAnalysisEntry->setStateName($stage_1_zonestate->getStateName());

                            //$stage_1_zonestate = new FederalLevelStage1Entry(); //REMOVE THIS

                            $stage4SubAnalysisEntry->setTotalGL1To3($stage_1_zonestate->calculateGL_1_to_3());
                            $stage4SubAnalysisEntry->setTotalGL4To5($stage_1_zonestate->calculateGL_4_to_5());
                            $stage4SubAnalysisEntry->setTotalGL6($stage_1_zonestate->getTotalGL6());

                            $stage4SubOverallGL_1_to_3 += $stage4SubAnalysisEntry->getTotalGL1To3();
                            $stage4SubOverallGL_4_to_5 += $stage4SubAnalysisEntry->getTotalGL4To5();
                            $stage4SubOverallGL6 += $stage4SubAnalysisEntry->getTotalGL6();

                            $stage4SubAnalysisEntry->calculateTotal();
                            $stage4SubOverallTotal += $stage4SubAnalysisEntry->getTotal();

                            $stage4SubAnalysisData[] = $stage4SubAnalysisEntry;
                        }

                        //sort sub analysis entries
                        usort($stage4SubAnalysisData, array("\\AppBundle\\Model\\Reporting\\FederalLevelStage4SubAnalysisEntry", "cmp_obj"));

                        //$subAnalysis = new FederalLevelStage4SubAnalysis(); //REMOVE LATER
                        $subAnalysis->setOverallGL1To3($stage4SubOverallGL_1_to_3);
                        $subAnalysis->setOverallGL4To5($stage4SubOverallGL_4_to_5);
                        $subAnalysis->setOverallGL6($stage4SubOverallGL6);
                        $subAnalysis->setOverallTotal($stage4SubOverallTotal);

                        $stage4SubCategoryTotals = array(
                            'overall_GL_1_to_3' => $subAnalysis->getOverallGL1To3(),
                            'overall_GL_4_to_5' => $subAnalysis->getOverallGL4To5(),
                            'overall_GL_6' => $subAnalysis->getOverallGL6()
                        );

                        foreach ($stage4SubAnalysisData as $stage_4_sub_entry) {

                            //$stage_4_sub_entry = new FederalLevelStage4SubAnalysisEntry(); //REMOVE LATER

                            $stage_4_sub_entry->calculatePercentage($subAnalysis->getOverallTotal());
                            $stage_4_sub_entry->calculateCategoryPercentages($stage4SubCategoryTotals);

                            $stage4SubOverallPercentageGL_1_to_3 += $stage_4_sub_entry->getPercentageGL1To3();
                            $stage4SubOverallPercentageGL_4_to_5 += $stage_4_sub_entry->getPercentageGL4To5();
                            $stage4SubOverallPercentageGL6 += $stage_4_sub_entry->getPercentageGL6();
                            $stage4SubOverallPercentage += $stage_4_sub_entry->getPercentage();

                        }

                        $subAnalysis->setOverallPercentageGL1To3(number_format($this->fix100PercentIssue($stage4SubOverallPercentageGL_1_to_3)));
                        $subAnalysis->setOverallPercentageGL4To5(number_format($this->fix100PercentIssue($stage4SubOverallPercentageGL_4_to_5)));
                        $subAnalysis->setOverallPercentageGL6(number_format($this->fix100PercentIssue($stage4SubOverallPercentageGL6)));
                        $subAnalysis->setOverallPercentage(number_format($this->fix100PercentIssue($stage4SubOverallPercentage)));

                        $subAnalysis->setSubAnalysisData($stage4SubAnalysisData);
                    }

                    $stage4Data = array_values($stage4TempData);

                    //sort sub analysis
                    usort($stage4Data, array("\\AppBundle\\Model\\Reporting\\FederalLevelStage4SubAnalysis", "cmp_obj"));

                    $stage4Analysis->setStage4Data($stage4Data);

                    $report->setStage4($stage4Analysis);


                    //STAGE 5 ANALYSIS

                    $stage5Analysis = new FederalLevelStage5Analysis();

                    $stage5TempData = array();

                    //setup the temp map of stage 5 subanalysis
                    foreach ($geoPoliticalZones as $geoPoliticalZone) {

                        $stage5SubAnalysis = new FederalLevelStage5SubAnalysis();
                        $stage5SubAnalysis->setZoneId($geoPoliticalZone['id']);
                        $stage5SubAnalysis->setZoneCode($geoPoliticalZone['zone_code']);
                        $stage5SubAnalysis->setZoneName($geoPoliticalZone['zone_name']);

                        $stage5TempData[$geoPoliticalZone['id']] = $stage5SubAnalysis;

                    }

                    //now do the stage 5 sub analysis entries
                    foreach ($stage5TempData as $k => $stage5_SubAnalysis) {

                        $stage5SubAnalysisData = array();

                        $stage5SubOverallGL_7_to_10 = 0;
                        $stage5SubOverallPercentageGL_7_to_10 = 0;
                        $stage5SubOverallGL_12_to_14 = 0;
                        $stage5SubOverallPercentageGL_12_to_14 = 0;
                        $stage5SubOverallGL_15_And_Above = 0;
                        $stage5SubOverallPercentageGL_15_And_Above = 0;
                        $stage5SubOverallTotal = 0;
                        $stage5SubOverallPercentage = 0;

                        foreach ($zoneStatesMap[$k] as $stage_1_zonestate) {
                            $stage5SubAnalysisEntry = new FederalLevelStage5SubAnalysisEntry();
                            $stage5SubAnalysisEntry->setStateCode($stage_1_zonestate->getStateCode());
                            $stage5SubAnalysisEntry->setStateName($stage_1_zonestate->getStateName());

                            //$stage_1_zonestate = new FederalLevelStage1Entry(); //REMOVE THIS

                            $stage5SubAnalysisEntry->setTotalGL7To10($stage_1_zonestate->calculateGL_7_to_10());
                            $stage5SubAnalysisEntry->setTotalGL12To14($stage_1_zonestate->calculateGL_12_to_14());
                            $stage5SubAnalysisEntry->setTotalGL15AndAbove($stage_1_zonestate->calculateGL_15_And_Above());

                            $stage5SubOverallGL_7_to_10 += $stage5SubAnalysisEntry->getTotalGL7To10();
                            $stage5SubOverallGL_12_to_14 += $stage5SubAnalysisEntry->getTotalGL12To14();
                            $stage5SubOverallGL_15_And_Above += $stage5SubAnalysisEntry->getTotalGL15AndAbove();

                            $stage5SubAnalysisEntry->calculateTotal();
                            $stage5SubOverallTotal += $stage5SubAnalysisEntry->getTotal();

                            $stage5SubAnalysisData[] = $stage5SubAnalysisEntry;
                        }

                        //sort sub analysis entries
                        usort($stage5SubAnalysisData, array("\\AppBundle\\Model\\Reporting\\FederalLevelStage5SubAnalysisEntry", "cmp_obj"));

                        //$stage5_SubAnalysis = new FederalLevelStage5SubAnalysis(); //REMOVE LATER
                        $stage5_SubAnalysis->setOverallGL7To10($stage5SubOverallGL_7_to_10);
                        $stage5_SubAnalysis->setOverallGL12To14($stage5SubOverallGL_12_to_14);
                        $stage5_SubAnalysis->setOverallGL15AndAbove($stage5SubOverallGL_15_And_Above);
                        $stage5_SubAnalysis->setOverallTotal($stage5SubOverallTotal);

                        $stage5SubCategoryTotals = array(
                            'overall_GL_7_to_10' => $stage5_SubAnalysis->getOverallGL7To10(),
                            'overall_GL_12_to_14' => $stage5_SubAnalysis->getOverallGL12To14(),
                            'overall_GL_15_And_Above' => $stage5_SubAnalysis->getOverallGL15AndAbove()
                        );

                        foreach ($stage5SubAnalysisData as $stage_5_sub_entry) {

                            //$stage_5_sub_entry = new FederalLevelStage5SubAnalysisEntry(); //REMOVE LATER

                            $stage_5_sub_entry->calculatePercentage($stage5_SubAnalysis->getOverallTotal());
                            $stage_5_sub_entry->calculateCategoryPercentages($stage5SubCategoryTotals);

                            $stage5SubOverallPercentageGL_7_to_10 += $stage_5_sub_entry->getPercentageGL7To10();
                            $stage5SubOverallPercentageGL_12_to_14 += $stage_5_sub_entry->getPercentageGL12To14();
                            $stage5SubOverallPercentageGL_15_And_Above += $stage_5_sub_entry->getPercentageGL15AndAbove();
                            $stage5SubOverallPercentage += $stage_5_sub_entry->getPercentage();

                        }

                        $stage5_SubAnalysis->setOverallPercentageGL7To10(number_format($this->fix100PercentIssue($stage5SubOverallPercentageGL_7_to_10)));
                        $stage5_SubAnalysis->setOverallPercentageGL12To14(number_format($this->fix100PercentIssue($stage5SubOverallPercentageGL_12_to_14)));
                        $stage5_SubAnalysis->setOverallPercentageGL15AndAbove(number_format($this->fix100PercentIssue($stage5SubOverallPercentageGL_15_And_Above)));
                        $stage5_SubAnalysis->setOverallPercentage(number_format($this->fix100PercentIssue($stage5SubOverallPercentage)));

                        $stage5_SubAnalysis->setSubAnalysisData($stage5SubAnalysisData);
                    }

                    $stage5Data = array_values($stage5TempData);

                    //sort sub analysis
                    usort($stage5Data, array("\\AppBundle\\Model\\Reporting\\FederalLevelStage5SubAnalysis", "cmp_obj"));

                    $stage5Analysis->setStage5Data($stage5Data);

                    $report->setStage5($stage5Analysis);

                    //STAGE 6 ANALYSIS

                    $stage6Analysis = new FederalLevelStage6Analysis();

                    $stage6_4pt0_Or_More_SubAnalysis = new FederalLevelStage6SubAnalysis("4.0% or more");
                    $stage6_4pt0_Or_More_SubAnalysis_Entries = array();
                    $stage6_3pt1_to_3pt9_SubAnalysis = new FederalLevelStage6SubAnalysis("between 3.1% and 3.9%");
                    $stage6_3pt1_to_3pt9_SubAnalysis_Entries = array();
                    $stage6_2pt5_to_3_SubAnalysis = new FederalLevelStage6SubAnalysis("between 2.5% and 3.0%");
                    $stage6_2pt5_to_3_SubAnalysis_Entries = array();
                    $stage6_1pt5_to_2pt4_SubAnalysis = new FederalLevelStage6SubAnalysis("between 1.5% and 2.4%");
                    $stage6_1pt5_to_2pt4_SubAnalysis_Entries = array();
                    $stage6_LessThan_1pt5_SubAnalysis = new FederalLevelStage6SubAnalysis("less than 1.5%");
                    $stage6_LessThan_1pt5_SubAnalysis_Entries = array();

                    $stage6AllEntriesTempData = array();

                    //setup the temp array of stage 6 entries
                    foreach ($stage1Analysis->getStage1Data() as $stage1_entry) {

                        $stage6SubAnalysisEntry = new FederalLevelStage6SubAnalysisEntry();
                        $stage6SubAnalysisEntry->setStateCode($stage1_entry->getStateCode());
                        $stage6SubAnalysisEntry->setStateName($stage1_entry->getStateName());

                        $stage6SubAnalysisEntry->setTotalGL7AndAbove($stage1_entry->calculateGL_7_And_Above());
                        $stage6SubAnalysisEntry->calculatePercentage($stage2Analysis->getOverallGL7To15AndAbove()); //calculate with stage 2 overal 7-15+

                        $stage6AllEntriesTempData[] = $stage6SubAnalysisEntry;

                    }

                    foreach ($stage6AllEntriesTempData as $stage6_SubAnalysis_Entry) {
                        //$stage6_SubAnalysis_Entry = new FederalLevelStage6SubAnalysisEntry(); //REMOVE LATER

                        $percentageGL7AndAbove = $stage6_SubAnalysis_Entry->getPercentageGL7AndAbove();
                        if ($percentageGL7AndAbove >= 4.0) {
                            $stage6_4pt0_Or_More_SubAnalysis_Entries[] = $stage6_SubAnalysis_Entry;
                        } else if ($percentageGL7AndAbove >= 3.1 && $percentageGL7AndAbove <= 3.9) {
                            $stage6_3pt1_to_3pt9_SubAnalysis_Entries[] = $stage6_SubAnalysis_Entry;
                        } else if ($percentageGL7AndAbove >= 2.5 && $percentageGL7AndAbove <= 3.0) {
                            $stage6_2pt5_to_3_SubAnalysis_Entries[] = $stage6_SubAnalysis_Entry;
                        } else if ($percentageGL7AndAbove >= 1.5 && $percentageGL7AndAbove <= 2.4) {
                            $stage6_1pt5_to_2pt4_SubAnalysis_Entries[] = $stage6_SubAnalysis_Entry;
                        } else if ($percentageGL7AndAbove < 1.5) {
                            $stage6_LessThan_1pt5_SubAnalysis_Entries[] = $stage6_SubAnalysis_Entry;
                        }

                        //$stage6_4pt0_Or_More_SubAnalysis_Entries[] = $stage6_SubAnalysis_Entry;
                    }

                    //sort sub analysis entries
                    usort($stage6_4pt0_Or_More_SubAnalysis_Entries, array("\\AppBundle\\Model\\Reporting\\FederalLevelStage6SubAnalysisEntry", "cmp_obj"));
                    usort($stage6_3pt1_to_3pt9_SubAnalysis_Entries, array("\\AppBundle\\Model\\Reporting\\FederalLevelStage6SubAnalysisEntry", "cmp_obj"));
                    usort($stage6_2pt5_to_3_SubAnalysis_Entries, array("\\AppBundle\\Model\\Reporting\\FederalLevelStage6SubAnalysisEntry", "cmp_obj"));
                    usort($stage6_1pt5_to_2pt4_SubAnalysis_Entries, array("\\AppBundle\\Model\\Reporting\\FederalLevelStage6SubAnalysisEntry", "cmp_obj"));
                    usort($stage6_LessThan_1pt5_SubAnalysis_Entries, array("\\AppBundle\\Model\\Reporting\\FederalLevelStage6SubAnalysisEntry", "cmp_obj"));

                    $stage6_4pt0_Or_More_SubAnalysis->setSubAnalysisData($stage6_4pt0_Or_More_SubAnalysis_Entries);
                    $stage6_3pt1_to_3pt9_SubAnalysis->setSubAnalysisData($stage6_3pt1_to_3pt9_SubAnalysis_Entries);
                    $stage6_2pt5_to_3_SubAnalysis->setSubAnalysisData($stage6_2pt5_to_3_SubAnalysis_Entries);
                    $stage6_1pt5_to_2pt4_SubAnalysis->setSubAnalysisData($stage6_1pt5_to_2pt4_SubAnalysis_Entries);
                    $stage6_LessThan_1pt5_SubAnalysis->setSubAnalysisData($stage6_LessThan_1pt5_SubAnalysis_Entries);

                    $stage6Data = array();
                    $stage6Data[] = $stage6_4pt0_Or_More_SubAnalysis;
                    $stage6Data[] = $stage6_3pt1_to_3pt9_SubAnalysis;
                    $stage6Data[] = $stage6_2pt5_to_3_SubAnalysis;
                    $stage6Data[] = $stage6_1pt5_to_2pt4_SubAnalysis;
                    $stage6Data[] = $stage6_LessThan_1pt5_SubAnalysis;

                    $stage6OverallSubAnalysisTotal = 0;

                    foreach ($stage6Data as $stage6_TempSubAnalysis) {
                        $stage6SubOverallGL_7_And_Above = 0;
                        $stage6SubOverallPercentageGL_7_And_Above = 0;

                        foreach ($stage6_TempSubAnalysis->getSubAnalysisData() as $stage6_Category_Entry) {
                            $stage6SubOverallGL_7_And_Above += $stage6_Category_Entry->getTotalGL7AndAbove();
                            $stage6SubOverallPercentageGL_7_And_Above += $stage6_Category_Entry->getPercentageGL7AndAbove();
                        }

                        $stage6_TempSubAnalysis->setOverallGL7AndAbove($stage6SubOverallGL_7_And_Above);
                        $stage6_TempSubAnalysis->setOverallPercentageGL7AndAbove($stage6SubOverallPercentageGL_7_And_Above);

                        $stage6OverallSubAnalysisTotal += $stage6_TempSubAnalysis->getOverallGL7AndAbove();
                    }

                    $stage6Analysis->setStage6Data($stage6Data);
                    $stage6Analysis->setOverallSubAnalysisTotal($stage6OverallSubAnalysisTotal);

                    $report->setStage6($stage6Analysis);

                    //STAGE 7 ANALYSIS

                    $stage7Analysis = new FederalLevelStage7Analysis();

                    $stage7_4pt0_Or_More_SubAnalysis = new FederalLevelStage7SubAnalysis("4.0% or more");
                    $stage7_4pt0_Or_More_SubAnalysis_Entries = array();
                    $stage7_3pt1_to_3pt9_SubAnalysis = new FederalLevelStage7SubAnalysis("between 3.1% and 3.9%");
                    $stage7_3pt1_to_3pt9_SubAnalysis_Entries = array();
                    $stage7_2pt5_to_3_SubAnalysis = new FederalLevelStage7SubAnalysis("between 2.5% and 3.0%");
                    $stage7_2pt5_to_3_SubAnalysis_Entries = array();
                    $stage7_1pt5_to_2pt4_SubAnalysis = new FederalLevelStage7SubAnalysis("between 1.5% and 2.4%");
                    $stage7_1pt5_to_2pt4_SubAnalysis_Entries = array();
                    $stage7_LessThan_1pt5_SubAnalysis = new FederalLevelStage7SubAnalysis("less than 1.5%");
                    $stage7_LessThan_1pt5_SubAnalysis_Entries = array();

                    $stage7AllEntriesTempData = array();

                    //setup the temp array of stage 7 entries
                    foreach ($stage1Analysis->getStage1Data() as $stage1_entry) {

                        $stage7SubAnalysisEntry = new FederalLevelStage7SubAnalysisEntry();
                        $stage7SubAnalysisEntry->setStateCode($stage1_entry->getStateCode());
                        $stage7SubAnalysisEntry->setStateName($stage1_entry->getStateName());

                        $stage7SubAnalysisEntry->setTotalGL15AndAbove($stage1_entry->calculateGL_15_And_Above());
                        $stage7SubAnalysisEntry->calculatePercentage($stage2Analysis->getOverallGL15AndAbove()); //calculate with stage 2 overal 15+

                        $stage7AllEntriesTempData[] = $stage7SubAnalysisEntry;

                    }

                    foreach ($stage7AllEntriesTempData as $stage7_SubAnalysis_Entry) {

                        $percentageGL15AndAbove = $stage7_SubAnalysis_Entry->getPercentageGL15AndAbove();
                        if ($percentageGL15AndAbove >= 4.0) {
                            $stage7_4pt0_Or_More_SubAnalysis_Entries[] = $stage7_SubAnalysis_Entry;
                        } else if ($percentageGL15AndAbove >= 3.1 && $percentageGL15AndAbove <= 3.9) {
                            $stage7_3pt1_to_3pt9_SubAnalysis_Entries[] = $stage7_SubAnalysis_Entry;
                        } else if ($percentageGL15AndAbove >= 2.5 && $percentageGL15AndAbove <= 3.0) {
                            $stage7_2pt5_to_3_SubAnalysis_Entries[] = $stage7_SubAnalysis_Entry;
                        } else if ($percentageGL15AndAbove >= 1.5 && $percentageGL15AndAbove <= 2.4) {
                            $stage7_1pt5_to_2pt4_SubAnalysis_Entries[] = $stage7_SubAnalysis_Entry;
                        } else if ($percentageGL15AndAbove < 1.5) {
                            $stage7_LessThan_1pt5_SubAnalysis_Entries[] = $stage7_SubAnalysis_Entry;
                        }

                        //$stage7_4pt0_Or_More_SubAnalysis_Entries[] = $stage7_SubAnalysis_Entry;
                    }

                    //sort sub analysis entries
                    usort($stage7_4pt0_Or_More_SubAnalysis_Entries, array("\\AppBundle\\Model\\Reporting\\FederalLevelStage7SubAnalysisEntry", "cmp_obj"));
                    usort($stage7_3pt1_to_3pt9_SubAnalysis_Entries, array("\\AppBundle\\Model\\Reporting\\FederalLevelStage7SubAnalysisEntry", "cmp_obj"));
                    usort($stage7_2pt5_to_3_SubAnalysis_Entries, array("\\AppBundle\\Model\\Reporting\\FederalLevelStage7SubAnalysisEntry", "cmp_obj"));
                    usort($stage7_1pt5_to_2pt4_SubAnalysis_Entries, array("\\AppBundle\\Model\\Reporting\\FederalLevelStage7SubAnalysisEntry", "cmp_obj"));
                    usort($stage7_LessThan_1pt5_SubAnalysis_Entries, array("\\AppBundle\\Model\\Reporting\\FederalLevelStage7SubAnalysisEntry", "cmp_obj"));

                    $stage7_4pt0_Or_More_SubAnalysis->setSubAnalysisData($stage7_4pt0_Or_More_SubAnalysis_Entries);
                    $stage7_3pt1_to_3pt9_SubAnalysis->setSubAnalysisData($stage7_3pt1_to_3pt9_SubAnalysis_Entries);
                    $stage7_2pt5_to_3_SubAnalysis->setSubAnalysisData($stage7_2pt5_to_3_SubAnalysis_Entries);
                    $stage7_1pt5_to_2pt4_SubAnalysis->setSubAnalysisData($stage7_1pt5_to_2pt4_SubAnalysis_Entries);
                    $stage7_LessThan_1pt5_SubAnalysis->setSubAnalysisData($stage7_LessThan_1pt5_SubAnalysis_Entries);

                    $stage7Data = array();
                    $stage7Data[] = $stage7_4pt0_Or_More_SubAnalysis;
                    $stage7Data[] = $stage7_3pt1_to_3pt9_SubAnalysis;
                    $stage7Data[] = $stage7_2pt5_to_3_SubAnalysis;
                    $stage7Data[] = $stage7_1pt5_to_2pt4_SubAnalysis;
                    $stage7Data[] = $stage7_LessThan_1pt5_SubAnalysis;

                    $stage7OverallSubAnalysisTotal = 0;

                    foreach ($stage7Data as $stage7_TempSubAnalysis) {
                        $stage7SubOverallGL_15_And_Above = 0;
                        $stage7SubOverallPercentageGL_15_And_Above = 0;

                        foreach ($stage7_TempSubAnalysis->getSubAnalysisData() as $stage7_Category_Entry) {
                            $stage7SubOverallGL_15_And_Above += $stage7_Category_Entry->getTotalGL15AndAbove();
                            $stage7SubOverallPercentageGL_15_And_Above += $stage7_Category_Entry->getPercentageGL15AndAbove();
                        }

                        $stage7_TempSubAnalysis->setOverallGL15AndAbove($stage7SubOverallGL_15_And_Above);
                        $stage7_TempSubAnalysis->setOverallPercentageGL15AndAbove($stage7SubOverallPercentageGL_15_And_Above);

                        $stage7OverallSubAnalysisTotal += $stage7_TempSubAnalysis->getOverallGL15AndAbove();
                    }

                    $stage7Analysis->setStage7Data($stage7Data);
                    $stage7Analysis->setOverallSubAnalysisTotal($stage7OverallSubAnalysisTotal);

                    $report->setStage7($stage7Analysis);

                    //STAGE 8 ANALYSIS

                    $stage8Analysis = new FederalLevelStage8Analysis();

                    $stage8GL7AndAboveAnalysis = new FederalLevelStage8CategoryAnalysis();

                    $stage8GL7AndAboveTempData = array();

                    //setup the temp map of stage 8 subanalysis
                    foreach ($geoPoliticalZones as $geoPoliticalZone) {

                        $stage8GL7AndAboveSubAnalysis = new FederalLevelStage8SubAnalysis();
                        $stage8GL7AndAboveSubAnalysis->setZoneId($geoPoliticalZone['id']);
                        $stage8GL7AndAboveSubAnalysis->setZoneCode($geoPoliticalZone['zone_code']);
                        $stage8GL7AndAboveSubAnalysis->setZoneName($geoPoliticalZone['zone_name']);

                        $stage8GL7AndAboveTempData[$geoPoliticalZone['id']] = $stage8GL7AndAboveSubAnalysis;

                    }

                    //now do the stage 8 gl7 and above sub analysis entries
                    $overallStage8GL7AndAboveSubAnalysisTotal = 0;
                    foreach ($stage8GL7AndAboveTempData as $k => $stage8_GL7AndAboveSubAnalysis) {

                        $stage8GL7AndAboveSubAnalysisData = array();

                        $stage8GL7AndAboveSubOverallTotal = 0;
                        $stage8GL7AndAboveSubOverallPercentage = 0;

                        foreach ($zoneStatesMap[$k] as $stage_1_zonestate) {
                            $stage8GL7AndAboveSubAnalysisEntry = new FederalLevelStage8SubAnalysisEntry();
                            $stage8GL7AndAboveSubAnalysisEntry->setStateCode($stage_1_zonestate->getStateCode());
                            $stage8GL7AndAboveSubAnalysisEntry->setStateName($stage_1_zonestate->getStateName());

                            //$stage_1_zonestate = new FederalLevelStage1Entry(); //REMOVE THIS

                            $stage8GL7AndAboveSubAnalysisEntry->setTotal($stage_1_zonestate->calculateGL_7_And_Above());
                            $stage8GL7AndAboveSubAnalysisEntry->calculatePercentage($stage2Analysis->getOverallGL7To15AndAbove());

                            $stage8GL7AndAboveSubOverallTotal += $stage8GL7AndAboveSubAnalysisEntry->getTotal();
                            $stage8GL7AndAboveSubOverallPercentage += $stage8GL7AndAboveSubAnalysisEntry->getPercentage();

                            $stage8GL7AndAboveSubAnalysisData[] = $stage8GL7AndAboveSubAnalysisEntry;
                        }

                        //sort sub analysis entries
                        usort($stage8GL7AndAboveSubAnalysisData, array("\\AppBundle\\Model\\Reporting\\FederalLevelStage8SubAnalysisEntry", "cmp_obj"));

                        $stage8_GL7AndAboveSubAnalysis->setOverallTotal($stage8GL7AndAboveSubOverallTotal);
                        $stage8_GL7AndAboveSubAnalysis->setOverallPercentage(number_format($stage8GL7AndAboveSubOverallPercentage));

                        $overallStage8GL7AndAboveSubAnalysisTotal += $stage8_GL7AndAboveSubAnalysis->getOverallTotal();

                        $stage8_GL7AndAboveSubAnalysis->setSubAnalysisData($stage8GL7AndAboveSubAnalysisData);
                    }

                    $stage8GL7AndAboveData = array_values($stage8GL7AndAboveTempData);

                    //sort sub analysis
                    usort($stage8GL7AndAboveData, array("\\AppBundle\\Model\\Reporting\\FederalLevelStage8SubAnalysis", "cmp_obj"));

                    $stage8GL7AndAboveAnalysis->setStage8CategoryData($stage8GL7AndAboveData);
                    $stage8GL7AndAboveAnalysis->setOverallCategorySubAnalysisTotal($overallStage8GL7AndAboveSubAnalysisTotal);

                    $stage8Analysis->setStage8GL7AndAboveData($stage8GL7AndAboveAnalysis);

                    //now calculate for STAGE 8 GL 15 AND ABOVE
                    $stage8GL15AndAboveAnalysis = new FederalLevelStage8CategoryAnalysis();

                    $stage8GL15AndAboveTempData = array();

                    //setup the temp map of stage 8 subanalysis
                    foreach ($geoPoliticalZones as $geoPoliticalZone) {

                        $stage8GL15AndAboveSubAnalysis = new FederalLevelStage8SubAnalysis();
                        $stage8GL15AndAboveSubAnalysis->setZoneId($geoPoliticalZone['id']);
                        $stage8GL15AndAboveSubAnalysis->setZoneCode($geoPoliticalZone['zone_code']);
                        $stage8GL15AndAboveSubAnalysis->setZoneName($geoPoliticalZone['zone_name']);

                        $stage8GL15AndAboveTempData[$geoPoliticalZone['id']] = $stage8GL15AndAboveSubAnalysis;

                    }

                    //now do the stage 8 gl5 and above sub analysis entries
                    $overallStage8GL15AndAboveSubAnalysisTotal = 0;
                    foreach ($stage8GL15AndAboveTempData as $k => $stage8_GL15AndAboveSubAnalysis) {

                        $stage8GL15AndAboveSubAnalysisData = array();

                        $stage8GL15AndAboveSubOverallTotal = 0;
                        $stage8GL15AndAboveSubOverallPercentage = 0;

                        foreach ($zoneStatesMap[$k] as $stage_1_zonestate) {
                            $stage8GL15AndAboveSubAnalysisEntry = new FederalLevelStage8SubAnalysisEntry();
                            $stage8GL15AndAboveSubAnalysisEntry->setStateCode($stage_1_zonestate->getStateCode());
                            $stage8GL15AndAboveSubAnalysisEntry->setStateName($stage_1_zonestate->getStateName());

                            //$stage_1_zonestate = new FederalLevelStage1Entry(); //REMOVE THIS

                            $stage8GL15AndAboveSubAnalysisEntry->setTotal($stage_1_zonestate->calculateGL_15_And_Above());
                            $stage8GL15AndAboveSubAnalysisEntry->calculatePercentage($stage2Analysis->getOverallGL15AndAbove());

                            $stage8GL15AndAboveSubOverallTotal += $stage8GL15AndAboveSubAnalysisEntry->getTotal();
                            $stage8GL15AndAboveSubOverallPercentage += $stage8GL15AndAboveSubAnalysisEntry->getPercentage();

                            $stage8GL15AndAboveSubAnalysisData[] = $stage8GL15AndAboveSubAnalysisEntry;
                        }

                        //sort sub analysis entries
                        usort($stage8GL15AndAboveSubAnalysisData, array("\\AppBundle\\Model\\Reporting\\FederalLevelStage8SubAnalysisEntry", "cmp_obj"));

                        $stage8_GL15AndAboveSubAnalysis->setOverallTotal($stage8GL15AndAboveSubOverallTotal);
                        $stage8_GL15AndAboveSubAnalysis->setOverallPercentage(number_format($stage8GL15AndAboveSubOverallPercentage));

                        $overallStage8GL15AndAboveSubAnalysisTotal += $stage8_GL15AndAboveSubAnalysis->getOverallTotal();

                        $stage8_GL15AndAboveSubAnalysis->setSubAnalysisData($stage8GL15AndAboveSubAnalysisData);
                    }

                    $stage8GL15AndAboveData = array_values($stage8GL15AndAboveTempData);
                    //sort sub analysis
                    usort($stage8GL15AndAboveData, array("\\AppBundle\\Model\\Reporting\\FederalLevelStage8SubAnalysis", "cmp_obj"));

                    $stage8GL15AndAboveAnalysis->setStage8CategoryData($stage8GL15AndAboveData);
                    $stage8GL15AndAboveAnalysis->setOverallCategorySubAnalysisTotal($overallStage8GL15AndAboveSubAnalysisTotal);

                    $stage8Analysis->setStage8GL15AndAboveData($stage8GL15AndAboveAnalysis);

                    $report->setStage8($stage8Analysis);

                    //STAGE 9
                    $stage9Analysis = new FederalLevelStage9Analysis();
                    $stage9Analysis->setStage64pt0OrMoreTotalStates(count($stage6_4pt0_Or_More_SubAnalysis_Entries));
                    $stage9Analysis->setStage63pt1to3pt9TotalStates(count($stage6_3pt1_to_3pt9_SubAnalysis_Entries));
                    $stage9Analysis->setStage62pt5to3TotalStates(count($stage6_2pt5_to_3_SubAnalysis_Entries));
                    $stage9Analysis->setStage61pt5to2pt4TotalStates(count($stage6_1pt5_to_2pt4_SubAnalysis_Entries));
                    $stage9Analysis->setStage6LessThan1pt5TotalStates(count($stage6_LessThan_1pt5_SubAnalysis_Entries));

                    $stage9Analysis->setStage74pt0OrMoreTotalStates(count($stage7_4pt0_Or_More_SubAnalysis_Entries));
                    $stage9Analysis->setStage73pt1to3pt9TotalStates(count($stage7_3pt1_to_3pt9_SubAnalysis_Entries));
                    $stage9Analysis->setStage72pt5to3TotalStates(count($stage7_2pt5_to_3_SubAnalysis_Entries));
                    $stage9Analysis->setStage71pt5to2pt4TotalStates(count($stage7_1pt5_to_2pt4_SubAnalysis_Entries));
                    $stage9Analysis->setStage7LessThan1pt5TotalStates(count($stage7_LessThan_1pt5_SubAnalysis_Entries));

                    //get the total records with missing state of origin and missing grade level

                    switch ($whichMode) {
                        case 'SINGLE_ORGANIZATION':

                            $query = "SELECT 'abc'"
                                . ",("
                                . "SELECT COUNT(DISTINCT(employee_number)) "
                                . "FROM confirmed_federal_level_nominal_roll_submission "
                                . "WHERE "
                                . "submission_id IN ( "
                                . "         SELECT s.submission_id  "
                                . "         FROM federal_level_nominal_roll_submissions s  "
                                . "         WHERE s.organization_id = :organization_id "
                                . ") "
                                . "AND submission_year=:submission_year "
                                . "AND (state_of_origin_code IS NULL OR TRIM(state_of_origin_code)='') "
                                . ") as _total_missing_state_of_origin "
                                . ",("
                                . "SELECT COUNT(DISTINCT(employee_number)) "
                                . "FROM confirmed_federal_level_nominal_roll_submission "
                                . "WHERE "
                                . "submission_id IN ( "
                                . "         SELECT s.submission_id  "
                                . "         FROM federal_level_nominal_roll_submissions s  "
                                . "         WHERE s.organization_id = :organization_id "
                                . ") "
                                . "AND submission_year=:submission_year "
                                . "AND (grade_level IS NULL OR TRIM(grade_level)='') "
                                . ") as _total_missing_grade_level ";
                            $statement = $this->connection->prepare($query);
                            $statement->bindValue(':organization_id', $organizationId);
                            $statement->bindValue(':submission_year', $submissionYear);

                            $statement->execute();


                            break;

                        case 'CONSOLIDATED_MINISTRY':
                            $query = "SELECT 'abc'"
                                . ",("
                                . "SELECT COUNT(DISTINCT(employee_number)) "
                                . "FROM confirmed_federal_level_nominal_roll_submission "
                                . "WHERE "
                                . "submission_id IN ( "
                                . "         SELECT s.submission_id  "
                                . "         FROM federal_level_nominal_roll_submissions s  "
                                . "         JOIN organization o ON (s.organization_id = o.id AND o.establishment_type_id=:establishment_type_id) "
                                . ") "
                                . "AND submission_year=:submission_year "
                                . "AND (state_of_origin_code IS NULL OR TRIM(state_of_origin_code)='') "
                                . ") as _total_missing_state_of_origin "
                                . ",("
                                . "SELECT COUNT(DISTINCT(employee_number)) "
                                . "FROM confirmed_federal_level_nominal_roll_submission "
                                . "WHERE "
                                . "submission_id IN ( "
                                . "         SELECT s.submission_id  "
                                . "         FROM federal_level_nominal_roll_submissions s  "
                                . "         JOIN organization o ON (s.organization_id = o.id AND o.establishment_type_id=:establishment_type_id) "
                                . ") "
                                . "AND submission_year=:submission_year "
                                . "AND (grade_level IS NULL OR TRIM(grade_level)='') "
                                . ") as _total_missing_grade_level ";
                            $statement = $this->connection->prepare($query);
                            $statement->bindValue(':establishment_type_id', AppConstants::FEDERAL_MINISTRY_ESTABLISHMENT);
                            $statement->bindValue(':submission_year', $submissionYear);

                            $statement->execute();
                            break;

                        case 'CONSOLIDATED_PARASTATAL':
                            $query = "SELECT 'abc'"
                                . ",("
                                . "SELECT COUNT(DISTINCT(employee_number)) "
                                . "FROM confirmed_federal_level_nominal_roll_submission "
                                . "WHERE "
                                . "submission_id IN ( "
                                . "         SELECT s.submission_id  "
                                . "         FROM federal_level_nominal_roll_submissions s  "
                                . "         JOIN organization o ON (s.organization_id = o.id AND o.establishment_type_id=:establishment_type_id) "
                                . ") "
                                . "AND submission_year=:submission_year "
                                . "AND (state_of_origin_code IS NULL OR TRIM(state_of_origin_code)='') "
                                . ") as _total_missing_state_of_origin "
                                . ",("
                                . "SELECT COUNT(DISTINCT(employee_number)) "
                                . "FROM confirmed_federal_level_nominal_roll_submission "
                                . "WHERE "
                                . "submission_id IN ( "
                                . "         SELECT s.submission_id  "
                                . "         FROM federal_level_nominal_roll_submissions s  "
                                . "         JOIN organization o ON (s.organization_id = o.id AND o.establishment_type_id=:establishment_type_id) "
                                . ") "
                                . "AND submission_year=:submission_year "
                                . "AND (grade_level IS NULL OR TRIM(grade_level)='') "
                                . ") as _total_missing_grade_level ";
                            $statement = $this->connection->prepare($query);
                            $statement->bindValue(':establishment_type_id', AppConstants::FEDERAL_PARASTATAL_ESTABLISHMENT);
                            $statement->bindValue(':submission_year', $submissionYear);

                            $statement->execute();
                            break;
                    }

                    $withoutRecords = $statement->fetch();

                    $stage9Analysis->setTotalWithoutStateOfOrigin($withoutRecords['_total_missing_state_of_origin']);
                    $stage9Analysis->setTotalWithoutGradeLevel($withoutRecords['_total_missing_grade_level']);

                    $report->setStage9($stage9Analysis);

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
}