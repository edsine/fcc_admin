<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 2:11 PM
 */

namespace AppBundle\Services\SuperAdmin\Organization;


use AppBundle\AppException\AppException;
use AppBundle\Model\Organizations\Committee;
use AppBundle\Model\Organizations\CommitteeMember;
use AppBundle\Model\Organizations\CommitteeOrganization;
use AppBundle\Model\SearchCriteria\CommitteeSearchCriteria;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\Paginator;
use Doctrine\DBAL\Connection;
use \PDO;
use \Throwable;

class CommitteeService
{
    private $connection;
    private $rows_per_page;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->rows_per_page = AppConstants::ROWS_PER_PAGE;
    }


    public function searchRecords(CommitteeSearchCriteria $searchCriteria, Paginator $paginator, $pageDirection) : array
    {
        $committees = array();
        $statement = null;

        try {

            $searchName = $searchCriteria->getName();
            $searchStatus = $searchCriteria->getStatus();

            $where = array("d.id > 0");

            if ($searchName) {
                $where[] = "d.committee_name LIKE :committee_name";
            }
            if ($searchStatus) {
                $where[] = "d.record_status = :record_status";
            }

            if ($where) {
                $where = implode(" AND ", $where);
            } else {
                $where = '';
            }

            //fetch total matching rows
            $countQuery = "SELECT COUNT(d.id) AS totalRows FROM committees d WHERE $where";
            $statement = $this->connection->prepare($countQuery);
            if ($searchName) {
                $statement->bindValue(':committee_name', "%" . $searchName . "%");
            }
            if ($searchStatus) {
                $statement->bindValue(':record_status', $searchStatus);
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
            $query = "SELECT d.id,d.committee_name,d.record_status,d.guid 
                FROM committees d 
                WHERE $where order by d.committee_name LIMIT $limitStartRow ,$this->rows_per_page" ;
            $statement = $this->connection->prepare($query);

            if ($searchName) {
                $statement->bindValue(':committee_name', "%" . $searchName . "%");
            }
            if ($searchStatus) {
                $statement->bindValue(':record_status', $searchStatus);
            }

            $statement->execute();

            $records = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($records) {

                $displaySerialNo = $paginator->getStartRow() + 1;

                for ($i = 0; $i < count($records); $i++) {

                    $record = $records[$i];

                    $committee = new Committee();
                    $committee->setId($record['id']);
                    $committee->setName($record['committee_name']);
                    $committee->setStatus($record['record_status']);
                    $committee->setGuid($record['guid']);

                    $committee->setDisplaySerialNo($displaySerialNo);
                    $displaySerialNo++;

                    $committees[] = $committee;
                }
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $committees;
    }

    public function addCommittee(Committee $committee) : bool
    {
        $outcome = false;
        $statement = null;

        try {
            //check duplicate name
            $query = "SELECT id FROM committees WHERE committee_name=:committee_name LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':committee_name', $committee->getName());
            $statement->execute();
            $existingCommittee = $statement->fetch();

            if ($existingCommittee) {
                throw new AppException(AppConstants::DUPLICATE_DESC);
            }

            //check duplicate guid
            $query = "SELECT id FROM committees WHERE guid=:guid LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':guid', $committee->getGuid());
            $statement->execute();
            $existingCommittee = $statement->fetch();

            if ($existingCommittee) {
                throw new AppException(AppConstants::DUPLICATE_GUID);
            }

            $this->connection->beginTransaction();

            //now insert
            $query = "INSERT INTO committees 
                (committee_name,chairman_user_profile_id,secretary_user_profile_id,record_status,created,created_by,last_mod,last_mod_by,guid) 
                VALUES 
                (:committee_name,:chairman_user_profile_id,:secretary_user_profile_id,:record_status,:created,:created_by,:last_mod,:last_mod_by,:guid)";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':committee_name', $committee->getName());
            $statement->bindValue(':chairman_user_profile_id', $committee->getChairmanUserId());
            $statement->bindValue(':secretary_user_profile_id', $committee->getSecretaryUserId());
            $statement->bindValue(':record_status', $committee->getStatus());
            $statement->bindValue(':created', $committee->getLastModified());
            $statement->bindValue(':created_by', $committee->getLastModifiedByUserId());
            $statement->bindValue(':last_mod', $committee->getLastModified());
            $statement->bindValue(':last_mod_by', $committee->getLastModifiedByUserId());
            $statement->bindValue(':guid', $committee->getGuid());

            $statement->execute();

            //get the just inserted id
            $query = "select id from committees where guid=:guid";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':guid', $committee->getGuid());
            $statement->execute();

            $committeeId = $statement->fetchColumn(0);

            //UPDATE MEMBERS
            $query = "DELETE FROM committee_members WHERE committee_id=:committee_id ";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':committee_id',$committeeId);
            $statement->execute();

            if ($committee->getCommitteeMemberIds()) {
                $batchRecords = array();
                foreach ($committee->getCommitteeMemberIds() as $committeeMemberId) {

                    $batchRecord = array();

                    $batchRecord['committee_id'] = $committeeId;
                    $batchRecord['staff_user_profile_id'] = $committeeMemberId;

                    $batchRecords[] = $batchRecord;
                }

                $this->pdoMultiInsert('committee_members', $batchRecords);
            }

            //UPDATE MDAS
            if($committee->getCommitteeMdaIds()){
                //get the mdas in string
                $committeeMdaIdsInString = implode(',', $committee->getCommitteeMdaIds());

                $query = "update organization
                    set
                    fcc_committee_id = :fcc_committee_id
                    where id in ($committeeMdaIdsInString)";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':fcc_committee_id',$committeeId);
                $statement->execute();
            }


            $this->connection->commit();

            $outcome = true;

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

    public function getCommittee($guid) : Committee
    {
        $committee = null;
        $statement = null;

        try {
            $query = "SELECT d.id,d.committee_name,d.chairman_user_profile_id,d.secretary_user_profile_id
                ,d.record_status,d.guid 
                FROM committees d 
                WHERE d.guid=:guid ";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':guid', $guid);
            $statement->execute();

            $record = $statement->fetch(PDO::FETCH_ASSOC);
            if ($record) {
                $committee = new Committee();
                $committee->setId($record['id']);
                $committee->setName($record['committee_name']);
                $committee->setChairmanUserId($record['chairman_user_profile_id']);
                $committee->setSecretaryUserId($record['secretary_user_profile_id']);
                $committee->setName($record['committee_name']);
                $committee->setStatus($record['record_status']);
                $committee->setGuid($record['guid']);

                //get the members
                $query = "select committee_member.committee_id
                      ,committee.committee_name
                      ,committee_member.staff_user_profile_id 
                      ,user_profile.first_name,user_profile.last_name
                      from committee_members as committee_member
                      join committees as committee on committee_member.committee_id = committee.id
                      join user_profile as user_profile on committee_member.staff_user_profile_id = user_profile.id 
                      where committee_member.committee_id = :committee_id";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':committee_id', $committee->getId());
                $statement->execute();

                $committeeMemberRecords = $statement->fetchAll();

                $committeeMembers = array();
                $committeeMembersIds = array();

                if($committeeMemberRecords){
                    foreach($committeeMemberRecords as $committeeMemberRecord){
                        $committeeMember = new CommitteeMember();
                        $committeeMember->setCommitteeId($committeeMemberRecord['committee_id']);
                        $committeeMember->setCommitteeName($committeeMemberRecord['committee_name']);
                        $committeeMember->setUserProfileId($committeeMemberRecord['staff_user_profile_id']);
                        $committeeMember->setFirstName($committeeMemberRecord['first_name']);
                        $committeeMember->setLastName($committeeMemberRecord['last_name']);

                        $committeeMembersIds[] = $committeeMember->getUserProfileId();
                        $committeeMembers[] = $committeeMember;
                    }
                }

                $committee->setCommitteeMembers($committeeMembers);
                $committee->setCommitteeMemberIds($committeeMembersIds);

                //fetch the mdas
                $query = "select organization.id as organization_id, organization.organization_name
                      ,organization.fcc_committee_id
                      ,committee.committee_name
                      from organization as organization
                      join committees as committee on organization.fcc_committee_id = committee.id
                      where organization.fcc_committee_id = :committee_id";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':committee_id', $committee->getId());
                $statement->execute();

                $committeeMdaRecords = $statement->fetchAll();

                $committeeMdas = array();
                $committeeMdasIds = array();

                if($committeeMdaRecords){
                    foreach($committeeMdaRecords as $committeeMdaRecord){
                        $committeeMda = new CommitteeOrganization();
                        $committeeMda->setCommitteeId($committeeMdaRecord['fcc_committee_id']);
                        $committeeMda->setCommitteeName($committeeMdaRecord['committee_name']);
                        $committeeMda->setOrganizationId($committeeMdaRecord['organization_id']);
                        $committeeMda->setOrganizationName($committeeMdaRecord['organization_name']);

                        $committeeMdasIds[] = $committeeMda->getOrganizationId();
                        $committeeMdas[] = $committeeMda;
                    }
                }

                $committee->setCommitteeMdas($committeeMdas);
                $committee->setCommitteeMdaIds($committeeMdasIds);
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $committee;
    }

    public function editCommittee(Committee $committee) : bool
    {
        $outcome = false;
        $statement = null;

        try {
            //check duplicate name
            $query = "SELECT id FROM committees WHERE committee_name=:committee_name AND guid<>:guid LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':committee_name', $committee->getName());
            $statement->bindValue(':guid', $committee->getGuid());
            $statement->execute();
            $existingCommittee = $statement->fetch();

            if ($existingCommittee) {
                throw new AppException(AppConstants::DUPLICATE_DESC);
            }

            $this->connection->beginTransaction();

            //now update
            $query = "UPDATE committees 
                SET 
                committee_name=:committee_name 
                ,chairman_user_profile_id=:chairman_user_profile_id
                ,secretary_user_profile_id=:secretary_user_profile_id
                ,last_mod=:last_mod,last_mod_by=:last_mod_by  
                WHERE guid=:guid";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':committee_name', $committee->getName());
            $statement->bindValue(':chairman_user_profile_id', $committee->getChairmanUserId());
            $statement->bindValue(':secretary_user_profile_id', $committee->getSecretaryUserId());
            $statement->bindValue(':last_mod', $committee->getLastModified());
            $statement->bindValue(':last_mod_by', $committee->getLastModifiedByUserId());
            $statement->bindValue(':guid', $committee->getGuid());
            $statement->execute();

            //get the id of this committee
            $query = "select id from committees where guid=:guid";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':guid', $committee->getGuid());
            $statement->execute();

            $committeeId = $statement->fetchColumn(0);

            //UPDATE MEMBERS
            $query = "DELETE FROM committee_members WHERE committee_id=:committee_id ";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':committee_id',$committeeId);
            $statement->execute();

            if ($committee->getCommitteeMemberIds()) {
                $batchRecords = array();
                foreach ($committee->getCommitteeMemberIds() as $committeeMemberId) {

                    $batchRecord = array();

                    $batchRecord['committee_id'] = $committeeId;
                    $batchRecord['staff_user_profile_id'] = $committeeMemberId;

                    $batchRecords[] = $batchRecord;
                }

                $this->pdoMultiInsert('committee_members', $batchRecords);
            }

            //UPDATE MDAS

            $query = "update organization
                    set
                    fcc_committee_id = null
                    where fcc_committee_id=:fcc_committee_id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':fcc_committee_id',$committeeId);
            $statement->execute();

            if($committee->getCommitteeMdaIds()){
                //get the mdas in string
                $committeeMdaIdsInString = implode(',', $committee->getCommitteeMdaIds());

                $query = "update organization
                    set
                    fcc_committee_id = :fcc_committee_id
                    where id in ($committeeMdaIdsInString)";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':fcc_committee_id',$committeeId);
                $statement->execute();
            }
            $this->connection->commit();

            $outcome = true;

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

    public function deleteCommittee(Committee $committee) : bool
    {
        $outcome = false;
        $statement = null;

        try {

            $this->connection->beginTransaction();

            //get the id of this committee
            $query = "select id from committees where guid=:guid";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':guid', $committee->getGuid());
            $statement->execute();

            $committeeId = $statement->fetchColumn(0);

            //now delete
            $query = "UPDATE committees 
                SET record_status=:record_status 
                ,last_mod=:last_mod,last_mod_by=:last_mod_by  
                WHERE guid=:guid";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':record_status', $committee->getStatus());
            $statement->bindValue(':last_mod', $committee->getLastModified());
            $statement->bindValue(':last_mod_by', $committee->getLastModifiedByUserId());
            $statement->bindValue(':guid', $committee->getGuid());
            $statement->execute();

            //CLEAR MEMBERS
            $query = "DELETE FROM committee_members WHERE committee_id=:committee_id ";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':committee_id',$committeeId);
            $statement->execute();

            //UPDATE MDAS
            $query = "update organization
                    set
                    fcc_committee_id = null
                    where fcc_committee_id=:fcc_committee_id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':fcc_committee_id',$committeeId);
            $statement->execute();

            $this->connection->commit();

            $outcome = true;

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

    private function pdoMultiInsert($tableName, $data)
    {
        $outcome = false;
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

        return $outcome;
    }

}