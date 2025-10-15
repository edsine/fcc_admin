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
use AppBundle\Model\Reporting\FederalLevel\NumberOfPositionCadreAnalysisEntry;
use AppBundle\Model\Reporting\FederalLevel\NumberOfPositionDataAnalysis;
use AppBundle\Model\Reporting\FederalLevel\NumberOfPositionOverallYearTotalEntry;
use AppBundle\Model\Reporting\FederalLevel\NumberOfPositionQueryConfig;
use AppBundle\Model\Reporting\FederalLevel\NumberOfPositionYearEntry;
use AppBundle\Model\Reporting\FederalLevel\ProportionOfPositionDataAnalysis;
use AppBundle\Model\Reporting\FederalLevel\ProportionOfPositionOverallYearTotalEntry;
use AppBundle\Model\Reporting\FederalLevel\ProportionOfPositionStateAnalysisEntry;
use AppBundle\Model\Reporting\FederalLevel\ProportionOfPositionYearEntry;
use AppBundle\Model\Reporting\Submission\SubmissionStatus;
use AppBundle\Model\SearchCriteria\SubmissionStatusSearchCriteria;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\Paginator;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\Finder\Comparator\NumberComparator;
use \PDO;

class ProportionOfPositionReportService
{

    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }


    public function getFCSCReport($startYear, $endYear)
    {
        $report = null;
        $statement = null;

        try {

            //get the organizations that have submitted in all the years
            $yearDifference = ($endYear - $startYear) + 1;
            $query = "SELECT nominal_roll_submission.organization_id
                        ,count(DISTINCT(concat(nominal_roll_submission.organization_id,nominal_roll_submission.submission_year))) AS total_unique_submissions
                        FROM federal_level_nominal_roll_submissions as nominal_roll_submission
                        JOIN organization as organization 
                              on nominal_roll_submission.organization_id = organization.id 
                              and organization.level_of_government=:level_of_government
                        WHERE submission_year BETWEEN $startYear AND $endYear
                        GROUP BY organization_id
                        HAVING total_unique_submissions = $yearDifference";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':level_of_government', AppConstants::FEDERAL);
            $statement->execute();

            $organizationsConsidered = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

            if ($organizationsConsidered) {

                $organizationIdsInsString = implode(',', $organizationsConsidered); //organizations would be array of ints

                //now select the submission ids that correspond to this and see
                $query = "select submission_id 
                          from federal_level_nominal_roll_submissions
                          WHERE submission_year BETWEEN $startYear AND $endYear
                          and organization_id in ($organizationIdsInsString)";
                $statement = $this->connection->prepare($query);
                $statement->execute();

                $submissionIds = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

                $submissionIdsInString = "'" . implode("','", $submissionIds) . "'";

                $query = "SELECT id,state_code, state_name FROM states WHERE record_status=:record_status";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':record_status', AppConstants::ACTIVE);
                $statement->execute();
                $states = $statement->fetchAll();

                //initialize the state entries
                /**
                 * @var ProportionOfPositionStateAnalysisEntry[]
                 */
                $tempStateEntries = array(); //stateId => stateEntry
                foreach ($states as $state) {
                    $stateAnalysisEntry = new ProportionOfPositionStateAnalysisEntry();
                    $stateAnalysisEntry->setStateId($state['id']);
                    $stateAnalysisEntry->setStateCode($state['state_code']);
                    $stateAnalysisEntry->setStateName($state['state_name']);

                    //initialize the year entries
                    $stateYearEntries = array();
                    for ($year = $startYear; $year <= $endYear; $year++) {
                        $yearEntry = new ProportionOfPositionYearEntry();
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
                        //14-29
                        $subQuery = ",(";
                        $subQuery .= "SELECT COUNT(gender)
                                FROM confirmed_federal_level_nominal_roll_submission
                                WHERE 
                                submission_id in ($submissionIdsInString) 
                                AND submission_year = :${year}_M_14_to_29_submission_year
                                AND state_of_origin_code = :${year}_M_14_to_29_state_of_origin_code
                                AND gender = 'M'
                                AND (:${year}_M_14_to_29_condition_1 - SUBSTRING(date_of_birth,-4,4)) BETWEEN 14 and 29";
                        $subQuery .= ") AS total_${year}_M_14_to_29";

                        $subQuery .= ",(";
                        $subQuery .= "SELECT COUNT(gender)
                                FROM confirmed_federal_level_nominal_roll_submission
                                WHERE 
                                submission_id in ($submissionIdsInString) 
                                AND submission_year = :${year}_F_14_to_29_submission_year
                                AND state_of_origin_code = :${year}_F_14_to_29_state_of_origin_code
                                AND gender = 'F'
                                AND (:${year}_F_14_to_29_condition_1 - SUBSTRING(date_of_birth,-4,4)) BETWEEN 14 and 29";
                        $subQuery .= ") AS total_${year}_F_14_to_29";

                        //30-45
                        $subQuery .= ",(";
                        $subQuery .= "SELECT COUNT(gender)
                                FROM confirmed_federal_level_nominal_roll_submission
                                WHERE 
                                submission_id in ($submissionIdsInString) 
                                AND submission_year = :${year}_M_30_to_45_submission_year
                                AND state_of_origin_code = :${year}_M_30_to_45_state_of_origin_code
                                AND gender = 'M'
                                AND (:${year}_M_30_to_45_condition_1 - SUBSTRING(date_of_birth,-4,4)) BETWEEN 30 and 45";
                        $subQuery .= ") AS total_${year}_M_30_to_45";

                        $subQuery .= ",(";
                        $subQuery .= "SELECT COUNT(gender)
                                FROM confirmed_federal_level_nominal_roll_submission
                                WHERE 
                                submission_id in ($submissionIdsInString) 
                                AND submission_year = :${year}_F_30_to_45_submission_year
                                AND state_of_origin_code = :${year}_F_30_to_45_state_of_origin_code
                                AND gender = 'F'
                                AND (:${year}_F_30_to_45_condition_1 - SUBSTRING(date_of_birth,-4,4)) BETWEEN 30 and 45";
                        $subQuery .= ") AS total_${year}_F_30_to_45";

                        //46+
                        $subQuery .= ",(";
                        $subQuery .= "SELECT COUNT(gender)
                                FROM confirmed_federal_level_nominal_roll_submission
                                WHERE 
                                submission_id in ($submissionIdsInString) 
                                AND submission_year = :${year}_M_46_And_Above_submission_year
                                AND state_of_origin_code = :${year}_M_46_And_Above_state_of_origin_code
                                AND gender = 'M'
                                AND (:${year}_M_46_And_Above_condition_1 - SUBSTRING(date_of_birth,-4,4)) BETWEEN 46 and 80"; //80 added cos of wrong data
                        $subQuery .= ") AS total_${year}_M_46_And_Above";

                        $subQuery .= ",(";
                        $subQuery .= "SELECT COUNT(gender)
                                FROM confirmed_federal_level_nominal_roll_submission
                                WHERE 
                                submission_id in ($submissionIdsInString) 
                                AND submission_year = :${year}_F_46_And_Above_submission_year
                                AND state_of_origin_code = :${year}_F_46_And_Above_state_of_origin_code
                                AND gender = 'F'
                                AND (:${year}_F_46_And_Above_condition_1 - SUBSTRING(date_of_birth,-4,4)) BETWEEN 46 and 80"; //80 added cos of wrong data
                        $subQuery .= ") AS total_${year}_F_46_And_Above";

                        $query .= $subQuery;
                    }

                    $statement = $this->connection->prepare($query);

                    for ($year = $startYear; $year <= $endYear; $year++) {
                        $statement->bindValue(":${year}_M_14_to_29_submission_year", $year);
                        $statement->bindValue(":${year}_M_14_to_29_state_of_origin_code", $state['state_code']);
                        $statement->bindValue(":${year}_M_14_to_29_condition_1", $year);

                        $statement->bindValue(":${year}_F_14_to_29_submission_year", $year);
                        $statement->bindValue(":${year}_F_14_to_29_state_of_origin_code", $state['state_code']);
                        $statement->bindValue(":${year}_F_14_to_29_condition_1", $year);

                        $statement->bindValue(":${year}_M_30_to_45_submission_year", $year);
                        $statement->bindValue(":${year}_M_30_to_45_state_of_origin_code", $state['state_code']);
                        $statement->bindValue(":${year}_M_30_to_45_condition_1", $year);

                        $statement->bindValue(":${year}_F_30_to_45_submission_year", $year);
                        $statement->bindValue(":${year}_F_30_to_45_state_of_origin_code", $state['state_code']);
                        $statement->bindValue(":${year}_F_30_to_45_condition_1", $year);

                        $statement->bindValue(":${year}_M_46_And_Above_submission_year", $year);
                        $statement->bindValue(":${year}_M_46_And_Above_state_of_origin_code", $state['state_code']);
                        $statement->bindValue(":${year}_M_46_And_Above_condition_1", $year);

                        $statement->bindValue(":${year}_F_46_And_Above_submission_year", $year);
                        $statement->bindValue(":${year}_F_46_And_Above_state_of_origin_code", $state['state_code']);
                        $statement->bindValue(":${year}_F_46_And_Above_condition_1", $year);
                    }

                    $statement->execute();

                    $stateResult = $statement->fetch();
                    if ($stateResult) {
                        for ($year = $startYear; $year <= $endYear; $year++) {
                            $stateId = $state['id'];
                            $tempStateEntries[$stateId]->updateYearEntry($year, '14_to_29', $stateResult["total_${year}_M_14_to_29"], $stateResult["total_${year}_F_14_to_29"]);

                            $tempStateEntries[$stateId]->updateYearEntry($year, '30_to_45', $stateResult["total_${year}_M_30_to_45"], $stateResult["total_${year}_F_30_to_45"]);

                            $tempStateEntries[$stateId]->updateYearEntry($year, '46_And_Above', $stateResult["total_${year}_M_46_And_Above"], $stateResult["total_${year}_F_46_And_Above"]);
                        }
                    }
                }

                //now calculate overall totals
                $overallYearEntryTotals = array();
                for ($year = $startYear; $year <= $endYear; $year++) {
                    $overallYearEntryTotal = new ProportionOfPositionOverallYearTotalEntry();
                    $overallYearEntryTotal->setYear($year);

                    $overallYearTotal_M_14_to_29 = 0;
                    $overallYearTotal_F_14_to_29 = 0;

                    $overallYearTotal_M_30_to_45 = 0;
                    $overallYearTotal_F_30_to_45 = 0;

                    $overallYearTotal_M_46_And_Above = 0;
                    $overallYearTotal_F_46_And_Above = 0;
                    /**
                     * @var ProportionOfPositionStateAnalysisEntry[] $tempStateEntries
                     */
                    foreach ($tempStateEntries as $tempStateEntry) {
                        $overallYearTotal_M_14_to_29 += $tempStateEntry->fetchYearEntry($year, '14_to_29', 'M');
                        $overallYearTotal_F_14_to_29 += $tempStateEntry->fetchYearEntry($year, '14_to_29', 'F');

                        $overallYearTotal_M_30_to_45 += $tempStateEntry->fetchYearEntry($year, '30_to_45', 'M');
                        $overallYearTotal_F_30_to_45 += $tempStateEntry->fetchYearEntry($year, '30_to_45', 'F');

                        $overallYearTotal_M_46_And_Above += $tempStateEntry->fetchYearEntry($year, '46_And_Above', 'M');
                        $overallYearTotal_F_46_And_Above += $tempStateEntry->fetchYearEntry($year, '46_And_Above', 'F');
                    }

                    $overallYearEntryTotal->setOverallTotalMale14To29($overallYearTotal_M_14_to_29);
                    $overallYearEntryTotal->setOverallTotalFemale14To29($overallYearTotal_F_14_to_29);

                    $overallYearEntryTotal->setOverallTotalMale30To45($overallYearTotal_M_30_to_45);
                    $overallYearEntryTotal->setOverallTotalFemale30To45($overallYearTotal_F_30_to_45);

                    $overallYearEntryTotal->setOverallTotalMale46AndAbove($overallYearTotal_M_46_And_Above);
                    $overallYearEntryTotal->setOverallTotalFemale46AndAbove($overallYearTotal_F_46_And_Above);

                    $overallYearEntryTotals[$year] = $overallYearEntryTotal;
                }

                //now fill the report
                $report = new ProportionOfPositionDataAnalysis();
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


    public function getLGSCReport($startYear, $endYear)
    {
        $report = null;
        $statement = null;

        try {

            //get the organizations that have submitted in all the years
            $yearDifference = ($endYear - $startYear) + 1;
            $query = "SELECT nominal_roll_submission.organization_id
                        ,count(DISTINCT(concat(nominal_roll_submission.organization_id,nominal_roll_submission.submission_year))) AS total_unique_submissions
                        FROM federal_level_nominal_roll_submissions as nominal_roll_submission
                        JOIN organization as organization 
                              on nominal_roll_submission.organization_id = organization.id 
                              and organization.level_of_government=:level_of_government
                        WHERE submission_year BETWEEN $startYear AND $endYear
                        GROUP BY organization_id
                        HAVING total_unique_submissions = $yearDifference";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':level_of_government', AppConstants::LOCAL_GOVERNMENT);
            $statement->execute();

            $organizationsConsidered = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

            if ($organizationsConsidered) {

                //sprintf('Organizations "%s" ', count($organizationsConsidered) . '');

                $organizationIdsInsString = implode(',', $organizationsConsidered); //organizations would be array of ints

                //now select the submission ids that correspond to this and see
                $query = "select submission_id 
                          from federal_level_nominal_roll_submissions
                          WHERE submission_year BETWEEN $startYear AND $endYear
                          and organization_id in ($organizationIdsInsString)";
                $statement = $this->connection->prepare($query);
                $statement->execute();

                $submissionIds = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

                $submissionIdsInString = "'" . implode("','", $submissionIds) . "'";

                //create the query configs
                $queryConfigs = array();

                $cadreQueryConfig = new NumberOfPositionQueryConfig('Executive Chairman');
                $cadreQueryConfig->setQueryCriteria(" (designation = '" . $cadreQueryConfig->getCadre() . "')");
                $queryConfigs[] = $cadreQueryConfig;

                $cadreQueryConfig = new NumberOfPositionQueryConfig('Executive Secretary');
                $cadreQueryConfig->setQueryCriteria(" (designation = '" . $cadreQueryConfig->getCadre() . "')");
                $queryConfigs[] = $cadreQueryConfig;

                $cadreQueryConfig = new NumberOfPositionQueryConfig('Councillor');
                $cadreQueryConfig->setQueryCriteria(" (designation = '" . $cadreQueryConfig->getCadre() . "')");
                $queryConfigs[] = $cadreQueryConfig;

                $cadreQueryConfig = new NumberOfPositionQueryConfig('Directorate Level (GL 15-17)');
                $cadreQueryConfig->setQueryCriteria(" (grade_level between 15 and 17) ");
                $queryConfigs[] = $cadreQueryConfig;

                $cadreQueryConfig = new NumberOfPositionQueryConfig('Management Level (GL 12-14)');
                $cadreQueryConfig->setQueryCriteria(" (grade_level between 12 and 14) ");
                $queryConfigs[] = $cadreQueryConfig;

                $cadreQueryConfig = new NumberOfPositionQueryConfig('Officers Excluding Management Staff');
                $cadreQueryConfig->setQueryCriteria(" (grade_level between 1 and 11) ");
                $queryConfigs[] = $cadreQueryConfig;

                $cadreQueryConfig = new NumberOfPositionQueryConfig('Officers Excluding Management Staff');
                $cadreQueryConfig->setQueryCriteria(" (grade_level between 1 and 11) ");
                $queryConfigs[] = $cadreQueryConfig;

                $cadreQueryConfig = new NumberOfPositionQueryConfig('Technical Workers');
                $cadreQueryConfig->setQueryCriteria(" (designation = 'Technical')");
                $queryConfigs[] = $cadreQueryConfig;

                $cadreQueryConfig = new NumberOfPositionQueryConfig('Operatives');
                $cadreQueryConfig->setQueryCriteria(" (designation = 'Operative')");
                $queryConfigs[] = $cadreQueryConfig;

                $cadreQueryConfig = new NumberOfPositionQueryConfig('Persons With Disability');
                $cadreQueryConfig->setQueryCriteria(" (designation = 'not yet supported')");
                $queryConfigs[] = $cadreQueryConfig;


                /**
                 * @var NumberOfPositionCadreAnalysisEntry[]
                 */
                $tempCadreEntries = array(); //cadreId => cadreEntry

                /**
                 * @var NumberOfPositionQueryConfig $queryConfig
                 */
                foreach ($queryConfigs as $queryConfig) {
                    $cadreAnalysisEntry = new NumberOfPositionCadreAnalysisEntry();
                    $cadreAnalysisEntry->setCadreId($queryConfig->getCadre());
                    $cadreAnalysisEntry->setCadreCode($queryConfig->getCadre());
                    $cadreAnalysisEntry->setCadreName($queryConfig->getCadre());
                    //initialize the year entries
                    $stateYearEntries = array();
                    for ($year = $startYear; $year <= $endYear; $year++) {
                        $yearEntry = new NumberOfPositionYearEntry();
                        $yearEntry->setYear($year);
                        $stateYearEntries[$year] = $yearEntry;
                    }

                    $cadreAnalysisEntry->setYearEntries($stateYearEntries);
                    $tempCadreEntries[$cadreAnalysisEntry->getCadreId()] = $cadreAnalysisEntry;
                }

                foreach ($queryConfigs as $queryConfig) {

                    $currentCadreCriteria = $queryConfig->getQueryCriteria();

                    //now fetch the data for cadre and update the entries
                    $query = "select 'abc'";
                    for ($year = $startYear; $year <= $endYear; $year++) {
                        //14-29
                        $subQuery = ",(";
                        $subQuery .= "SELECT COUNT(gender)
                                FROM confirmed_federal_level_nominal_roll_submission
                                WHERE 
                                submission_id in ($submissionIdsInString) 
                                AND submission_year = :${year}_M_submission_year
                                AND $currentCadreCriteria
                                AND gender = 'M'";
                        $subQuery .= ") AS total_${year}_M";

                        $subQuery .= ",(";
                        $subQuery .= "SELECT COUNT(gender)
                                FROM confirmed_federal_level_nominal_roll_submission
                                WHERE 
                                submission_id in ($submissionIdsInString) 
                                AND submission_year = :${year}_F_submission_year
                                AND $currentCadreCriteria
                                AND gender = 'F'";
                        $subQuery .= ") AS total_${year}_F";

                        $query .= $subQuery;
                    }

                    $statement = $this->connection->prepare($query);
                    for ($year = $startYear; $year <= $endYear; $year++) {
                        $statement->bindValue(":${year}_M_submission_year", $year);
                        $statement->bindValue(":${year}_F_submission_year", $year);
                    }

                    $statement->execute();
                    $stateResult = $statement->fetch();
                    if ($stateResult) {
                        for ($year = $startYear; $year <= $endYear; $year++) {
                            $tempCadreEntries[$queryConfig->getCadre()]->updateYearEntry($year, $stateResult["total_${year}_M"], $stateResult["total_${year}_F"]);
                        }
                    }

                }


                //now calculate overall totals
                $overallYearEntryTotals = array();
                for ($year = $startYear; $year <= $endYear; $year++) {
                    $overallYearEntryTotal = new NumberOfPositionOverallYearTotalEntry();
                    $overallYearEntryTotal->setYear($year);

                    $overallYearTotal_M = 0;
                    $overallYearTotal_F = 0;

                    /**
                     * @var NumberOfPositionCadreAnalysisEntry[] $tempCadreEntries
                     */
                    foreach ($tempCadreEntries as $tempStateEntry) {
                        $overallYearTotal_M += $tempStateEntry->fetchYearEntry($year, 'M');
                        $overallYearTotal_F += $tempStateEntry->fetchYearEntry($year, 'F');
                    }

                    $overallYearEntryTotal->setOverallTotalMale($overallYearTotal_M);
                    $overallYearEntryTotal->setOverallTotalFemale($overallYearTotal_F);

                    $overallYearEntryTotals[$year] = $overallYearEntryTotal;
                }

                //now fill the report
                $report = new NumberOfPositionDataAnalysis();
                $report->setStartYear($startYear);
                $report->setEndYear($endYear);

                $report->setCadreEntries(array_values($tempCadreEntries));
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


    public function getNUCReport($startYear, $endYear)
    {
        $report = null;
        $statement = null;

        try {

            //get the organizations that have submitted in all the years
            $yearDifference = ($endYear - $startYear) + 1;
            $query = "SELECT nominal_roll_submission.organization_id
                        ,count(DISTINCT(concat(nominal_roll_submission.organization_id,nominal_roll_submission.submission_year))) AS total_unique_submissions
                        FROM federal_level_nominal_roll_submissions as nominal_roll_submission
                        JOIN organization as organization 
                              on nominal_roll_submission.organization_id = organization.id 
                              and organization.level_of_government=:level_of_government
                        JOIN organization_categories as organization_category 
                              on nominal_roll_submission.organization_id = organization_category.organization_id
                              and organization_category.organization_category_type_id=:organization_category_type_id
                        WHERE submission_year BETWEEN $startYear AND $endYear
                        GROUP BY organization_id
                        HAVING total_unique_submissions = $yearDifference";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':level_of_government', AppConstants::FEDERAL);
            $statement->bindValue(':organization_category_type_id', AppConstants::ACADEMIC_ESTABLISHMENT);
            $statement->execute();

            $organizationsConsidered = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

            if ($organizationsConsidered) {

                //sprintf('Organizations "%s" ', count($organizationsConsidered) . '');

                $organizationIdsInsString = implode(',', $organizationsConsidered); //organizations would be array of ints

                //now select the submission ids that correspond to this and see
                $query = "select submission_id 
                          from federal_level_nominal_roll_submissions
                          WHERE submission_year BETWEEN $startYear AND $endYear
                          and organization_id in ($organizationIdsInsString)";
                $statement = $this->connection->prepare($query);
                $statement->execute();

                $submissionIds = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

                $submissionIdsInString = "'" . implode("','", $submissionIds) . "'";

                //create the query configs
                $queryConfigs = array();

                $cadreQueryConfig = new NumberOfPositionQueryConfig('Vice Chancellor');
                $cadreQueryConfig->setQueryCriteria(" (designation = 'Vice Chancellor')");
                $queryConfigs[] = $cadreQueryConfig;

                $cadreQueryConfig = new NumberOfPositionQueryConfig('Rector/Provost');
                $cadreQueryConfig->setQueryCriteria(" (designation = 'Rector' or designation = 'Provost')");
                $queryConfigs[] = $cadreQueryConfig;

                $cadreQueryConfig = new NumberOfPositionQueryConfig('Professor');
                $cadreQueryConfig->setQueryCriteria(" (designation = 'Professor')");
                $queryConfigs[] = $cadreQueryConfig;

                $cadreQueryConfig = new NumberOfPositionQueryConfig('Reader/Associate Professor');
                $cadreQueryConfig->setQueryCriteria(" (designation = 'Associate Professor' or designation = 'Reader Professor')");
                $queryConfigs[] = $cadreQueryConfig;

                $cadreQueryConfig = new NumberOfPositionQueryConfig('Principal/Senior Lecturer');
                $cadreQueryConfig->setQueryCriteria(" (designation = 'Principal Lecturer' or designation = 'Senior Lecturer')");
                $queryConfigs[] = $cadreQueryConfig;

                $cadreQueryConfig = new NumberOfPositionQueryConfig('Lecturer I');
                $cadreQueryConfig->setQueryCriteria(" (designation = 'Lecturer I')");
                $queryConfigs[] = $cadreQueryConfig;

                $cadreQueryConfig = new NumberOfPositionQueryConfig('Lecturer II');
                $cadreQueryConfig->setQueryCriteria(" (designation = 'Lecturer II')");
                $queryConfigs[] = $cadreQueryConfig;

                $cadreQueryConfig = new NumberOfPositionQueryConfig('Lecturer III');
                $cadreQueryConfig->setQueryCriteria(" (designation = 'Lecturer III')");
                $queryConfigs[] = $cadreQueryConfig;

                $cadreQueryConfig = new NumberOfPositionQueryConfig('Assistant Lecturer');
                $cadreQueryConfig->setQueryCriteria(" (designation = 'Assistant Lecturer')");
                $queryConfigs[] = $cadreQueryConfig;

                $cadreQueryConfig = new NumberOfPositionQueryConfig('Graduate Assistant');
                $cadreQueryConfig->setQueryCriteria(" (designation = 'Graduate Assistant')");

                $cadreQueryConfig = new NumberOfPositionQueryConfig('Persons With Disability');
                $cadreQueryConfig->setQueryCriteria(" (designation = 'not yet supported')");
                $queryConfigs[] = $cadreQueryConfig;


                /**
                 * @var NumberOfPositionCadreAnalysisEntry[]
                 */
                $tempCadreEntries = array(); //cadreId => cadreEntry

                /**
                 * @var NumberOfPositionQueryConfig $queryConfig
                 */
                foreach ($queryConfigs as $queryConfig) {
                    $cadreAnalysisEntry = new NumberOfPositionCadreAnalysisEntry();
                    $cadreAnalysisEntry->setCadreId($queryConfig->getCadre());
                    $cadreAnalysisEntry->setCadreCode($queryConfig->getCadre());
                    $cadreAnalysisEntry->setCadreName($queryConfig->getCadre());
                    //initialize the year entries
                    $stateYearEntries = array();
                    for ($year = $startYear; $year <= $endYear; $year++) {
                        $yearEntry = new NumberOfPositionYearEntry();
                        $yearEntry->setYear($year);
                        $stateYearEntries[$year] = $yearEntry;
                    }

                    $cadreAnalysisEntry->setYearEntries($stateYearEntries);
                    $tempCadreEntries[$cadreAnalysisEntry->getCadreId()] = $cadreAnalysisEntry;
                }

                foreach ($queryConfigs as $queryConfig) {

                    $currentCadreCriteria = $queryConfig->getQueryCriteria();

                    //now fetch the data for cadre and update the entries
                    $query = "select 'abc'";
                    for ($year = $startYear; $year <= $endYear; $year++) {
                        //14-29
                        $subQuery = ",(";
                        $subQuery .= "SELECT COUNT(gender)
                                FROM confirmed_federal_level_nominal_roll_submission
                                WHERE 
                                submission_id in ($submissionIdsInString) 
                                AND submission_year = :${year}_M_submission_year
                                AND $currentCadreCriteria
                                AND gender = 'M'";
                        $subQuery .= ") AS total_${year}_M";

                        $subQuery .= ",(";
                        $subQuery .= "SELECT COUNT(gender)
                                FROM confirmed_federal_level_nominal_roll_submission
                                WHERE 
                                submission_id in ($submissionIdsInString) 
                                AND submission_year = :${year}_F_submission_year
                                AND $currentCadreCriteria
                                AND gender = 'F'";
                        $subQuery .= ") AS total_${year}_F";

                        $query .= $subQuery;
                    }

                    $statement = $this->connection->prepare($query);
                    for ($year = $startYear; $year <= $endYear; $year++) {
                        $statement->bindValue(":${year}_M_submission_year", $year);
                        $statement->bindValue(":${year}_F_submission_year", $year);
                    }

                    $statement->execute();
                    $stateResult = $statement->fetch();
                    if ($stateResult) {
                        for ($year = $startYear; $year <= $endYear; $year++) {
                            $tempCadreEntries[$queryConfig->getCadre()]->updateYearEntry($year, $stateResult["total_${year}_M"], $stateResult["total_${year}_F"]);
                        }
                    }
                }

                //now calculate overall totals
                $overallYearEntryTotals = array();
                for ($year = $startYear; $year <= $endYear; $year++) {
                    $overallYearEntryTotal = new NumberOfPositionOverallYearTotalEntry();
                    $overallYearEntryTotal->setYear($year);

                    $overallYearTotal_M = 0;
                    $overallYearTotal_F = 0;

                    /**
                     * @var NumberOfPositionCadreAnalysisEntry[] $tempCadreEntries
                     */
                    foreach ($tempCadreEntries as $tempStateEntry) {
                        $overallYearTotal_M += $tempStateEntry->fetchYearEntry($year, 'M');
                        $overallYearTotal_F += $tempStateEntry->fetchYearEntry($year, 'F');
                    }

                    $overallYearEntryTotal->setOverallTotalMale($overallYearTotal_M);
                    $overallYearEntryTotal->setOverallTotalFemale($overallYearTotal_F);

                    $overallYearEntryTotals[$year] = $overallYearEntryTotal;
                }

                //now fill the report
                $report = new NumberOfPositionDataAnalysis();
                $report->setStartYear($startYear);
                $report->setEndYear($endYear);

                $report->setCadreEntries(array_values($tempCadreEntries));
                $report->setOverallYearTotals($overallYearEntryTotals);

                $report->setOrganizationCategoryId(AppConstants::ACADEMIC_ESTABLISHMENT);

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