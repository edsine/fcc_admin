<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/7/2017
 * Time: 11:27 AM
 */

namespace AppBundle\Services\MisHead;


use AppBundle\AppException\AppException;
use AppBundle\AppException\AppExceptionMessages;
use AppBundle\Model\Organizations\Organization;
use AppBundle\Model\SearchCriteria\NominalRollSubmissionSearchCriteria;
use AppBundle\Model\Submission\NominalRollSubmission;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\HttpHelper;
use AppBundle\Utils\Paginator;
use AppBundle\Utils\StringHelper;
use Doctrine\DBAL\Connection;
use GuzzleHttp\Client;
use \Throwable;
use \PDO;

class MisHeadNominalRollService
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

            $searchOrganizationId = $searchCriteria->getOrganizationId();
            $searchValidationStatus = $searchCriteria->getValidationStatus();
            $searchFccDeskOfficerApproval = $searchCriteria->getFccDeskOfficerConfirmationStatus();
            $searchFccMisHeadApproval = $searchCriteria->getFccMisHeadApprovalStatus();
            $searchProcessingStatus = $searchCriteria->getProcessingStatus();

            $where = array("d.submission_id IS NOT NULL");

            if ($searchOrganizationId) {
                $where[] = "d.organization_id = :organization_id";
            }

            if ($searchValidationStatus) {
                $where[] = "d.validation_status = :validation_status";
            }

            if ($searchFccDeskOfficerApproval) {
                $where[] = "d.fcc_desk_officer_confirmation_status = :fcc_desk_officer_confirmation_status";
            }

            if ($searchFccMisHeadApproval) {
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
            $countQuery = "SELECT COUNT(d.submission_id) AS totalRows 
                FROM federal_level_nominal_roll_submissions d 
                JOIN organization o on d.organization_id = o.id 
                WHERE $where";
            $statement = $this->connection->prepare($countQuery);

            if ($searchOrganizationId) {
                $statement->bindValue(':organization_id', $searchOrganizationId);
            }

            if ($searchValidationStatus) {
                $statement->bindValue(':validation_status', $searchValidationStatus);
            }

            if ($searchFccDeskOfficerApproval) {
                $statement->bindValue(':fcc_desk_officer_confirmation_status', $searchFccDeskOfficerApproval);
            }

            if ($searchFccMisHeadApproval) {
                $statement->bindValue(':fcc_mis_head_approval_status', $searchFccMisHeadApproval);
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
                ,date_format(d.date_validation_passed,'%e-%b-%Y %h:%i %p') as _date_validation_passed 
                ,date_format(d.date_fcc_desk_officer_confirmed,'%e-%b-%Y %h:%i %p') as _date_fcc_desk_officer_confirmed 
                ,date_format(d.date_fcc_mis_head_approved,'%e-%b-%Y %h:%i %p') as _date_fcc_mis_head_approved 
                ,date_format(d.created,'%e-%b-%Y %h:%i %p') as _created 
                ,o.establishment_code,o.establishment_mnemonic,o.organization_name,o.state_owned_establishment_state_id 
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

            if ($searchFccDeskOfficerApproval) {
                $statement->bindValue(':fcc_desk_officer_confirmation_status', $searchFccDeskOfficerApproval);
            }

            if ($searchFccMisHeadApproval) {
                $statement->bindValue(':fcc_mis_head_approval_status', $searchFccMisHeadApproval);
            }

            if ($searchProcessingStatus) {
                $statement->bindValue(':processing_status', $searchProcessingStatus);
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

                    $nominalRoleSubmission->setStateOwnedEstablishmentStateId($record['state_owned_establishment_state_id']);

                    if (!trim($nominalRoleSubmission->getStateOwnedEstablishmentStateId())) {
                        $nominalRoleSubmission->setIsFederalLevelSubmission(true);
                    } else {
                        $nominalRoleSubmission->setIsStateLevelSubmission(true);
                    }

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

                    $nominalRoleUploads[] = $nominalRoleSubmission;
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
     * @return array
     * @throws AppException
     */
    public function getMisHeadSubmissionSummary(): array
    {
        $submissionSummary = array();
        $submissionSummary['totalPending'] = 0;
        $submissionSummary['totalStillProcessing'] = 0;
        $statement = null;

        try {
            $query = "SELECT 'abc' 
                ,(
                SELECT count(d.submission_id) 
                FROM federal_level_nominal_roll_submissions d 
                WHERE d.fcc_desk_officer_confirmation_status=:fcc_desk_officer_confirmation_status 
                AND d.fcc_mis_head_approval_status=:fcc_mis_head_approval_status 
                 ) AS total_pending 
                ,(
                SELECT count(d.submission_id) 
                FROM federal_level_nominal_roll_submissions d 
                WHERE d.fcc_mis_head_approval_status=:fcc_mis_head_approval_status_2 
                AND d.processing_status=:processing_status_2 
                 ) AS total_still_processing ";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':fcc_desk_officer_confirmation_status', AppConstants::CONFIRMED);
            $statement->bindValue(':fcc_mis_head_approval_status', AppConstants::PENDING);
            $statement->bindValue(':fcc_mis_head_approval_status_2', AppConstants::APPROVED);
            $statement->bindValue(':processing_status_2', AppConstants::PENDING);

            $statement->execute();

            $result = $statement->fetch();

            $submissionSummary['totalPending'] = $result['total_pending'];
            $submissionSummary['totalStillProcessing'] = $result['total_still_processing'];


        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $submissionSummary;
    }

    /**
     * @param array $submissionRecordIds
     * @param $userProfileId
     * @return bool
     * @throws AppException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function approveNominalRollSubmission(array $submissionRecordIds, $userProfileId): bool
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
                    fcc_mis_head_approval_status=:fcc_mis_head_approval_status 
                    ,date_fcc_mis_head_approved=:date_fcc_mis_head_approved
                    ,approved_by=:approved_by
                    WHERE submission_id IN ($inString)";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(":fcc_mis_head_approval_status", AppConstants::APPROVED);
                $statement->bindValue(":date_fcc_mis_head_approved", $today);
                $statement->bindValue(":approved_by", $userProfileId);
                $statement->execute();

                //call the servlet, if not 200, then throw excepttion to roll back changes
                $submissionIdsToSend = str_replace("'", "", $inString);
                $guzzleClient = new Client();
                $response = $guzzleClient->request('GET', HttpHelper::getProxyFederalNominalRollBaseReportAnalysisSchedulerUrl(), [
                    'query' => ['submissionIds' => $submissionIdsToSend]
                ]);

                $code = $response->getStatusCode();
                //$body = $response->getBody();

                //$stringBody = (string)$body;

                if ($code != 200) {
                    throw new AppException(AppExceptionMessages::CANNOT_REACH_PROCESSING_ENDPOINT);
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


    /**
     * @return bool
     * @throws AppException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function resetHangingReportAnalysis(): bool
    {
        $outcome = false;
        $statement = null;

        try {

            $this->connection->beginTransaction();

            $query = "update 
                        federal_level_nominal_roll_submissions 
                      set 
                        fcc_mis_head_approval_status=:pending_fcc_mis_head_approval_status 
                      where 
                        fcc_mis_head_approval_status=:approved_fcc_mis_head_approval_status
                        and 
                        processing_status=:pending_processing_status
                        and 
                        date_fcc_mis_head_approved < (now() - interval 24 hour)";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(":pending_fcc_mis_head_approval_status", AppConstants::PENDING);
            $statement->bindValue(":approved_fcc_mis_head_approval_status", AppConstants::APPROVED);
            $statement->bindValue(":pending_processing_status", AppConstants::PENDING);
            $statement->execute();

            $this->connection->commit();
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