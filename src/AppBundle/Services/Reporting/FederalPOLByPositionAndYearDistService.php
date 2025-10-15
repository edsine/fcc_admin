<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:43 PM
 */

namespace AppBundle\Services\Reporting;


use AppBundle\AppException\AppException;
use AppBundle\Model\Reporting\FederalLevel\Political\FederalPOLOHByPositionAndYearAnalysis;
use AppBundle\Model\Reporting\FederalLevel\Political\FederalPOLOHByPositionAndYearStage1Analysis;
use AppBundle\Model\Reporting\FederalLevel\Political\FederalPOLOHByPositionAndYearStage1Entry;
use AppBundle\Model\Reporting\FederalLevel\Political\FederalPOLOHByPositionAndYearStage2Analysis;
use AppBundle\Model\Reporting\FederalLevel\Political\FederalPOLOHByPositionAndYearStage2Entry;
use AppBundle\Utils\AppConstants;
use Doctrine\DBAL\Connection;
use \Throwable;

class FederalPOLByPositionAndYearDistService
{
    private $connection;
    private $rows_per_page;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->rows_per_page = AppConstants::ROWS_PER_PAGE;
    }

    public function getFederalLevelPOLOHByPositionAndYearDist($positionGradeLevel = -1, $submissionYear)
    {
        $report = null;

        $statement = null;

        try {

            $whichGradeLevel = null;

            switch ($positionGradeLevel) {

                case 90:
                    $whichGradeLevel = 'total_gl_90';
                    break;

                case 91:
                    $whichGradeLevel = 'total_gl_91';
                    break;

                case 92:
                    $whichGradeLevel = 'total_gl_92';
                    break;

                case 93:
                    $whichGradeLevel = 'total_gl_93';
                    break;

                case 94:
                    $whichGradeLevel = 'total_gl_94';
                    break;

                case 95:
                    $whichGradeLevel = 'total_gl_95';
                    break;

                case 96:
                    $whichGradeLevel = 'total_gl_96';
                    break;

                case 97:
                    $whichGradeLevel = 'total_gl_97';
                    break;

                case 98:
                    $whichGradeLevel = 'total_gl_98';
                    break;

                case 99:
                    $whichGradeLevel = 'total_gl_99';
                    break;

                default:
                    $whichGradeLevel = null;
                    break;
            }

            //$logger->info($submissionYear . ', POSITION Grade Level: ' . $positionGradeLevel . ', Which Grade Level: ' . $whichGradeLevel);

            if ($whichGradeLevel) {

                $query = "SELECT  "
                    . "d." . $whichGradeLevel
                    . ",s.id as state_id,s.state_code,s.state_name, g.id as _zone_id, g.zone_name "
                    . "FROM federal_level_nominal_roll_career_post_analysis d "
                    . "LEFT JOIN states s on d.state_of_origin_id = s.id "
                    . "LEFT JOIN geo_political_zone g on s.geo_political_zone_id = g.id "
                    . "WHERE " . "d." . $whichGradeLevel . "<>0 AND d.submission_year=:submission_year order by s.order_id ";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':submission_year', $submissionYear);

                $statement->execute();

                $analysisRecords = $statement->fetchAll();
                if ($analysisRecords) {

                    $report = new FederalPOLOHByPositionAndYearAnalysis();
                    $report->setSubmissionYear($submissionYear);

                    //get the report name of the position
                    $query = "SELECT description FROM grade_level_codes WHERE grade_level_code=:grade_level_code";
                    $statement = $this->connection->prepare($query);
                    $statement->bindValue(":grade_level_code", $positionGradeLevel);
                    $statement->execute();

                    $report->setPositionDescription($statement->fetchColumn(0));

                    //first fill in the states
                    $tempStage1Data = array();
                    $query = "select s.id as state_id,s.state_code,s.state_name, g.id as _zone_id, g.zone_name "
                        . "FROM states s "
                        . "LEFT JOIN geo_political_zone g on s.geo_political_zone_id = g.id "
                        . "WHERE s.record_status=:record_status order by s.state_name ";
                    $statement = $this->connection->prepare($query);
                    $statement->bindValue(':record_status', AppConstants::ACTIVE);
                    $statement->execute();

                    $states = $statement->fetchAll();

                    foreach ($states as $state){

                        $stage1Entry = new FederalPOLOHByPositionAndYearStage1Entry();
                        $stage1Entry->setStateId($state['state_id']);
                        $stage1Entry->setStateCode($state['state_code']);
                        $stage1Entry->setStateName($state['state_name']);
                        $stage1Entry->setGeoPoliticalZoneId($state['_zone_id']);
                        $stage1Entry->setGeoPoliticalZoneName($state['zone_name']);

                        $tempStage1Data[$stage1Entry->getStateId()] = $stage1Entry;

                    }

                    //now update the totals for each record
                    foreach ($analysisRecords as $stateAnalysis) {
                        $tempStage1Data[$stateAnalysis['state_id']]->updateTotal($stateAnalysis[$whichGradeLevel]);
                    }

                    //calculate overall total
                    $overallStage1Total = 0;
                    foreach ($tempStage1Data as $tempStage1Entry) {
                        $overallStage1Total += $tempStage1Entry->getTotal();
                    }

                    //calculate the percentages for each stage 1 entry and the overall precentage
                    $overallStage1Percentage = 0;

                    foreach ($tempStage1Data as $tempStage1Entry) {
                        $tempStage1Entry->calculatePercentage($overallStage1Total);
                        $overallStage1Percentage += $tempStage1Entry->getPercentage();
                    }

                    $stage1Data = array_values($tempStage1Data);

                    $stage1Analysis = new FederalPOLOHByPositionAndYearStage1Analysis();
                    $stage1Analysis->setStage1Data($stage1Data);
                    $stage1Analysis->setOverallTotal($overallStage1Total);
                    $stage1Analysis->setOverallPercentage($overallStage1Percentage);
                    $report->setStage1($stage1Analysis);

                    //STAGE 2
                    $stage2Analysis = new FederalPOLOHByPositionAndYearStage2Analysis();
                    //$stage2Data = array();

                    $query = "SELECT d.id, d.zone_code, d.zone_name FROM geo_political_zone d WHERE d.country_code=:country_code";
                    $statement = $this->connection->prepare($query);
                    $statement->bindValue(':country_code', 'NIG');
                    $statement->execute();

                    $geoPoliticalZones = $statement->fetchAll();
                    $tempStage2Data = array();

                    if ($geoPoliticalZones) {
                        foreach ($geoPoliticalZones as $geoPoliticalZone) {

                            $stage2Entry = new FederalPOLOHByPositionAndYearStage2Entry();

                            $stage2Entry->setGeoPoliticalZoneId($geoPoliticalZone['id']);
                            $stage2Entry->setGeoPoliticalZoneCode($geoPoliticalZone['zone_code']);
                            $stage2Entry->setGeoPoliticalZoneName($geoPoliticalZone['zone_name']);

                            $tempStage2Data[$stage2Entry->getGeoPoliticalZoneId()] = $stage2Entry;
                        }

                        //$logger->info(print_r($tempStage2Data, true));

                        //now update the totals of the stage 2 entries with states in the zone
                        /**
                         * @var FederalPOLOHByPositionAndYearStage1Entry $stage1Entry
                         */

                        foreach ($stage1Data as $stage1Entry) {
                            if (array_key_exists($stage1Entry->getGeoPoliticalZoneId(), $tempStage2Data)) {
                                $tempStage2Data[$stage1Entry->getGeoPoliticalZoneId()]->addTotal($stage1Entry->getTotal());
                            }
                        }

                        //now calculate totals and percentages of geo-political zones
                        $overallStage2Total = 0;

                        foreach ($tempStage2Data as $state2Entry_2) {
                            //calculate totals
                            $overallStage2Total += $state2Entry_2->getTotal();
                        }

                        //calculate the percentages for each stage 2 entry and the overall percentage
                        $overallStage2Percentage = 0;

                        foreach ($tempStage2Data as $state2Entry_2) {
                            $state2Entry_2->calculatePercentage($overallStage2Total);
                            $overallStage2Percentage += $state2Entry_2->getPercentage();
                        }

                        //extract stage 2 data
                        $stage2Data = array_values($tempStage2Data);
                        $stage2Analysis->setStage2Data($stage2Data);
                        $stage2Analysis->setOverallTotal($overallStage2Total);
                        $stage2Analysis->setOverallPercentage($overallStage2Percentage);

                        $report->setStage2($stage2Analysis);

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

}