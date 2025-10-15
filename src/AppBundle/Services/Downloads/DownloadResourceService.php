<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/2/2018
 * Time: 3:05 PM
 */

namespace AppBundle\Services\Downloads;


use AppBundle\AppException\AppExceptionMessages;
use AppBundle\Model\Document\AttachedDocument;
use AppBundle\Model\Download\DownloadResource;
use AppBundle\Model\Download\DownloadResourceCategory;
use AppBundle\Utils\ServiceHelper;
use AppBundle\AppException\AppException;
use AppBundle\Utils\AppConstants;
use Doctrine\DBAL\Connection;
use \Throwable;
use \PDO;

class DownloadResourceService extends ServiceHelper
{

    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function addDownloadResource(DownloadResource $downloadResource)
    {
        $outcome = false;
        $statement = null;

        try {
            //check for duplicate title
            $query = "SELECT id FROM downloads WHERE title=:title LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':title', $downloadResource->getTitle());
            $statement->execute();
            $existingRecord = $statement->fetch();

            if ($existingRecord) {
                throw new AppException(AppExceptionMessages::DUPLICATE_TITLE);
            }

            //check for duplicate description
            $query = "SELECT id FROM downloads WHERE description=:description LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':description', $downloadResource->getDescription());
            $statement->execute();
            $existingRecord = $statement->fetch();

            if ($existingRecord) {
                throw new AppException(AppExceptionMessages::DUPLICATE_DESCRIPTION);
            }

            //check for duplicate file name
            $query = "SELECT id FROM downloads WHERE attachment_file_name=:attachment_file_name LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':attachment_file_name', $downloadResource->getAttachment()->getFileName());
            $statement->execute();
            $existingRecord = $statement->fetch();

            if ($existingRecord) {
                throw new AppException(AppExceptionMessages::DUPLICATE_FILE_NAME);
            }

            //insert
            $query = "INSERT INTO downloads 
                (
                title,description,attachment_file_name,download_category_id
                ,record_status,created,created_by,last_mod,last_mod_by,selector
                )
                 VALUES 
                 (
                 :title,:description,:attachment_file_name,:download_category_id
                ,:record_status,:created,:created_by,:last_mod,:last_mod_by,:selector
                )";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':title', $this->getValueOrNull($downloadResource->getTitle()));
            $statement->bindValue(':description', $this->getValueOrNull($downloadResource->getDescription()));
            $statement->bindValue(':attachment_file_name', $this->getValueOrNull($downloadResource->getAttachment()->getFileName()));
            $statement->bindValue(':download_category_id', $this->getValueOrNull($downloadResource->getCategoryId()));
            $statement->bindValue(':record_status', $this->getValueOrNull($downloadResource->getRecordStatus()));
            $statement->bindValue(':created', $this->getValueOrNull($downloadResource->getLastModified()));
            $statement->bindValue(':created_by', $this->getValueOrNull($downloadResource->getLastModifiedByUserId()));
            $statement->bindValue(':last_mod', $this->getValueOrNull($downloadResource->getLastModified()));
            $statement->bindValue(':last_mod_by', $this->getValueOrNull($downloadResource->getLastModifiedByUserId()));
            $statement->bindValue(':selector', $this->getValueOrNull($downloadResource->getSelector()));

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

    public function getDownloadResources($levelOfGovernment)
    {
        $downloadResources = array();
        $statement = null;
        try {

            //fetch the categories
            $query = "SELECT id, title 
                      FROM download_categories 
                      WHERE level_of_government=:level_of_government AND record_status=:record_status";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':level_of_government', $levelOfGovernment);
            $statement->bindValue(':record_status', AppConstants::ACTIVE);
            $statement->execute();

            $categoryRecords = $statement->fetchAll();

            if ($categoryRecords) {

                $_tempCategoryMap = array();

                foreach ($categoryRecords as $categoryRecord) {
                    $downloadCategory = new DownloadResourceCategory();
                    $downloadCategory->setId($categoryRecord['id']);
                    $downloadCategory->setTitle($categoryRecord['title']);

                    $_tempCategoryMap[$downloadCategory->getId()] = $downloadCategory;
                }

                //select the downloads under this level of government
                $query = "SELECT download.id AS download_id, download.title , download.description
                      , download.attachment_file_name, download.download_category_id, download.selector
                      FROM downloads download
                      JOIN download_categories category ON download.download_category_id = category.id
                      WHERE category.level_of_government=:level_of_government AND download.record_status=:record_status";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':level_of_government', $levelOfGovernment);
                $statement->bindValue(':record_status', AppConstants::ACTIVE);
                $statement->execute();

                $downloadRecords = $statement->fetchAll();

                if ($downloadRecords) {
                    foreach ($downloadRecords as $downloadRecord) {
                        $downloadResource = new DownloadResource();
                        $downloadResource->setId($downloadRecord['download_id']);
                        $downloadResource->setTitle($downloadRecord['title']);
                        $downloadResource->setDescription($downloadRecord['description']);
                        $downloadResource->setCategoryId($downloadRecord['download_category_id']);

                        $attachment = new AttachedDocument($downloadRecord['title'], $downloadRecord['attachment_file_name']);
                        $downloadResource->setAttachment($attachment);

                        $downloadResource->setSelector($downloadRecord['selector']);

                        $_tempCategoryMap[$downloadResource->getCategoryId()]->addResource($downloadResource);
                    }
                }

                $downloadResources = array_values($_tempCategoryMap);
            }


        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }
        return $downloadResources;
    }

    public function getDownloadResource($selector)
    {
        $downloadResource = null;
        $statement = null;
        try {
            $query = "SELECT download.id AS download_id, download.title , download.description
                      , download.attachment_file_name, download.download_category_id, download.selector
                      FROM downloads download
                      JOIN download_categories category ON download.download_category_id = category.id
                WHERE download.selector=:selector ";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':selector', $selector);
            $statement->execute();

            $record = $statement->fetch(PDO::FETCH_ASSOC);
            if ($record) {
                $downloadResource = new DownloadResource();
                $downloadResource->setId($record['download_id']);
                $downloadResource->setTitle($record['title']);
                $downloadResource->setDescription($record['description']);
                $downloadResource->setCategoryId($record['download_category_id']);

                $attachment = new AttachedDocument($record['title'], $record['attachment_file_name']);
                $downloadResource->setAttachment($attachment);

                $downloadResource->setSelector($record['selector']);
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }
        return $downloadResource;
    }

    public function editDownloadResource(DownloadResource $downloadResource)
    {
        $outcome = false;
        $statement = null;

        try {

            $this->connection->beginTransaction();

            //check for duplicate title
            $query = "SELECT id FROM downloads WHERE title=:title AND selector<>:selector LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':title', $downloadResource->getTitle());
            $statement->bindValue(':selector', $downloadResource->getSelector());
            $statement->execute();
            $existingRecord = $statement->fetch();

            if ($existingRecord) {
                throw new AppException(AppExceptionMessages::DUPLICATE_TITLE);
            }

            //check for duplicate description
            $query = "SELECT id FROM downloads WHERE description=:description AND selector<>:selector LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':description', $downloadResource->getDescription());
            $statement->bindValue(':selector', $downloadResource->getSelector());
            $statement->execute();
            $existingRecord = $statement->fetch();

            if ($existingRecord) {
                throw new AppException(AppExceptionMessages::DUPLICATE_DESCRIPTION);
            }

            //check for duplicate file name
            $query = "SELECT id FROM downloads WHERE attachment_file_name=:attachment_file_name AND selector<>:selector LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':attachment_file_name', $downloadResource->getAttachment()->getFileName());
            $statement->bindValue(':selector', $downloadResource->getSelector());
            $statement->execute();
            $existingRecord = $statement->fetch();

            if ($existingRecord) {
                throw new AppException(AppExceptionMessages::DUPLICATE_FILE_NAME);
            }

            //update
            $query = "UPDATE downloads 
                SET 
                title=:title
                ,description=:description
                ,download_category_id=:download_category_id
                ,last_mod=:last_mod
                ,last_mod_by=:last_mod_by 
                 WHERE selector=:selector";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':title', $this->getValueOrNull($downloadResource->getTitle()));
            $statement->bindValue(':description', $this->getValueOrNull($downloadResource->getDescription()));
            $statement->bindValue(':download_category_id', $this->getValueOrNull($downloadResource->getCategoryId()));
            $statement->bindValue(':last_mod', $this->getValueOrNull($downloadResource->getLastModified()));
            $statement->bindValue(':last_mod_by', $this->getValueOrNull($downloadResource->getLastModifiedByUserId()));
            $statement->bindValue(':selector', $this->getValueOrNull($downloadResource->getSelector()));
            $statement->execute();

            if ($downloadResource->getAttachment()->getFileName()) {
                $query = "UPDATE downloads 
                        SET 
                        attachment_file_name=:attachment_file_name
                         WHERE selector=:selector";

                $statement = $this->connection->prepare($query);
                $statement->bindValue(':attachment_file_name', $this->getValueOrNull($downloadResource->getAttachment()->getFileName()));
                $statement->bindValue(':selector', $this->getValueOrNull($downloadResource->getSelector()));
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

    public function deleteDownloadResource(DownloadResource $downloadResource)
    {
        $outcome = false;
        $statement = null;

        try {

            //delete
            $query = "DELETE FROM downloads WHERE selector=:selector";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':selector', $this->getValueOrNull($downloadResource->getSelector()));

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