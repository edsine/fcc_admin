<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/6/2017
 * Time: 3:47 PM
 */

namespace AppBundle\Services\Reporting;


use AppBundle\AppException\AppException;
use AppBundle\Model\Reporting\Shared\MDAComparativeAnalysisEntry;
use AppBundle\Model\Reporting\Shared\MDAComparativeDataAnalysis;
use AppBundle\Model\Reporting\Shared\MDAComparativeOverallYearTotalEntry;
use AppBundle\Model\Reporting\Shared\MDAYearEntry;
use AppBundle\Utils\AppConstants;
use Doctrine\DBAL\Driver\Connection;
use \PDO;

class AffectedMDAsDataService
{

    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }


    public function getAffectedFedMDAComparativeReport($startYear, $endYear, $organizationCategoryId = null)
    {
        $report = null;
        $statement = null;

        try {

            //get the organizations that have submitted in all the years
            $yearDifference = ($endYear - $startYear) + 1;
            if(!$organizationCategoryId){
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
            }else{

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
            }

            $organizationsConsidered = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

            if ($organizationsConsidered) {

                $organizationIdsInsString = implode(',', $organizationsConsidered); //organizations would be array of ints

                $query = "SELECT id,establishment_code, organization_name 
                      FROM organization 
                      WHERE id in ($organizationIdsInsString) 
                      order by organization_name";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':record_status', AppConstants::ACTIVE);
                $statement->execute();
                $affectedMdas = $statement->fetchAll();

                //initialize the mda entries
                /**
                 * @var MDAComparativeAnalysisEntry[] $tempMdaEntries
                 */
                $tempMdaEntries = array(); //stateId => stateEntry
                foreach ($affectedMdas as $affectedMda) {
                    $mdaAnalysisEntry = new MDAComparativeAnalysisEntry();
                    $mdaAnalysisEntry->setOrganizationId($affectedMda['id']);
                    $mdaAnalysisEntry->setOrganizationCode($affectedMda['establishment_code']);
                    $mdaAnalysisEntry->setOrganizationName($affectedMda['organization_name']);

                    //initialize the year entries
                    $mdaYearEntries = array();
                    for ($year = $startYear; $year <= $endYear; $year++) {
                        $yearEntry = new MDAYearEntry();
                        $yearEntry->setYear($year);
                        $mdaYearEntries[$year] = $yearEntry;
                    }

                    $mdaAnalysisEntry->setYearEntries($mdaYearEntries);
                    $tempMdaEntries[$mdaAnalysisEntry->getOrganizationId()] = $mdaAnalysisEntry;
                }


                //now fetch the data
                for ($year = $startYear; $year <= $endYear; $year++) {

                    $query = "SELECT d.organization_id, SUM(total_rows_imported) as total_staff_submitted
                            FROM federal_level_nominal_roll_submissions d 
                            WHERE d.organization_id in ($organizationIdsInsString) 
                            AND d.submission_year=:submission_year
                            group by d.organization_id";

                    $statement = $this->connection->prepare($query);
                    $statement->bindValue(":submission_year", $year);
                    $statement->execute();

                    $yearResultRecords = $statement->fetchAll();
                    if ($yearResultRecords) {
                        foreach ($yearResultRecords as $yearResultRecord){
                            $tempMdaEntries[$yearResultRecord['organization_id']]->updateYearEntryTotal($year, $yearResultRecord["total_staff_submitted"]);
                        }
                    }

                }

                //now calculate overall totals
                $overallYearEntryTotals = array();
                for ($year = $startYear; $year <= $endYear; $year++) {
                    $overallYearEntryTotal = new MDAComparativeOverallYearTotalEntry();
                    $overallYearEntryTotal->setYear($year);

                    $overallYearTotal = 0;
                    foreach ($tempMdaEntries as $tempStateEntry) {
                        $overallYearTotal += $tempStateEntry->fetchYearEntryTotal($year);
                    }
                    $overallYearEntryTotal->setOverallTotal($overallYearTotal);

                    $overallYearEntryTotals[$year] = $overallYearEntryTotal;
                }

                //now calculate the organization year entry percentages
                foreach ($tempMdaEntries as $tempMdaEntry) {
                    for ($year = $startYear; $year <= $endYear; $year++) {
                        $tempMdaEntry->updateYearEntryPercentage($year, $overallYearEntryTotals[$year]->getOverallTotal());
                    }
                }

                //now calculate the overall percentages
                foreach ($overallYearEntryTotals as $overallYearEntryTotal) {
                    $overallYearPercentage = 0;
                    foreach ($tempMdaEntries as $tempMdaEntry) {
                        $overallYearPercentage += $tempMdaEntry->fetchYearEntryPercentage($overallYearEntryTotal->getYear());
                    }
                    $overallYearEntryTotal->setOverallPercentage($overallYearPercentage);

                }

                //format vals
                foreach ($tempMdaEntries as $tempMdaEntry) {
                    $tempMdaEntry->formatValues();
                }

                foreach ($overallYearEntryTotals as $overallYearEntryTotal) {
                    $overallYearEntryTotal->setOverallPercentage(round($overallYearEntryTotal->getOverallPercentage()));
                    if ($overallYearEntryTotal->getOverallPercentage() == 99) {
                        $overallYearEntryTotal->setOverallPercentage(100);
                    }
                }

                //now fill the report
                $report = new MDAComparativeDataAnalysis();
                $report->setStartYear($startYear);
                $report->setEndYear($endYear);

                $report->setMdaEntries(array_values($tempMdaEntries));
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