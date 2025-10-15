<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 2:11 PM
 */

namespace AppBundle\Services\MdaDeskOffice;


use AppBundle\AppException\AppException;
use AppBundle\AppException\AppExceptionMessages;
use AppBundle\Model\SearchCriteria\NominalRoleSearchCriteria;
use AppBundle\Model\SearchCriteria\NominalRollSubmissionSearchCriteria;
use AppBundle\Model\Submission\NominalRoll;
use AppBundle\Model\Submission\NominalRollValidation;
use AppBundle\Model\Submission\NominalRollSubmission;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\Paginator;
use AppBundle\Utils\StringHelper;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use \PDO;
use \Throwable;

class FederalMDANominalRollService
{
    private $connection;
    private $rows_per_page;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->rows_per_page = AppConstants::ROWS_PER_PAGE;
    }

    /**
     * @param NominalRollSubmissionSearchCriteria $searchCriteria
     * @param Paginator $paginator
     * @param $pageDirection
     * @return array
     * @throws AppException
     */
    public function searchNominalRollUploads(NominalRollSubmissionSearchCriteria $searchCriteria, Paginator $paginator, $pageDirection): array
    {
        $nominalRoleUploads = array();
        $statement = null;

        try {

            //$searchSubmissionId = $searchCriteria->getSubmissionId();

            /*if (!$searchSubmissionId) {
                $searchSubmissionId = '-1';
            }*/
            $searchOrganizationId = $searchCriteria->getOrganizationId();

            if (!$searchOrganizationId) {
                $searchOrganizationId = '-1';
            }

            $searchSubmissionYear = $searchCriteria->getSubmissionYear();
            $searchSubmissionType = $searchCriteria->getSubmissionType();
            $searchUploadedFileName = $searchCriteria->getUploadedFileName();
            $searchValidationStatus = $searchCriteria->getValidationStatus();
            $searchApprovalStatus = $searchCriteria->getFccDeskOfficerConfirmationStatus();
            //$searchProcessingStatus = $searchCriteria->getProcessingStatus();
            $searchActiveStatus = $searchCriteria->getActiveStatus();


            $where = array("d.organization_id='$searchOrganizationId'");

            /*if ($searchSubmissionId) {
                $where[] = "d.submission_id = :submission_id";
            }*/
            if ($searchSubmissionYear) {
                $where[] = "d.submission_year = :submission_year";
            }
            if ($searchSubmissionType) {
                $where[] = "d.submission_type = :submission_type";
            }
            if ($searchUploadedFileName) {
                $where[] = "d.uploaded_file_name LIKE :uploaded_file_name";
            }
            if ($searchValidationStatus) {
                $where[] = "d.validation_status = :validation_status";
            }
            if ($searchApprovalStatus) {
                $where[] = "d.fcc_desk_officer_confirmation_status = :fcc_desk_officer_confirmation_status";
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
                WHERE $where ";
            $statement = $this->connection->prepare($countQuery);

            /*if ($searchSubmissionId) {
                $statement->bindValue(':submission_id', $searchSubmissionId);
            }*/
            if ($searchSubmissionYear) {
                $statement->bindValue(':submission_year', $searchSubmissionYear);
            }
            if ($searchSubmissionType) {
                $statement->bindValue(':submission_type', $searchSubmissionType);
            }
            if ($searchUploadedFileName) {
                $statement->bindValue(':uploaded_file_name', "%" . $searchUploadedFileName . "%");
            }
            if ($searchValidationStatus) {
                $statement->bindValue(':validation_status', $searchValidationStatus);
            }
            if ($searchApprovalStatus) {
                $statement->bindValue(':fcc_desk_officer_confirmation_status', $searchApprovalStatus);
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
                ,d.validation_status,d.date_last_validated,d.date_validation_passed,d.submission_type,d.fcc_desk_officer_confirmation_status 
                ,d.date_fcc_desk_officer_confirmed,d.fcc_mis_head_approval_status,d.date_fcc_mis_head_approved 
                ,d.processing_status,d.date_processing_started,d.date_processing_completed 
                ,date_format(d.created,'%e-%b-%Y %h:%i %p') as _created 
                ,o.establishment_code,o.establishment_mnemonic,o.organization_name 
                FROM federal_level_nominal_roll_submissions d 
                JOIN organization o on d.organization_id = o.id 
                WHERE $where 
                ORDER BY d.created desc LIMIT $limitStartRow ,$this->rows_per_page ";

            $statement = $this->connection->prepare($query);

            /*if ($searchSubmissionId) {
                $statement->bindValue(':submission_id', $searchSubmissionId);
            }*/
            if ($searchSubmissionYear) {
                $statement->bindValue(':submission_year', $searchSubmissionYear);
            }
            if ($searchSubmissionType) {
                $statement->bindValue(':submission_type', $searchSubmissionType);
            }
            if ($searchUploadedFileName) {
                $statement->bindValue(':uploaded_file_name', "%" . $searchUploadedFileName . "%");
            }
            if ($searchValidationStatus) {
                $statement->bindValue(':validation_status', $searchValidationStatus);
            }
            if ($searchApprovalStatus) {
                $statement->bindValue(':fcc_desk_officer_confirmation_status', $searchApprovalStatus);
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
                    $nominalRollSubmission = new NominalRollSubmission();
                    $nominalRollSubmission->setSubmissionId($record['submission_id']);
                    $nominalRollSubmission->setSubmissionYear($record['submission_year']);
                    $nominalRollSubmission->setOrganizationId($record['organization_id']);
                    $nominalRollSubmission->setOrganizationEstablishmentCode($record['establishment_code']);
                    $nominalRollSubmission->setOrganizationMnemonic($record['establishment_mnemonic']);
                    $nominalRollSubmission->setOrganizationName($record['organization_name']);
                    $nominalRollSubmission->setUploadedFileName($record['uploaded_file_name']);

                    $uploadedFileName = $nominalRollSubmission->getUploadedFileName();
                    $nominalRollSubmission->setSimpleFileName($stringHelper->substringBeforeLast($uploadedFileName, "_")
                        . $stringHelper->substringFromLast($uploadedFileName, "."));

                    $nominalRollSubmission->setTotalRowsImported($record['total_rows_imported']);
                    $nominalRollSubmission->setTotalRowsImportedFormatted(number_format($nominalRollSubmission->getTotalRowsImported()));

                    $nominalRollSubmission->setSubmissionType($record['submission_type']);

                    $nominalRollSubmission->setValidationStatus($record['validation_status']);
                    $nominalRollSubmission->setFccDeskOfficerConfirmationStatus($record['fcc_desk_officer_confirmation_status']);
                    $nominalRollSubmission->setFccMisHeadApprovalStatus($record['fcc_mis_head_approval_status']);
                    $nominalRollSubmission->setProcessingStatus($record['processing_status']);

                    $nominalRollSubmission->setCreated($record['_created']);

                    $nominalRollSubmission->setDisplaySerialNo($displaySerialNo);

                    $this->updateNominalRollStatusTrackers($nominalRollSubmission);

                    $displaySerialNo++;

                    $nominalRoleUploads[] = $nominalRollSubmission;
                }
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $nominalRoleUploads;
    }

    /**
     * @param NominalRollSubmission $nominalRollSubmission
     * @return bool
     * @throws AppException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function addMainSubmission(NominalRollSubmission $nominalRollSubmission): bool
    {
        $outcome = false;
        $statement = null;

        try {

            $this->connection->beginTransaction();

            //DE-ACTIVE ANY PREVIOUS SUBMISSIONS FOR SAME YEAR
            //AND REMOVE THEIR ANALYSIS
            $query = "update federal_level_nominal_roll_submissions
                      set
                      is_active = :is_active
                      where 
                      organization_id=:organization_id
                      and submission_year=:submission_year
                      and submission_type=:submission_type";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':is_active', AppConstants::N);
            $statement->bindValue(':organization_id', $nominalRollSubmission->getOrganizationId());
            $statement->bindValue(':submission_year', $nominalRollSubmission->getSubmissionYear());
            $statement->bindValue(':submission_type', AppConstants::MAIN_SUBMISSION);
            $statement->execute();

            //DELETE PREV ANALYSIS
            $query = "DELETE 
                    FROM federal_level_nominal_roll_career_post_analysis 
                    WHERE 
                    organization_id=:organization_id 
                    AND submission_year=:submission_year";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':organization_id', $nominalRollSubmission->getOrganizationId());
            $statement->bindValue(':submission_year', $nominalRollSubmission->getSubmissionYear());
            $statement->execute();

            //CHECK FOR DUPLICATE SUBMISSION ID
            $query = "SELECT submission_id FROM federal_level_nominal_roll_submissions WHERE submission_id=:submission_id LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':submission_id', $nominalRollSubmission->getSubmissionId());
            $statement->execute();
            $existingSubmissionId = $statement->fetch();
            if ($existingSubmissionId) {
                throw new AppException(AppExceptionMessages::DUPLICATE_SUBMISSION_ID);
            }

            //now insert
            $query = "INSERT INTO federal_level_nominal_roll_submissions 
                (
                submission_id,submission_year,organization_id,uploaded_file_name,total_rows_imported 
                ,validation_status,submission_type,fcc_desk_officer_confirmation_status,fcc_mis_head_approval_status 
                ,processing_status,is_active,created,created_by,last_mod,last_mod_by
                ) 
                VALUES 
                (
                :submission_id,:submission_year,:organization_id,:uploaded_file_name,:total_rows_imported 
                ,:validation_status,:submission_type,:fcc_desk_officer_confirmation_status,:fcc_mis_head_approval_status 
                ,:processing_status,:is_active,:created,:created_by,:last_mod,:last_mod_by
                )";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':submission_id', $nominalRollSubmission->getSubmissionId());
            $statement->bindValue(':submission_year', $nominalRollSubmission->getSubmissionYear());
            $statement->bindValue(':organization_id', $nominalRollSubmission->getOrganizationId());
            $statement->bindValue(':uploaded_file_name', $nominalRollSubmission->getUploadedFileName());
            $statement->bindValue(':total_rows_imported', $nominalRollSubmission->getTotalRowsImported());
            $statement->bindValue(':validation_status', $nominalRollSubmission->getValidationStatus());
            $statement->bindValue(':submission_type', $nominalRollSubmission->getSubmissionType());
            $statement->bindValue(':fcc_desk_officer_confirmation_status', $nominalRollSubmission->getFccDeskOfficerConfirmationStatus());
            $statement->bindValue(':fcc_mis_head_approval_status', $nominalRollSubmission->getFccMisHeadApprovalStatus());
            $statement->bindValue(':processing_status', $nominalRollSubmission->getProcessingStatus());
            $statement->bindValue(':is_active', $nominalRollSubmission->getActiveStatus());
            $statement->bindValue(':created', $nominalRollSubmission->getLastModified());
            $statement->bindValue(':created_by', $nominalRollSubmission->getLastModifiedByUserId());
            $statement->bindValue(':last_mod', $nominalRollSubmission->getLastModified());
            $statement->bindValue(':last_mod_by', $nominalRollSubmission->getLastModifiedByUserId());
            $statement->execute();

            $this->connection->commit();
            $outcome = true;

        } catch (Throwable $e) {
            if($this->connection->isTransactionActive()){
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

    /**
     * @param NominalRollSubmission $nominalRoleSubmission
     * @return bool
     * @throws AppException
     */
    public function checkIfSubmissionCanBeMade(NominalRollSubmission $nominalRoleSubmission): bool
    {
        $outcome = false;
        $statement = null;

        try {

            $submissionYear = $nominalRoleSubmission->getSubmissionYear();

            //MAIN SUBMISSION CHECK
            if($nominalRoleSubmission->getSubmissionType() == AppConstants::MAIN_SUBMISSION){

                //CHECK IF THERE IS A FAILED VALIDATION IN THAT YEAR
                $query = "SELECT submission_id 
                      FROM federal_level_nominal_roll_submissions 
                      WHERE organization_id=:organization_id
                      and submission_year=:submission_year 
                      and (validation_status=:failed_validation_status or validation_status=:fatal_validation_status)";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':organization_id', $nominalRoleSubmission->getOrganizationId());
                $statement->bindValue(':submission_year', $nominalRoleSubmission->getSubmissionYear());
                $statement->bindValue(':failed_validation_status', AppConstants::FAILED);
                $statement->bindValue(':fatal_validation_status', AppConstants::FATAL_ERROR);
                $statement->execute();
                $failedSubmissionRecords = $statement->fetchAll();

                if ($failedSubmissionRecords) {
                    throw new AppException(AppExceptionMessages::PREV_FAILED_SUBMISSION);
                }

                //GET THE BASELINE YEAR
                $query = "SELECT baseline_year FROM mda_baseline_year WHERE organization_id=:organization_id";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':organization_id', $nominalRoleSubmission->getOrganizationId());
                $statement->execute();
                $baselineYear = $statement->fetchColumn(0);
                if (!$baselineYear) {
                    $baselineYear = 2008;
                }
                $isBaselineYear = ($baselineYear == $submissionYear);

                //GET THE EXPECTED YEAR TO BE UPLOADED
                $query = "SELECT max(submission_year) as latest_submission_year
                      FROM federal_level_nominal_roll_submissions 
                      WHERE organization_id=:organization_id";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':organization_id', $nominalRoleSubmission->getOrganizationId());
                $statement->execute();
                $mostRecentYearOfSubmission = $statement->fetchColumn(0);

                $expectedSubmissionYear = null;
                if($mostRecentYearOfSubmission){
                    $expectedSubmissionYear = $mostRecentYearOfSubmission + 1;
                }else{
                    $expectedSubmissionYear = $baselineYear;
                }
                $isExpectedSubmissionYear = ($expectedSubmissionYear == $submissionYear);

                //CHECK WHETHER THIS IS THE FIRST SUBMISSION FOR THIS YEAR
                $query = "SELECT count(submission_id) 
                      FROM federal_level_nominal_roll_submissions 
                      WHERE organization_id=:organization_id 
                      AND submission_year=:submission_year";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':organization_id', $nominalRoleSubmission->getOrganizationId());
                $statement->bindValue(':submission_year', $submissionYear);
                $statement->execute();
                $totalSubmissionsFoundForYear = $statement->fetchColumn(0);

                $isFirstSubmissionForYear = ($totalSubmissionsFoundForYear == 0);


                //IF YEAR IS NOT EXPECTED YEAR, CHECK WHETHER OPEN PERMISSION EXISTS
                $hasOpenPermissionForYear = false;
                if($expectedSubmissionYear != $submissionYear){
                    $query = "SELECT count(*) 
                      FROM submission_upload_permission_requests 
                      WHERE 
                      organization_id=:organization_id 
                      AND submission_year=:submission_year
                      AND expired=:expired";
                    $statement = $this->connection->prepare($query);
                    $statement->bindValue(':organization_id', $nominalRoleSubmission->getOrganizationId());
                    $statement->bindValue(':submission_year', $submissionYear);
                    $statement->bindValue(':expired', AppConstants::N);
                    $statement->execute();
                    $totalOpenPermissionForYear = $statement->fetchColumn(0);
                    $hasOpenPermissionForYear = ($totalOpenPermissionForYear > 0);
                }

                $isPreviousYear = ($submissionYear >= $baselineYear) && ($submissionYear < $expectedSubmissionYear);

                //IF YEAR IS NOT EXPECTED YEAR, CHECK IF PREV YEAR WAS SKIPPED
                $skippedPreviousYearSubmission = false;
                $previousYear = $baselineYear;
                if(!$isBaselineYear && ($expectedSubmissionYear != $submissionYear)){
                    if ($baselineYear < $nominalRoleSubmission->getSubmissionYear()) {
                        $previousYear = $nominalRoleSubmission->getSubmissionYear() - 1;
                    }

                    $query = "SELECT count(submission_id)
                      FROM federal_level_nominal_roll_submissions 
                      WHERE organization_id=:organization_id AND submission_year=:submission_year LIMIT 1";
                    $statement = $this->connection->prepare($query);
                    $statement->bindValue(':organization_id', $nominalRoleSubmission->getOrganizationId());
                    $statement->bindValue(':submission_year', $previousYear);
                    $statement->execute();
                    $totalPreviousYearSubmissions = $statement->fetchColumn(0);

                    $skippedPreviousYearSubmission = ($totalPreviousYearSubmissions == 0);
                }


                //START DOING THE CHECKS
                if ($baselineYear > $submissionYear) {
                    throw new AppException(AppExceptionMessages::BELOW_BASELINE_YEAR);
                }

                if(!$isExpectedSubmissionYear && !$hasOpenPermissionForYear){
                    throw new AppException(AppExceptionMessages::NOT_PERMITTED_YEAR);
                }

                if(!$isFirstSubmissionForYear && !$hasOpenPermissionForYear){
                    throw new AppException(AppExceptionMessages::DUPLICATE_MAIN_SUBMISSION);
                }

                if($isExpectedSubmissionYear && !$isBaselineYear && $skippedPreviousYearSubmission && !$hasOpenPermissionForYear){
                    throw new AppException(sprintf(AppExceptionMessages::SKIPPED_PREVIOUS_YEAR,$submissionYear,$previousYear,$submissionYear));
                }

                if(!$isPreviousYear && !$hasOpenPermissionForYear){
                    throw new AppException(AppExceptionMessages::DUPLICATE_MAIN_SUBMISSION_2);
                }

            }
            //QUARTERLY RETURN
            else if($nominalRoleSubmission->getSubmissionType() == AppConstants::QUARTERLY_RETURN){ //QUARTERLY RETURN

                $query = "SELECT max(submission_year) as latest_submission_year
                      FROM federal_level_nominal_roll_submissions 
                      WHERE organization_id=:organization_id";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':organization_id', $nominalRoleSubmission->getOrganizationId());
                $statement->execute();
                $mostRecentYearOfSubmission = $statement->fetchColumn(0);

                if(!$mostRecentYearOfSubmission){
                    throw new AppException(AppExceptionMessages::INVALID_QUARTERLY_RETURN_YEAR);
                }else if($mostRecentYearOfSubmission != $submissionYear){
                    throw new AppException(AppExceptionMessages::INVALID_QUARTERLY_RETURN_YEAR);
                }

            }else{
                throw new AppException(AppExceptionMessages::OPERATION_NOT_ALLOWED);
            }

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

    /**
     * @param $organizationId
     * @param null $submissionYear
     * @return array|null
     * @throws AppException
     */
    public function getMissingSubmissionYears($organizationId, $submissionYear = null): ?array
    {
        $missingYears = null;
        $statement = null;

        try {
            //get the years from baseline up to immediate previous year
            $query = "SELECT baseline_year from mda_baseline_year 
                WHERE organization_id=:organization_id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':organization_id', $organizationId);
            $statement->execute();

            $mdaBaselineYear = $statement->fetchColumn(0);

            if(!$mdaBaselineYear){
                throw new AppException(AppExceptionMessages::OPERATION_NOT_ALLOWED);
            }

            $expectedYearsForSubmission = array();

            if(!$submissionYear){
                $submissionYear = date('Y') - 1;
            }else{
                $submissionYear = $submissionYear - 1;
            }

            for($i = $mdaBaselineYear; $i <= $submissionYear; $i++){
                $expectedYearsForSubmission[] = $i;
            }

            //fetch all the main submission years
            $query = "SELECT DISTINCT submission_year 
                from federal_level_nominal_roll_submissions 
                WHERE organization_id=:organization_id 
                and submission_type=:submission_type
                and is_active = :is_active
                order by submission_year asc";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':organization_id', $organizationId);
            $statement->bindValue(':submission_type', AppConstants::MAIN_SUBMISSION);
            $statement->bindValue(':is_active', AppConstants::Y);
            $statement->execute();

            $existingSubmissionYears = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

            $missingYears = array_diff($expectedYearsForSubmission, $existingSubmissionYears);

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $missingYears;
    }

    /**
     * @param $organizationId
     * @param $submissionYear
     * @return bool|null
     * @throws AppException
     */
    public function checkIfReturnCanBeUploaded($organizationId, $submissionYear): ?bool
    {
        $outcome = false;
        $statement = null;

        try {

            //check if a main submission exists
            $query = "SELECT submission_year 
                from federal_level_nominal_roll_submissions 
                WHERE organization_id=:organization_id 
                and submission_year=:submission_year
                and submission_type=:submission_type LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':organization_id', $organizationId);
            $statement->bindValue(':submission_year', $submissionYear);
            $statement->bindValue(':submission_type', AppConstants::MAIN_SUBMISSION);
            $statement->execute();

            $mainSubmissionYear = $statement->fetchColumn(0);

            if($mainSubmissionYear){
                $outcome = true;
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $outcome;
    }

    /**
     * @param $submissionId
     * @return NominalRollSubmission|null
     * @throws AppException
     */
    public function getSubmission($submissionId): ?NominalRollSubmission
    {
        $nominalRollSubmission = null;
        $statement = null;

        try {
            $query = "SELECT d.submission_id,d.submission_year,d.organization_id,d.uploaded_file_name,d.total_rows_imported 
                ,format(d.total_rows_imported,2) as _total_rows_imported
                ,d.validation_status,d.date_last_validated,d.date_validation_passed,d.submission_type,d.fcc_desk_officer_confirmation_status 
                ,d.date_fcc_desk_officer_confirmed,d.fcc_mis_head_approval_status,d.date_fcc_mis_head_approved 
                ,d.processing_status,d.date_processing_started,d.date_processing_completed 
                ,d.created,d.created_by,d.last_mod,d.last_mod_by
                ,date_format(d.date_validation_passed,'%e-%b-%Y %h:%i %p') as _date_validation_passed 
                ,date_format(d.date_fcc_desk_officer_confirmed,'%e-%b-%Y %h:%i %p') as _date_fcc_desk_officer_confirmed 
                ,date_format(d.date_fcc_mis_head_approved,'%e-%b-%Y %h:%i %p') as _date_fcc_mis_head_approved 
                ,date_format(d.created,'%e-%b-%Y %h:%i %p') as _created 
                ,o.establishment_code,o.establishment_mnemonic,o.organization_name,o.fcc_desk_officer_id 
                ,o.state_owned_establishment_state_id, s.state_name as state_owned_establishment_state_name 
                ,concat_ws(' ',u.first_name,u.last_name) as _fcc_desk_officer_name,u.email_address as _fcc_desk_officer_email 
                ,concat(u.primary_phone,IFNULL(concat(',',u.secondary_phone),'')) as _fcc_desk_officer_phone 
                ,concat(IFNULL(concat(m.first_name,' ',m.last_name),'')) as _mda_desk_officer_name 
                ,m.email_address as _mda_desk_officer_email
                ,concat(IFNULL(m.primary_phone,''),IFNULL(concat(',',m.secondary_phone),'')) as _mda_desk_officer_phone 
                FROM federal_level_nominal_roll_submissions d 
                LEFT JOIN organization o on d.organization_id = o.id 
                LEFT JOIN states s on o.state_owned_establishment_state_id = s.id 
                LEFT JOIN user_profile u on (o.fcc_committee_id = u.fcc_committee_id 
                                              and u.primary_role=:fcc_desk_officer_role)
                LEFT JOIN user_profile m on d.last_mod_by = m.id 
                WHERE d.submission_id=:submission_id ";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':fcc_desk_officer_role', AppConstants::ROLE_FCC_DESK_OFFICER_FEDERAL);
            $statement->bindValue(':submission_id', $submissionId);
            $statement->execute();

            $record = $statement->fetch(PDO::FETCH_ASSOC);
            if ($record) {
                $nominalRollSubmission = new NominalRollSubmission();
                $nominalRollSubmission->setSubmissionId($record['submission_id']);
                $nominalRollSubmission->setSubmissionYear($record['submission_year']);
                $nominalRollSubmission->setOrganizationId($record['organization_id']);
                $nominalRollSubmission->setOrganizationEstablishmentCode($record['establishment_code']);
                $nominalRollSubmission->setOrganizationMnemonic($record['establishment_mnemonic']);
                $nominalRollSubmission->setOrganizationName($record['organization_name']);
                $nominalRollSubmission->setUploadedFileName($record['uploaded_file_name']);

                $nominalRollSubmission->setStateOwnedEstablishmentStateId($record['state_owned_establishment_state_id']);
                $nominalRollSubmission->setStateOwnedEstablishmentStateName($record['state_owned_establishment_state_name']);

                if (!trim($nominalRollSubmission->getStateOwnedEstablishmentStateId())) {
                    $nominalRollSubmission->setIsFederalLevelSubmission(true);
                } else {
                    $nominalRollSubmission->setIsStateLevelSubmission(true);
                }

                $uploadedFileName = $nominalRollSubmission->getUploadedFileName();

                $stringHelper = new StringHelper();
                $nominalRollSubmission->setSimpleFileName($stringHelper->substringBeforeLast($uploadedFileName, "_")
                    . $stringHelper->substringFromLast($uploadedFileName, "."));

                $nominalRollSubmission->setTotalRowsImported($record['total_rows_imported']);
                $nominalRollSubmission->setTotalRowsImportedFormatted(number_format($nominalRollSubmission->getTotalRowsImported()));

                $nominalRollSubmission->setValidationStatus($record['validation_status']);
                $nominalRollSubmission->setDateValidationPassed($record['date_validation_passed']);
                $nominalRollSubmission->setDateValidationPassedFormatted($record['_date_validation_passed']);

                $nominalRollSubmission->setSubmissionType($record['submission_type']);

                $nominalRollSubmission->setFccDeskOfficerUserId($record['fcc_desk_officer_id']);
                $nominalRollSubmission->setFccDeskOfficerName($record['_fcc_desk_officer_name']);
                $nominalRollSubmission->setFccDeskOfficerEmail($record['_fcc_desk_officer_email']);
                $nominalRollSubmission->setFccDeskOfficerPhone($record['_fcc_desk_officer_phone']);

                $nominalRollSubmission->setFccDeskOfficerConfirmationStatus($record['fcc_desk_officer_confirmation_status']);
                $nominalRollSubmission->setDateFccDeskOfficerConfirmed($record['date_fcc_desk_officer_confirmed']);
                $nominalRollSubmission->setDateFccDeskOfficerConfirmedFormatted($record['_date_fcc_desk_officer_confirmed']);

                $nominalRollSubmission->setFccMisHeadApprovalStatus($record['fcc_mis_head_approval_status']);
                $nominalRollSubmission->setDateFccMisHeadApproved($record['date_fcc_mis_head_approved']);
                $nominalRollSubmission->setDateFccMisHeadApprovedFormatted($record['_date_fcc_mis_head_approved']);

                $nominalRollSubmission->setProcessingStatus($record['processing_status']);
                $nominalRollSubmission->setCreated($record['_created']);
                $nominalRollSubmission->setLastModifiedByUserId($record['last_mod_by']);

                $nominalRollSubmission->setMdaAdminUserId($nominalRollSubmission->getLastModifiedByUserId());
                $nominalRollSubmission->setMdaAdminName($record['_mda_desk_officer_name']);
                $nominalRollSubmission->setMdaAdminEmail($record['_mda_desk_officer_email']);
                $nominalRollSubmission->setMdaAdminPhone($record['_mda_desk_officer_phone']);
                
                $this->updateNominalRollStatusTrackers($nominalRollSubmission);

            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $nominalRollSubmission;
    }

    /**
     * @param NominalRollSubmission $nominalRollSubmission
     */
    private function updateNominalRollStatusTrackers(NominalRollSubmission $nominalRollSubmission){
        $nominalRollSubmission->setValidationPending(($nominalRollSubmission->getValidationStatus()==AppConstants::PENDING) ? true : false);
        $nominalRollSubmission->setFailedValidation(($nominalRollSubmission->getValidationStatus()==AppConstants::FAILED) ? true : false);
        $nominalRollSubmission->setFailedValidationWithFatalError(($nominalRollSubmission->getValidationStatus()==AppConstants::FATAL_ERROR) ? true : false);
        $nominalRollSubmission->setPassedValidation(($nominalRollSubmission->getValidationStatus()==AppConstants::PASSED) ? true : false);
        $nominalRollSubmission->setFccDeskOfficerConfirmed(($nominalRollSubmission->getFccDeskOfficerConfirmationStatus()==AppConstants::CONFIRMED) ? true : false);
        $nominalRollSubmission->setMisHeadApproved(($nominalRollSubmission->getFccMisHeadApprovalStatus()==AppConstants::APPROVED) ? true : false);
        $nominalRollSubmission->setProcessed(($nominalRollSubmission->getProcessingStatus()==AppConstants::COMPLETED) ? true : false);

        $nominalRollSubmission->setMainSubmission(($nominalRollSubmission->getSubmissionType()==AppConstants::MAIN_SUBMISSION) ? true : false);
        $nominalRollSubmission->setQuarterlyReturn(($nominalRollSubmission->getSubmissionType()==AppConstants::QUARTERLY_RETURN) ? true : false);
    }

    /**
     * @param $submissionId
     * @return bool|null|string
     * @throws AppException
     */
    public function getSubmissionValidationStatus($submissionId) //return string
    {
        $validationStatus = null;
        $statement = null;

        try {

            $query = "SELECT validation_status FROM federal_level_nominal_roll_submissions WHERE submission_id=:submission_id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':submission_id', $submissionId);
            $statement->execute();

            $validationStatus = $statement->fetchColumn(0);

            if($validationStatus == AppConstants::FATAL_ERROR){
                try{
                    $this->connection->beginTransaction();
                    //BACKUP SUBMISSION WITH FATAL ERROR
                    $query = "insert into federal_level_nominal_roll_submissions_with_fatal_error
                              (
                                  select s.* 
                                  from federal_level_nominal_roll_submissions s
                                  where s.submission_id=:submission_id
                              )";
                    $statement = $this->connection->prepare($query);
                    $statement->bindValue(':submission_id', $submissionId);
                    $statement->execute();

                    //NOW DELETE THE SUBMISSION WITH FATAL ERROR
                    $query = "delete from federal_level_nominal_roll_submissions 
                              where submission_id=:submission_id";
                    $statement = $this->connection->prepare($query);
                    $statement->bindValue(':submission_id', $submissionId);
                    $statement->execute();

                    $this->connection->commit();

                }catch(AppException $e){
                    if($this->connection->isTransactionActive()){
                        $this->connection->rollBack();
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
        return $validationStatus;
    }

    /**
     * @return bool
     * @throws AppException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function clearFatalErrorSubmissions()
    {
        $outcome = false;
        $statement = null;

        try{
            $this->connection->beginTransaction();

            //BACKUP SUBMISSIONS WITH FATAL ERROR
            $query = "insert into federal_level_nominal_roll_submissions_with_fatal_error
                              (
                                  select s.* 
                                  from federal_level_nominal_roll_submissions s
                                  where (s.validation_status=:fatal_validation_status)
                                  or (s.total_rows_imported=0 and s.validation_status=:pending_validation_status)
                              )";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':fatal_validation_status', AppConstants::FATAL_ERROR);
            $statement->bindValue(':pending_validation_status', AppConstants::PENDING);
            $statement->execute();

            //NOW DELETE THE SUBMISSIONS WITH FATAL ERROR
            $query = "delete from federal_level_nominal_roll_submissions
                      where (validation_status=:fatal_validation_status)
                      or (total_rows_imported=0 and validation_status=:pending_validation_status)";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':fatal_validation_status', AppConstants::FATAL_ERROR);
            $statement->bindValue(':pending_validation_status', AppConstants::PENDING);
            $statement->execute();

            $this->connection->commit();

            $outcome = true;

        }catch(AppException | DBALException $e){
            if($this->connection->isTransactionActive()){
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


    /**
     * @param $submissionId
     * @param Paginator $paginator
     * @param $pageDirection
     * @return array
     * @throws AppException
     */
    public function searchFailedValidationDetail($submissionId, Paginator $paginator, $pageDirection): array
    {
        $failedValidations = array();
        $statement = null;

        try {

            if (!$submissionId) {
                $submissionId = '-1'; //match nothing
            }

            //fetch total matching rows
            $countQuery = "SELECT COUNT(d.id) AS totalRows 
                FROM federal_level_nominal_roll_failed_validations_detail d 
                WHERE d.submission_id=:submission_id ";
            $statement = $this->connection->prepare($countQuery);
            $statement->bindValue(':submission_id', $submissionId);

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
            $query = "SELECT d.failed_employee_status,d.failed_employee_number,d.failed_employee_name,d.failed_nationality_code,d.failed_state_of_origin_code 
                ,d.failed_date_of_birth,d.failed_date_of_employment,d.failed_date_of_present_appointment
                ,d.failed_grade_level,d.failed_designation,d.failed_state_of_deployment_code 
                ,d.failed_gender,d.failed_marital_status,d.failed_lga_code,d.failed_region_code
                ,d.failed_physically_challenged_status,d.failed_quarterly_return_employment_status 
                ,s.id as _upload_record_id,s.submission_id,s.submission_year,s.serial_number,s.employee_status,s.employee_number 
                ,s.employee_name,s.nationality_code,s.state_of_origin_code,s.date_of_birth,s.date_of_employment,s.date_of_present_appointment 
                ,s.grade_level,s.designation,s.state_of_deployment_code,s.gender,s.marital_status,s.lga_code,s.region_code
                 ,s.physically_challenged_status,s.quarterly_return_employment_status
                FROM federal_level_nominal_roll_failed_validations_detail d 
                JOIN federal_level_nominal_roll_submission_staging s ON (d.id = s.id AND d.submission_id = s.submission_id)
                WHERE d.submission_id=:submission_id 
                ORDER BY d.id LIMIT $limitStartRow ,$this->rows_per_page ";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':submission_id', $submissionId);

            $statement->execute();

            $records = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($records) {
                foreach ($records as $record) {
                    $failedRecord = new NominalRollValidation();
                    $failedRecord->setId($record['_upload_record_id']);
                    $failedRecord->setSubmissionId($record['submission_id']);
                    $failedRecord->setSubmissionYear($record['submission_year']);

                    $failedRecord->setSerialNo($record['serial_number']);
                    $failedRecord->setEmployeeStatus($record['employee_status']);
                    $failedRecord->setEmployeeNumber($record['employee_number']);
                    $failedRecord->setName($record['employee_name']);
                    $failedRecord->setNationality($record['nationality_code']);
                    $failedRecord->setStateOfOrigin($record['state_of_origin_code']);
                    $failedRecord->setDateOfBirth($record['date_of_birth']);
                    $failedRecord->setDateOfEmployment($record['date_of_employment']);
                    $failedRecord->setDateOfPresentAppointment($record['date_of_present_appointment']);
                    $failedRecord->setGradeLevel($record['grade_level']);
                    $failedRecord->setDesignation($record['designation']);
                    $failedRecord->setStateOfLocation($record['state_of_deployment_code']);
                    $failedRecord->setGender($record['gender']);
                    $failedRecord->setMaritalStatus($record['marital_status']);
                    $failedRecord->setLga($record['lga_code']);
                    $failedRecord->setGeoPoliticalZone($record['region_code']);
                    $failedRecord->setPhysicallyChallengedStatus($record['physically_challenged_status']);
                    $failedRecord->setQuarterlyReturnEmploymentStatus($record['quarterly_return_employment_status']);

                    $failedRecord->setFailedEmployeeStatus($record['failed_employee_status']);
                    $failedRecord->setFailedEmployeeNumber($record['failed_employee_number']);
                    $failedRecord->setFailedName($record['failed_employee_name']);
                    $failedRecord->setFailedNationality($record['failed_nationality_code']);
                    $failedRecord->setFailedStateOfOrigin($record['failed_state_of_origin_code']);
                    $failedRecord->setFailedDateOfBirth($record['failed_date_of_birth']);
                    $failedRecord->setFailedDateOfEmployment($record['failed_date_of_employment']);
                    $failedRecord->setFailedDateOfPresentAppointment($record['failed_date_of_present_appointment']);
                    $failedRecord->setFailedGradeLevel($record['failed_grade_level']);
                    $failedRecord->setFailedDesignation($record['failed_designation']);
                    $failedRecord->setFailedStateOfLocation($record['failed_state_of_deployment_code']);
                    $failedRecord->setFailedGender($record['failed_gender']);
                    $failedRecord->setFailedMaritalStatus($record['failed_marital_status']);
                    $failedRecord->setFailedLga($record['failed_lga_code']);
                    $failedRecord->setFailedGeopoliticalZone($record['failed_region_code']);
                    $failedRecord->setFailedPhysicallyChallenged($record['failed_physically_challenged_status']);
                    $failedRecord->setFailedQuarterlyReturnStatus($record['failed_quarterly_return_employment_status']);

                    $failedValidations[] = $failedRecord;
                }
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $failedValidations;
    }

    /**
     * @param NominalRollSubmission $nominalRoleSubmission
     * @param NominalRoleSearchCriteria $searchCriteria
     * @param Paginator $paginator
     * @param $pageDirection
     * @return array
     * @throws AppException
     */
    public function searchPassedOrConfirmedNominalRollSubmissionDetail(NominalRollSubmission $nominalRoleSubmission, NominalRoleSearchCriteria $searchCriteria, Paginator $paginator, $pageDirection): array
    {
        $passedValidations = array();
        $statement = null;

        try {

            $searchSubmissionId = $searchCriteria->getSubmissionId();

            if (!$searchSubmissionId) {
                $searchSubmissionId = '-1';
            }

            $searchEmployeeNumber = $searchCriteria->getEmployeeNumber();
            $searchName = $searchCriteria->getName();

            $searchNationality = $searchCriteria->getNationality();
            $searchStateOfOrigin = $searchCriteria->getStateOfOrigin();
            //$searchDateOfBirth = $searchCriteria->getDateOfBirth();
            //$searchDateOfEmployment = $searchCriteria->getDateOfEmployment();

            $searchGradeLevel = $searchCriteria->getGradeLevel();
            //$searchDesignation = $searchCriteria->getDesignation();
            //$searchStateOfLocation = $searchCriteria->getStateOfLocation();
            //$searchGender = $searchCriteria->getGender();

            //$searchMaritalStatus = $searchCriteria->getMaritalStatus();
            //$searchLga = $searchCriteria->getLga();
            $searchGeoPoliticalZone = $searchCriteria->getGeoPoliticalZone();

            $where = array("d.submission_id = '$searchSubmissionId'");

            /*if ($searchSubmissionId) {
                $where[] = "d.submission_id = :submission_id";
            }*/
            if ($searchEmployeeNumber) {
                $where[] = "d.employee_number = :employee_number";
            }
            if ($searchName) {
                $where[] = "d.employee_name LIKE :employee_name";
            }
            if ($searchNationality) {
                $where[] = "d.nationality_code = :nationality_code";
            }
            if ($searchStateOfOrigin) {
                $where[] = "d.state_of_origin_code = :state_of_origin_code";
            }
            if ($searchGradeLevel) {
                $where[] = "d.grade_level = :grade_level";
            }
            if ($searchGeoPoliticalZone) {
                $where[] = "d.region_code = :region_code";
            }

            if ($where) {
                $where = implode(" AND ", $where);
            } else {
                $where = '';
            }

            $tableToSearch = '';
            if($nominalRoleSubmission->isFccDeskOfficerConfirmed()){
                $tableToSearch = 'confirmed_federal_level_nominal_roll_submission';
            }else{
                $tableToSearch = 'federal_level_nominal_roll_submission_staging';
            }

            //fetch total matching rows
            $countQuery = "SELECT COUNT(d.id) AS totalRows "
                . "FROM $tableToSearch d "
                . "WHERE $where ";
            $statement = $this->connection->prepare($countQuery);

            /*if ($searchSubmissionId) {
                $statement->bindValue(':submission_id', $searchSubmissionId);
            }*/
            if ($searchEmployeeNumber) {
                $statement->bindValue(':employee_number', $searchEmployeeNumber);
            }
            if ($searchName) {
                $statement->bindValue(':employee_name', "%" . $searchName . "%");
            }
            if ($searchNationality) {
                $statement->bindValue(':nationality_code', $searchNationality);
            }
            if ($searchStateOfOrigin) {
                $statement->bindValue(':state_of_origin_code', $searchStateOfOrigin);
            }
            if ($searchGradeLevel) {
                $statement->bindValue(':grade_level', $searchGradeLevel);
            }
            if ($searchGeoPoliticalZone) {
                $statement->bindValue(':region_code', $searchGeoPoliticalZone);
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
            $query = "SELECT d.id,d.submission_id,d.submission_year,d.serial_number,d.employee_status,d.employee_number "
                . ",d.employee_name,d.nationality_code,d.state_of_origin_code,d.date_of_birth,d.date_of_employment,d.date_of_present_appointment "
                . ",d.grade_level,d.designation,d.state_of_deployment_code,d.gender,d.marital_status,d.lga_code,d.region_code "
                . ",d.physically_challenged_status,d.quarterly_return_employment_status "
                . "FROM $tableToSearch d "
                . "WHERE $where "
                . "ORDER BY d.id LIMIT $limitStartRow ,$this->rows_per_page ";

            $statement = $this->connection->prepare($query);

            /*if ($searchSubmissionId) {
                $statement->bindValue(':submission_id', $searchSubmissionId);
            }*/
            if ($searchEmployeeNumber) {
                $statement->bindValue(':employee_number', $searchEmployeeNumber);
            }
            if ($searchName) {
                $statement->bindValue(':employee_name', "%" . $searchName . "%");
            }
            if ($searchNationality) {
                $statement->bindValue(':nationality_code', $searchNationality);
            }
            if ($searchStateOfOrigin) {
                $statement->bindValue(':state_of_origin_code', $searchStateOfOrigin);
            }
            if ($searchGradeLevel) {
                $statement->bindValue(':grade_level', $searchGradeLevel);
            }
            if ($searchGeoPoliticalZone) {
                $statement->bindValue(':region_code', $searchGeoPoliticalZone);
            }

            $statement->execute();

            $records = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($records) {
                foreach ($records as $record) {
                    $passedRecord = new NominalRollValidation();
                    $passedRecord->setId($record['id']);
                    $passedRecord->setSubmissionId($record['submission_id']);
                    $passedRecord->setSubmissionYear($record['submission_year']);

                    $passedRecord->setSerialNo($record['serial_number']);
                    $passedRecord->setEmployeeStatus($record['employee_status']);
                    $passedRecord->setEmployeeNumber($record['employee_number']);
                    $passedRecord->setName($record['employee_name']);
                    $passedRecord->setNationality($record['nationality_code']);
                    $passedRecord->setStateOfOrigin($record['state_of_origin_code']);
                    $passedRecord->setDateOfBirth($record['date_of_birth']);
                    $passedRecord->setDateOfEmployment($record['date_of_employment']);
                    $passedRecord->setDateOfPresentAppointment($record['date_of_present_appointment']);
                    $passedRecord->setGradeLevel($record['grade_level']);
                    $passedRecord->setDesignation($record['designation']);
                    $passedRecord->setStateOfLocation($record['state_of_deployment_code']);
                    $passedRecord->setGender($record['gender']);
                    $passedRecord->setMaritalStatus($record['marital_status']);
                    $passedRecord->setLga($record['lga_code']);
                    $passedRecord->setGeoPoliticalZone($record['region_code']);
                    $passedRecord->setPhysicallyChallengedStatus($record['physically_challenged_status']);
                    $passedRecord->setQuarterlyReturnEmploymentStatus($record['quarterly_return_employment_status']);

                    $passedValidations[] = $passedRecord;
                }
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $passedValidations;
    }

    /**
     * @param NominalRoleSearchCriteria $searchCriteria
     * @param Paginator $paginator
     * @param $pageDirection
     * @return array
     * @throws AppException
     */
    public function searchPassedValidationDetail(NominalRoleSearchCriteria $searchCriteria, Paginator $paginator, $pageDirection): array
    {
        $passedValidations = array();
        $statement = null;

        try {

            $searchSubmissionId = $searchCriteria->getSubmissionId();

            if (!$searchSubmissionId) {
                $searchSubmissionId = '-1';
            }

            $searchEmployeeNumber = $searchCriteria->getEmployeeNumber();
            $searchName = $searchCriteria->getName();

            $searchNationality = $searchCriteria->getNationality();
            $searchStateOfOrigin = $searchCriteria->getStateOfOrigin();
            //$searchDateOfBirth = $searchCriteria->getDateOfBirth();
            //$searchDateOfEmployment = $searchCriteria->getDateOfEmployment();

            $searchGradeLevel = $searchCriteria->getGradeLevel();
            //$searchDesignation = $searchCriteria->getDesignation();
            //$searchStateOfLocation = $searchCriteria->getStateOfLocation();
            //$searchGender = $searchCriteria->getGender();

            //$searchMaritalStatus = $searchCriteria->getMaritalStatus();
            //$searchLga = $searchCriteria->getLga();
            $searchGeoPoliticalZone = $searchCriteria->getGeoPoliticalZone();

            $where = array("d.submission_id = '$searchSubmissionId'");

            /*if ($searchSubmissionId) {
                $where[] = "d.submission_id = :submission_id";
            }*/
            if ($searchEmployeeNumber) {
                $where[] = "d.employee_number = :employee_number";
            }
            if ($searchName) {
                $where[] = "d.employee_name LIKE :employee_name";
            }
            if ($searchNationality) {
                $where[] = "d.nationality_code = :nationality_code";
            }
            if ($searchStateOfOrigin) {
                $where[] = "d.state_of_origin_code = :state_of_origin_code";
            }
            if ($searchGradeLevel) {
                $where[] = "d.grade_level = :grade_level";
            }
            if ($searchGeoPoliticalZone) {
                $where[] = "d.region_code = :region_code";
            }

            if ($where) {
                $where = implode(" AND ", $where);
            } else {
                $where = '';
            }

            //fetch total matching rows
            $countQuery = "SELECT COUNT(d.id) AS totalRows 
                FROM federal_level_nominal_roll_submission_staging d 
                WHERE $where ";
            $statement = $this->connection->prepare($countQuery);

            /*if ($searchSubmissionId) {
                $statement->bindValue(':submission_id', $searchSubmissionId);
            }*/
            if ($searchEmployeeNumber) {
                $statement->bindValue(':employee_number', $searchEmployeeNumber);
            }
            if ($searchName) {
                $statement->bindValue(':employee_name', "%" . $searchName . "%");
            }
            if ($searchNationality) {
                $statement->bindValue(':nationality_code', $searchNationality);
            }
            if ($searchStateOfOrigin) {
                $statement->bindValue(':state_of_origin_code', $searchStateOfOrigin);
            }
            if ($searchGradeLevel) {
                $statement->bindValue(':grade_level', $searchGradeLevel);
            }
            if ($searchGeoPoliticalZone) {
                $statement->bindValue(':region_code', $searchGeoPoliticalZone);
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
            $query = "SELECT d.id,d.submission_id,d.submission_year,d.serial_number,d.employee_status,d.employee_number 
                ,d.employee_name,d.nationality_code,d.state_of_origin_code,d.date_of_birth,d.date_of_employment,d.date_of_present_appointment 
                ,d.grade_level,d.designation,d.state_of_deployment_code,d.gender,d.marital_status,d.lga_code,d.region_code 
                ,d.physically_challenged_status,d.quarterly_return_employment_status
                FROM federal_level_nominal_roll_submission_staging d 
                WHERE $where 
                ORDER BY d.id LIMIT $limitStartRow ,$this->rows_per_page ";

            $statement = $this->connection->prepare($query);

            /*if ($searchSubmissionId) {
                $statement->bindValue(':submission_id', $searchSubmissionId);
            }*/
            if ($searchEmployeeNumber) {
                $statement->bindValue(':employee_number', $searchEmployeeNumber);
            }
            if ($searchName) {
                $statement->bindValue(':employee_name', "%" . $searchName . "%");
            }
            if ($searchNationality) {
                $statement->bindValue(':nationality_code', $searchNationality);
            }
            if ($searchStateOfOrigin) {
                $statement->bindValue(':state_of_origin_code', $searchStateOfOrigin);
            }
            if ($searchGradeLevel) {
                $statement->bindValue(':grade_level', $searchGradeLevel);
            }
            if ($searchGeoPoliticalZone) {
                $statement->bindValue(':region_code', $searchGeoPoliticalZone);
            }

            $statement->execute();

            $records = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($records) {
                foreach ($records as $record) {
                    $passedRecord = new NominalRollValidation();
                    $passedRecord->setId($record['id']);
                    $passedRecord->setSubmissionId($record['submission_id']);
                    $passedRecord->setSubmissionYear($record['submission_year']);

                    $passedRecord->setSerialNo($record['serial_number']);
                    $passedRecord->setEmployeeStatus($record['employee_status']);
                    $passedRecord->setEmployeeNumber($record['employee_number']);
                    $passedRecord->setName($record['employee_name']);
                    $passedRecord->setNationality($record['nationality_code']);
                    $passedRecord->setStateOfOrigin($record['state_of_origin_code']);
                    $passedRecord->setDateOfBirth($record['date_of_birth']);
                    $passedRecord->setDateOfEmployment($record['date_of_employment']);
                    $passedRecord->setDateOfPresentAppointment($record['date_of_present_appointment']);
                    $passedRecord->setGradeLevel($record['grade_level']);
                    $passedRecord->setDesignation($record['designation']);
                    $passedRecord->setStateOfLocation($record['state_of_deployment_code']);
                    $passedRecord->setGender($record['gender']);
                    $passedRecord->setMaritalStatus($record['marital_status']);
                    $passedRecord->setLga($record['lga_code']);
                    $passedRecord->setGeoPoliticalZone($record['region_code']);
                    $passedRecord->setPhysicallyChallengedStatus($record['physically_challenged_status']);
                    $passedRecord->setQuarterlyReturnEmploymentStatus($record['quarterly_return_employment_status']);

                    $passedValidations[] = $passedRecord;
                }
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $passedValidations;
    }

    /**
     * @param NominalRoleSearchCriteria $searchCriteria
     * @param Paginator $paginator
     * @param $pageDirection
     * @return array
     * @throws AppException
     */
    public function searchApprovedNominalRoleDetail(NominalRoleSearchCriteria $searchCriteria, Paginator $paginator, $pageDirection): array
    {
        $nominalRoles = array();
        $statement = null;

        try {

            $searchSubmissionId = $searchCriteria->getSubmissionId();

            if (!$searchSubmissionId) {
                $searchSubmissionId = '-1';
            }

            $searchEmployeeNumber = $searchCriteria->getEmployeeNumber();
            $searchName = $searchCriteria->getName();

            $searchNationality = $searchCriteria->getNationality();
            $searchStateOfOrigin = $searchCriteria->getStateOfOrigin();
            //$searchDateOfBirth = $searchCriteria->getDateOfBirth();
            //$searchDateOfEmployment = $searchCriteria->getDateOfEmployment();

            $searchGradeLevel = $searchCriteria->getGradeLevel();
            //$searchDesignation = $searchCriteria->getDesignation();
            //$searchStateOfLocation = $searchCriteria->getStateOfLocation();
            //$searchGender = $searchCriteria->getGender();

            //$searchMaritalStatus = $searchCriteria->getMaritalStatus();
            //$searchLga = $searchCriteria->getLga();
            $searchGeoPoliticalZone = $searchCriteria->getGeoPoliticalZone();

            $where = array("d.submission_id = '$searchSubmissionId'");

            /*if ($searchSubmissionId) {
                $where[] = "d.submission_id = :submission_id";
            }*/
            if ($searchEmployeeNumber) {
                $where[] = "d.employee_number = :employee_number";
            }
            if ($searchName) {
                $where[] = "d.employee_name LIKE :employee_name";
            }
            if ($searchNationality) {
                $where[] = "d.nationality_code = :nationality_code";
            }
            if ($searchStateOfOrigin) {
                $where[] = "d.state_of_origin_code = :state_of_origin_code";
            }
            if ($searchGradeLevel) {
                $where[] = "d.grade_level = :grade_level";
            }
            if ($searchGeoPoliticalZone) {
                $where[] = "d.region_code = :region_code";
            }

            if ($where) {
                $where = implode(" AND ", $where);
            } else {
                $where = '';
            }

            //fetch total matching rows
            $countQuery = "SELECT COUNT(d.id) AS totalRows 
                FROM confirmed_federal_level_nominal_roll_submission d 
                WHERE $where ";
            $statement = $this->connection->prepare($countQuery);

            /*if ($searchSubmissionId) {
                $statement->bindValue(':submission_id', $searchSubmissionId);
            }*/
            if ($searchEmployeeNumber) {
                $statement->bindValue(':employee_number', $searchEmployeeNumber);
            }
            if ($searchName) {
                $statement->bindValue(':employee_name', "%" . $searchName . "%");
            }
            if ($searchNationality) {
                $statement->bindValue(':nationality_code', $searchNationality);
            }
            if ($searchStateOfOrigin) {
                $statement->bindValue(':state_of_origin_code', $searchStateOfOrigin);
            }
            if ($searchGradeLevel) {
                $where[] = "d.grade_level = :grade_level";
                $statement->bindValue(':grade_level', $searchGradeLevel);
            }
            if ($searchGeoPoliticalZone) {
                $statement->bindValue(':region_code', $searchGeoPoliticalZone);
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
            $query = "SELECT d.id,d.submission_id,d.submission_year,d.serial_number,d.employee_status,d.employee_number 
                ,d.employee_name,d.nationality_code,d.state_of_origin_code,d.date_of_birth,d.date_of_employment,d.date_of_present_appointment 
                ,d.grade_level,d.designation,d.state_of_deployment_code,d.gender,d.marital_status,d.lga_code,d.region_code 
                ,d.physically_challenged_status,d.quarterly_return_employment_status
                FROM confirmed_federal_level_nominal_roll_submission d 
                WHERE $where 
                ORDER BY d.id LIMIT $limitStartRow ,$this->rows_per_page ";

            $statement = $this->connection->prepare($query);

            /*if ($searchSubmissionId) {
                $statement->bindValue(':submission_id', $searchSubmissionId);
            }*/
            if ($searchEmployeeNumber) {
                $statement->bindValue(':employee_number', $searchEmployeeNumber);
            }
            if ($searchName) {
                $statement->bindValue(':employee_name', "%" . $searchName . "%");
            }
            if ($searchNationality) {
                $statement->bindValue(':nationality_code', $searchNationality);
            }
            if ($searchStateOfOrigin) {
                $statement->bindValue(':state_of_origin_code', $searchStateOfOrigin);
            }
            if ($searchGradeLevel) {
                $where[] = "d.grade_level = :grade_level";
                $statement->bindValue(':grade_level', $searchGradeLevel);
            }
            if ($searchGeoPoliticalZone) {
                $statement->bindValue(':region_code', $searchGeoPoliticalZone);
            }

            $statement->execute();

            $records = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($records) {
                foreach ($records as $record) {
                    $nominalRole = new NominalRoll();
                    $nominalRole->setId($record['id']);
                    $nominalRole->setSubmissionId($record['submission_id']);
                    $nominalRole->setSubmissionYear($record['submission_year']);

                    $nominalRole->setSerialNo($record['serial_number']);
                    $nominalRole->setEmployeeStatus($record['employee_status']);
                    $nominalRole->setEmployeeNumber($record['employee_number']);
                    $nominalRole->setName($record['employee_name']);
                    $nominalRole->setNationality($record['nationality_code']);
                    $nominalRole->setStateOfOrigin($record['state_of_origin_code']);
                    $nominalRole->setDateOfBirth($record['date_of_birth']);
                    $nominalRole->setDateOfEmployment($record['date_of_employment']);
                    $nominalRole->setDateOfPresentAppointment($record['date_of_present_appointment']);
                    $nominalRole->setGradeLevel($record['grade_level']);
                    $nominalRole->setDesignation($record['designation']);
                    $nominalRole->setStateOfLocation($record['state_of_deployment_code']);
                    $nominalRole->setGender($record['gender']);
                    $nominalRole->setMaritalStatus($record['marital_status']);
                    $nominalRole->setLga($record['lga_code']);
                    $nominalRole->setGeoPoliticalZone($record['region_code']);
                    $nominalRole->setPhysicallyChallengedStatus($record['physically_challenged_status']);
                    $nominalRole->setQuarterlyReturnEmploymentStatus($record['quarterly_return_employment_status']);

                    $nominalRoles[] = $nominalRole;
                }
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $nominalRoles;
    }

    /**
     * @param $submissionId
     * @return bool
     * @throws AppException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function deleteNominalRoll($submissionId): bool
    {
        $outcome = false;
        $statement = null;

        try {

            //make sure u can delete first
            $query = "SELECT fcc_desk_officer_confirmation_status 
                      FROM federal_level_nominal_roll_submissions 
                      WHERE submission_id=:submission_id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':submission_id', $submissionId);
            $outcome = $statement->execute();

            $fccDeskOfficerConfirmationStatus = $statement->fetchColumn(0);

            if ($fccDeskOfficerConfirmationStatus == AppConstants::PENDING) {

                $this->connection->beginTransaction();

                //now delete, consider backing up later or setting inactive
                $query = "DELETE FROM federal_level_nominal_roll_submissions WHERE submission_id=:submission_id";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':submission_id', $submissionId);
                $statement->execute();

                $query = "DELETE FROM federal_level_nominal_roll_submission_staging WHERE submission_id=:submission_id";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':submission_id', $submissionId);
                $statement->execute();

                $query = "DELETE FROM federal_level_nominal_roll_failed_validations WHERE submission_id=:submission_id";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':submission_id', $submissionId);
                $statement->execute();

                $query = "DELETE FROM federal_level_nominal_roll_failed_validations_detail WHERE submission_id=:submission_id";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':submission_id', $submissionId);
                $statement->execute();

                $this->connection->commit();
                $outcome = true;
            }

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

    /**
     * @param $submissionId
     * @param $validationStatus
     * @return bool
     * @throws AppException
     */
    public function updateNominalRoleValidationStatus($submissionId, $validationStatus): bool
    {
        $outcome = false;
        $statement = null;

        try {

            $query = "update federal_level_nominal_roll_submissions 
                 set validation_status=:validation_status 
                 where submission_id=:submission_id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':validation_status', $validationStatus);
            $statement->bindValue(':submission_id', $submissionId);
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