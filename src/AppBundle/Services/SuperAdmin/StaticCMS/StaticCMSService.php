<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 2/21/2017
 * Time: 2:43 PM
 */

namespace AppBundle\Services\SuperAdmin\StaticCMS;

use AppBundle\AppException\AppException;
use AppBundle\Model\StaticCMS\StaticCMS;
use AppBundle\Utils\ServiceHelper;
use Doctrine\DBAL\Connection;
use \PDO;
use \Throwable;


class StaticCMSService extends ServiceHelper
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getStaticContents() : array
    {
        $staticContents = array();
        $statement = null;

        try {
            $query = "SELECT d.content_code,d.description,concat(substring(d.content,1,80),'...') as _content,d.is_rich_text "
                . ",date_format(d.last_mod,'%e-%b-%Y') as _last_mod,d.guid "
                . "FROM static_text_cms d order by d.order_position ";
            $statement = $this->connection->prepare($query);
            $statement->execute();

            $records = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($records) {

                $displaySerialNo = 1;

                for ($i = 0; $i < count($records); $i++) {

                    $record = $records[$i];

                    $staticContent = new StaticCMS();
                    $staticContent->setContentCode($record['content_code']);
                    $staticContent->setDescription($record['description']);
                    $staticContent->setContent(html_entity_decode($record['_content'], ENT_QUOTES|ENT_IGNORE));
                    $staticContent->setRichText($record['is_rich_text']);

                    $staticContent->setLastModified($record['_last_mod']);
                    $staticContent->setGuid($record['guid']);

                    $staticContent->setDisplaySerialNo($displaySerialNo);
                    $displaySerialNo++;

                    $staticContents[] = $staticContent;
                }
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $staticContents;
    }


    public function getStaticContent($guid) : StaticCMS
    {
        $staticContent = null;
        $statement = null;

        try {
            $query = "SELECT d.content_code,d.description,d.content,d.is_rich_text "
                . ",date_format(d.last_mod,'%e-%b-%Y') as _last_mod, d.guid "
                . "FROM static_text_cms d "
                . "WHERE d.guid=:guid ";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':guid', $guid);
            $statement->execute();

            $record = $statement->fetch(PDO::FETCH_ASSOC);
            if ($record) {
                $staticContent = new StaticCMS();
                $staticContent->setContentCode($record['content_code']);
                $staticContent->setDescription($record['description']);
                $staticContent->setContent(html_entity_decode($record['content'], ENT_QUOTES));

                $staticContent->setRichText($record['is_rich_text']);

                $staticContent->setLastModified($record['_last_mod']);
                $staticContent->setGuid($record['guid']);
            }
        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $staticContent;
    }

    public function editStaticContent(StaticCMS $staticContent) : bool
    {
        $outcome = false;
        $statement = null;

        try {
            //now insert
            $query = "UPDATE static_text_cms "
                . " set "
                . "content=:content,last_mod=:last_mod, last_mod_by=:last_mod_by "
                . "where guid=:guid ";

            $statement = $this->connection->prepare($query);

            $contentText = htmlentities($staticContent->getContent(), ENT_QUOTES);

            $statement->bindValue(':content', $this->getValueOrNull($contentText));
            $statement->bindValue(':last_mod', $this->getValueOrNull($staticContent->getLastModified()));
            $statement->bindValue(':last_mod_by', $this->getValueOrNull($staticContent->getLastModifiedByUserId()));
            $statement->bindValue(':guid', $this->getValueOrNull($staticContent->getGuid()));

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