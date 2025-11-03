<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:43 PM
 */

namespace AppBundle\Services\Reporting;


use AppBundle\AppException\AppException;
use AppBundle\Model\Reporting\FederalLevel\Career\CBIndexAnalysis;
use AppBundle\Model\Reporting\FederalLevel\Career\CBIndexStage1Analysis;
use AppBundle\Model\Reporting\FederalLevel\Career\CBIndexStage1Entry;
use AppBundle\Model\Reporting\FederalLevel\Career\CBIndexStage2Analysis;
use AppBundle\Model\Reporting\FederalLevel\Career\CBIndexStage2SubAnalysis;
use AppBundle\Model\Reporting\FederalLevel\Career\CBIndexStage2SubAnalysisEntry;
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
use Doctrine\DBAL\Connection;
use \Throwable;
use \PDO;

class CharacterBalancingIndexDistributionService
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getCBIndexDistribution($numberToBeRecruited,$whichGradeLevelGroup, $organizationId) : CBIndexAnalysis
    {
        $report = null;

        $statement = null;

        try {

            $exists = null;

            //get the last submission by this mda
            $query = "select submission_id,submission_year from federal_level_nominal_roll_submissions "
                . "where organization_id=:organization_id AND processing_status=:processing_status order by submission_year DESC limit 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':organization_id', $organizationId);
            $statement->bindValue(':processing_status', AppConstants::COMPLETED);
            $statement->execute();
            $nominalRoleSubmission = $statement->fetch();

            if ($nominalRoleSubmission) { //if exists

                $report = new CBIndexAnalysis();

                //get the submission details
                $submissionYear = $nominalRoleSubmission['submission_year'];

                //get the organization name
                $query = "select organization_name from organization where id=:id";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':id', $organizationId);
                $statement->execute();

                $organizationName = $statement->fetchColumn(0);
                $report->setOrganizationName($organizationName);
                $report->setSubmissionYear($submissionYear);

                //set the category desc name
                $distributionCategory = '';
                switch ($whichGradeLevelGroup){
                    case 'gl1_and_Above':
                        $distributionCategory = 'GL 01 AND ABOVE';
                        break;

                    case 'gl1_to_6':
                        $distributionCategory = 'GL 01 - 06';
                        break;

                    case 'gl_7_and_above':
                        $distributionCategory = 'GL 07 AND ABOVE';
                        break;

                    case 'gl_15_and_above':
                        $distributionCategory = 'GL 15 AND ABOVE';
                        break;
                }
                $report->setDistributionCategory($distributionCategory);

                //STAGE 1 ANALYSIS
                $stage1Analysis = new CBIndexStage1Analysis();

                $stage1Data = array(); //array of stage1 entries representing each state and associated data

                $query = "SELECT d.organization_id,d.submission_year,d.state_of_origin_id "
                    . ",d.total_gl_1 as _total_gl_1,d.total_gl_2 as _total_gl_2,d.total_gl_3 as _total_gl_3"
                    . ",d.total_gl_4 as _total_gl_4,d.total_gl_5 as _total_gl_5,d.total_gl_6 as _total_gl_6,d.total_gl_7 as _total_gl_7 "
                    . ",d.total_gl_8 as _total_gl_8,d.total_gl_9 as _total_gl_9,d.total_gl_10 as _total_gl_10,d.total_gl_11 as _total_gl_11 "
                    . ",d.total_gl_12 as _total_gl_12,d.total_gl_13 as _total_gl_13,d.total_gl_14 as _total_gl_14 "
                    . ",d.total_gl_15 as _total_gl_15,d.total_gl_16 as _total_gl_16,d.total_gl_17 as _total_gl_17"
                    . ",d.total_consolidated as _total_consolidated "
                    . ",s.state_code,s.state_name, g.id as _zone_id, g.zone_name "
                    . "FROM federal_level_nominal_roll_career_post_analysis d "
                    . "JOIN states s on (d.state_of_origin_id = s.id) "
                    . "LEFT JOIN geo_political_zone g on s.geo_political_zone_id = g.id "
                    . "WHERE d.organization_id=:organization_id AND d.submission_year=:submission_year AND d.state_of_origin_id<>:non_nigerian order by s.order_id ";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':organization_id', $organizationId);
                $statement->bindValue(':submission_year', $submissionYear);
                $statement->bindValue(':non_nigerian', '38');

                $statement->execute();

                $analysisRecords = $statement->fetchAll();
                if ($analysisRecords) {

                    $stage1OverallTotal = 0;

                    $tempTotalsArray = array();

                    foreach ($analysisRecords as $stateAnalysis) {
                        $stage1Entry = new CBIndexStage1Entry();
                        $stage1Entry->setStateId($stateAnalysis['state_of_origin_id']);
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
                        $stage1Entry->calculateTotal($whichGradeLevelGroup);

                        $stage1OverallTotal += $stage1Entry->getTotal();
                        $tempTotalsArray[] = $stage1Entry->getTotal();

                    }

                    //now get the highest state value from
                    usort($stage1Data, array("\\AppBundle\\Model\\Reporting\\FederalLevel\\Career\\CBIndexStage1Entry", "cmp_obj"));
                    $highestValueState = $stage1Data[0];

                    $highestStateFrequencyValue = $highestValueState->getTotal();

                    //calculate variations and overall variation
                    /**
                     * @var CBIndexStage1Entry $stage1_entry
                     */
                    $stage1OverallVariation = 0;
                    foreach ($stage1Data as $stage1_entry){
                        $stage1_entry->calculateVariation($highestStateFrequencyValue);
                        $stage1OverallVariation += $stage1_entry->getVariation();
                    }

                    //calculate %BFactor
                    $stage1OverallPercentBFactor = 0;
                    foreach ($stage1Data as $stage1_entry){
                        $stage1_entry->calculatePercentBFactor($stage1OverallVariation);
                        $stage1OverallPercentBFactor += $stage1_entry->getPercentBFactor();
                    }

                    //calculate CBIndex
                    $stage1OverallCBIndex = 0;
                    foreach ($stage1Data as $stage1_entry){
                        $stage1_entry->calculateCBIndex($numberToBeRecruited);
                        $stage1OverallCBIndex += $stage1_entry->getCbIndex();
                    }

                    //calculate Proportion
                    $stage1OverallProportion = 0;
                    foreach ($stage1Data as $stage1_entry){
                        $stage1_entry->calculateProportion();
                        $stage1OverallProportion += $stage1_entry->getProportion();
                    }

                    //calculate percentage
                    $totalNumberAfterRecruitment = $numberToBeRecruited + $stage1OverallTotal;
                    $stage1OverallPercentage = 0;
                    foreach ($stage1Data as $stage1_entry){
                        $stage1_entry->calculatePercentage($totalNumberAfterRecruitment);
                        $stage1OverallPercentage += $stage1_entry->getPercentage();
                    }

                    $stage1Analysis->setStage1Data($stage1Data);


                    //set the stage 1 overall totals
                    $stage1Analysis->setOverallTotal($stage1OverallTotal);
                    $stage1Analysis->setOverallVariation($stage1OverallVariation);
                    $stage1Analysis->setOverallPercentBFactor($stage1OverallPercentBFactor);
                    $stage1Analysis->setOverallCBIndex($stage1OverallCBIndex);
                    $stage1Analysis->setOverallProportion($stage1OverallProportion);
                    $stage1Analysis->setOverallPercentage($stage1OverallPercentage);

                    //set the stage 1 chart series
                    $stage1ChartSeries = array();

                    $proportionChartSeries = array();
                    $cbIndexChartSeries = array();
                    $percentBFactorChartSeries = array();

                    $stage1XAxisLabels = array();

                    foreach ($stage1Data as $stage1_entry){
                        $stage1XAxisLabels[] = $stage1_entry->getStateCode();

                        $proportionChartSeries[] = $stage1_entry->getProportion();
                        $cbIndexChartSeries[] = $stage1_entry->getCbIndex();
                        $percentBFactorChartSeries[] = $stage1_entry->getPercentBFactor();
                    }

                    $stage1ChartSeries[] = json_encode($proportionChartSeries);
                    $stage1ChartSeries[] = json_encode($cbIndexChartSeries);
                    //$stage1ChartSeries[] = json_encode($percentBFactorChartSeries);

                    $stage1ChartSeries[] = "[" . implode(",", $percentBFactorChartSeries) . "]";

                    $stage1Analysis->setChartSeries($stage1ChartSeries);
                    $stage1Analysis->setXAxisLabels("['" . implode("','", $stage1XAxisLabels) . "']");


                    //FORMAT STAGE 1 VALUES
                    foreach ($stage1Data as $stage1_entry){
                        $stage1_entry->formatValues();
                    }

                    $report->setStage1($stage1Analysis);

                    //set the report totals
                    $report->setNumberToBeRecruited($numberToBeRecruited);
                    $report->setNumberRequiredToBalance($stage1OverallVariation);
                    $report->setTotalNumberAfterRecruitment($totalNumberAfterRecruitment);
                    $report->setPercentBalanced(($numberToBeRecruited/$stage1OverallVariation) * 100);

                    //STAGE 2
                    $stage2Analysis = new CBIndexStage2Analysis();

                    $tempZoneStatesMap = array(); //zone_id -> states array

                    //select the geo-political zone ids and name
                    $query = "select id, zone_code, zone_name from geo_political_zone where zone_code<>:zone_code and record_status=:record_status";
                    $statement = $this->connection->prepare($query);
                    $statement->bindValue(':zone_code', 'NON');
                    $statement->bindValue(':record_status', AppConstants::ACTIVE);

                    $statement->execute();

                    $geoPoliticalZones = $statement->fetchAll();

                    $stage2TempSubAnalysisData = array();

                    //setup the zonestatesmap and a temp map of subanalysis
                    foreach ($geoPoliticalZones as $geoPoliticalZone) {

                        $tempZoneStatesMap[$geoPoliticalZone['id']] = array();

                        $stage2SubAnalysis = new CBIndexStage2SubAnalysis();
                        $stage2SubAnalysis->setZoneId($geoPoliticalZone['id']);
                        $stage2SubAnalysis->setZoneCode($geoPoliticalZone['zone_code']);
                        $stage2SubAnalysis->setZoneName($geoPoliticalZone['zone_name']);
                        $stage2TempSubAnalysisData[$geoPoliticalZone['id']] = $stage2SubAnalysis;

                    }

                    //fill the zone states map
                    /**
                     * @var CBIndexStage1Entry $stage_1_data
                     */
                    foreach ($stage1Analysis->getStage1Data() as $stage_1_data) {
                        $tempZoneStatesMap[$stage_1_data->getGeoPoliticalZoneId()][] = $stage_1_data;
                    }

                    //now do the sub analysis entries
                    /**
                     * @var CBIndexStage2SubAnalysis $subAnalysis
                     */
                    foreach ($stage2TempSubAnalysisData as $k => $subAnalysis) {

                        $stage2SubAnalysisData = array();

                        $subOverallTotal = 0;
                        $subOverallVariation = 0;
                        $subOverallPercentBFactor = 0;
                        $subOverallCBIndex = 0;
                        $subOverallProportion = 0;
                        $subOverallPercentage = 0;

                        /**
                         * @var CBIndexStage1Entry $stage_1_zonestate
                         */
                        foreach ($tempZoneStatesMap[$k] as $stage_1_zonestate) {
                            $stage2SubAnalysisEntry = new CBIndexStage2SubAnalysisEntry();
                            $stage2SubAnalysisEntry->setStateCode($stage_1_zonestate->getStateCode());
                            $stage2SubAnalysisEntry->setStateName($stage_1_zonestate->getStateName());

                            $stage2SubAnalysisEntry->setTotal($stage_1_zonestate->getTotal());
                            $stage2SubAnalysisEntry->setVariation($stage_1_zonestate->getVariation());
                            $stage2SubAnalysisEntry->setPercentBFactor($stage_1_zonestate->getPercentBFactor());
                            $stage2SubAnalysisEntry->setCbIndex($stage_1_zonestate->getCbIndex());
                            $stage2SubAnalysisEntry->setProportion($stage_1_zonestate->getProportion());
                            $stage2SubAnalysisEntry->setPercentage($stage_1_zonestate->getPercentage());

                            $subOverallTotal += $stage2SubAnalysisEntry->getTotal();
                            $subOverallVariation += $stage2SubAnalysisEntry->getVariation();
                            $subOverallPercentBFactor += $stage2SubAnalysisEntry->getPercentBFactor();
                            $subOverallCBIndex += $stage2SubAnalysisEntry->getCbIndex();
                            $subOverallProportion += $stage2SubAnalysisEntry->getProportion();
                            $subOverallPercentage += $stage2SubAnalysisEntry->getPercentage();

                            $stage2SubAnalysisData[] = $stage2SubAnalysisEntry;
                        }

                        //sort sub analysis entries
                        usort($stage2SubAnalysisData, array("\\AppBundle\\Model\\Reporting\\FederalLevel\\Career\\CBIndexStage2SubAnalysisEntry", "cmp_obj"));

                        //$subAnalysis = new FederalLevelStage4SubAnalysis(); //REMOVE LATER
                        $subAnalysis->setOverallTotal($subOverallTotal);
                        $subAnalysis->setOverallVariation($subOverallVariation);
                        $subAnalysis->setOverallPercentBFactor($subOverallPercentBFactor);
                        $subAnalysis->setOverallCBIndex($subOverallCBIndex);
                        $subAnalysis->setOverallProportion($subOverallProportion);
                        $subAnalysis->setOverallPercentage($subOverallPercentage);

                        $subAnalysis->setSubAnalysisData($stage2SubAnalysisData);
                    }

                    $stage2Data = array_values($stage2TempSubAnalysisData);

                    //sort sub analysis
                    usort($stage2Data, array("\\AppBundle\\Model\\Reporting\\FederalLevel\\Career\\CBIndexStage2SubAnalysis", "cmp_obj"));

                    $stage2Analysis->setStage2Data($stage2Data);

                    $report->setStage2($stage2Analysis);

                    //FORMAT REPORT VALUES
                    $report->formatValues();

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