<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 2:11 PM
 */

namespace AppBundle\Services\Vacancy;


use AppBundle\AppException\AppException;
use AppBundle\Model\SearchCriteria\VacancySearchCriteria;
use AppBundle\Model\Vacancy\Vacancy;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\FileUploadHelper;
use AppBundle\Utils\Paginator;
use AppBundle\Utils\ServiceHelper;
use Doctrine\DBAL\Connection;
use \PDO;
use \Throwable;

class VacancyService extends ServiceHelper
{
    private $connection;
    private $rows_per_page;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->rows_per_page = AppConstants::ROWS_PER_PAGE;
    }


    public function searchRecords(VacancySearchCriteria $searchCriteria, Paginator $paginator, $pageDirection): array
    {
        $vacancies = array();
        $statement = null;

        try {

            $searchTitle = $searchCriteria->getTitle();
            $searchOrganization = $searchCriteria->getOrganizationId();
            $searchStartDate = $searchCriteria->getStartDate();
            $searchEndDate = $searchCriteria->getEndDate();

            $where = array();

            if (!$searchOrganization) {
                $searchOrganization = 0;
            }

            if ($searchTitle) {
                $where[] = "d.title like :title";
            }
            if ($searchOrganization) {
                $where[] = "d.organization_id = :organization_id";
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
            $countQuery = "SELECT COUNT(d.id) AS totalRows FROM vacancy d WHERE $where";
            $statement = $this->connection->prepare($countQuery);
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
            $query = "SELECT d.id,d.organization_id,d.title,d.uploaded_file_name 
                ,date_format(d.start_date,'%e-%b-%Y') as _start_date 
                ,date_format(d.end_date,'%e-%b-%Y') as _end_date, d.selector 
                ,o.organization_name 
                FROM vacancy d 
                JOIN organization o on d.organization_id = o.id 
                WHERE $where order by d.start_date desc LIMIT $limitStartRow ,$this->rows_per_page";
            $statement = $this->connection->prepare($query);

            if ($searchTitle) {
                $statement->bindValue(':title', $searchTitle);
            }
            if ($searchOrganization) {
                $statement->bindValue(':organization_id', "%" . $searchOrganization . "%");
            }

            $statement->execute();

            $records = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($records) {
                $fileUploadHelper = new FileUploadHelper();
                $httpUrlPrefix = null;
                $displaySerialNo = $paginator->getStartRow() + 1;

                for ($i = 0; $i < count($records); $i++) {

                    $record = $records[$i];

                    $vacancy = new Vacancy();
                    $vacancy->setId($record['id']);
                    $vacancy->setOrganizationId($record['organization_id']);
                    $vacancy->setOrganizationName($record['organization_name']);
                    $vacancy->setTitle($record['title']);
                    $vacancy->setUploadedFileName($record['uploaded_file_name']);
                    $vacancy->setStartDate($record['_start_date']);
                    $vacancy->setEndDate($record['_end_date']);

                    $vacancy->setSelector($record['selector']);

                    if ($vacancy->getRecordStatus() == AppConstants::ACTIVE) {
                        $httpUrlPrefix = $fileUploadHelper->getPublicVacancyUploadUrl();
                    }else{
                        $httpUrlPrefix = $fileUploadHelper->getPublicTrashUrl();
                    }

                    $vacancy->setUploadedFilePreviewUrl( $httpUrlPrefix . $vacancy->getOrganizationEstablishmentCode() . '/' . $vacancy->getUploadedFileName());

                    $vacancy->setDisplaySerialNo($displaySerialNo);
                    $displaySerialNo++;

                    $vacancies[] = $vacancy;
                }
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $vacancies;
    }

    public function addVacancy(Vacancy $vacancy): bool
    {
        $outcome = false;
        $statement = null;

        try {
            //now insert
            $query = "INSERT INTO vacancy 
                (
                organization_id,title,post,uploaded_file_name,start_date,end_date,record_status
                ,created,created_by,last_mod,last_mod_by,selector
                ) 
                VALUES 
                (
                :organization_id,:title,:post,:uploaded_file_name,:start_date,:end_date,:record_status
                ,:created,:created_by,:last_mod,:last_mod_by,:selector
                )";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':organization_id', $vacancy->getOrganizationId());
            $statement->bindValue(':title', $vacancy->getTitle());
            $statement->bindValue(':post', $vacancy->getVacancyPost());
            $statement->bindValue(':uploaded_file_name', $vacancy->getUploadedFileName());
            $statement->bindValue(':start_date', $this->getDateBetweenFormats($vacancy->getStartDate(), 'd/m/Y', 'Y-m-d'));
            $statement->bindValue(':end_date', $this->getDateBetweenFormats($vacancy->getEndDate(), 'd/m/Y', 'Y-m-d'));
            $statement->bindValue(':record_status', $vacancy->getRecordStatus());
            $statement->bindValue(':created', $vacancy->getLastModified());
            $statement->bindValue(':created_by', $vacancy->getLastModifiedByUserId());
            $statement->bindValue(':last_mod', $vacancy->getLastModified());
            $statement->bindValue(':last_mod_by', $vacancy->getLastModifiedByUserId());
            $statement->bindValue(':selector', $vacancy->getSelector());

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

    public function getVacancy($selector):?Vacancy
    {
        $vacancy = null;
        $statement = null;

        try {
            $query = "SELECT d.id,d.organization_id,d.title,d.post,d.uploaded_file_name,d.record_status
                ,date_format(d.start_date,'%d/%m/%Y') AS _start_date 
                ,date_format(d.end_date,'%d/%m/%Y') AS _end_date, d.selector 
                ,o.organization_name,o.establishment_code 
                FROM vacancy d 
                JOIN organization o ON d.organization_id = o.id 
                WHERE d.selector=:selector";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':selector', $selector);
            $statement->execute();

            $record = $statement->fetch(PDO::FETCH_ASSOC);
            if ($record) {
                $vacancy = new Vacancy();
                $vacancy->setId($record['id']);
                $vacancy->setOrganizationId($record['organization_id']);
                $vacancy->setOrganizationName($record['organization_name']);
                $vacancy->setOrganizationEstablishmentCode($record['establishment_code']);
                $vacancy->setTitle($record['title']);
                $vacancy->setVacancyPost($record['post']);
                $vacancy->setUploadedFileName($record['uploaded_file_name']);
                $vacancy->setStartDate($record['_start_date']);
                $vacancy->setEndDate($record['_end_date']);
                $vacancy->setRecordStatus($record['record_status']);
                $vacancy->setSelector($record['selector']);

                $fileUploadHelper = new FileUploadHelper();
                $httpUrlPrefix = null;
                if ($vacancy->getRecordStatus() == AppConstants::ACTIVE) {
                    $httpUrlPrefix = $fileUploadHelper->getPublicVacancyUploadUrl();
                }else{
                    $httpUrlPrefix = $fileUploadHelper->getPublicTrashUrl();
                }

                $vacancy->setUploadedFilePreviewUrl( $httpUrlPrefix . $vacancy->getOrganizationEstablishmentCode() . '/' . $vacancy->getUploadedFileName());

            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $vacancy;
    }

    public function editVacancy(Vacancy $vacancy): bool
    {
        $outcome = false;
        $statement = null;

        try {

            $this->connection->beginTransaction();

            //now update
            $query = "UPDATE vacancy 
                SET 
                title=:title
                ,post=:post
                ,start_date=:start_date
                ,end_date=:end_date
                ,last_mod=:last_mod
                ,last_mod_by=:last_mod_by
                WHERE selector=:selector";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':title', $vacancy->getTitle());
            $statement->bindValue(':post', $vacancy->getVacancyPost());
            $statement->bindValue(':start_date', $this->getDateBetweenFormats($vacancy->getStartDate(), 'd/m/Y', 'Y-m-d'));
            $statement->bindValue(':end_date', $this->getDateBetweenFormats($vacancy->getEndDate(), 'd/m/Y', 'Y-m-d'));
            $statement->bindValue(':last_mod', $vacancy->getLastModified());
            $statement->bindValue(':last_mod_by', $vacancy->getLastModifiedByUserId());
            $statement->bindValue(':selector', $vacancy->getSelector());
            $statement->execute();

            //update filename if exists
            if ($vacancy->getUploadedFileName()) {
                $query = "UPDATE vacancy 
                SET 
                uploaded_file_name=:uploaded_file_name
                WHERE selector=:selector";

                $statement = $this->connection->prepare($query);
                $statement->bindValue(':uploaded_file_name', $vacancy->getUploadedFileName());
                $statement->bindValue(':selector', $vacancy->getSelector());
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

    public function deleteVacancy(Vacancy $vacancy): bool
    {
        $outcome = false;
        $statement = null;

        try {

            //now delete
            $query = "UPDATE vacancy 
                SET 
                record_status=:record_status
                ,last_mod=:last_mod
                ,last_mod_by=:last_mod_by
                WHERE selector=:selector";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':record_status', $vacancy->getRecordStatus());
            $statement->bindValue(':last_mod', $vacancy->getLastModified());
            $statement->bindValue(':last_mod_by', $vacancy->getLastModifiedByUserId());
            $statement->bindValue(':selector', $vacancy->getSelector());
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