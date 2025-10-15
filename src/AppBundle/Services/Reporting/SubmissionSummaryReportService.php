<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/6/2017
 * Time: 3:47 PM
 */

namespace AppBundle\Services\Reporting;


use AppBundle\AppException\AppException;
use AppBundle\Model\Reporting\Submission\SubmissionSummaryAnalysis;
use AppBundle\Model\Reporting\Submission\SubmissionSummaryYearEntry;
use Doctrine\DBAL\Driver\Connection;
use \PDO;

class SubmissionSummaryReportService
{

    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }


    public function getSubmissionSummaryReport($startYear, $endYear)
    {
        $report = null;
        $statement = null;

        try {

            $tempYearAnalysis = array();

            for ($year = $startYear; $year <= $endYear; $year++) {

                $query = "select 'abc'";
                $subQuery = ",(";
                $subQuery .= "SELECT COUNT(id)
                                FROM organization
                                WHERE 
                                year_of_establishment <= :submission_year_1";
                $subQuery .= ") AS total_qualified";

                $subQuery .= ",(";
                $subQuery .= "SELECT COUNT(DISTINCT(organization_id))
                                FROM federal_level_nominal_roll_submissions
                                WHERE 
                                submission_year = :submission_year_2";
                $subQuery .= ") AS total_actual_submissions";

                $query .= $subQuery;

                $statement = $this->connection->prepare($query);
                $statement->bindValue(":submission_year_1", $year);
                $statement->bindValue(":submission_year_2", $year);

                $statement->execute();
                $yearResult = $statement->fetch();
                if ($yearResult) {
                    $summaryYearEntry = new SubmissionSummaryYearEntry();
                    $summaryYearEntry->setYear($year);
                    $summaryYearEntry->setTotalQualifiedOrganizations($yearResult['total_qualified']);
                    $summaryYearEntry->setTotalActualSubmissions($yearResult['total_actual_submissions']);

                    $tempYearAnalysis[] = $summaryYearEntry;
                }

            }

            //now fill the report
            $report = new SubmissionSummaryAnalysis();
            $report->setStartYear($startYear);
            $report->setEndYear($endYear);

            $report->setYearEntries(array_values($tempYearAnalysis));


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