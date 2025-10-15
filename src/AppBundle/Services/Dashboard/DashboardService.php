<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/9/2017
 * Time: 12:26 AM
 */

namespace AppBundle\Services\Dashboard;

use AppBundle\AppException\AppException;
use AppBundle\Utils\AppConstants;
use Doctrine\DBAL\Connection;
use \Throwable;
use \PDO;

class DashboardService
{
    private $connection;
    private $rows_per_page;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->rows_per_page = AppConstants::ROWS_PER_PAGE;
    }

    public function getFederalMdaAdminDashboard($organizationId) : array
    {
        $dashboard = array();
        $dashboard['totalMainSubmissions'] = 0;
        $dashboard['totalQuarterlyReturn'] = 0;
        $dashboard['totalFailedValidation'] = 0;
        $statement = null;

        try {
            $query = "SELECT 'abc' 
                ,(
                SELECT count(d.submission_id) 
                FROM federal_level_nominal_roll_submissions d  
                where d.organization_id=:organization_id_1 and d.submission_type=:submission_type_1 and d.is_active = :is_active_1
                 ) as total_main_submissions 
                ,(
                SELECT count(d.submission_id) 
                FROM federal_level_nominal_roll_submissions d  
                where d.organization_id=:organization_id_2 and d.submission_type=:submission_type_2 and d.is_active = :is_active_2
                 ) as total_quarterly_return 
                ,(
                SELECT count(d.submission_id) 
                FROM federal_level_nominal_roll_submissions d  
                where d.organization_id=:organization_id_3 
                and (d.validation_status=:validation_status_3a or d.validation_status=:validation_status_3b) 
                and d.is_active = :is_active_2
                 ) as total_failed_validation ";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':organization_id_1', $organizationId);
            $statement->bindValue(':submission_type_1', AppConstants::MAIN_SUBMISSION);
            $statement->bindValue(':is_active_1', AppConstants::Y);
            $statement->bindValue(':organization_id_2', $organizationId);
            $statement->bindValue(':submission_type_2', AppConstants::QUARTERLY_RETURN);
            $statement->bindValue(':is_active_2', AppConstants::Y);
            $statement->bindValue(':organization_id_3', $organizationId);
            $statement->bindValue(':validation_status_3a', AppConstants::FAILED);
            $statement->bindValue(':validation_status_3b', AppConstants::FATAL_ERROR);
            $statement->bindValue(':is_active_3', AppConstants::Y);

            $statement->execute();

            $result = $statement->fetch();

            $dashboard['totalMainSubmissions'] = $result['total_main_submissions'];
            $dashboard['totalQuarterlyReturn'] = $result['total_quarterly_return'];
            $dashboard['totalFailedValidation'] = $result['total_failed_validation'];


        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $dashboard;
    }

    public function getFccDeskOfficerDashboard($fccDeskOfficerCommitteeId) : array
    {
        $dashboard = array();
        $dashboard['totalPending'] = 0;
        $dashboard['totalFailed'] = 0;
        $statement = null;

        try {
            $query = "SELECT 'abc' 
                ,(
                SELECT count(d.submission_id) 
                FROM federal_level_nominal_roll_submissions d 
                JOIN organization o ON (d.organization_id = o.id AND (o.fcc_committee_id=:fcc_desk_officer_committee_id or $fccDeskOfficerCommitteeId=478)) 
                where d.validation_status=:validation_status 
                AND d.fcc_desk_officer_confirmation_status=:fcc_desk_officer_confirmation_status 
                 ) as total_pending 
                ,(
                SELECT count(d.submission_id) 
                FROM federal_level_nominal_roll_submissions d 
                JOIN organization o ON (d.organization_id = o.id AND (o.fcc_committee_id=:failed_fcc_desk_officer_committee_id  or $fccDeskOfficerCommitteeId=478)) 
                where d.validation_status=:failed_validation_status 
                 ) as total_failed";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':fcc_desk_officer_committee_id', $fccDeskOfficerCommitteeId);
            $statement->bindValue(':validation_status', AppConstants::PASSED);
            $statement->bindValue(':fcc_desk_officer_confirmation_status', AppConstants::PENDING);
            $statement->bindValue(':failed_fcc_desk_officer_committee_id', $fccDeskOfficerCommitteeId);
            $statement->bindValue(':failed_validation_status', AppConstants::FAILED);

            $statement->execute();

            $result = $statement->fetch();

            $dashboard['totalPending'] = $result['total_pending'];
            $dashboard['totalFailed'] = $result['total_failed'];


        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $dashboard;
    }

    public function getMisHeadDashboard() : array
    {
        $dashboard = array();
        $dashboard['totalPending'] = 0;
        $dashboard['totalStillProcessing'] = 0;
        $statement = null;

        try {
            $query = "SELECT 'abc' 
                ,(
                SELECT count(d.submission_id) 
                FROM federal_level_nominal_roll_submissions d 
                where d.fcc_desk_officer_confirmation_status=:fcc_desk_officer_confirmation_status 
                AND d.fcc_mis_head_approval_status=:fcc_mis_head_approval_status 
                 ) as total_pending 
                ,(
                SELECT count(d.submission_id) 
                FROM federal_level_nominal_roll_submissions d 
                where d.fcc_mis_head_approval_status=:fcc_mis_head_approval_status_2 
                AND d.processing_status=:processing_status_2 
                 ) as total_still_processing";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':fcc_desk_officer_confirmation_status', AppConstants::CONFIRMED);
            $statement->bindValue(':fcc_mis_head_approval_status', AppConstants::PENDING);
            $statement->bindValue(':fcc_mis_head_approval_status_2', AppConstants::APPROVED);
            $statement->bindValue(':processing_status_2', AppConstants::PENDING);

            $statement->execute();

            $result = $statement->fetch();

            $dashboard['totalPending'] = $result['total_pending'];
            $dashboard['totalStillProcessing'] = $result['total_still_processing'];


        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $dashboard;
    }
}