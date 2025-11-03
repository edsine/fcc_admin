<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 2:11 PM
 */

namespace AppBundle\Services\Recruitment;


use AppBundle\AppException\AppException;
use AppBundle\AppException\AppExceptionMessages;
use AppBundle\Model\Recruitment\Recruitment;
use AppBundle\Model\SearchCriteria\CandidateSearchCriteria;
use AppBundle\Model\Recruitment\ShortListCandidate;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\Paginator;
use AppBundle\Utils\ServiceHelper;
use Doctrine\DBAL\Connection;
use \PDO;
use \Throwable;

class ShortListCandidatesService extends ServiceHelper
{
    private $connection;
    private $rows_per_page;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->rows_per_page = AppConstants::ROWS_PER_PAGE;
    }


    public function searchRecords(CandidateSearchCriteria $searchCriteria, Paginator $paginator, $pageDirection): array
    {
        $searchResults = array();
        $statement = null;

        try {

            $searchRecruitment = $searchCriteria->getRecruitmentId();
            $searchSurname = $searchCriteria->getSurname();
            $searchPhone = $searchCriteria->getPhoneNumber();
            $searchPostApplied = $searchCriteria->getPostApplied();

            $where = array();

            if(!$searchRecruitment){
                $searchRecruitment = 0;
            }

            if ($searchRecruitment) {
                $where[] = "d.recruitment_id = :recruitment_id";
            }

            if ($searchSurname) {
                $where[] = "d.surname = :surname";
            }

            if ($searchPhone) {
                $where[] = "d.phone_number = :phone_number";
            }

            if ($searchPostApplied) {
                $where[] = "d.post_applied LIKE :post_applied";
            }

            if ($where) {
                $where = implode(" AND ", $where);
            } else {
                $where = '';
            }

            //fetch total matching rows
            $countQuery = "SELECT COUNT(d.id) AS totalRows 
                          FROM recruitment_short_list_candidates d 
                          JOIN recruitment r on d.recruitment_id = r.id
                          JOIN organization o on r.organization_id = o.id 
                          WHERE $where";
            $statement = $this->connection->prepare($countQuery);

            if ($searchRecruitment) {
                $statement->bindValue(':recruitment_id', $searchRecruitment);
            }

            if ($searchSurname) {
                $statement->bindValue(':surname', $searchSurname);
            }

            if ($searchPhone) {
                $statement->bindValue(':phone_number', $searchPhone);
            }

            if ($searchPostApplied) {
                $statement->bindValue(':post_applied', "%$searchPostApplied%");
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
                ,d.serial_no, d.surname, d.first_name, d.other_names, d.date_of_birth, d.address, d.center_state
                , d.phone_number, d.email_address, d.location, d.gender, d.post_applied
                , d.university_of_study, d.course_of_study, d.state_of_origin, d.class_of_degree
                , d.selector 
                , r.organization_id,r.recruitment_year, r.selector as recruitment_selector
                , c.description as recruitment_category_name
                , o.organization_name , o.guid as organization_selector
                FROM recruitment_short_list_candidates d 
                JOIN recruitment r on d.recruitment_id = r.id
                JOIN recruitment_category_types c on r.recruitment_category_id = c.id 
                JOIN organization o on r.organization_id = o.id 
                WHERE $where order by d.created desc LIMIT $limitStartRow ,$this->rows_per_page";
            $statement = $this->connection->prepare($query);

            if ($searchRecruitment) {
                $statement->bindValue(':recruitment_id', $searchRecruitment);
            }

            if ($searchSurname) {
                $statement->bindValue(':surname', $searchSurname);
            }

            if ($searchPhone) {
                $statement->bindValue(':phone_number', $searchPhone);
            }

            if ($searchPostApplied) {
                $statement->bindValue(':post_applied', "%$searchPostApplied%");
            }

            $statement->execute();

            $records = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($records) {
                $displaySerialNo = $paginator->getStartRow() + 1;

                for ($i = 0; $i < count($records); $i++) {

                    $record = $records[$i];

                    $candidate = new ShortListCandidate();
                    $candidate->setId($record['id']);
                    $candidate->setRecruitmentId($record['recruitment_id']);
                    $candidate->setRecruitmentSelector($record['recruitment_selector']);
                    $candidate->setRecruitmentYear($record['recruitment_year']);
                    $candidate->setSerialNo($record['serial_no']);
                    $candidate->setSurname($record['surname']);
                    $candidate->setFirstName($record['first_name']);
                    $candidate->setOtherNames($record['other_names']);
                    $candidate->setDateOfBirth($record['date_of_birth']);
                    $candidate->setAddress($record['address']);
                    $candidate->setCenterState($record['center_state']);
                    $candidate->setPhoneNumber($record['phone_number']);
                    $candidate->setEmailAddress($record['email_address']);
                    $candidate->setLocation($record['location']);
                    $candidate->setGender($record['gender']);
                    $candidate->setPostApplied($record['post_applied']);
                    $candidate->setUniversity($record['university_of_study']);
                    $candidate->setCourse($record['course_of_study']);
                    $candidate->setStateOfOrigin($record['state_of_origin']);
                    $candidate->setClassOfDegree($record['class_of_degree']);

                    $candidate->setSelector($record['selector']);
                    
                    $candidate->setDisplaySerialNo($displaySerialNo);
                    $displaySerialNo++;

                    $searchResults[] = $candidate;
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

    public function getShortListCandidate($selector):?ShortListCandidate
    {
        $candidate = null;
        $statement = null;

        try {
            $query = "SELECT d.id,d.recruitment_id
                ,d.serial_no, d.surname, d.first_name, d.other_names, d.date_of_birth, d.address, d.center_state
                , d.phone_number, d.email_address, d.location, d.gender, d.post_applied
                , d.university_of_study, d.course_of_study, d.state_of_origin, d.class_of_degree
                , d.selector 
                , r.organization_id,r.recruitment_year, r.selector as recruitment_selector
                , c.description as recruitment_category_name
                , o.organization_name , o.guid as organization_selector
                FROM recruitment_short_list_candidates d 
                JOIN recruitment r on d.recruitment_id = r.id
                JOIN recruitment_category_types c on r.recruitment_category_id = c.id 
                JOIN organization o on r.organization_id = o.id 
                WHERE d.selector=:selector";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':selector', $selector);
            $statement->execute();

            $record = $statement->fetch(PDO::FETCH_ASSOC);
            if ($record) {
                $candidate = new ShortListCandidate();
                $candidate->setId($record['id']);
                $candidate->setRecruitmentId($record['recruitment_id']);
                $candidate->setRecruitmentSelector($record['recruitment_selector']);
                $candidate->setRecruitmentYear($record['recruitment_year']);
                $candidate->setSerialNo($record['serial_no']);
                $candidate->setSurname($record['surname']);
                $candidate->setFirstName($record['first_name']);
                $candidate->setOtherNames($record['other_names']);
                $candidate->setDateOfBirth($record['date_of_birth']);
                $candidate->setAddress($record['address']);
                $candidate->setCenterState($record['center_state']);
                $candidate->setPhoneNumber($record['phone_number']);
                $candidate->setEmailAddress($record['email_address']);
                $candidate->setLocation($record['location']);
                $candidate->setGender($record['gender']);
                $candidate->setPostApplied($record['post_applied']);
                $candidate->setUniversity($record['university_of_study']);
                $candidate->setCourse($record['course_of_study']);
                $candidate->setStateOfOrigin($record['state_of_origin']);
                $candidate->setClassOfDegree($record['class_of_degree']);

                $candidate->setSelector($record['selector']);
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $candidate;
    }

    public function importShortListMultiInsert($shortListCandidates, Recruitment $recruitment): int
    {
        $affectedRows = 0;
        $statement = null;

        try {

            //start transaction
            $this->connection->beginTransaction();

            $query = "delete FROM recruitment_short_list_candidates WHERE recruitment_id=:recruitment_id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':recruitment_id', $recruitment->getId());
            $statement->execute();


            //PREPARE THE DETAILED BATCH DATA
            $batchCandidateData = array();
            foreach ($shortListCandidates as $candidate) {
                $candidateDataRecord = array();

                $candidateDataRecord['recruitment_id'] = $this->getValueOrNull($candidate[0]);
                $candidateDataRecord['serial_no'] = $this->getValueOrNull($candidate[1]);

                $candidateDataRecord['surname'] = $this->getValueOrNull($candidate[2]);
                $candidateDataRecord['first_name'] = $this->getValueOrNull($candidate[3]);
                $candidateDataRecord['employee_number'] = $this->getValueOrNull($candidate[4]);
                $candidateDataRecord['other_names'] = $this->getValueOrNull($candidate[5]);
                $candidateDataRecord['date_of_birth'] = $this->getValueOrNull($candidate[6]);
                $candidateDataRecord['address'] = $this->getValueOrNull($candidate[7]);
                $candidateDataRecord['center_state'] = $this->getValueOrNull($candidate[8]);
                $candidateDataRecord['phone_number'] = $this->getValueOrNull($candidate[9]);
                $candidateDataRecord['email_address'] = $this->getValueOrNull($candidate[10]);
                $candidateDataRecord['location'] = $this->getValueOrNull($candidate[11]);
                $candidateDataRecord['gender'] = $this->getValueOrNull($candidate[12]);
                $candidateDataRecord['post_applied'] = $this->getValueOrNull($candidate[13]);
                $candidateDataRecord['university_of_study'] = $this->getValueOrNull($candidate[14]);
                $candidateDataRecord['course_of_study'] = $this->getValueOrNull($candidate[15]);
                $candidateDataRecord['state_of_origin'] = $this->getValueOrNull($candidate[16]);
                $candidateDataRecord['class_of_degree'] = $this->getValueOrNull($candidate[17]);
                $candidateDataRecord['created'] = $this->getValueOrNull($candidate[18]);
                $candidateDataRecord['created_by'] = $this->getValueOrNull($candidate[18]);
                $candidateDataRecord['last_mod'] = $this->getValueOrNull($candidate[18]);
                $candidateDataRecord['last_mod_by'] = $this->getValueOrNull($candidate[18]);
                $candidateDataRecord['selector'] = $this->getValueOrNull($candidate[18]);

                $batchCandidateData[] = $candidateDataRecord;
            }

            $outcome = $this->pdoMultiInsertReturnTotal('recruitment_short_list_candidates', $batchCandidateData);

            $query = "SELECT count(*) FROM recruitment_short_list_candidates WHERE recruitment_id=:recruitment_id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':recruitment_id', $recruitment->getId());
            $statement->execute();

            $affectedRows = $statement->fetchColumn(0);


            $query = "UPDATE 
                    recruitment 
                    SET short_list_upload_file_name=:short_list_upload_file_name 
                    , short_list_last_uploaded=:short_list_last_uploaded 
                    , total_short_list_candidates=:total_short_list_candidates 
                    WHERE selector =:selector";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':short_list_upload_file_name', $this->getValueOrNull($recruitment->getShortListAttachment()->getFileName()));
            $statement->bindValue(':short_list_last_uploaded', $this->getValueOrNull($recruitment->getShortListLastUploaded()));
            $statement->bindValue(':total_short_list_candidates', $affectedRows);
            $statement->bindValue(':selector', $recruitment->getSelector());
            $statement->execute();

            //commit transaction
            $this->connection->commit();

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

        return $affectedRows;
    }

    public function deleteShortList(Recruitment $recruitment): bool
    {
        $outcome = false;
        $statement = null;

        try {

            $this->connection->beginTransaction();

            //check that no invitation to monitor has been sent
            $query = "select recruitment_id from recruitment_monitoring where recruitment_id = :recruitment_id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':recruitment_id', $recruitment->getId());
            $statement->execute();

            $recruitmentMonitoring = $statement->fetch();

            if($recruitmentMonitoring){
                throw new AppException(AppExceptionMessages::OPERATION_NOT_ALLOWED);
            }

            //update the recruitment table
            $query = "UPDATE recruitment 
                SET 
                short_list_upload_file_name= NULL
                ,short_list_last_uploaded= NULL
                ,total_short_list_candidates= NULL
                ,last_mod=:last_mod
                ,last_mod_by=:last_mod_by
                WHERE selector=:selector";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':last_mod', $recruitment->getLastModified());
            $statement->bindValue(':last_mod_by', $recruitment->getLastModifiedByUserId());
            $statement->bindValue(':selector', $recruitment->getSelector());
            $statement->execute();

            //delete the long list candidates
            $query = "delete from recruitment_short_list_candidates where recruitment_id = :recruitment_id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':recruitment_id', $recruitment->getId());
            $statement->execute();

            $outcome = true;
            $this->connection->commit();

        } catch (Throwable $e) {
            if($this->connection->isTransactionActive()){
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

    //************************** MULTI INSERT **********************************
    private function pdoMultiInsertReturnTotal($tableName, $data)
    {
        $totalRows = 0;
        $pdoStatement = null;

        try {
            //Will contain SQL snippets.
            $rowsSQL = array();

            //Will contain the values that we need to bind.
            $toBind = array();

            //Get a list of column names to use in the SQL statement.
            $columnNames = array_keys($data[0]);

            //$_columnNames = print_r($columnNames, true);

            //Loop through our $data array.
            foreach ($data as $arrayIndex => $row) {
                $params = array();
                foreach ($row as $columnName => $columnValue) {
                    $param = ":" . $columnName . '_row_' . $arrayIndex;
                    $params[] = $param;
                    $toBind[$param] = $columnValue;
                }
                $rowsSQL[] = "(" . implode(", ", $params) . ")";
            }

            //$_toBind = print_r($toBind, true);

            //$_rowsSQL = print_r($rowsSQL, true);

            //Construct our SQL statement
            $sql = "INSERT INTO `$tableName` (" . implode(", ", $columnNames) . ") VALUES " . implode(", ", $rowsSQL);

            //Prepare our PDO statement.
            $pdoStatement = $this->connection->prepare($sql);

            //Bind our values.
            foreach ($toBind as $param => $val) {
                $pdoStatement->bindValue($param, $val);
            }

            //Execute our statement (i.e. insert the data).
            $totalRows = $pdoStatement->execute();

            $outcome = true;
        } catch (Throwable $t) {
            throw new AppException($t->getMessage());
        } finally {
            if ($pdoStatement) {
                $pdoStatement->closeCursor();
            }
        }

        return $totalRows;
    }

}