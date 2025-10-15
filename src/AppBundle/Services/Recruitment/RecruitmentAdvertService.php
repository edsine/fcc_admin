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
use AppBundle\Model\SearchCriteria\RecruitmentAdvertSearchCriteria;
use AppBundle\Model\Recruitment\RecruitmentAdvert;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\FileUploadHelper;
use AppBundle\Utils\Paginator;
use AppBundle\Utils\ServiceHelper;
use Doctrine\DBAL\Connection;
use \PDO;
use \Throwable;

class RecruitmentAdvertService extends ServiceHelper
{
    private $connection;
    private $rows_per_page;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->rows_per_page = AppConstants::ROWS_PER_PAGE;
    }

    /**
     * @param RecruitmentAdvertSearchCriteria $searchCriteria
     * @param Paginator $paginator
     * @param $pageDirection
     * @return array
     * @throws AppException
     */
    public function searchRecords(RecruitmentAdvertSearchCriteria $searchCriteria, Paginator $paginator, $pageDirection): array
    {
        $searchResults = array();
        $statement = null;

        try {

            $searchRecruitment = $searchCriteria->getRecruitment();
            $searchTitle = $searchCriteria->getTitle();
            $searchOrganization = $searchCriteria->getOrganizationId();
            $searchStartDate = $searchCriteria->getStartDate();
            $searchEndDate = $searchCriteria->getEndDate();

            $where = array();

            if ($searchRecruitment) {
                $where[] = "d.recruitment_id = :recruitment_id";
            }
            if ($searchTitle) {
                $where[] = "d.title like :title";
            }
            if ($searchOrganization) {
                $where[] = "r.organization_id = :organization_id";
            }

            /*if ($searchStartDate && $searchEndDate) {
                $where[] = "d.start_date between = :record_status";
            }*/
            $where[] = "d.record_status='ACTIVE'";

            if ($where) {
                $where = implode(" AND ", $where);
            } else {
                $where = '';
            }

            //fetch total matching rows
            $countQuery = "SELECT COUNT(d.id) AS totalRows 
                          FROM recruitment_advert d 
                          JOIN recruitment r on d.recruitment_id = r.id
                          JOIN organization o on r.organization_id = o.id 
                          WHERE $where";
            $statement = $this->connection->prepare($countQuery);
            if ($searchRecruitment) {
                $statement->bindValue(':recruitment_id', $searchRecruitment);
            }
            if ($searchTitle) {
                $statement->bindValue(':title', "%$searchTitle%");
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
            $query = "SELECT d.id,d.recruitment_id,d.title,d.attachment_file_name 
                ,date_format(d.start_date,'%d %b, %Y') as start_date_formatted
                ,date_format(d.end_date,'%d %b, %Y') as end_date_formatted
                ,d.confirmation_status, date_format(d.date_of_confirmation,'%d %b, %Y') as date_of_confirmation_formatted
                ,(curdate() > d.end_date) as expired, d.selector 
                , r.organization_id,r.recruitment_year, r.selector as recruitment_selector
                , c.description as recruitment_category_name
                , o.organization_name , o.guid as organization_selector
                FROM recruitment_advert d 
                JOIN recruitment r on d.recruitment_id = r.id
                JOIN recruitment_category_types c on r.recruitment_category_id = c.id 
                JOIN organization o on r.organization_id = o.id 
                WHERE $where order by d.start_date desc LIMIT $limitStartRow ,$this->rows_per_page";
            $statement = $this->connection->prepare($query);

            if ($searchRecruitment) {
                $statement->bindValue(':recruitment_id', $searchRecruitment);
            }
            if ($searchTitle) {
                $statement->bindValue(':title', "%$searchTitle%");
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

                    $recruitmentAdvert = new RecruitmentAdvert();
                    $recruitmentAdvert->setId($record['id']);
                    $recruitmentAdvert->setRecruitmentId($record['recruitment_id']);
                    $recruitmentAdvert->setRecruitmentSelector($record['recruitment_selector']);
                    $recruitmentAdvert->setRecruitmentYear($record['recruitment_year']);
                    $recruitmentAdvert->setRecruitmentCategoryName($record['recruitment_category_name']);
                    $recruitmentAdvert->setOrganizationId($record['organization_id']);
                    $recruitmentAdvert->setOrganizationName($record['organization_name']);
                    $recruitmentAdvert->setOrganizationSelector($record['organization_selector']);
                    $recruitmentAdvert->setTitle($record['title']);
                    $recruitmentAdvert->setStartDate($record['_start_date']);
                    $recruitmentAdvert->setEndDate($record['_end_date']);
                    $recruitmentAdvert->setConfirmationStatus($record['confirmation_status']);
                    $recruitmentAdvert->setDateOfConfirmation($record['date_of_confirmation_formatted']);

                    $recruitmentAdvert->setExpired($record['expired'] == 1);
                    $recruitmentAdvert->setConfirmed($recruitmentAdvert->getConfirmationStatus() == AppConstants::CONFIRMED);

                    $recruitmentAdvert->setSelector($record['selector']);
                    
                    $recruitmentAdvert->setDisplaySerialNo($displaySerialNo);
                    $displaySerialNo++;

                    $searchResults[] = $recruitmentAdvert;
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
     * @return RecruitmentAdvert|null
     * @throws AppException
     */
    public function getRecruitmentAdver($selector):?RecruitmentAdvert
    {
        $recruitmentAdvert = null;
        $statement = null;

        try {
            $query = "SELECT d.id,d.recruitment_id,d.title,d.attachment_file_name 
                ,date_format(d.start_date,'%d %b, %Y') as start_date_formatted
                ,date_format(d.end_date,'%d %b, %Y') as end_date_formatted
                ,d.confirmation_status, date_format(d.date_of_confirmation,'%d %b, %Y') as date_of_confirmation_formatted
                ,(curdate() > d.end_date) as expired, d.selector 
                , r.organization_id,r.recruitment_year, r.selector as recruitment_selector
                , c.description as recruitment_category_name
                , o.organization_name , o.guid as organization_selector
                FROM recruitment_advert d 
                JOIN recruitment r on d.recruitment_id = r.id
                JOIN recruitment_category_types c on r.recruitment_category_id = c.id 
                JOIN organization o on r.organization_id = o.id 
                WHERE d.selector=:selector";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':selector', $selector);
            $statement->execute();

            $record = $statement->fetch(PDO::FETCH_ASSOC);
            if ($record) {
                $recruitmentAdvert = new RecruitmentAdvert();
                $recruitmentAdvert->setId($record['id']);
                $recruitmentAdvert->setRecruitmentId($record['recruitment_id']);
                $recruitmentAdvert->setRecruitmentSelector($record['recruitment_selector']);
                $recruitmentAdvert->setRecruitmentYear($record['recruitment_year']);
                $recruitmentAdvert->setRecruitmentCategoryName($record['recruitment_category_name']);
                $recruitmentAdvert->setOrganizationId($record['organization_id']);
                $recruitmentAdvert->setOrganizationName($record['organization_name']);
                $recruitmentAdvert->setOrganizationSelector($record['organization_selector']);
                $recruitmentAdvert->setTitle($record['title']);
                $recruitmentAdvert->setStartDate($record['_start_date']);
                $recruitmentAdvert->setEndDate($record['_end_date']);
                $recruitmentAdvert->setConfirmationStatus($record['confirmation_status']);
                $recruitmentAdvert->setDateOfConfirmation($record['date_of_confirmation_formatted']);

                $recruitmentAdvert->setExpired($record['expired'] == 1);
                $recruitmentAdvert->setConfirmed($recruitmentAdvert->getConfirmationStatus() == AppConstants::CONFIRMED);

                $attachment = new AttachedDocument(null, $record['attachment_file_name']);
                $recruitmentAdvert->setAttachment($attachment);

                $recruitmentAdvert->setSelector($record['selector']);
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $recruitmentAdvert;
    }

    /**
     * @param RecruitmentAdvert $recruitmentAdvert
     * @return bool
     * @throws AppException
     */
    public function addRecruitmentAdvert(RecruitmentAdvert $recruitmentAdvert): bool
    {
        $outcome = false;
        $statement = null;

        try {
            //now insert
            $query = "INSERT INTO recruitment_advert 
                (
                recruitment_id,title,post,attachment_file_name,start_date,end_date,record_status
                ,created,created_by,last_mod,last_mod_by,selector
                ) 
                VALUES 
                (
                :organization_id,:title,:post,:uploaded_file_name,:start_date,:end_date,:record_status
                ,:created,:created_by,:last_mod,:last_mod_by,:selector
                )";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':recruitment_id', $this->getValueOrNull($recruitmentAdvert->getRecruitmentId()));
            $statement->bindValue(':title', $this->getValueOrNull($recruitmentAdvert->getTitle()));
            $statement->bindValue(':post', $this->getValueOrNull($recruitmentAdvert->getVacancyPost()));
            $statement->bindValue(':attachment_file_name', $this->getValueOrNull($recruitmentAdvert->getAttachment()->getFileName()));
            $statement->bindValue(':start_date', $this->getDateBetweenFormats($recruitmentAdvert->getStartDate(), 'd M, Y', 'Y-m-d'));
            $statement->bindValue(':end_date', $this->getDateBetweenFormats($recruitmentAdvert->getEndDate(), 'd M, Y', 'Y-m-d'));
            $statement->bindValue(':record_status', $this->getValueOrNull($recruitmentAdvert->getRecordStatus()));
            $statement->bindValue(':created', $this->getValueOrNull($recruitmentAdvert->getLastModified()));
            $statement->bindValue(':created_by', $this->getValueOrNull($recruitmentAdvert->getLastModifiedByUserId()));
            $statement->bindValue(':last_mod', $this->getValueOrNull($recruitmentAdvert->getLastModified()));
            $statement->bindValue(':last_mod_by', $this->getValueOrNull($recruitmentAdvert->getLastModifiedByUserId()));
            $statement->bindValue(':selector', $this->getValueOrNull($recruitmentAdvert->getSelector()));
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
     * @param RecruitmentAdvert $recruitmentAdvert
     * @return bool
     * @throws AppException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function editRecruitmentAdvert(RecruitmentAdvert $recruitmentAdvert): bool
    {
        $outcome = false;
        $statement = null;

        try {

            $this->connection->beginTransaction();

            //now update
            $query = "UPDATE recruitment_advert 
                SET 
                title=:title
                ,post=:post
                ,start_date=:start_date
                ,end_date=:end_date
                ,last_mod=:last_mod
                ,last_mod_by=:last_mod_by
                WHERE selector=:selector";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':title', $this->getValueOrNull($recruitmentAdvert->getTitle()));
            $statement->bindValue(':post', $this->getValueOrNull($recruitmentAdvert->getVacancyPost()));
            $statement->bindValue(':start_date', $this->getDateBetweenFormats($recruitmentAdvert->getStartDate(), 'd M, Y', 'Y-m-d'));
            $statement->bindValue(':end_date', $this->getDateBetweenFormats($recruitmentAdvert->getEndDate(), 'd M, Y', 'Y-m-d'));
            $statement->bindValue(':last_mod', $this->getValueOrNull($recruitmentAdvert->getLastModified()));
            $statement->bindValue(':last_mod_by', $this->getValueOrNull($recruitmentAdvert->getLastModifiedByUserId()));
            $statement->bindValue(':selector', $this->getValueOrNull($recruitmentAdvert->getSelector()));
            $statement->execute();

            //update filename if exists
            if ($recruitmentAdvert->getAttachment()->getFileName()) {
                $query = "UPDATE recruitment_advert 
                SET 
                attachment_file_name=:attachment_file_name
                WHERE selector=:selector";

                $statement = $this->connection->prepare($query);
                $statement->bindValue(':attachment_file_name', $recruitmentAdvert->getAttachment()->getFileName());
                $statement->bindValue(':selector', $recruitmentAdvert->getSelector());
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
     * @param RecruitmentAdvert $recruitmentAdvert
     * @return bool
     * @throws AppException
     */
    public function deleteRecruitmentAdvert(RecruitmentAdvert $recruitmentAdvert): bool
    {
        $outcome = false;
        $statement = null;

        try {

            //now delete
            $query = "UPDATE recruitment_advert 
                SET 
                record_status=:record_status
                ,last_mod=:last_mod
                ,last_mod_by=:last_mod_by
                WHERE selector=:selector";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':record_status', $recruitmentAdvert->getRecordStatus());
            $statement->bindValue(':last_mod', $recruitmentAdvert->getLastModified());
            $statement->bindValue(':last_mod_by', $recruitmentAdvert->getLastModifiedByUserId());
            $statement->bindValue(':selector', $recruitmentAdvert->getSelector());
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

    //***************************** CONFIRMATION ***************************************

    /**
     * @param RecruitmentAdvert $recruitmentAdvert
     * @return bool
     * @throws AppException
     */
    public function confirmRecruitmentAdvert(RecruitmentAdvert $recruitmentAdvert): bool
    {
        $outcome = false;
        $statement = null;

        try {

            //now delete
            $query = "UPDATE recruitment_advert 
                SET 
                confirmation_status=:confirmation_status
                ,confirmation_by=:confirmation_by
                ,date_of_confirmation=:date_of_confirmation
                ,confirmation_remarks=:confirmation_remarks
                ,last_mod=:last_mod
                ,last_mod_by=:last_mod_by
                WHERE selector=:selector";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':confirmation_status', $recruitmentAdvert->getConfirmationStatus());
            $statement->bindValue(':confirmation_by', $recruitmentAdvert->getLastModifiedByUserId());
            $statement->bindValue(':date_of_confirmation', $recruitmentAdvert->getLastModified());
            $statement->bindValue(':confirmation_remarks', $recruitmentAdvert->getConfirmationStatusRemarks());
            $statement->bindValue(':last_mod', $recruitmentAdvert->getLastModified());
            $statement->bindValue(':last_mod_by', $recruitmentAdvert->getLastModifiedByUserId());
            $statement->bindValue(':selector', $recruitmentAdvert->getSelector());
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