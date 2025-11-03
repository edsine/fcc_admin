<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/6/2017
 * Time: 3:47 PM
 */

namespace AppBundle\Services\Reporting;


use AppBundle\AppException\AppException;
use AppBundle\Model\Reporting\Submission\SubmissionStatus;
use AppBundle\Model\SearchCriteria\SubmissionStatusSearchCriteria;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\Paginator;
use Doctrine\DBAL\Driver\Connection;

class SubmissionStatusService
{

    private $connection;
    private $rows_per_page;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->rows_per_page = AppConstants::ROWS_PER_PAGE;
    }


    public function searchRecords(SubmissionStatusSearchCriteria $searchCriteria, Paginator $paginator, $pageDirection) : array
    {
        $nominalRollSubmissions = array();
        $statement = null;

        try {

            $searchOrganization = $searchCriteria->getOrganizationId();
            $searchYear = $searchCriteria->getSubmissionYear();
            $searchValidationStatus = $searchCriteria->getValidationStatus();
            $searchFccConfirmationStatus = $searchCriteria->getFccDeskOfficerConfirmationStatus();
            $searchMisHeadApprovalStatus = $searchCriteria->getMisHeadApprovalStatus();
            $searchProcessingStatus = $searchCriteria->getProcessingStatus();

            $where = array("d.submission_id <> '0'");

            if ($searchOrganization) {
                $where[] = "d.organization_id = :organization_id";
            }

            if ($searchYear) {
                $where[] = "d.submission_year = :submission_year";
            }

            if ($searchValidationStatus) {
                $where[] = "d.validation_status = :validation_status";
            }

            if ($searchFccConfirmationStatus) {
                $where[] = "d.fcc_desk_officer_confirmation_status = :fcc_desk_officer_confirmation_status";
            }

            if ($searchMisHeadApprovalStatus) {
                $where[] = "d.fcc_mis_head_approval_status = :fcc_mis_head_approval_status";
            }

            if ($searchProcessingStatus) {
                $where[] = "d.processing_status = :processing_status";
            }

            if ($where) {
                $where = implode(" AND ", $where);
            } else {
                $where = '';
            }

            //fetch total matching rows
            $countQuery = "SELECT COUNT(d.submission_id) AS totalRows FROM federal_level_nominal_roll_submissions d WHERE $where";
            $statement = $this->connection->prepare($countQuery);

            if ($searchOrganization) {
                $statement->bindValue(':organization_id', $searchOrganization);
            }

            if ($searchYear) {
                $statement->bindValue(':submission_year', $searchYear);
            }

            if ($searchValidationStatus) {
                $statement->bindValue(':validation_status', $searchValidationStatus);
            }

            if ($searchFccConfirmationStatus) {
                $statement->bindValue(':fcc_desk_officer_confirmation_status', $searchFccConfirmationStatus);
            }

            if ($searchMisHeadApprovalStatus) {
                $statement->bindValue(':fcc_mis_head_approval_status', $searchMisHeadApprovalStatus);
            }

            if ($searchProcessingStatus) {
                $statement->bindValue(':processing_status', $searchProcessingStatus);
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
                ,date_format(d.created,'%e-%b-%Y %h:%i %p') as _created 
                ,o.establishment_code,o.establishment_mnemonic,o.organization_name 
                ,concat_ws(' ', fcc_desk_officer.last_name, fcc_desk_officer.first_name) as fcc_desk_officer_name
                ,fcc_desk_officer.primary_phone as fcc_desk_officer_phone
                ,fcc_desk_officer.email_address as fcc_desk_officer_email_address
                ,concat_ws(' ', mda_desk_officer.last_name, mda_desk_officer.first_name) as mda_desk_officer_name
                ,mda_desk_officer.primary_phone as mda_desk_officer_phone
                ,mda_desk_officer.email_address as mda_desk_officer_email_address
                FROM federal_level_nominal_roll_submissions d 
                JOIN organization o on d.organization_id = o.id 
                LEFT JOIN user_profile as fcc_desk_officer on (o.fcc_committee_id = fcc_desk_officer.fcc_committee_id 
                                                                    and fcc_desk_officer.primary_role=:fcc_desk_officer_role)
                LEFT JOIN user_profile as mda_desk_officer on d.created_by = mda_desk_officer.id
                WHERE $where order by d.created desc LIMIT $limitStartRow ,$this->rows_per_page ";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':fcc_desk_officer_role', AppConstants::ROLE_FCC_DESK_OFFICER_FEDERAL);

            if ($searchOrganization) {
                $statement->bindValue(':organization_id', $searchOrganization);
            }

            if ($searchYear) {
                $statement->bindValue(':submission_year', $searchYear);
            }

            if ($searchValidationStatus) {
                $statement->bindValue(':validation_status', $searchValidationStatus);
            }

            if ($searchFccConfirmationStatus) {
                $statement->bindValue(':fcc_desk_officer_confirmation_status', $searchFccConfirmationStatus);
            }

            if ($searchMisHeadApprovalStatus) {
                $statement->bindValue(':fcc_mis_head_approval_status', $searchMisHeadApprovalStatus);
            }

            if ($searchProcessingStatus) {
                $statement->bindValue(':processing_status', $searchProcessingStatus);
            }

            $statement->execute();

            $records = $statement->fetchAll(\PDO::FETCH_ASSOC);
            if ($records) {

                $displaySerialNo = $paginator->getStartRow() + 1;

                for ($i = 0; $i < count($records); $i++) {

                    $record = $records[$i];

                    $submissionStatus = new SubmissionStatus();
                    $submissionStatus->setSubmissionId($record['submission_id']);
                    $submissionStatus->setSubmissionYear($record['submission_year']);
                    $submissionStatus->setOrganizationId($record['organization_id']);
                    $submissionStatus->setOrganizationEstablishmentCode($record['establishment_code']);
                    $submissionStatus->setOrganizationName($record['organization_name']);
                    $submissionStatus->setUploadedFileName($record['uploaded_file_name']);

                    $submissionStatus->setTotalRowsImportedFormatted(number_format($record['total_rows_imported']));

                    $submissionStatus->setMdaDeskOfficerName($record['mda_desk_officer_name']);
                    $submissionStatus->setMdaDeskOfficerPhone($record['mda_desk_officer_phone']);
                    $submissionStatus->setMdaDeskOfficerEmail($record['mda_desk_officer_email_address']);

                    $submissionStatus->setFccDeskOfficerName($record['fcc_desk_officer_name']);
                    $submissionStatus->setFccDeskOfficerPhone($record['fcc_desk_officer_phone']);
                    $submissionStatus->setFccDeskOfficerEmail($record['fcc_desk_officer_email_address']);

                    $submissionStatus->setValidationStatus($record['validation_status']);
                    $submissionStatus->setFccDeskOfficerConfirmationStatus($record['fcc_desk_officer_confirmation_status']);
                    $submissionStatus->setMisHeadApprovalStatus($record['fcc_mis_head_approval_status']);
                    $submissionStatus->setProcessingStatus($record['processing_status']);
                    $submissionStatus->setDateOfSubmission($record['_created']);

                    $submissionStatus->setDisplaySerialNo($displaySerialNo);

                    $submissionStatus->initializeStepHtml();

                    $displaySerialNo++;

                    $nominalRollSubmissions[] = $submissionStatus;
                }
            }

        } catch (\Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $nominalRollSubmissions;
    }

}