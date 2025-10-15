<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 2:11 PM
 */

namespace AppBundle\Services\SuperAdmin\SubmissionSetup;


use AppBundle\AppException\AppException;
use AppBundle\Model\SubmissionSetup\SubmissionSchedule;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\Paginator;
use AppBundle\Utils\ServiceHelper;
use Doctrine\DBAL\Connection;
use \PDO;
use \Throwable;

class SubmissionScheduleService extends ServiceHelper
{
    private $connection;
    private $rows_per_page;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->rows_per_page = AppConstants::ROWS_PER_PAGE;
    }


    public function searchRecords(Paginator $paginator, $pageDirection) : array
    {
        $submissionSchedules = array();
        $statement = null;

        try {

            $where = array("d.id > 0");

            if ($where) {
                $where = implode(" AND ", $where);
            } else {
                $where = '';
            }

            //fetch total matching rows
            $countQuery = "SELECT COUNT(d.id) AS totalRows FROM submission_demand_schedule d WHERE $where";
            $statement = $this->connection->prepare($countQuery);
            

            $statement->execute();
            $totalRows = $statement->fetchColumn();

            $paginator->setTotalRows($totalRows);
            $paginator->setRowsPerPage($this->rows_per_page);

            switch ($pageDirection) {
                case 'FIRST':
                    $paginator->pageFirst();
                    break;
                case 'PREVIOUS':
                    $paginator->pagePrevious();
                    break;
                case 'NEXT':
                    $paginator->pageNext();
                    break;
                case 'LAST':
                    $paginator->pageLast();
                    break;
                default:
                    $paginator->pageFirst();
                    break;
            }

            $limitStartRow = $paginator->getStartRow();

            //now exec SELECT with limit clause
            $query = "SELECT d.id,d.submission_year,d.start_date,d.end_date "
                . ",date_format(d.start_date,'%e-%b-%Y') as _start_date"
                . ",date_format(d.end_date,'%e-%b-%Y') as _end_date"
                . "FROM submission_demand_schedule d "
                . "WHERE $where order by d.submission_year desc LIMIT $limitStartRow ,$this->rows_per_page ";
            $statement = $this->connection->prepare($query);

            $statement->execute();

            $records = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($records) {

                $displaySerialNo = $paginator->getStartRow() + 1;

                for ($i = 0; $i < count($records); $i++) {

                    $record = $records[$i];

                    $submissionSchedule = new SubmissionSchedule();
                    $submissionSchedule->setId($record['id']);
                    $submissionSchedule->setSubmissionYear($record['committee_code']);
                    $submissionSchedule->setStartDate($record['committee_name']);
                    $submissionSchedule->setEndDate($record['record_status']);

                    $submissionSchedule->setDisplaySerialNo($displaySerialNo);
                    $displaySerialNo++;

                    $submissionSchedules[] = $submissionSchedule;
                }
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $submissionSchedules;
    }

    public function addSubmissionSchedule(SubmissionSchedule $submissionSchedule) : bool
    {
        $outcome = false;
        $statement = null;

        try {

            //check for duplicate year
            $query = "SELECT id FROM submission_demand_schedule WHERE submission_year=:submission_year LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':submission_year', $submissionSchedule->getSubmissionYear());
            $statement->execute();
            $existingRecord = $statement->fetch();

            if ($existingRecord) {
                throw new AppException("Duplicate Submission Year");
            }

            //now insert
            $query = "INSERT INTO submission_demand_schedule "
                . "(submission_year,start_date,end_date,created,created_by,last_mod,last_mod_by) "
                . "VALUES (:submission_year,:start_date,:end_date,:created,:created_by,:last_mod,:last_mod_by)";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':submission_year', $submissionSchedule->getSubmissionYear());
            $statement->bindValue(':start_date', $submissionSchedule->getStartDate());
            $statement->bindValue(':end_date', $submissionSchedule->getEndDate());
            $statement->bindValue(':created', $submissionSchedule->getLastModified());
            $statement->bindValue(':created_by', $submissionSchedule->getLastModifiedByUserId());
            $statement->bindValue(':last_mod', $submissionSchedule->getLastModified());
            $statement->bindValue(':last_mod_by', $submissionSchedule->getLastModifiedByUserId());

            $statement->execute();

            $outcome = true;

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $outcome;
    }

}