<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 2:11 PM
 */

namespace AppBundle\Services\Recruitment;


use AppBundle\AppException\AppException;
use AppBundle\Model\SearchCriteria\RecruitmentWaiverSearchCriteria;
use AppBundle\Model\Recruitment\RecruitmentWaiver;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\Paginator;
use AppBundle\Utils\ServiceHelper;
use Doctrine\DBAL\Connection;
use \PDO;
use \Throwable;

class RecruitmentWaiverService extends ServiceHelper
{
    private $connection;
    private $rows_per_page;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->rows_per_page = AppConstants::ROWS_PER_PAGE;
    }

    /**
     * @param RecruitmentWaiverSearchCriteria $searchCriteria
     * @param Paginator $paginator
     * @param $pageDirection
     * @return array
     * @throws AppException
     */
    public function searchRecords(RecruitmentWaiverSearchCriteria $searchCriteria, Paginator $paginator, $pageDirection): array
    {
        $searchResults = array();
        $statement = null;

        try {

            $searchRecruitment = $searchCriteria->getRecruitment();
            $searchOrganization = $searchCriteria->getOrganizationId();

            $where = array();

            if ($searchRecruitment) {
                $where[] = "d.recruitment_id = :recruitment_id";
            }

            if ($searchOrganization) {
                $where[] = "r.organization_id = :organization_id";
            }

            $where[] = "d.record_status='ACTIVE'";

            if ($where) {
                $where = implode(" AND ", $where);
            } else {
                $where = '';
            }

            //fetch total matching rows
            $countQuery = "SELECT COUNT(d.id) AS totalRows 
                          FROM recruitment_waivers d 
                          JOIN recruitment r on d.recruitment_id = r.id
                          JOIN organization o on r.organization_id = o.id 
                          WHERE $where";
            $statement = $this->connection->prepare($countQuery);

            if ($searchRecruitment) {
                $statement->bindValue(':recruitment_id', $searchRecruitment);
            }
            
            if ($searchOrganization) {
                $statement->bindValue(':organization_id', $searchOrganization);
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
            $query = "SELECT d.id,d.recruitment_id,d.reason
                ,d.approval_status, date_format(d.date_of_approval,'%d %b, %Y') as date_of_approval_formatted
                , d.selector 
                , r.organization_id,r.recruitment_year, r.selector as recruitment_selector
                , c.description as recruitment_category_name
                , o.organization_name , o.guid as organization_selector
                FROM recruitment_waivers d 
                JOIN recruitment r on d.recruitment_id = r.id
                JOIN recruitment_category_types c on r.recruitment_category_id = c.id 
                JOIN organization o on r.organization_id = o.id 
                WHERE $where order by d.created desc LIMIT $limitStartRow ,$this->rows_per_page";
            $statement = $this->connection->prepare($query);

            if ($searchRecruitment) {
                $statement->bindValue(':recruitment_id', $searchRecruitment);
            }

            if ($searchOrganization) {
                $statement->bindValue(':organization_id', $searchOrganization);
            }

            $statement->execute();

            $records = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($records) {
                $displaySerialNo = $paginator->getStartRow() + 1;

                for ($i = 0; $i < count($records); $i++) {

                    $record = $records[$i];

                    $recruitmentWaiver = new RecruitmentWaiver();
                    $recruitmentWaiver->setId($record['id']);
                    $recruitmentWaiver->setRecruitmentId($record['recruitment_id']);
                    $recruitmentWaiver->setRecruitmentSelector($record['recruitment_selector']);
                    $recruitmentWaiver->setRecruitmentYear($record['recruitment_year']);
                    $recruitmentWaiver->setRecruitmentCategoryName($record['recruitment_category_name']);
                    $recruitmentWaiver->setOrganizationId($record['organization_id']);
                    $recruitmentWaiver->setOrganizationName($record['organization_name']);
                    $recruitmentWaiver->setOrganizationSelector($record['organization_selector']);
                    $recruitmentWaiver->setReason($record['reason']);
                    $recruitmentWaiver->setApprovalStatus($record['approval_status']);
                    $recruitmentWaiver->setDateOfApproval($record['date_of_approval_formatted']);

                    $recruitmentWaiver->setApproved($recruitmentWaiver->getApprovalStatus() == AppConstants::APPROVED);

                    $recruitmentWaiver->setSelector($record['selector']);
                    
                    $recruitmentWaiver->setDisplaySerialNo($displaySerialNo);
                    $displaySerialNo++;

                    $searchResults[] = $recruitmentWaiver;
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

    /**
     * @param $selector
     * @return RecruitmentWaiver|null
     * @throws AppException
     */
    public function getWaiver($selector):?RecruitmentWaiver
    {
        $recruitmentWaiver = null;
        $statement = null;

        try {
            $query = "SELECT d.id,d.recruitment_id,d.reason
                ,d.approval_status, date_format(d.date_of_approval,'%d %b, %Y') as date_of_approval_formatted
                , d.selector 
                , r.organization_id,r.recruitment_year, r.selector as recruitment_selector
                , c.description as recruitment_category_name
                , o.organization_name , o.guid as organization_selector
                FROM recruitment_waivers d 
                JOIN recruitment r on d.recruitment_id = r.id
                JOIN recruitment_category_types c on r.recruitment_category_id = c.id 
                JOIN organization o on r.organization_id = o.id 
                WHERE d.selector=:selector";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':selector', $selector);
            $statement->execute();

            $record = $statement->fetch(PDO::FETCH_ASSOC);
            if ($record) {
                $recruitmentWaiver = new RecruitmentWaiver();
                $recruitmentWaiver->setId($record['id']);
                $recruitmentWaiver->setRecruitmentId($record['recruitment_id']);
                $recruitmentWaiver->setRecruitmentSelector($record['recruitment_selector']);
                $recruitmentWaiver->setRecruitmentYear($record['recruitment_year']);
                $recruitmentWaiver->setRecruitmentCategoryName($record['recruitment_category_name']);
                $recruitmentWaiver->setOrganizationId($record['organization_id']);
                $recruitmentWaiver->setOrganizationName($record['organization_name']);
                $recruitmentWaiver->setOrganizationSelector($record['organization_selector']);
                $recruitmentWaiver->setReason($record['reason']);
                $recruitmentWaiver->setApprovalStatus($record['approval_status']);
                $recruitmentWaiver->setDateOfApproval($record['date_of_approval_formatted']);

                $recruitmentWaiver->setApproved($recruitmentWaiver->getApprovalStatus() == AppConstants::APPROVED);

                $recruitmentWaiver->setSelector($record['selector']);
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $recruitmentWaiver;
    }

    /**
     * @param RecruitmentWaiver $recruitmentWaiver
     * @return bool
     * @throws AppException
     */
    public function addRecruitmentWaiver(RecruitmentWaiver $recruitmentWaiver): bool
    {
        $outcome = false;
        $statement = null;

        try {
            //now insert
            $query = "INSERT INTO recruitment_waivers 
                (
                recruitment_id,reason,record_status
                ,created,created_by,last_mod,last_mod_by,selector
                ) 
                VALUES 
                (
                :recruitment_id,:reason,:record_status
                ,:created,:created_by,:last_mod,:last_mod_by,:selector
                )";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':recruitment_id', $this->getValueOrNull($recruitmentWaiver->getRecruitmentId()));
            $statement->bindValue(':reason', $this->getValueOrNull($recruitmentWaiver->getReason()));
            $statement->bindValue(':record_status', $this->getValueOrNull($recruitmentWaiver->getRecordStatus()));
            $statement->bindValue(':created', $this->getValueOrNull($recruitmentWaiver->getLastModified()));
            $statement->bindValue(':created_by', $this->getValueOrNull($recruitmentWaiver->getLastModifiedByUserId()));
            $statement->bindValue(':last_mod', $this->getValueOrNull($recruitmentWaiver->getLastModified()));
            $statement->bindValue(':last_mod_by', $this->getValueOrNull($recruitmentWaiver->getLastModifiedByUserId()));
            $statement->bindValue(':selector', $this->getValueOrNull($recruitmentWaiver->getSelector()));
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

    /**
     * @param RecruitmentWaiver $recruitmentWaiver
     * @return bool
     * @throws AppException
     */
    public function editRecruitmentWaiver(RecruitmentWaiver $recruitmentWaiver): bool
    {
        $outcome = false;
        $statement = null;

        try {

            //now update
            $query = "UPDATE recruitment_waivers 
                SET 
                reason=:reason
                ,last_mod=:last_mod
                ,last_mod_by=:last_mod_by
                WHERE selector=:selector";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':reason', $this->getValueOrNull($recruitmentWaiver->getReason()));
            $statement->bindValue(':last_mod', $this->getValueOrNull($recruitmentWaiver->getLastModified()));
            $statement->bindValue(':last_mod_by', $this->getValueOrNull($recruitmentWaiver->getLastModifiedByUserId()));
            $statement->bindValue(':selector', $this->getValueOrNull($recruitmentWaiver->getSelector()));
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

    /**
     * @param RecruitmentWaiver $recruitmentWaiver
     * @return bool
     * @throws AppException
     */
    public function deleteRecruitmentWaiver(RecruitmentWaiver $recruitmentWaiver): bool
    {
        $outcome = false;
        $statement = null;

        try {

            //now delete
            $query = "UPDATE recruitment_waivers 
                SET 
                record_status=:record_status
                ,last_mod=:last_mod
                ,last_mod_by=:last_mod_by
                WHERE selector=:selector";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':record_status', $recruitmentWaiver->getRecordStatus());
            $statement->bindValue(':last_mod', $recruitmentWaiver->getLastModified());
            $statement->bindValue(':last_mod_by', $recruitmentWaiver->getLastModifiedByUserId());
            $statement->bindValue(':selector', $recruitmentWaiver->getSelector());
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

    //***************************** APPROVAL ***************************************

    /**
     * @param RecruitmentWaiver $recruitmentWaiver
     * @return bool
     * @throws AppException
     */
    public function confirmRecruitmentWaiver(RecruitmentWaiver $recruitmentWaiver): bool
    {
        $outcome = false;
        $statement = null;

        try {

            //now delete
            $query = "UPDATE recruitment_waivers 
                SET 
                approval_status=:approval_status
                ,approved_by=:approved_by
                ,date_of_approval=:date_of_approval
                ,approval_status_remarks=:approval_status_remarks
                ,last_mod=:last_mod
                ,last_mod_by=:last_mod_by
                WHERE selector=:selector";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':approval_status', $recruitmentWaiver->getApprovalStatus());
            $statement->bindValue(':approved_by', $recruitmentWaiver->getLastModifiedByUserId());
            $statement->bindValue(':date_of_approval', $recruitmentWaiver->getLastModified());
            $statement->bindValue(':approval_status_remarks', $recruitmentWaiver->getApprovalStatusRemarks());
            $statement->bindValue(':last_mod', $recruitmentWaiver->getLastModified());
            $statement->bindValue(':last_mod_by', $recruitmentWaiver->getLastModifiedByUserId());
            $statement->bindValue(':selector', $recruitmentWaiver->getSelector());
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