<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/7/2017
 * Time: 11:27 AM
 */

namespace AppBundle\Services\FccDeskOffice;


use AppBundle\AppException\AppException;
use AppBundle\Model\Organizations\Organization;
use AppBundle\Model\SearchCriteria\NominalRollSubmissionSearchCriteria;
use AppBundle\Model\Submission\NominalRollSubmission;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\Paginator;
use AppBundle\Utils\StringHelper;
use Doctrine\DBAL\Connection;
use \Throwable;
use \PDO;

class FccDeskOfficerNominalRollService
{
    private $connection;
    private $rows_per_page;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->rows_per_page = AppConstants::ROWS_PER_PAGE;
    }

    public function searchNominalRollUploads(NominalRollSubmissionSearchCriteria $searchCriteria, $fccDeskOfficerCommitteeId, Paginator $paginator, $pageDirection): array
    {
        $nominalRollUploads = array();
        $statement = null;

        try {

            $searchFccDeskOfficerCommitteeId = $fccDeskOfficerCommitteeId;
            if (!$searchFccDeskOfficerCommitteeId) {
                $searchFccDeskOfficerCommitteeId = "-1";
            }

            $searchOrganizationId = $searchCriteria->getOrganizationId();
            $searchValidationStatus = $searchCriteria->getValidationStatus();
            $searchFccDeskOfficerConfirmation = $searchCriteria->getFccDeskOfficerConfirmationStatus();
            $searchFccMisHeadApproval = $searchCriteria->getFccMisHeadApprovalStatus();
            $searchActiveStatus = $searchCriteria->getActiveStatus();

            $where = array("(o.fcc_committee_id=$searchFccDeskOfficerCommitteeId or $searchFccDeskOfficerCommitteeId=478)");

            if ($searchOrganizationId) {
                $where[] = "d.organization_id = :organization_id";
            }

            if ($searchValidationStatus) {
                $where[] = "d.validation_status = :validation_status";
            }

            if ($searchFccDeskOfficerConfirmation) {
                $where[] = "d.fcc_desk_officer_confirmation_status = :fcc_desk_officer_confirmation_status";
            }

            if ($searchFccMisHeadApproval) {
                $where[] = "d.fcc_mis_head_approval_status = :fcc_mis_head_approval_status";
            }

            if ($searchActiveStatus) {
                $where[] = "d.is_active = :is_active";
            }

            if ($where) {
                $where = implode(" AND ", $where);
            } else {
                $where = '';
            }

            //fetch total matching rows
            $countQuery = "SELECT COUNT(d.submission_id) AS totalRows 
                FROM federal_level_nominal_roll_submissions d 
                JOIN organization o on d.organization_id = o.id 
                WHERE $where ";
            $statement = $this->connection->prepare($countQuery);

            if ($searchOrganizationId) {
                $statement->bindValue(':organization_id', $searchOrganizationId);
            }

            if ($searchValidationStatus) {
                $statement->bindValue(':validation_status', $searchValidationStatus);
            }

            if ($searchFccDeskOfficerConfirmation) {
                $statement->bindValue(':fcc_desk_officer_confirmation_status', $searchFccDeskOfficerConfirmation);
            }

            if ($searchFccMisHeadApproval) {
                $statement->bindValue(':fcc_mis_head_approval_status', $searchFccMisHeadApproval);
            }

            if ($searchActiveStatus) {
                $statement->bindValue(':is_active', $searchActiveStatus);
            }

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
            $query = "SELECT d.submission_id,d.submission_year,d.organization_id,d.uploaded_file_name,d.total_rows_imported 
                ,format(d.total_rows_imported,2) as _total_rows_imported
                ,d.validation_status,d.date_last_validated,d.date_validation_passed,d.fcc_desk_officer_confirmation_status 
                ,d.date_fcc_desk_officer_confirmed,d.fcc_mis_head_approval_status,d.date_fcc_mis_head_approved 
                ,d.processing_status,d.date_processing_started,d.date_processing_completed 
                ,date_format(d.date_validation_passed,'%e-%b-%Y %h:%i %p') as _date_validation_passed 
                ,date_format(d.date_fcc_desk_officer_confirmed,'%e-%b-%Y %h:%i %p') as _date_fcc_desk_officer_confirmed 
                ,date_format(d.date_fcc_mis_head_approved,'%e-%b-%Y %h:%i %p') as _date_fcc_mis_head_approved 
                ,date_format(d.created,'%e-%b-%Y %h:%i %p') as _created 
                ,o.establishment_code,o.establishment_mnemonic,o.organization_name 
                FROM federal_level_nominal_roll_submissions d 
                JOIN organization o on d.organization_id = o.id 
                WHERE $where 
                ORDER BY d.created desc LIMIT $limitStartRow ,$this->rows_per_page ";

            $statement = $this->connection->prepare($query);

            if ($searchOrganizationId) {
                $statement->bindValue(':organization_id', $searchOrganizationId);
            }

            if ($searchValidationStatus) {
                $statement->bindValue(':validation_status', $searchValidationStatus);
            }

            if ($searchFccDeskOfficerConfirmation) {
                $statement->bindValue(':fcc_desk_officer_confirmation_status', $searchFccDeskOfficerConfirmation);
            }

            if ($searchFccMisHeadApproval) {
                $statement->bindValue(':fcc_mis_head_approval_status', $searchFccMisHeadApproval);
            }

            if ($searchActiveStatus) {
                $statement->bindValue(':is_active', $searchActiveStatus);
            }

            $statement->execute();

            $records = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($records) {

                $displaySerialNo = $paginator->getStartRow() + 1;

                $stringHelper = new StringHelper();

                foreach ($records as $record) {
                    $nominalRoleSubmission = new NominalRollSubmission();
                    $nominalRoleSubmission->setSubmissionId($record['submission_id']);
                    $nominalRoleSubmission->setSubmissionYear($record['submission_year']);
                    $nominalRoleSubmission->setOrganizationId($record['organization_id']);
                    $nominalRoleSubmission->setOrganizationEstablishmentCode($record['establishment_code']);
                    $nominalRoleSubmission->setOrganizationMnemonic($record['establishment_mnemonic']);
                    $nominalRoleSubmission->setOrganizationName($record['organization_name']);
                    $nominalRoleSubmission->setUploadedFileName($record['uploaded_file_name']);

                    $uploadedFileName = $nominalRoleSubmission->getUploadedFileName();
                    $nominalRoleSubmission->setSimpleFileName($stringHelper->substringBeforeLast($uploadedFileName, "_")
                        . $stringHelper->substringFromLast($uploadedFileName, "."));

                    $nominalRoleSubmission->setTotalRowsImported($record['total_rows_imported']);
                    $nominalRoleSubmission->setTotalRowsImportedFormatted(number_format($nominalRoleSubmission->getTotalRowsImported()));

                    $nominalRoleSubmission->setValidationStatus($record['validation_status']);
                    $nominalRoleSubmission->setDateValidationPassed($record['date_validation_passed']);
                    $nominalRoleSubmission->setDateValidationPassedFormatted($record['_date_validation_passed']);

                    $nominalRoleSubmission->setFccDeskOfficerConfirmationStatus($record['fcc_desk_officer_confirmation_status']);
                    $nominalRoleSubmission->setDateFccDeskOfficerConfirmed($record['date_fcc_desk_officer_confirmed']);
                    $nominalRoleSubmission->setDateFccDeskOfficerConfirmedFormatted($record['_date_fcc_desk_officer_confirmed']);

                    $nominalRoleSubmission->setFccMisHeadApprovalStatus($record['fcc_mis_head_approval_status']);
                    $nominalRoleSubmission->setDateFccMisHeadApproved($record['date_fcc_mis_head_approved']);
                    $nominalRoleSubmission->setDateFccMisHeadApprovedFormatted($record['_date_fcc_mis_head_approved']);

                    $nominalRoleSubmission->setProcessingStatus($record['processing_status']);
                    $nominalRoleSubmission->setCreated($record['_created']);

                    $nominalRoleSubmission->setDisplaySerialNo($displaySerialNo);
                    $displaySerialNo++;

                    $nominalRollUploads[] = $nominalRoleSubmission;
                }
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $nominalRollUploads;
    }

    public function getFccDeskOfficerOrganizations($fccDeskOfficerUserCommitteeId): array
    {
        $organizations = array();
        $statement = null;

        try {
            $query = "SELECT d.id,d.establishment_code,d.establishment_mnemonic,d.organization_name 
                FROM organization d 
                WHERE (d.fcc_committee_id=:fcc_committee_id or $fccDeskOfficerUserCommitteeId=478) AND d.record_status=:record_status";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':fcc_committee_id', $fccDeskOfficerUserCommitteeId);
            $statement->bindValue(':record_status', AppConstants::ACTIVE);
            $statement->execute();

            $records = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($records) {
                foreach ($records as $record) {
                    $organization = new Organization();
                    $organization->setId($record['id']);
                    $organization->setEstablishmentCode($record['establishment_code']);
                    $organization->setEstablishmentMnemonic($record['establishment_mnemonic']);
                    $organization->setOrganizationName($record['organization_name']);

                    $organizations[] = $organization;
                }
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $organizations;
    }

    public function getFccDeskOfficerSubmissionSummary($fccDeskOfficerUserCommitteeId): array
    {
        $submissionSummary = array();
        $submissionSummary['totalPending'] = 0;
        $submissionSummary['totalFailed'] = 0;
        $statement = null;

        try {
            $query = "SELECT 'abc' 
                ,(
                SELECT count(d.submission_id) 
                FROM federal_level_nominal_roll_submissions d 
                JOIN organization o ON (d.organization_id = o.id AND (o.fcc_committee_id=:fcc_desk_officer_committee_id  or $fccDeskOfficerUserCommitteeId=478)) 
                WHERE d.validation_status=:validation_status 
                AND d.fcc_desk_officer_confirmation_status=:fcc_desk_officer_confirmation_status 
                 ) AS total_pending 
                ,(
                SELECT count(d.submission_id) 
                FROM federal_level_nominal_roll_submissions d 
                JOIN organization o ON (d.organization_id = o.id AND (o.fcc_desk_officer_id=:failed_fcc_desk_officer_committee_id  or $fccDeskOfficerUserCommitteeId=478)) 
                WHERE d.validation_status=:failed_validation_status
                 ) AS total_failed ";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':fcc_desk_officer_committee_id', $fccDeskOfficerUserCommitteeId);
            $statement->bindValue(':validation_status', AppConstants::PASSED);
            $statement->bindValue(':fcc_desk_officer_confirmation_status', AppConstants::PENDING);
            $statement->bindValue(':failed_fcc_desk_officer_committee_id', $fccDeskOfficerUserCommitteeId);
            $statement->bindValue(':failed_validation_status', AppConstants::FAILED);

            $statement->execute();

            $result = $statement->fetch();

            $submissionSummary['totalPending'] = $result['total_pending'];
            $submissionSummary['totalFailed'] = $result['total_failed'];


        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $submissionSummary;
    }

    public function confirmNominalRollSubmission(array $submissionRecordIds, $isFccFederalDeskOfficer, $isFccStateDeskOfficer, $userProfileId): bool
    {
        $outcome = false;
        $statement = null;

        try {

            if ($submissionRecordIds) {

                $this->connection->beginTransaction();

                //build the in string
                $inString = '';
                for ($i = 0; $i < count($submissionRecordIds); $i++) {
                    if ($i === 0) {
                        $inString = "'$submissionRecordIds[$i]'";
                    } else {
                        $inString .= ",'$submissionRecordIds[$i]'";
                    }
                }

                $today = date("Y-m-d H:i:s");

                $query = "update federal_level_nominal_roll_submissions 
                    set 
                    fcc_desk_officer_confirmation_status=:fcc_desk_officer_confirmation_status
                    ,date_fcc_desk_officer_confirmed=:date_fcc_desk_officer_confirmed
                    ,confirmed_by=:confirmed_by
                    WHERE submission_id IN ($inString)";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(":fcc_desk_officer_confirmation_status", AppConstants::CONFIRMED);
                $statement->bindValue(":date_fcc_desk_officer_confirmed", $today);
                $statement->bindValue(":confirmed_by", $userProfileId);
                $statement->execute();

                if ($isFccFederalDeskOfficer) {

                    //now move the records from the staging section to the main table
                    //CONSIDER RE-IMPLEMENTING THIS, maybe
                    $query = "insert into confirmed_federal_level_nominal_roll_submission 
                            (
                             id,submission_id,submission_year
                             ,submission_upload_date
                             ,serial_number,staff_disposition,staff_type
                             ,employee_status,employee_number,employee_name
                             ,nationality_code,state_of_origin_code,lga_code,region_code
                             ,date_of_birth,date_of_employment,date_of_present_appointment,grade_level,designation 
                             ,state_of_deployment_code,gender,marital_status 
                             ,physically_challenged_status,quarterly_return_employment_status
                            ) 
                            ( 
                             select 
                             d.id,d.submission_id,d.submission_year
                             ,(select s.created from federal_level_nominal_roll_submissions s where s.submission_id = d.submission_id) as submission_upload_date
                             ,d.serial_number,d.staff_disposition,d.staff_type
                             ,d.employee_status,d.employee_number,d.employee_name
                             ,d.nationality_code,d.state_of_origin_code,d.lga_code,d.region_code
                             ,d.date_of_birth,d.date_of_employment,d.date_of_present_appointment,d.grade_level,d.designation 
                             ,d.state_of_deployment_code,d.gender,d.marital_status 
                             ,d.physically_challenged_status,d.quarterly_return_employment_status
                             from federal_level_nominal_roll_submission_staging d 
                             where submission_id IN ($inString)
                            )";
                    $statement = $this->connection->prepare($query);
                    $statement->execute();

                } else if ($isFccStateDeskOfficer) {

                }

                $this->connection->commit();
            }

            $outcome = true;

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

        return $outcome;

    }
}