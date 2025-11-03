<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/1/2017
 * Time: 4:28 PM
 */

namespace AppBundle\Services\LongRunningTasks;


use AppBundle\AppException\AppException;
use AppBundle\Model\LongRunning\BatchRevalidation;
use Doctrine\DBAL\Connection;
use \Throwable;

class BatchReValidationService
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function initializeBatchToReValidate()
    {
        $failedSubmissions = false;
        $statement = null;

        try {

            //start transaction
            $this->connection->beginTransaction();

            $query = "DELETE FROM batch_revalidation_tracking";
            $statement = $this->connection->prepare($query);
            $statement->execute();

            //delete any passed in case it was not deleted at the end of a process
            /*$query = "DELETE FROM batch_revalidation_tracking WHERE new_validation_status='PASSED'";
            $statement = $this->connection->prepare($query);
            $statement->execute();*/

            //update existing new status to null
            /*$query = "UPDATE batch_revalidation_tracking set new_validation_status=NULL";
            $statement = $this->connection->prepare($query);
            $statement->execute();*/

            //select submissions that did not pass
            $query = "REPLACE INTO batch_revalidation_tracking "
                . "("
                . "SELECT o.establishment_type_id, d.submission_id, d.validation_status, NULL "
                . " FROM federal_level_nominal_roll_submissions d "
                . " JOIN organization o ON d.organization_id = o.id "
                . " WHERE d.validation_status<>'PASSED'"
                . ")";

            $statement = $this->connection->prepare($query);
            $statement->execute();

            //now select them
            $query = "SELECT * FROM batch_revalidation_tracking";
            $statement = $this->connection->prepare($query);
            $statement->execute();

            $records = $statement->fetchAll();

            if ($records) {
                for ($i = 0; $i < count($records); $i++) {
                    $record = $records[$i];

                    $failedSubmission = new BatchRevalidation();
                    $failedSubmission->setSubmissionId($record['submission_id']);
                    $failedSubmission->setValidationStatus($record['old_validation_status']);
                    $failedSubmission->setEstablishmentType($record['establishment_type']);

                    $failedSubmissions[] = $failedSubmission;
                }
            }

            //commit transaction
            $this->connection->commit();

        } catch (Throwable $e) {

            if ($this->connection->isTransactionActive()) {
                $this->connection->rollBack();
            }

            throw new AppException($e->getMessage());

        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $failedSubmissions;
    }

    public function updateBatchRevalidationStatus($submissionId)
    {
        $statement = null;
        try {

            $query = "update batch_revalidation_tracking "
                . " set new_validation_status = (select s.validation_status from federal_level_nominal_roll_submissions s where s.submission_id=:nominal_roll_submission_id) "
                . " where submission_id=:batch_revalidation_submission_id ";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':nominal_roll_submission_id', $submissionId);
            $statement->bindValue(':batch_revalidation_submission_id', $submissionId);
            $statement->execute();

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }
    }

    public function getBatchRevalidationStatus()
    {
        $analysis = array();
        $statement = null;
        try {

            $query = "select 'abc' "
                . ",(select count(*) from batch_revalidation_tracking) as total_batch "
                . ",(select count(*) from batch_revalidation_tracking where new_validation_status='FAILED') as total_failed "
                . ",(select count(*) from batch_revalidation_tracking where new_validation_status='PASSED') as total_passed "
                . ",(select count(*) from batch_revalidation_tracking where new_validation_status IS NULL) as total_pending_revalidation ";
            $statement = $this->connection->prepare($query);
            $statement->execute();

            $record = $statement->fetch();
            if($record){
                $analysis['totalBatch'] = $record['total_batch'];
                $analysis['totalFailed'] = $record['total_failed'];
                $analysis['totalPassed'] = $record['total_passed'];
                $analysis['totalPendingReValidation'] = $record['total_pending_revalidation'];
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }
        return $analysis;
    }

    public function cleanUpBatchRevalidationTracking()
    {
        $statement = null;

        try {

            //delete any passed in case it was not deleted at the end of a process
            $query = "DELETE FROM batch_revalidation_tracking";
            $statement = $this->connection->prepare($query);
            $statement->execute();

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

    }
}