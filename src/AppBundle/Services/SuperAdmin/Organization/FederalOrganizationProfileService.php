<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 2:11 PM
 */

namespace AppBundle\Services\SuperAdmin\Organization;


use AppBundle\AppException\AppException;
use AppBundle\Model\Organizations\Organization;
use AppBundle\Model\SearchCriteria\OrganizationSearchCriteria;
use AppBundle\Model\Security\SecurityUser;
use AppBundle\Model\Users\UserProfile;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\Paginator;
use AppBundle\Utils\ServiceHelper;
use Doctrine\DBAL\Connection;
use \PDO;
use Psr\Log\LoggerInterface;
use \Throwable;

class FederalOrganizationProfileService extends ServiceHelper
{
    private $connection;
    private $rows_per_page;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->rows_per_page = AppConstants::ROWS_PER_PAGE;
    }

    public function getOrganizationProfileUsersByPrivilege($organizationId, $privilege) : array
    {
        $userProfiles = array();
        $statement = null;

        try {
            $query = "SELECT d.id,d.profile_type,d.user_login,d.first_name,d.last_name,d.middle_name,d.email_address "
                . ",d.primary_phone,d.secondary_phone,d.organization_id,d.state_of_posting_id,d.fcc_department_id "
                . ",d.fcc_committee_id,d.fcc_supervisor_id,d.primary_role,d.record_status,d.first_login,d.guid "
                . ",sp.state_code as state_of_posting_code,sp.state_name as state_of_posting_name "
                . ",dept.department_code,dept.department_name,c.committee_code,c.committee_name"
                . ",o.establishment_code,o.establishment_mnemonic,o.organization_name, o.establishment_type_id "
                . ",concat_ws(' ',u.first_name, u.last_name) as supervisor_name, u.primary_phone as supervisor_phone"
                . ",u.email_address as supervisor_email "
                . "FROM user_profile d "
                . "LEFT JOIN states sp on d.state_of_posting_id=sp.id  "
                . "LEFT JOIN departments dept on d.fcc_department_id=dept.id  "
                . "LEFT JOIN committees c on d.fcc_department_id=c.id  "
                . "LEFT JOIN organization o on d.organization_id=o.id  "
                . "LEFT JOIN user_profile u on d.fcc_supervisor_id=u.id  "
                . "WHERE d.organization_id=:organization_id "
                . "AND u.primary_role in "
                . "( "
                . "select p.role_id from system_role_privileges p where p.privilege_id = :privilege_id "
                . ") "
                . "and d.record_status=:record_status";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':organization_id', $organizationId);
            $statement->bindValue(':privilege_id', $privilege);
            $statement->bindValue(':record_status', AppConstants::ACTIVE);
            $statement->execute();

            $records = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($records) {

                $displaySerialNo = 1;

                foreach($records as $record){
                    $userProfile = new UserProfile();
                    $userProfile->setId($record['id']);
                    $userProfile->setProfileType($record['committee_code']);
                    $userProfile->setUsername($record['user_login']);
                    $userProfile->setFirstName($record['first_name']);
                    $userProfile->setLastName($record['last_name']);
                    $userProfile->setEmailAddress($record['email_address']);
                    $userProfile->setPrimaryPhone($record['primary_phone']);
                    $userProfile->setSecondaryPhone($record['secondary_phone']);

                    $userProfile->setOrganizationId($record['organization_id']);
                    $userProfile->setOrganizationName($record['organization_name']);
                    $userProfile->setOrganizationEstablishmentCode($record['establishment_code']);
                    $userProfile->setOrganizationMnemonic($record['establishment_mnemonic']);
                    $userProfile->setOrganizationEstablishmentType($record['establishment_type_id']);

                    $userProfile->setStateOfPostingId($record['state_of_posting_id']);
                    $userProfile->setStateOfPostingCode($record['state_of_posting_code']);
                    $userProfile->setStateOfPostingName($record['state_of_posting_name']);

                    $userProfile->setFccDepartmentId($record['fcc_department_id']);
                    $userProfile->setFccDepartmentCode($record['department_code']);
                    $userProfile->setFccDepartmentName($record['department_name']);

                    $userProfile->setFccCommitteeId($record['fcc_committee_id']);
                    $userProfile->setFccCommitteeCode($record['committee_code']);
                    $userProfile->setFccCommitteeName($record['committee_name']);

                    $userProfile->setFccSupervisorId($record['fcc_supervisor_id']);
                    $userProfile->setFccSupervisorName($record['supervisor_name']);
                    $userProfile->setFccSupervisorPhone($record['supervisor_phone']);
                    $userProfile->setFccSupervisorEmail($record['supervisor_email']);

                    $userProfile->setPrimaryRole($record['primary_role']);
                    $userProfile->setStatus($record['record_status']);
                    $userProfile->setFirstLogin($record['first_login']);
                    $userProfile->setGuid($record['guid']);

                    $userProfile->setDisplaySerialNo($displaySerialNo);
                    $displaySerialNo++;

                    $userProfiles[] = $userProfile;
                }
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $userProfiles;
    }

    public function getStatesWithoutFederalOrganizationUser($federalOrganizationId){

        $states = array();
        $statement = null;
        try {
            $query = "select concat_ws('#',d.id,replace(lower(d.state_name), ' ', '')) as state_details,d.state_name "
                . " from states d "
                . " WHERE d.id NOT IN ( "
                . "    select IFNULL(u.state_of_posting_id,'0') from user_profile u where u.organization_id=:organization_id "
                . "    and u.primary_role=:primary_role "
                . ") "
                . " AND d.record_status = :record_status";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':organization_id', $federalOrganizationId);
            $statement->bindValue(':primary_role', 'ABCD');
            $statement->bindValue(':record_status', 'ACTIVE');

            $statement->execute();

            $states = $statement->fetchAll();
        } catch (Throwable $t) {
        } finally {
            $statement->closeCursor();
        }
        return $states;

    }

    public function addFederalOrganizationStateLevelDeskOfficers($fedMdaStateDeskOfficers, LoggerInterface $logger) : bool
    {
        $outcome = false;
        $statement = null;

        try {

            $batchUserRecords = array();

            foreach ($fedMdaStateDeskOfficers as $fedMdaStateDeskOfficer){

                $stateUserRecord = array();

                $stateUserRecord['profile_type'] =  $this->getValueOrNull($fedMdaStateDeskOfficer->getProfileType());
                $stateUserRecord['user_login'] =  $this->getValueOrNull($fedMdaStateDeskOfficer->getUsername());
                $stateUserRecord['user_pass'] =  $this->getValueOrNull($fedMdaStateDeskOfficer->getPassword());
                $stateUserRecord['organization_id'] =  $this->getValueOrNull($fedMdaStateDeskOfficer->getOrganizationId());
                $stateUserRecord['state_of_posting_id'] =  $this->getValueOrNull($fedMdaStateDeskOfficer->getStateOfPostingId());
                $stateUserRecord['primary_role'] =  $this->getValueOrNull($fedMdaStateDeskOfficer->getPrimaryRole());
                $stateUserRecord['record_status'] =  $this->getValueOrNull($fedMdaStateDeskOfficer->getStatus());
                $stateUserRecord['first_login'] =  $this->getValueOrNull($fedMdaStateDeskOfficer->getFirstLogin());
                $stateUserRecord['created'] =  $this->getValueOrNull($fedMdaStateDeskOfficer->getLastModified());
                $stateUserRecord['created_by'] =  $this->getValueOrNull($fedMdaStateDeskOfficer->getLastModifiedByUserId());
                $stateUserRecord['last_mod'] =  $this->getValueOrNull($fedMdaStateDeskOfficer->getLastModified());
                $stateUserRecord['last_mod_by'] =  $this->getValueOrNull($fedMdaStateDeskOfficer->getLastModifiedByUserId());
                $stateUserRecord['guid'] =  $this->getValueOrNull($fedMdaStateDeskOfficer->getGuid());

                $batchUserRecords[] = $stateUserRecord;
            }

            //begin transaction
            $this->connection->beginTransaction();
            
            $multiInsertOutcome = $this->pdoMultiInsert('user_profile', $batchUserRecords, $logger);

            //commit
            $this->connection->commit();

            $outcome = true;

        } catch (Throwable $e) {
            //roll back
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

    private function pdoMultiInsert($tableName, $data,LoggerInterface $logger)
    {
        $outcome = false;
        $pdoStatement = null;

        try {
            //Will contain SQL snippets.
            $rowsSQL = array();

            //Will contain the values that we need to bind.
            $toBind = array();

            $logger->info('got here');

            //Get a list of column names to use in the SQL statement.
            $columnNames = array_keys($data[0]);

            $logger->info('got here 2');

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
            $logger->info($t->getMessage());
            throw new AppException($t->getMessage());
        } finally {
            if ($pdoStatement) {
                $pdoStatement->closeCursor();
            }
        }

        return $outcome;
    }

}