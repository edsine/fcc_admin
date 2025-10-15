<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/28/2017
 * Time: 10:52 PM
 */

namespace AppBundle\Services\Recruitment;


use AppBundle\AppException\AppException;
use AppBundle\Model\Document\AttachedDocument;
use AppBundle\Model\Recruitment\Recruitment;
use AppBundle\Model\SearchCriteria\RecruitmentSearchCriteria;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\Paginator;
use AppBundle\Utils\ServiceHelper;
use Doctrine\DBAL\Connection;
use \PDO;
use \Throwable;

class RecruitmentService extends ServiceHelper
{

    private $connection;
    private $rows_per_page;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->rows_per_page = AppConstants::ROWS_PER_PAGE;
    }

    /**
     * @param RecruitmentSearchCriteria $searchCriteria
     * @param Paginator $paginator
     * @param $pageDirection
     * @return array
     * @throws AppException
     */
    public function searchRecords(RecruitmentSearchCriteria $searchCriteria, Paginator $paginator, $pageDirection): array
    {
        $searchResults = array();
        $statement = null;

        try {

            $searchYear = $searchCriteria->getRecruitmentYear();
            $searchOrganization = $searchCriteria->getOrganization();

            $where = array();

            if ($searchYear) {
                $where[] = "d.recruitment_year = :recruitment_year";
            }

            if ($searchOrganization) {
                $where[] = "d.organization_id = :organization_id";
            }

            $where[] = "d.record_status='ACTIVE'";

            if ($where) {
                $where = implode(" AND ", $where);
            } else {
                $where = '';
            }

            //fetch total matching rows
            $countQuery = "SELECT COUNT(d.id) AS totalRows 
                          FROM recruitment d 
                          JOIN organization o on d.organization_id = o.id 
                          JOIN recruitment_category_types c on d.recruitment_category_id = c.id
                          WHERE $where";
            $statement = $this->connection->prepare($countQuery);
            if ($searchYear) {
                $statement->bindValue(':recruitment_year', $searchYear);
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
            $query = "SELECT d.id,d.title,d.organization_id,d.recruitment_year,d.recruitment_category_id
                , d.remarks, d.attachment_file_name
                ,date_format(d.created,'%d %b, %Y') as created_formatted ,d.selector 
                ,o.organization_name , o.guid as organization_selector
                ,c.description as recruitment_category_name
                FROM recruitment d 
                JOIN organization o on d.organization_id = o.id 
                JOIN recruitment_category_types c on d.recruitment_category_id = c.id
                WHERE $where order by d.created desc LIMIT $limitStartRow ,$this->rows_per_page";
            $statement = $this->connection->prepare($query);

            if ($searchYear) {
                $statement->bindValue(':recruitment_year', $searchYear);
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

                    $recruitment = new Recruitment();
                    $recruitment->setId($record['id']);
                    $recruitment->setTitle($record['title']);
                    $recruitment->setOrganizationId($record['organization_id']);
                    $recruitment->setOrganizationName($record['organization_name']);
                    $recruitment->setOrganizationSelector($record['organization_selector']);
                    $recruitment->setRecruitmentYear($record['recruitment_year']);
                    $recruitment->setRecruitmentCategoryId($record['recruitment_category_id']);
                    $recruitment->setRecruitmentCategoryName($record['recruitment_category_name']);
                    $recruitment->setCreated($record['created_formatted']);

                    $recruitment->setSelector($record['selector']);

                    $recruitment->setDisplaySerialNo($displaySerialNo);
                    $displaySerialNo++;

                    $searchResults[] = $recruitment;
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
     * @return Recruitment|null
     * @throws AppException
     */
    public function getRecruitment($selector):?Recruitment
    {
        $recruitment = null;
        $statement = null;

        try {
            $query = "SELECT d.id,d.title,d.organization_id,d.recruitment_year,d.recruitment_category_id
                , d.remarks, d.attachment_file_name
                ,date_format(d.created,'%d %b, %Y') as created_formatted ,d.selector 
                ,o.organization_name , o.guid as organization_selector
                ,c.description as recruitment_category_name
                FROM recruitment d 
                JOIN organization o on d.organization_id = o.id 
                JOIN recruitment_category_types c on d.recruitment_category_id = c.id
                WHERE d.selector=:selector";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':selector', $selector);
            $statement->execute();

            $record = $statement->fetch(PDO::FETCH_ASSOC);
            if ($record) {
                $recruitment = new Recruitment();
                $recruitment->setId($record['id']);
                $recruitment->setTitle($record['title']);
                $recruitment->setOrganizationId($record['organization_id']);
                $recruitment->setOrganizationName($record['organization_name']);
                $recruitment->setOrganizationSelector($record['organization_selector']);
                $recruitment->setRecruitmentYear($record['recruitment_year']);
                $recruitment->setRecruitmentCategoryId($record['recruitment_category_id']);
                $recruitment->setRecruitmentCategoryName($record['recruitment_category_name']);
                $recruitment->setCreated($record['created_formatted']);

                $attachment = new AttachedDocument(null, $record['attachment_file_name']);
                $recruitment->setAttachment($attachment);

                $recruitment->setSelector($record['selector']);
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $recruitment;
    }

    /**
     * @param Recruitment $recruitment
     * @return bool
     * @throws AppException
     */
    public function addRecruitment(Recruitment $recruitment): bool
    {
        $outcome = false;
        $statement = null;

        try {
            //now insert
            $query = "INSERT INTO recruitment 
                (
                title,organization_id,recruitment_year,recruitment_category_id,remarks,attachment_file_name,record_status
                ,created,created_by,last_mod,last_mod_by,selector
                ) 
                VALUES 
                (
                :title,:organization_id,:recruitment_year,:recruitment_category_id,:remarks,:attachment_file_name,:record_status
                ,:created,:created_by,:last_mod,:last_mod_by,:selector
                )";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':title', $this->getValueOrNull($recruitment->getTitle()));
            $statement->bindValue(':organization_id', $this->getValueOrNull($recruitment->getOrganizationId()));
            $statement->bindValue(':recruitment_year', $this->getValueOrNull($recruitment->getRecruitmentYear()));
            $statement->bindValue(':recruitment_category_id', $this->getValueOrNull($recruitment->getRecruitmentCategoryId()));
            $statement->bindValue(':remarks', $this->getValueOrNull($recruitment->getRemarks()));
            $statement->bindValue(':attachment_file_name', $this->getValueOrNull($recruitment->getAttachment()->getFileName()));
            $statement->bindValue(':record_status', $this->getValueOrNull($recruitment->getRecordStatus()));
            $statement->bindValue(':created', $this->getValueOrNull($recruitment->getLastModified()));
            $statement->bindValue(':created_by', $this->getValueOrNull($recruitment->getLastModifiedByUserId()));
            $statement->bindValue(':last_mod', $this->getValueOrNull($recruitment->getLastModified()));
            $statement->bindValue(':last_mod_by', $this->getValueOrNull($recruitment->getLastModifiedByUserId()));
            $statement->bindValue(':selector', $this->getValueOrNull($recruitment->getSelector()));

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
     * @param Recruitment $recruitment
     * @return bool
     * @throws AppException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function editRecruitment(Recruitment $recruitment): bool
    {
        $outcome = false;
        $statement = null;

        try {

            $this->connection->beginTransaction();

            //now update
            $query = "UPDATE recruitment 
                SET 
                title=:title
                ,recruitment_year=:recruitment_year
                ,recruitment_category_id=:recruitment_category_id
                ,remarks=:remarks
                ,last_mod=:last_mod
                ,last_mod_by=:last_mod_by
                WHERE selector=:selector";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':title', $this->getValueOrNull($recruitment->getTitle()));
            $statement->bindValue(':recruitment_year', $this->getValueOrNull($recruitment->getRecruitmentYear()));
            $statement->bindValue(':recruitment_category_id', $this->getValueOrNull($recruitment->getRecruitmentCategoryId()));
            $statement->bindValue(':remarks', $this->getValueOrNull($recruitment->getRemarks()));
            $statement->bindValue(':last_mod', $this->getValueOrNull($recruitment->getLastModified()));
            $statement->bindValue(':last_mod_by', $this->getValueOrNull($recruitment->getLastModifiedByUserId()));
            $statement->bindValue(':selector', $this->getValueOrNull($recruitment->getSelector()));
            $statement->execute();

            //update filename if exists
            if ($recruitment->getAttachment()->getFileName()) {
                $query = "UPDATE recruitment 
                SET 
                attachment_file_name=:attachment_file_name
                WHERE selector=:selector";

                $statement = $this->connection->prepare($query);
                $statement->bindValue(':attachment_file_name', $recruitment->getAttachment()->getFileName());
                $statement->bindValue(':selector', $recruitment->getSelector());
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
     * @param Recruitment $recruitment
     * @return bool
     * @throws AppException
     */
    public function deleteRecruitment(Recruitment $recruitment): bool
    {
        $outcome = false;
        $statement = null;

        try {

            //now delete
            $query = "UPDATE recruitment 
                SET 
                record_status=:record_status
                ,last_mod=:last_mod
                ,last_mod_by=:last_mod_by
                WHERE selector=:selector";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':record_status', $recruitment->getRecordStatus());
            $statement->bindValue(':last_mod', $recruitment->getLastModified());
            $statement->bindValue(':last_mod_by', $recruitment->getLastModifiedByUserId());
            $statement->bindValue(':selector', $recruitment->getSelector());
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