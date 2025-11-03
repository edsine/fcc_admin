<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 2:11 PM
 */

namespace AppBundle\Services\MdaDeskOffice;


use AppBundle\AppException\AppException;
use AppBundle\Model\SearchCriteria\MdaBaselineSearchCriteria;
use AppBundle\Model\SubmissionSetup\MdaBaseline;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\Paginator;
use Doctrine\DBAL\Connection;
use \PDO;
use \Throwable;

class MdaBaselineService
{
    private $connection;
    private $rows_per_page;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->rows_per_page = AppConstants::ROWS_PER_PAGE;
    }


    public function searchRecords(MdaBaselineSearchCriteria $searchCriteria, Paginator $paginator, $pageDirection) : array
    {
        $mdaBaselines = array();
        $statement = null;

        try {

            $searchOrganization = $searchCriteria->getOrganizationId();
            $searchYear = $searchCriteria->getBaselineYear();

            $where = array("d.organization_id > 0");

            if ($searchOrganization) {
                $where[] = "d.organization_id = :organization_id";
            }
            if ($searchYear) {
                $where[] = "d.baseline_year = :baseline_year";
            }

            if ($where) {
                $where = implode(" AND ", $where);
            } else {
                $where = '';
            }

            //fetch total matching rows
            $countQuery = "SELECT COUNT(d.organization_id) AS totalRows FROM mda_baseline_year d WHERE $where";
            $statement = $this->connection->prepare($countQuery);
            if ($searchOrganization) {
                $statement->bindValue(':organization_id', $searchOrganization);
            }
            if ($searchYear) {
                $statement->bindValue(':baseline_year', $searchYear);
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
            $query = "SELECT d.organization_id,d.baseline_year, date_format(d.last_mod,'%e-%b-%Y') as _last_mod, d.selector "
                . ",o.organization_name "
                . "FROM mda_baseline_year d "
                . "JOIN organization o on d.organization_id=o.id "
                . "WHERE $where order by d.committee_code LIMIT $limitStartRow ,$this->rows_per_page ";
            $statement = $this->connection->prepare($query);
            if ($searchOrganization) {
                $statement->bindValue(':organization_id', $searchOrganization);
            }
            if ($searchYear) {
                $statement->bindValue(':baseline_year', "%" . $searchYear . "%");
            }

            $statement->execute();

            $records = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($records) {

                $displaySerialNo = $paginator->getStartRow() + 1;

                for ($i = 0; $i < count($records); $i++) {

                    $record = $records[$i];

                    $mdaBaseline = new MdaBaseline();
                    $mdaBaseline->setOrganizationId($record['organization_id']);
                    $mdaBaseline->setOrganizationName($record['organization_name']);
                    $mdaBaseline->setBaselineYear($record['baseline_year']);
                    $mdaBaseline->setLastModified($record['_last_mod']);
                    $mdaBaseline->setSelector($record['selector']);

                    $mdaBaseline->setDisplaySerialNo($displaySerialNo);
                    $displaySerialNo++;

                    $mdaBaselines[] = $mdaBaseline;
                }
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $mdaBaselines;
    }

    public function addMdaBaseline(MdaBaseline $mdaBaseline) : bool
    {
        $outcome = false;
        $statement = null;

        try {

            //check for duplicate
            $query = "SELECT organization_id FROM mda_baseline_year WHERE organization_id=:organization_id LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':organization_id', $mdaBaseline->getOrganizationId());
            $statement->execute();
            $existingRecord = $statement->fetch();

            if ($existingRecord) {
                throw new AppException(AppConstants::DUPLICATE);
            }

            //now insert
            $query = "INSERT INTO mda_baseline_year 
                (
                organization_id,year_of_establishment,baseline_year,created,created_by,last_mod,last_mod_by,selector
                ) 
                VALUES 
                (
                :organization_id,:year_of_establishment,:baseline_year,:created,:created_by,:last_mod,:last_mod_by,:selector
                )";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':organization_id', $mdaBaseline->getOrganizationId());
            $statement->bindValue(':year_of_establishment', $mdaBaseline->getYearOfEstablishment());
            $statement->bindValue(':baseline_year', $mdaBaseline->getBaselineYear());
            $statement->bindValue(':created', $mdaBaseline->getLastModified());
            $statement->bindValue(':created_by', $mdaBaseline->getLastModifiedByUserId());
            $statement->bindValue(':last_mod', $mdaBaseline->getLastModified());
            $statement->bindValue(':last_mod_by', $mdaBaseline->getLastModifiedByUserId());
            $statement->bindValue(':selector', $mdaBaseline->getSelector());

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

    public function getMdaBaselineBySelector($selector)
    {
        $mdaBaseline = null;
        $statement = null;

        try {
            $query = "SELECT d.organization_id,d.year_of_establishment,d.baseline_year
                ,date_format(d.last_mod,'%e-%b-%Y') as _last_mod, d.selector 
                ,o.organization_name 
                FROM mda_baseline_year d 
                JOIN organization o on d.organization_id=o.id 
                WHERE d.selector=:selector ";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':selector', $selector);
            $statement->execute();

            $record = $statement->fetch(PDO::FETCH_ASSOC);
            if ($record) {
                $mdaBaseline = new MdaBaseline();
                $mdaBaseline->setOrganizationId($record['organization_id']);
                $mdaBaseline->setOrganizationName($record['organization_name']);
                $mdaBaseline->setYearOfEstablishment($record['year_of_establishment']);
                $mdaBaseline->setConfirmYearOfEstablishment($mdaBaseline->getYearOfEstablishment());
                $mdaBaseline->setBaselineYear($record['baseline_year']);
                $mdaBaseline->setConfirmBaselineYear($mdaBaseline->getBaselineYear());
                $mdaBaseline->setLastModified($record['_last_mod']);
                $mdaBaseline->setSelector($record['selector']);
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $mdaBaseline;
    }

    public function getMdaBaselineByOrganization($organizationId)
    {
        $mdaBaseline = null;
        $statement = null;

        try {
            $query = "SELECT d.organization_id,d.year_of_establishment,d.baseline_year
                ,date_format(d.last_mod,'%e-%b-%Y') as _last_mod, d.selector 
                ,o.organization_name 
                FROM mda_baseline_year d 
                JOIN organization o on d.organization_id=o.id 
                WHERE d.organization_id=:organization_id ";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':organization_id', $organizationId);
            $statement->execute();

            $record = $statement->fetch(PDO::FETCH_ASSOC);
            if ($record) {
                $mdaBaseline = new MdaBaseline();
                $mdaBaseline->setOrganizationId($record['organization_id']);
                $mdaBaseline->setOrganizationName($record['organization_name']);
                $mdaBaseline->setYearOfEstablishment($record['year_of_establishment']);
                $mdaBaseline->setConfirmYearOfEstablishment($mdaBaseline->getYearOfEstablishment());
                $mdaBaseline->setBaselineYear($record['baseline_year']);
                $mdaBaseline->setConfirmBaselineYear($mdaBaseline->getBaselineYear());
                $mdaBaseline->setLastModified($record['_last_mod']);
                $mdaBaseline->setSelector($record['selector']);
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $mdaBaseline;
    }

    public function editMdaBaseline(MdaBaseline $mdaBaseline) : bool
    {
        $outcome = false;
        $statement = null;

        try {
            //now update
            $query = "UPDATE mda_baseline_year 
                SET 
                year_of_establishment=:year_of_establishment
                ,baseline_year=:baseline_year 
                ,last_mod=:last_mod,last_mod_by=:last_mod_by  
                WHERE selector=:selector";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':year_of_establishment', $mdaBaseline->getYearOfEstablishment());
            $statement->bindValue(':baseline_year', $mdaBaseline->getBaselineYear());
            $statement->bindValue(':last_mod', $mdaBaseline->getLastModified());
            $statement->bindValue(':last_mod_by', $mdaBaseline->getLastModifiedByUserId());
            $statement->bindValue(':selector', $mdaBaseline->getSelector());

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

    public function checkOrganizationBaseline($organizationId) :?bool
    {
        $outcome = false;
        $statement = null;

        try {
            $query = "SELECT d.year_of_establishment,d.baseline_year
                FROM mda_baseline_year d 
                WHERE d.organization_id=:organization_id ";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':organization_id', $organizationId);
            $statement->execute();

            $record = $statement->fetch(PDO::FETCH_ASSOC);
            if ($record) {
                $yearOfEstablishment = $record['year_of_establishment'];
                $baselineYear = $record['baseline_year'];
                if($yearOfEstablishment && $baselineYear){
                    $outcome = true;
                }
            }

        } catch (Throwable $e) {
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $outcome;
    }

    public function getMdaSubmissionYears($baseLineYear = 2008) :?array
    {
        $mdaSubmissionYears = array();

        if($baseLineYear){
            $presentYear = date('Y');

            for($i = $presentYear; $i >= $baseLineYear; $i--){
                $mdaSubmissionYears[] = $i;
            }
        }

        return $mdaSubmissionYears;
    }

}