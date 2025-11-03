<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 2:11 PM
 */

namespace AppBundle\Services\SuperAdmin\Roles;


use AppBundle\AppException\AppException;
use AppBundle\Model\SearchCriteria\SystemRoleSearchCriteria;
use AppBundle\Model\Security\SystemRole;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\Paginator;
use AppBundle\Utils\ServiceHelper;
use Doctrine\DBAL\Connection;
use \PDO;
use \Throwable;

class SystemRoleService extends ServiceHelper
{
    private $connection;
    private $rows_per_page;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->rows_per_page = AppConstants::ROWS_PER_PAGE;
    }


    public function searchRecords(SystemRoleSearchCriteria $searchCriteria, Paginator $paginator, $pageDirection) : array
    {
        $systemRoles = array();
        $statement = null;

        try {

            $searchName = $searchCriteria->getRoleName();
            $searchCategory = $searchCriteria->getCategory();
            $searchStatus = $searchCriteria->getStatus();

            $where = array("d.id > 0","d.is_reserved='N'");

            if ($searchName) {
                $where[] = "d.role_description LIKE :role_description";
            }
            if ($searchCategory) {
                $where[] = "d.role_category_id = :role_category_id";
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
            $countQuery = "SELECT COUNT(d.id) AS totalRows FROM system_roles d WHERE $where";
            $statement = $this->connection->prepare($countQuery);

            if ($searchName) {
                $statement->bindValue(':role_description', "%" . $searchName . "%");
            }
            if ($searchCategory) {
                $statement->bindValue(':role_category_id', $searchCategory);
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
            $query = "SELECT d.id,d.role_description,d.role_category_id,d.record_status,d.guid "
                . ",c.description as role_category_name "
                . ",(select count(u.user_login) from user_profile u where u.primary_role=d.id and u.record_status='ACTIVE' ) as total_active_users "
                . "FROM system_roles d "
                . "LEFT JOIN system_role_categories c on d.role_category_id=c.id  "
                . "WHERE $where order by d.role_description LIMIT $limitStartRow ,$this->rows_per_page ";
            $statement = $this->connection->prepare($query);

            if ($searchName) {
                $statement->bindValue(':role_description', "%" . $searchName . "%");
            }
            if ($searchCategory) {
                $statement->bindValue(':role_category_id', $searchCategory);
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

                    $systemRole = new SystemRole();
                    $systemRole->setId($record['id']);
                    $systemRole->setRoleName($record['role_description']);
                    $systemRole->setCategoryId($record['role_category_id']);
                    $systemRole->setCategoryName($record['role_category_name']);
                    $systemRole->setRecordStatus($record['record_status']);
                    $systemRole->setGuid($record['guid']);
                    $systemRole->setTotalActiveUsers($record['total_active_users']);

                    $systemRole->setDisplaySerialNo($displaySerialNo);
                    $displaySerialNo++;

                    $systemRoles[] = $systemRole;
                }
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $systemRoles;
    }

    public function addSystemRole(SystemRole $systemRole) : bool
    {
        $outcome = false;
        $statement = null;

        try {

            //check for duplicate name
            $query = "SELECT id FROM system_roles WHERE role_description=:role_description LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':role_description', $systemRole->getRoleName());
            $statement->execute();
            $existingRecord = $statement->fetch();

            if ($existingRecord) {
                throw new AppException(AppConstants::DUPLICATE_NAME);
            }

            $this->connection->beginTransaction();

            //now insert
            $query = "INSERT INTO system_roles "
                . "(role_description,role_category_id,record_status,created,created_by,last_mod,last_mod_by,guid)"
                . "VALUES (:role_description,:role_category_id,:record_status,:created,:created_by,:last_mod,:last_mod_by,:guid)";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':role_description', $this->getValueOrNull($systemRole->getRoleName()));
            $statement->bindValue(':role_category_id', $this->getValueOrNull($systemRole->getCategoryId()));
            $statement->bindValue(':record_status', $this->getValueOrNull($systemRole->getRecordStatus()));
            $statement->bindValue(':created', $this->getValueOrNull($systemRole->getLastModified()));
            $statement->bindValue(':created_by', $this->getValueOrNull($systemRole->getLastModifiedByUserId()));
            $statement->bindValue(':last_mod', $this->getValueOrNull($systemRole->getLastModified()));
            $statement->bindValue(':last_mod_by', $this->getValueOrNull($systemRole->getLastModifiedByUserId()));
            $statement->bindValue(':guid', $this->getValueOrNull($systemRole->getGuid()));

            $statement->execute();

            //get the just inserted id
            $query = "select id from system_roles where guid=:guid";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':guid', $systemRole->getGuid());
            $statement->execute();

            $new_role_id = $statement->fetchColumn(0);

            //insert the privileges
            if ($systemRole->getPrivileges()) {
                $batchPrivilegeRecords = array();

                foreach ($systemRole->getPrivileges() as $privilege) {

                    $rolePrivilegeRecord = array();

                    $rolePrivilegeRecord['role_id'] = $this->getValueOrNull($new_role_id);
                    $rolePrivilegeRecord['privilege_id'] = $this->getValueOrNull($privilege);

                    $batchPrivilegeRecords[] = $rolePrivilegeRecord;
                }

                $multiInsertOutcome = $this->pdoMultiInsert('system_role_privileges', $batchPrivilegeRecords);
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

    public function getSystemRole($guid) : SystemRole
    {
        $systemRole = null;
        $statement = null;

        try {
            $query = "SELECT d.id,d.role_description,d.role_category_id,d.record_status,d.guid "
                . ",c.description as role_category_name "
                . ",(select count(u.user_login) from user_profile u where u.primary_role=d.id and u.record_status='ACTIVE' ) as total_active_users "
                . "FROM system_roles d "
                . "LEFT JOIN system_role_categories c on d.role_category_id=c.id  "
                . "WHERE d.guid=:guid ";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':guid', $guid);
            $statement->execute();

            $record = $statement->fetch(PDO::FETCH_ASSOC);
            if ($record) {
                $systemRole = new SystemRole();
                $systemRole->setId($record['id']);
                $systemRole->setRoleName($record['role_description']);
                $systemRole->setCategoryId($record['role_category_id']);
                $systemRole->setCategoryName($record['role_category_name']);
                $systemRole->setRecordStatus($record['record_status']);
                $systemRole->setGuid($record['guid']);
                $systemRole->setTotalActiveUsers($record['total_active_users']);

                //get the privileges
                $query = "select privilege_id from system_role_privileges where role_id=:role_id";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':role_id', $systemRole->getId());

                $statement->execute();

                $privilegeRecords = $statement->fetchAll();

                if ($privilegeRecords) {
                    $rolePrivileges = array();
                    foreach ($privilegeRecords as $privilege) {
                        $rolePrivileges[] = $privilege['privilege_id'];
                    }
                    $systemRole->setPrivileges($rolePrivileges);
                }else{
                    $systemRole->setPrivileges(array());
                }
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $systemRole;
    }

    public function editSystemRole(SystemRole $systemRole) : bool
    {
        $outcome = false;
        $statement = null;

        try {

            //check for duplicate name
            $query = "SELECT id FROM system_roles WHERE role_description=:role_description and guid<>:guid LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':role_description', $systemRole->getRoleName());
            $statement->bindValue(':guid', $systemRole->getGuid());
            $statement->execute();
            $existingRecord = $statement->fetch();

            if ($existingRecord) {
                throw new AppException(AppConstants::DUPLICATE_NAME);
            }

            $this->connection->beginTransaction();

            //now insert
            $query = "UPDATE system_roles "
                . " set "
                . "role_description=:role_description, role_category_id=:role_category_id"
                . ",last_mod=:last_mod, last_mod_by=:last_mod_by "
                . "where guid=:guid ";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':role_description', $this->getValueOrNull($systemRole->getRoleName()));
            $statement->bindValue(':role_category_id', $this->getValueOrNull($systemRole->getCategoryId()));
            $statement->bindValue(':last_mod', $this->getValueOrNull($systemRole->getLastModified()));
            $statement->bindValue(':last_mod_by', $this->getValueOrNull($systemRole->getLastModifiedByUserId()));
            $statement->bindValue(':guid', $this->getValueOrNull($systemRole->getGuid()));

            $statement->execute();

            //delete and re-insert the insert the privileges

            //get the role id
            $query = "select id from system_roles where guid=:guid";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':guid', $systemRole->getGuid());
            $statement->execute();

            $role_id = $statement->fetchColumn(0);

            $query = "delete from system_role_privileges where role_id=:role_id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':role_id', $this->getValueOrNull($role_id));
            $statement->execute();

            if ($systemRole->getPrivileges()) {
                $batchPrivilegeRecords = array();

                foreach ($systemRole->getPrivileges() as $privilege) {

                    $rolePrivilegeRecord = array();

                    $rolePrivilegeRecord['role_id'] = $this->getValueOrNull($role_id);
                    $rolePrivilegeRecord['privilege_id'] = $this->getValueOrNull($privilege);

                    $batchPrivilegeRecords[] = $rolePrivilegeRecord;
                }

                $multiInsertOutcome = $this->pdoMultiInsert('system_role_privileges', $batchPrivilegeRecords);
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

    public function deleteSystemRole(SystemRole $systemRole) : bool
    {
        $outcome = false;
        $statement = null;

        try {

            //now delete
            $query = "UPDATE system_roles "
                . "SET record_status=:record_status "
                . ",last_mod=:last_mod,last_mod_by=:last_mod_by  "
                . "WHERE guid=:guid";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':record_status', $systemRole->getRecordStatus());
            $statement->bindValue(':last_mod', $systemRole->getLastModified());
            $statement->bindValue(':last_mod_by', $systemRole->getLastModifiedByUserId());
            $statement->bindValue(':guid', $systemRole->getGuid());

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