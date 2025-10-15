<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 2:11 PM
 */

namespace AppBundle\Services\ApprovalRequests;


use AppBundle\AppException\AppException;
use AppBundle\AppException\AppExceptionMessages;
use AppBundle\Model\ApprovalRequests\SubmissionPermissionRequest;
use AppBundle\Model\SearchCriteria\SubmissionPermissionSearchCriteria;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\Paginator;
use AppBundle\Utils\ServiceHelper;
use Doctrine\DBAL\Connection;
use \PDO;
use \Throwable;

class SubmissionPermissionRequestService extends ServiceHelper
{
    private $connection;
    private $rows_per_page;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->rows_per_page = AppConstants::ROWS_PER_PAGE;
    }


    public function searchRecords(SubmissionPermissionSearchCriteria $searchCriteria, Paginator $paginator, $pageDirection, bool $isMdaDeskOfficer = true): array
    {
        $searchResults = array();
        $statement = null;

        try {

            $searchOrganization = $searchCriteria->getOrganizationId();
            $searchYear = $searchCriteria->getSubmissionYear();
            $searchApprovalStatus = $searchCriteria->getApprovalStatus();

            if (!$searchOrganization) {
                if ($isMdaDeskOfficer) {
                    $searchOrganization = -1;
                }
            }

            $where = array();

            if ($searchOrganization) {
                $where[] = "d.organization_id = :organization_id";
            }
            if ($searchYear) {
                $where[] = "d.submission_year = :submission_year";
            }
            if ($searchApprovalStatus) {
                $where[] = "d.approval_status = :approval_status";
            }

            if ($where) {
                $where = implode(" AND ", $where);
            } else {
                $where = '';
            }

            //fetch total matching rows
            $countQuery = "SELECT COUNT(d.submission_year) AS totalRows FROM submission_upload_permission_requests d WHERE $where";
            $statement = $this->connection->prepare($countQuery);
            if ($searchOrganization) {
                $statement->bindValue(':organization_id', $searchOrganization);
            }
            if ($searchYear) {
                $statement->bindValue(':submission_year', $searchYear);
            }
            if ($searchApprovalStatus) {
                $statement->bindValue(':approval_status', $searchApprovalStatus);
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
            $query = "SELECT d.organization_id,d.submission_year,d.remarks 
                ,d.approval_status,d.approved_by
                ,date_format(d.date_of_approval,'%e-%b-%Y') as _date_of_approval,d.expired
                ,date_format(d.created,'%e-%b-%Y') as _created, d.selector
                ,o.organization_name 
                FROM submission_upload_permission_requests d 
                JOIN organization o on d.organization_id=o.id 
                WHERE $where order by d.submission_year desc LIMIT $limitStartRow ,$this->rows_per_page ";
            $statement = $this->connection->prepare($query);
            if ($searchOrganization) {
                $statement->bindValue(':organization_id', $searchOrganization);
            }
            if ($searchYear) {
                $statement->bindValue(':submission_year', "%" . $searchYear . "%");
            }
            if ($searchApprovalStatus) {
                $statement->bindValue(':approval_status', $searchApprovalStatus);
            }

            $statement->execute();

            $records = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($records) {

                $displaySerialNo = $paginator->getStartRow() + 1;

                for ($i = 0; $i < count($records); $i++) {

                    $record = $records[$i];

                    $submissionPermissionRequest = new SubmissionPermissionRequest();
                    $submissionPermissionRequest->setOrganizationId($record['organization_id']);
                    $submissionPermissionRequest->setOrganizationName($record['organization_name']);
                    $submissionPermissionRequest->setSubmissionYear($record['submission_year']);
                    $submissionPermissionRequest->setRemarks($record['remarks']);
                    $submissionPermissionRequest->setApprovalStatus($record['approval_status']);
                    $submissionPermissionRequest->setDateOfApproval($record['_date_of_approval']);
                    $submissionPermissionRequest->setExpired($record['expired']);
                    $submissionPermissionRequest->setCreated($record['_created']);
                    $submissionPermissionRequest->setSelector($record['selector']);

                    $submissionPermissionRequest->setHasExpired(($submissionPermissionRequest->getExpired()=='Y') ? true : false);
                    $submissionPermissionRequest->setApproved(($submissionPermissionRequest->getApprovalStatus() == AppConstants::APPROVED) ? true : false);

                    $submissionPermissionRequest->setDisplaySerialNo($displaySerialNo);
                    $displaySerialNo++;

                    $searchResults[] = $submissionPermissionRequest;
                }
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $searchResults;
    }

    public function addSubmissionPermissionRequest(SubmissionPermissionRequest $submissionPermissionRequest): bool
    {
        $outcome = false;
        $statement = null;

        try {

            //check that it has not been approved
            $query = "select organization_id 
                      from submission_upload_permission_requests
                      where organization_id=:organization_id and submission_year=:submission_year and expired=:expired";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':organization_id', $submissionPermissionRequest->getOrganizationId());
            $statement->bindValue(':submission_year', $submissionPermissionRequest->getSubmissionYear());
            $statement->bindValue(':expired', AppConstants::N);
            $statement->execute();
            $existingApproval = $statement->fetch();

            if($existingApproval){
                throw new AppException(AppExceptionMessages::DUPLICATE_OPEN_PERMISSION);
            }

            $query = "INSERT INTO submission_upload_permission_requests 
                (
                organization_id,submission_year,remarks,approval_status 
                ,created,created_by,last_mod,last_mod_by,selector
                ) 
                VALUES 
                (
                :organization_id,:submission_year,:remarks,:approval_status
                ,:created,:created_by,:last_mod,:last_mod_by,:selector
                ) ";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':organization_id', $this->getValueOrNull($submissionPermissionRequest->getOrganizationId()));
            $statement->bindValue(':submission_year', $this->getValueOrNull($submissionPermissionRequest->getSubmissionYear()));
            $statement->bindValue(':remarks', $this->getValueOrNull($submissionPermissionRequest->getRemarks()));
            $statement->bindValue(':approval_status', $this->getValueOrNull($submissionPermissionRequest->getApprovalStatus()));
            $statement->bindValue(':created', $this->getValueOrNull($submissionPermissionRequest->getLastModified()));
            $statement->bindValue(':created_by', $this->getValueOrNull($submissionPermissionRequest->getLastModifiedByUserId()));
            $statement->bindValue(':last_mod', $this->getValueOrNull($submissionPermissionRequest->getLastModified()));
            $statement->bindValue(':last_mod_by', $this->getValueOrNull($submissionPermissionRequest->getLastModifiedByUserId()));
            $statement->bindValue(':selector', $this->getValueOrNull($submissionPermissionRequest->getSelector()));

            $outcome = $statement->execute();

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $outcome;

    }

    public function getSubmissionPermissionRequest($selector)
    {
        $submissionPermissionRequest = null;
        $statement = null;

        try {
            $query = "SELECT d.organization_id,d.submission_year,d.remarks 
                ,d.approval_status,d.approved_by
                ,date_format(d.date_of_approval,'%e-%b-%Y') as _date_of_approval,d.expired
                ,date_format(d.created,'%e-%b-%Y') as _created, d.selector
                ,o.organization_name 
                FROM submission_upload_permission_requests d 
                JOIN organization o on d.organization_id=o.id 
                WHERE d.selector=:selector ";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':selector', $selector);
            $statement->execute();

            $record = $statement->fetch(PDO::FETCH_ASSOC);
            if ($record) {
                $submissionPermissionRequest = new SubmissionPermissionRequest();
                $submissionPermissionRequest->setOrganizationId($record['organization_id']);
                $submissionPermissionRequest->setOrganizationName($record['organization_name']);
                $submissionPermissionRequest->setSubmissionYear($record['submission_year']);
                $submissionPermissionRequest->setRemarks($record['remarks']);
                $submissionPermissionRequest->setApprovalStatus($record['approval_status']);
                $submissionPermissionRequest->setDateOfApproval($record['_date_of_approval']);
                $submissionPermissionRequest->setExpired($record['expired']);
                $submissionPermissionRequest->setCreated($record['_created']);
                $submissionPermissionRequest->setSelector($record['selector']);

                $submissionPermissionRequest->setHasExpired(($submissionPermissionRequest->getExpired()=='Y') ? true : false);
                $submissionPermissionRequest->setApproved(($submissionPermissionRequest->getApprovalStatus() == AppConstants::APPROVED) ? true : false);
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $submissionPermissionRequest;
    }

    public function editSubmissionPermissionRequest(SubmissionPermissionRequest $submissionPermissionRequest): bool
    {
        $outcome = false;
        $statement = null;

        try {

            //check that it has not been approved
            $query = "select organization_id 
                      from submission_upload_permission_requests
                      where approval_status=:approval_status and selector=:selector";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':approval_status', AppConstants::APPROVED);
            $statement->bindValue(':selector', $submissionPermissionRequest->getSelector());
            $statement->execute();

            $alreadyApproved = $statement->fetch();

            if($alreadyApproved){
                throw new AppException(AppExceptionMessages::OPERATION_NOT_ALLOWED);
            }

            //now update
            $query = "UPDATE submission_upload_permission_requests 
                SET 
                submission_year=:submission_year
                ,remarks=:remarks
                WHERE selector=:selector";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':submission_year', $submissionPermissionRequest->getSubmissionYear());
            $statement->bindValue(':remarks', $submissionPermissionRequest->getRemarks());
            $statement->bindValue(':selector', $submissionPermissionRequest->getSelector());

            $outcome = $statement->execute();

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }
        return $outcome;
    }

    public function approveSubmissionPermissionRequest(SubmissionPermissionRequest $submissionPermissionRequest): bool
    {
        $outcome = false;
        $statement = null;

        try {

            //check that it has not been used
            $query = "select expired 
                      from submission_upload_permission_requests
                      where selector=:selector";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':selector', $submissionPermissionRequest->getSelector());
            $statement->execute();

            $expiredStatus = $statement->fetchColumn(0);

            if($expiredStatus == AppConstants::Y){
                throw new AppException(AppExceptionMessages::OPERATION_NOT_ALLOWED);
            }

            //now update
            $query = "UPDATE submission_upload_permission_requests 
                SET 
                approval_status=:approval_status
                ,approved_by = :approved_by
                ,date_of_approval=:date_of_approval 
                WHERE selector=:selector";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':approval_status', $submissionPermissionRequest->getApprovalStatus());
            $statement->bindValue(':approved_by', $submissionPermissionRequest->getApprovedByUserId());
            $statement->bindValue(':date_of_approval', $submissionPermissionRequest->getDateOfApproval());
            $statement->bindValue(':selector', $submissionPermissionRequest->getSelector());

            $outcome = $statement->execute();

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }
        return $outcome;
    }

    public function deleteSubmissionPermissionRequest(SubmissionPermissionRequest $submissionPermissionRequest): bool
    {
        $outcome = false;
        $statement = null;

        try {
            //check that it has not been approved
            $query = "select organization_id 
                      from submission_upload_permission_requests
                      where approval_status=:approval_status and selector=:selector";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':approval_status', AppConstants::APPROVED);
            $statement->bindValue(':selector', $submissionPermissionRequest->getSelector());
            $statement->execute();

            $alreadyApproved = $statement->fetch();

            if($alreadyApproved){
                throw new AppException(AppExceptionMessages::OPERATION_NOT_ALLOWED);
            }

            $query = "delete from submission_upload_permission_requests WHERE selector=:selector";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':selector', $submissionPermissionRequest->getSelector());

            $outcome = $statement->execute();

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