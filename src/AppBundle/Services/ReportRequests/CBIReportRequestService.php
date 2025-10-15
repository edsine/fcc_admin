<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 2:11 PM
 */

namespace AppBundle\Services\ReportRequests;


use AppBundle\AppException\AppException;
use AppBundle\AppException\AppExceptionMessages;
use AppBundle\Model\ReportRequests\CBIReportRequest;
use AppBundle\Model\SearchCriteria\CBIReportRequestSearchCriteria;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\Paginator;
use AppBundle\Utils\ServiceHelper;
use Doctrine\DBAL\Connection;
use \PDO;
use \Throwable;

class CBIReportRequestService extends ServiceHelper
{
    private $connection;
    private $rows_per_page;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->rows_per_page = AppConstants::ROWS_PER_PAGE;
    }


    public function searchRecords(CBIReportRequestSearchCriteria $searchCriteria, Paginator $paginator, $pageDirection, bool $isMdaDeskOfficer = true): array
    {
        $searchResults = array();
        $statement = null;

        try {

            $searchOrganization = $searchCriteria->getOrganizationId();
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
            if ($searchApprovalStatus) {
                $where[] = "d.approval_status = :approval_status";
            }

            if ($where) {
                $where = implode(" AND ", $where);
            } else {
                $where = '';
            }

            //fetch total matching rows
            $countQuery = "SELECT COUNT(d.organization_id) AS totalRows FROM character_balancing_index_report_requests d WHERE $where";
            $statement = $this->connection->prepare($countQuery);
            if ($searchOrganization) {
                $statement->bindValue(':organization_id', $searchOrganization);
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
            $query = "SELECT d.organization_id,d.request_type,d.recruitment_id,d.recruitment_value
                ,d.cbi_grade_level_category,d.submission_year_used,d.remarks,d.approval_status,d.approved_by
                ,date_format(d.date_of_approval,'%e %b, %Y') as _date_of_approval
                ,date_format(d.created,'%e %b, %Y') as _created, d.selector
                ,o.organization_name 
                ,c.description as cbi_grade_level_category_name
                ,r.title as recruitment_title, r.selector as recruitment_selector
                FROM character_balancing_index_report_requests d 
                JOIN organization o on d.organization_id=o.id 
                JOIN cbi_report_recruitment_categories c on d.cbi_grade_level_category = c.id
                left join recruitment r on d.recruitment_id = r.id
                WHERE $where order by d.created desc LIMIT $limitStartRow ,$this->rows_per_page ";
            $statement = $this->connection->prepare($query);
            if ($searchOrganization) {
                $statement->bindValue(':organization_id', $searchOrganization);
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

                    $cbiReportRequest = new CBIReportRequest();
                    $cbiReportRequest->setOrganizationId($record['organization_id']);
                    $cbiReportRequest->setOrganizationName($record['organization_name']);
                    $cbiReportRequest->setRequestType($record['request_type']);
                    $cbiReportRequest->setRecruitmentId($record['recruitment_id']);
                    $cbiReportRequest->setRecruitmentTitle($record['recruitment_title']);
                    $cbiReportRequest->setRecruitmentSelector($record['recruitment_selector']);
                    $cbiReportRequest->setRecruitmentValue($record['recruitment_value']);
                    $cbiReportRequest->setCbiGradeLevelCategory($record['cbi_grade_level_category']);
                    $cbiReportRequest->setCbiGradeLevelCategoryName($record['cbi_grade_level_category_name']);
                    $cbiReportRequest->setSubmissionYearUsed($record['submission_year_used']);
                    $cbiReportRequest->setRemarks($record['remarks']);
                    $cbiReportRequest->setApprovalStatus($record['approval_status']);
                    $cbiReportRequest->setDateOfApproval($record['_date_of_approval']);
                    $cbiReportRequest->setCreated($record['_created']);
                    $cbiReportRequest->setSelector($record['selector']);

                    $cbiReportRequest->setApproved(($cbiReportRequest->getApprovalStatus() == AppConstants::APPROVED) ? true : false);

                    $cbiReportRequest->setDisplaySerialNo($displaySerialNo);
                    $displaySerialNo++;

                    $searchResults[] = $cbiReportRequest;
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

    public function getCbiReportRequest($selector)
    {
        $cbiReportRequest = null;
        $statement = null;

        try {
            $query = "SELECT d.organization_id,d.request_type,d.recruitment_id,d.recruitment_value 
                ,d.cbi_grade_level_category,d.submission_year_used,d.remarks,d.approval_status,d.approved_by
                ,date_format(d.date_of_approval,'%e %b, %Y') as _date_of_approval
                ,date_format(d.created,'%e %b, %Y') as _created, d.selector
                ,o.organization_name 
                ,c.description as cbi_grade_level_category_name
                ,r.title as recruitment_title, r.selector as recruitment_selector
                FROM character_balancing_index_report_requests d 
                JOIN organization o on d.organization_id=o.id 
                JOIN cbi_report_recruitment_categories c on d.cbi_grade_level_category = c.id
                left join recruitment r on d.recruitment_id = r.id
                WHERE d.selector=:selector ";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':selector', $selector);
            $statement->execute();

            $record = $statement->fetch(PDO::FETCH_ASSOC);
            if ($record) {
                $cbiReportRequest = new CBIReportRequest();
                $cbiReportRequest->setOrganizationId($record['organization_id']);
                $cbiReportRequest->setOrganizationName($record['organization_name']);
                $cbiReportRequest->setRequestType($record['request_type']);
                $cbiReportRequest->setRecruitmentId($record['recruitment_id']);
                $cbiReportRequest->setRecruitmentTitle($record['recruitment_title']);
                $cbiReportRequest->setRecruitmentSelector($record['recruitment_selector']);
                $cbiReportRequest->setRecruitmentValue($record['recruitment_value']);
                $cbiReportRequest->setCbiGradeLevelCategory($record['cbi_grade_level_category']);
                $cbiReportRequest->setCbiGradeLevelCategoryName($record['cbi_grade_level_category_name']);
                $cbiReportRequest->setSubmissionYearUsed($record['submission_year_used']);
                $cbiReportRequest->setRemarks($record['remarks']);
                $cbiReportRequest->setApprovalStatus($record['approval_status']);
                $cbiReportRequest->setDateOfApproval($record['_date_of_approval']);
                $cbiReportRequest->setCreated($record['_created']);
                $cbiReportRequest->setSelector($record['selector']);

                $cbiReportRequest->setApproved(($cbiReportRequest->getApprovalStatus() == AppConstants::APPROVED) ? true : false);
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $cbiReportRequest;
    }

    public function addCbiReportRequest(CBIReportRequest $cbiReportRequest): bool
    {
        $outcome = false;
        $statement = null;

        try {

            //get the mda most recent active process submission year
            $query = "select max(submission_year) as most_recent_active_processed_year
                      from federal_level_nominal_roll_submissions
                      where organization_id=:organization_id
                      and is_active=:is_active
                      and processing_status=:processing_status";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':organization_id', $this->getValueOrNull($cbiReportRequest->getOrganizationId()));
            $statement->bindValue(':is_active', AppConstants::Y);
            $statement->bindValue(':processing_status', AppConstants::COMPLETED);
            $statement->execute();

            $mostRecentActiveProcessedYear = $statement->fetchColumn(0);


            if(!$mostRecentActiveProcessedYear){
                throw new AppException(AppExceptionMessages::NO_PROCESSED_MDA_REPORT);
            }

            $query = "INSERT INTO character_balancing_index_report_requests 
                (
                organization_id,request_type,recruitment_id,recruitment_value,cbi_grade_level_category,submission_year_used,remarks,approval_status 
                ,created,created_by,last_mod,last_mod_by,selector
                ) 
                VALUES 
                (
                :organization_id,:request_type,:recruitment_id,:recruitment_value,:cbi_grade_level_category,:submission_year_used,:remarks,:approval_status 
                ,:created,:created_by,:last_mod,:last_mod_by,:selector
                ) ";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':organization_id', $this->getValueOrNull($cbiReportRequest->getOrganizationId()));
            $statement->bindValue(':request_type', $this->getValueOrNull($cbiReportRequest->getRequestType()));
            $statement->bindValue(':recruitment_id', $this->getValueOrNull($cbiReportRequest->getRecruitmentId()));
            $statement->bindValue(':recruitment_value', $this->getValueOrNull($cbiReportRequest->getRecruitmentValue()));
            $statement->bindValue(':cbi_grade_level_category', $this->getValueOrNull($cbiReportRequest->getCbiGradeLevelCategory()));
            $statement->bindValue(':submission_year_used', $this->getValueOrNull($mostRecentActiveProcessedYear));
            $statement->bindValue(':remarks', $this->getValueOrNull($cbiReportRequest->getRemarks()));
            $statement->bindValue(':approval_status', $this->getValueOrNull($cbiReportRequest->getApprovalStatus()));
            $statement->bindValue(':created', $this->getValueOrNull($cbiReportRequest->getLastModified()));
            $statement->bindValue(':created_by', $this->getValueOrNull($cbiReportRequest->getLastModifiedByUserId()));
            $statement->bindValue(':last_mod', $this->getValueOrNull($cbiReportRequest->getLastModified()));
            $statement->bindValue(':last_mod_by', $this->getValueOrNull($cbiReportRequest->getLastModifiedByUserId()));
            $statement->bindValue(':selector', $this->getValueOrNull($cbiReportRequest->getSelector()));

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

    public function approveCbiReportRequestRequest(CBIReportRequest $cbiReportRequest): bool
    {
        $outcome = false;
        $statement = null;

        try {
            //now update
            $query = "UPDATE character_balancing_index_report_requests 
                SET 
                approval_status=:approval_status
                ,approved_by = :approved_by
                ,date_of_approval=:date_of_approval 
                WHERE selector=:selector";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':approval_status', $cbiReportRequest->getApprovalStatus());
            $statement->bindValue(':approved_by', $cbiReportRequest->getApprovedByUserId());
            $statement->bindValue(':date_of_approval', $cbiReportRequest->getDateOfApproval());
            $statement->bindValue(':selector', $cbiReportRequest->getSelector());

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