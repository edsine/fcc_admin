<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/6/2017
 * Time: 3:47 PM
 */

namespace AppBundle\Services\Reporting;


use AppBundle\AppException\AppException;
use AppBundle\Model\Reporting\FederalLevel\Career\ComparativeDataAnalysis;
use AppBundle\Model\Reporting\FederalLevel\Career\ComparativeOverallYearTotalEntry;
use AppBundle\Model\Reporting\FederalLevel\Career\ComparativeStateAnalysisEntry;
use AppBundle\Model\Reporting\FederalLevel\Career\ComparativeYearEntry;
use AppBundle\Utils\AppConstants;
use Doctrine\DBAL\Driver\Connection;
use \PDO;

class ComparativeDataService
{

    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }


    public function getReport($startGradeLevel, $endGradeLevel, $startYear, $endYear)
    {
        $report = null;
        $statement = null;

        try {

            //get the organizations that have submitted in all the years
            $yearDifference = ($endYear - $startYear) + 1;
            $query = "SELECT organization_id
                        ,count(DISTINCT(concat(organization_id,submission_year))) AS total_unique_submissions
                        FROM federal_level_nominal_roll_submissions 
                        WHERE submission_year BETWEEN $startYear AND $endYear
                        GROUP BY organization_id
                        HAVING total_unique_submissions = $yearDifference";
            $statement = $this->connection->prepare($query);
            $statement->execute();

            $organizationsConsidered = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

            if ($organizationsConsidered) {

                $organizationIdsInsString = implode(',', $organizationsConsidered); //organizations would be array of ints

                /*//now select the submission ids that correspond to this and see
                $query = "select submission_id 
                          from federal_level_nominal_roll_submissions
                          WHERE submission_year BETWEEN $startYear AND $endYear
                          and organization_id in ($organizationIdsInsString)";
                $statement = $this->connection->prepare($query);
                $statement->execute();

                $submissionIds = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

                $submissionIdsInString = "'" . implode("','", $submissionIds) . "'";*/

                $query = "SELECT id,state_code, state_name FROM states WHERE record_status=:record_status";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':record_status', AppConstants::ACTIVE);
                $statement->execute();
                $states = $statement->fetchAll();

                //initialize the state entries
                $tempStateEntries = array(); //stateId => stateEntry
                foreach ($states as $state) {
                    $stateAnalysisEntry = new ComparativeStateAnalysisEntry();
                    $stateAnalysisEntry->setStateId($state['id']);
                    $stateAnalysisEntry->setStateCode($state['state_code']);
                    $stateAnalysisEntry->setStateName($state['state_name']);

                    //initialize the year entries
                    $stateYearEntries = array();
                    for ($year = $startYear; $year <= $endYear; $year++) {
                        $yearEntry = new ComparativeYearEntry();
                        $yearEntry->setYear($year);
                        $stateYearEntries[$year] = $yearEntry;
                    }

                    $stateAnalysisEntry->setYearEntries($stateYearEntries);
                    $tempStateEntries[$stateAnalysisEntry->getStateId()] = $stateAnalysisEntry;
                }


                //now fetch the data for each state and all years and update the state entries
                foreach ($states as $state) {

                    $query = "select 'abc'";

                    for ($year = $startYear; $year <= $endYear; $year++) {

                        $subQuery = ",(SELECT 
                            SUM( 
                                 (d.total_gl_1 + d.total_gl_2 + d.total_gl_3 + d.total_gl_4 + d.total_gl_5 + d.total_gl_6 + d.total_gl_7 
                             + d.total_gl_8 + d.total_gl_9 + d.total_gl_10 + d.total_gl_11 + d.total_gl_12 + d.total_gl_13 + d.total_gl_14
                             + d.total_gl_15 + d.total_gl_16 + d.total_gl_17) 
                             ) 
                            FROM federal_level_nominal_roll_career_post_analysis d 
                            WHERE d.organization_id in ($organizationIdsInsString) 
                            AND d.state_of_origin_id=:${year}_state_of_origin_id  
                            AND d.submission_year=:${year}_submission_year ) as total_${year}_staff";
                        $query .= $subQuery;
                    }

                    $statement = $this->connection->prepare($query);

                    for ($year = $startYear; $year <= $endYear; $year++) {
                        $statement->bindValue(":" . $year . "_state_of_origin_id", $state['id']);
                        $statement->bindValue(":" . $year . "_submission_year", $year);
                    }

                    $statement->execute();

                    $stateResult = $statement->fetch();
                    if ($stateResult) {
                        for ($year = $startYear; $year <= $endYear; $year++) {
                            $tempStateEntries[$state['id']]->updateYearEntryTotal($year, $stateResult["total_" . $year . "_staff"]);
                        }
                    }
                }

                //now calculate overall totals
                $overallYearEntryTotals = array();
                for ($year = $startYear; $year <= $endYear; $year++) {
                    $overallYearEntryTotal = new ComparativeOverallYearTotalEntry();
                    $overallYearEntryTotal->setYear($year);

                    $overallYearTotal = 0;
                    foreach ($tempStateEntries as $tempStateEntry) {
                        $overallYearTotal += $tempStateEntry->fetchYearEntryTotal($year);
                    }
                    $overallYearEntryTotal->setOverallTotal($overallYearTotal);

                    $overallYearEntryTotals[$year] = $overallYearEntryTotal;
                }

                //now calculate the state year entry percentages
                foreach ($tempStateEntries as $tempStateEntry) {
                    for ($year = $startYear; $year <= $endYear; $year++) {
                        $tempStateEntry->updateYearEntryPercentage($year, $overallYearEntryTotals[$year]->getOverallTotal());
                    }
                }

                //now calculate the overall percentages
                foreach ($overallYearEntryTotals as $overallYearEntryTotal) {
                    $overallYearPercentage = 0;
                    foreach ($tempStateEntries as $tempStateEntry) {
                        $overallYearPercentage += $tempStateEntry->fetchYearEntryPercentage($overallYearEntryTotal->getYear());
                    }
                    $overallYearEntryTotal->setOverallPercentage($overallYearPercentage);

                }

                //format vals
                foreach ($tempStateEntries as $tempStateEntry) {
                    $tempStateEntry->formatValues();
                }

                foreach ($overallYearEntryTotals as $overallYearEntryTotal) {
                    $overallYearEntryTotal->setOverallPercentage(round($overallYearEntryTotal->getOverallPercentage()));
                    if ($overallYearEntryTotal->getOverallPercentage() == 99) {
                        $overallYearEntryTotal->setOverallPercentage(100);
                    }
                }

                //now fill the report
                $report = new ComparativeDataAnalysis();
                $report->setStartYear($startYear);
                $report->setEndYear($endYear);

                $report->setStateEntries(array_values($tempStateEntries));
                $report->setOverallYearTotals($overallYearEntryTotals);

                $report->setTotalOrganizationsConsidered(count($organizationsConsidered));

            }

        } catch (\Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $report;
    }

}