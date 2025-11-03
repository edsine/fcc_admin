<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 2:11 PM
 */

namespace AppBundle\Services\Recruitment;


use AppBundle\AppException\AppException;
use AppBundle\Model\Document\AttachedDocument;
use AppBundle\Model\Recruitment\RecruitmentMonitoring;
use AppBundle\Model\SearchCriteria\RecruitmentMonitoringSearchCriteria;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\Paginator;
use AppBundle\Utils\ServiceHelper;
use Doctrine\DBAL\Connection;
use \PDO;
use \Throwable;

class RecruitmentMonitoringService extends ServiceHelper
{
    private $connection;
    private $rows_per_page;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->rows_per_page = AppConstants::ROWS_PER_PAGE;
    }

    /**
     * @param RecruitmentMonitoringSearchCriteria $searchCriteria
     * @param Paginator $paginator
     * @param $pageDirection
     * @return array
     * @throws AppException
     */
    public function searchRecords(RecruitmentMonitoringSearchCriteria $searchCriteria, Paginator $paginator, $pageDirection): array
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
                          FROM recruitment_monitoring d 
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
            $query = "SELECT d.id,d.recruitment_id 
                ,date_format(d.invitation_date,'%d %b, %Y') as invitation_date_formatted
                , d.invitation_remarks, d.invitation_attachment_file_name
                ,d.dme_confirmation_status, date_format(d.date_of_dme_confirmation,'%d %b, %Y') as date_of_dme_confirmation_formatted
                ,d.exercise_completion_status, date_format(d.date_of_exercise_completion,'%d %b, %Y') as date_of_exercise_completion_formatted
                , d.selector 
                , r.organization_id,r.recruitment_year, r.selector as recruitment_selector
                , c.description as recruitment_category_name
                , o.organization_name , o.guid as organization_selector
                FROM recruitment_monitoring d 
                JOIN recruitment r on d.recruitment_id = r.id
                JOIN recruitment_category_types c on r.recruitment_category_id = c.id 
                JOIN organization o on r.organization_id = o.id 
                WHERE $where order by d.invitation_date desc LIMIT $limitStartRow ,$this->rows_per_page";
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

                    $recruitmentMonitoring = new RecruitmentMonitoring();
                    $recruitmentMonitoring->setId($record['id']);
                    $recruitmentMonitoring->setRecruitmentId($record['recruitment_id']);
                    $recruitmentMonitoring->setRecruitmentSelector($record['recruitment_selector']);
                    $recruitmentMonitoring->setRecruitmentYear($record['recruitment_year']);
                    $recruitmentMonitoring->setRecruitmentCategoryName($record['recruitment_category_name']);
                    $recruitmentMonitoring->setOrganizationId($record['organization_id']);
                    $recruitmentMonitoring->setOrganizationName($record['organization_name']);
                    $recruitmentMonitoring->setOrganizationSelector($record['organization_selector']);
                    $recruitmentMonitoring->setInvitationDate($record['invitation_date_formatted']);
                    $recruitmentMonitoring->setInvitationRemarks($record['invitation_remarks']);

                    $recruitmentMonitoring->setDmeConfirmationStatus($record['dme_confirmation_status']);
                    $recruitmentMonitoring->setDateOfDmeConfirmation($record['date_of_dme_confirmation_formatted']);

                    $recruitmentMonitoring->setConfirmed($recruitmentMonitoring->getDmeConfirmationStatus() == AppConstants::CONFIRMED);
                    $recruitmentMonitoring->setCompleted($recruitmentMonitoring->getCompletionStatus() == AppConstants::COMPLETED);

                    $recruitmentMonitoring->setSelector($record['selector']);
                    
                    $recruitmentMonitoring->setDisplaySerialNo($displaySerialNo);
                    $displaySerialNo++;

                    $searchResults[] = $recruitmentMonitoring;
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
     * @return RecruitmentMonitoring|null
     * @throws AppException
     */
    public function getRecruitmentMonitoring($selector):?RecruitmentMonitoring
    {
        $recruitmentMonitoring = null;
        $statement = null;

        try {
            $query = "SELECT d.id,d.recruitment_id 
                ,date_format(d.invitation_date,'%d %b, %Y') as invitation_date_formatted
                , d.invitation_remarks, d.invitation_attachment_file_name
                ,d.dme_confirmation_status, date_format(d.date_of_dme_confirmation,'%d %b, %Y') as date_of_dme_confirmation_formatted
                ,d.exercise_completion_status, date_format(d.date_of_exercise_completion,'%d %b, %Y') as date_of_exercise_completion_formatted
                , d.selector 
                , r.organization_id,r.recruitment_year, r.selector as recruitment_selector
                , c.description as recruitment_category_name
                , o.organization_name , o.guid as organization_selector
                FROM recruitment_monitoring d 
                JOIN recruitment r on d.recruitment_id = r.id
                JOIN recruitment_category_types c on r.recruitment_category_id = c.id 
                JOIN organization o on r.organization_id = o.id 
                WHERE d.selector=:selector";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':selector', $selector);
            $statement->execute();

            $record = $statement->fetch(PDO::FETCH_ASSOC);
            if ($record) {
                $recruitmentMonitoring = new RecruitmentMonitoring();
                $recruitmentMonitoring->setId($record['id']);
                $recruitmentMonitoring->setRecruitmentId($record['recruitment_id']);
                $recruitmentMonitoring->setRecruitmentSelector($record['recruitment_selector']);
                $recruitmentMonitoring->setRecruitmentYear($record['recruitment_year']);
                $recruitmentMonitoring->setRecruitmentCategoryName($record['recruitment_category_name']);
                $recruitmentMonitoring->setOrganizationId($record['organization_id']);
                $recruitmentMonitoring->setOrganizationName($record['organization_name']);
                $recruitmentMonitoring->setOrganizationSelector($record['organization_selector']);
                $recruitmentMonitoring->setInvitationDate($record['invitation_date_formatted']);
                $recruitmentMonitoring->setInvitationRemarks($record['invitation_remarks']);

                $recruitmentMonitoring->setDmeConfirmationStatus($record['dme_confirmation_status']);
                $recruitmentMonitoring->setDateOfDmeConfirmation($record['date_of_dme_confirmation_formatted']);

                $recruitmentMonitoring->setConfirmed($recruitmentMonitoring->getDmeConfirmationStatus() == AppConstants::CONFIRMED);
                $recruitmentMonitoring->setCompleted($recruitmentMonitoring->getCompletionStatus() == AppConstants::COMPLETED);

                $attachment = new AttachedDocument(null, $record['invitation_attachment_file_name']);
                $recruitmentMonitoring->setInvitationAttachment($attachment);

                $recruitmentMonitoring->setSelector($record['selector']);
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $recruitmentMonitoring;
    }

    /**
     * @param RecruitmentMonitoring $recruitmentMonitoring
     * @return bool
     * @throws AppException
     */
    public function addRecruitmentMonitoring(RecruitmentMonitoring $recruitmentMonitoring): bool
    {
        $outcome = false;
        $statement = null;

        try {
            //now insert
            $query = "INSERT INTO recruitment_monitoring 
                (
                recruitment_id,invitation_date,invitation_remarks,invitation_attachment_file_name,record_status
                ,created,created_by,last_mod,last_mod_by,selector
                ) 
                VALUES 
                (
                :recruitment_id,:invitation_date,:invitation_remarks,:invitation_attachment_file_name,:record_status
                ,:created,:created_by,:last_mod,:last_mod_by,:selector
                )";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':recruitment_id', $this->getValueOrNull($recruitmentMonitoring->getRecruitmentId()));
            $statement->bindValue(':invitation_date', $this->getDateBetweenFormats($recruitmentMonitoring->getInvitationDate(),'d M, Y','Y-m-d'));
            $statement->bindValue(':invitation_remarks', $this->getValueOrNull($recruitmentMonitoring->getInvitationRemarks()));
            $statement->bindValue(':invitation_attachment_file_name', $this->getValueOrNull($recruitmentMonitoring->getInvitationAttachment()->getFileName()));
            $statement->bindValue(':record_status', $this->getValueOrNull($recruitmentMonitoring->getRecordStatus()));
            $statement->bindValue(':created', $this->getValueOrNull($recruitmentMonitoring->getLastModified()));
            $statement->bindValue(':created_by', $this->getValueOrNull($recruitmentMonitoring->getLastModifiedByUserId()));
            $statement->bindValue(':last_mod', $this->getValueOrNull($recruitmentMonitoring->getLastModified()));
            $statement->bindValue(':last_mod_by', $this->getValueOrNull($recruitmentMonitoring->getLastModifiedByUserId()));
            $statement->bindValue(':selector', $this->getValueOrNull($recruitmentMonitoring->getSelector()));
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
     * @param RecruitmentMonitoring $recruitmentMonitoring
     * @return bool
     * @throws AppException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function editRecruitmentMonitoring(RecruitmentMonitoring $recruitmentMonitoring): bool
    {
        $outcome = false;
        $statement = null;

        try {

            $this->connection->beginTransaction();

            //now update
            $query = "UPDATE recruitment_monitoring 
                SET 
                invitation_date=:invitation_date
                ,invitation_remarks=:invitation_remarks
                ,last_mod=:last_mod
                ,last_mod_by=:last_mod_by
                WHERE selector=:selector";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':invitation_date', $this->getDateBetweenFormats($recruitmentMonitoring->getInvitationDate(),'d M, Y','Y-m-d'));
            $statement->bindValue(':invitation_remarks', $this->getValueOrNull($recruitmentMonitoring->getInvitationRemarks()));
            $statement->bindValue(':last_mod', $this->getValueOrNull($recruitmentMonitoring->getLastModified()));
            $statement->bindValue(':last_mod_by', $this->getValueOrNull($recruitmentMonitoring->getLastModifiedByUserId()));
            $statement->bindValue(':selector', $this->getValueOrNull($recruitmentMonitoring->getSelector()));
            $statement->execute();

            //update filename if exists
            if ($recruitmentMonitoring->getInvitationAttachment()->getFileName()) {
                $query = "UPDATE recruitment_monitoring 
                SET 
                invitation_attachment_file_name=:invitation_attachment_file_name
                WHERE selector=:selector";

                $statement = $this->connection->prepare($query);
                $statement->bindValue(':invitation_attachment_file_name', $recruitmentMonitoring->getInvitationAttachment()->getFileName());
                $statement->bindValue(':selector', $recruitmentMonitoring->getSelector());
                $statement->execute();
            }

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

    /**
     * @param RecruitmentMonitoring $recruitmentMonitoring
     * @return bool
     * @throws AppException
     */
    public function deleteRecruitmentMonitoring(RecruitmentMonitoring $recruitmentMonitoring): bool
    {
        $outcome = false;
        $statement = null;

        try {

            //now delete
            $query = "UPDATE recruitment_monitoring 
                SET 
                record_status=:record_status
                ,last_mod=:last_mod
                ,last_mod_by=:last_mod_by
                WHERE selector=:selector";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':record_status', $recruitmentMonitoring->getRecordStatus());
            $statement->bindValue(':last_mod', $recruitmentMonitoring->getLastModified());
            $statement->bindValue(':last_mod_by', $recruitmentMonitoring->getLastModifiedByUserId());
            $statement->bindValue(':selector', $recruitmentMonitoring->getSelector());
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

    //***************************** DME CONFIRMATION ***************************************

    /**
     * @param RecruitmentMonitoring $recruitmentMonitoring
     * @return bool
     * @throws AppException
     */
    public function dmeConfirmation(RecruitmentMonitoring $recruitmentMonitoring): bool
    {
        $outcome = false;
        $statement = null;

        try {

            $query = "UPDATE recruitment_monitoring 
                SET 
                dme_confirmation_status=:dme_confirmation_status
                ,date_of_dme_confirmation=:date_of_dme_confirmation
                ,dme_confirmation_by=:dme_confirmation_by
                ,dme_confirmation_remarks=:dme_confirmation_remarks
                ,last_mod=:last_mod
                ,last_mod_by=:last_mod_by
                WHERE selector=:selector";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':dme_confirmation_status', $recruitmentMonitoring->getDmeConfirmationStatus());
            $statement->bindValue(':date_of_dme_confirmation', $recruitmentMonitoring->getLastModified());
            $statement->bindValue(':dme_confirmation_by', $recruitmentMonitoring->getLastModifiedByUserId());
            $statement->bindValue(':dme_confirmation_remarks', $recruitmentMonitoring->getDmeConfirmationRemarks());
            $statement->bindValue(':last_mod', $recruitmentMonitoring->getLastModified());
            $statement->bindValue(':last_mod_by', $recruitmentMonitoring->getLastModifiedByUserId());
            $statement->bindValue(':selector', $recruitmentMonitoring->getSelector());
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
     * @param RecruitmentMonitoring $recruitmentMonitoring
     * @return bool
     * @throws AppException
     */
    public function exerciseCompletion(RecruitmentMonitoring $recruitmentMonitoring): bool
    {
        $outcome = false;
        $statement = null;

        try {

            $query = "UPDATE recruitment_monitoring 
                SET 
                exercise_completion_status=:exercise_completion_status
                ,date_of_exercise_completion=:date_of_exercise_completion
                ,exercise_completion_by=:exercise_completion_by
                ,exercise_completion_remarks=:exercise_completion_remarks
                ,last_mod=:last_mod
                ,last_mod_by=:last_mod_by
                WHERE selector=:selector";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':exercise_completion_status', $recruitmentMonitoring->getCompletionStatus());
            $statement->bindValue(':date_of_exercise_completion', $recruitmentMonitoring->getLastModified());
            $statement->bindValue(':exercise_completion_by', $recruitmentMonitoring->getLastModifiedByUserId());
            $statement->bindValue(':exercise_completion_remarks', $recruitmentMonitoring->getCompletionRemarks());
            $statement->bindValue(':last_mod', $recruitmentMonitoring->getLastModified());
            $statement->bindValue(':last_mod_by', $recruitmentMonitoring->getLastModifiedByUserId());
            $statement->bindValue(':selector', $recruitmentMonitoring->getSelector());
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