<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 2:11 PM
 */

namespace AppBundle\Services\SuperAdmin\Users;


use AppBundle\AppException\AppException;
use AppBundle\Model\SearchCriteria\UserProfileSearchCriteria;
use AppBundle\Model\Users\UserProfile;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\Paginator;
use AppBundle\Utils\ServiceHelper;
use Doctrine\DBAL\Connection;
use \PDO;
use \Throwable;

class UserProfileService extends ServiceHelper
{
    private $connection;
    private $rows_per_page;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->rows_per_page = AppConstants::ROWS_PER_PAGE;
    }


    public function searchRecords(UserProfileSearchCriteria $searchCriteria, Paginator $paginator, $pageDirection): array
    {
        $userProfiles = array();
        $statement = null;

        try {

            $searchUsername = $searchCriteria->getUsername();
            $searchFirstName = $searchCriteria->getFirstName();
            $searchLastName = $searchCriteria->getLastName();
            $searchEmail = $searchCriteria->getEmailAddress();
            $searchPhone = $searchCriteria->getPhoneNumber();
            $searchPrimaryRole = $searchCriteria->getPrimaryRole();
            $searchStateOfPosting = $searchCriteria->getFccLocation();
            $searchFccDepartment = $searchCriteria->getFccDepartment();
            $searchFccCommittee = $searchCriteria->getFccCommittee();
            $searchOrganization = $searchCriteria->getOrganization();
            $searchStatus = $searchCriteria->getStatus();

            $profileType = $searchCriteria->getProfileType();
            $where = array("d.id > 0", "d.profile_type='$profileType'","d.user_login<>'p0105'");

            if ($searchUsername) {
                $where[] = "d.user_login = :user_login";
            }
            if ($searchFirstName) {
                $where[] = "d.first_name LIKE :first_name";
            }
            if ($searchLastName) {
                $where[] = "d.last_name LIKE :last_name";
            }
            if ($searchEmail) {
                $where[] = "d.email_address = :email_address";
            }
            if ($searchPhone) {
                $where[] = "d.primary_phone = :primary_phone or d.secondary_phone = :secondary_phone";
            }
            if ($searchPrimaryRole) {
                $where[] = "d.primary_role = :primary_role";
            }
            if ($searchStateOfPosting) {
                $where[] = "d.state_of_posting_id = :state_of_posting_id";
            }
            if ($searchFccDepartment) {
                $where[] = "d.fcc_department_id = :fcc_department_id";
            }
            if ($searchFccCommittee) {
                $where[] = "d.fcc_committee_id = :fcc_committee_id";
            }
            if ($searchOrganization) {
                $where[] = "d.organization_id = :organization_id";
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
            $countQuery = "SELECT COUNT(d.id) AS totalRows FROM user_profile d WHERE $where";
            $statement = $this->connection->prepare($countQuery);

            if ($searchUsername) {
                $statement->bindValue(':user_login', $searchUsername);
            }
            if ($searchFirstName) {
                $statement->bindValue(':first_name', "%" . $searchFirstName . "%");
            }
            if ($searchLastName) {
                $statement->bindValue(':last_name', "%" . $searchLastName . "%");
            }
            if ($searchEmail) {
                $statement->bindValue(':email_address', $searchEmail);
            }
            if ($searchPhone) {
                $statement->bindValue(':primary_phone', $searchPhone);
                $statement->bindValue(':secondary_phone', $searchPhone);
            }
            if ($searchPrimaryRole) {
                $statement->bindValue(':primary_role', $searchPrimaryRole);
            }
            if ($searchStateOfPosting) {
                $statement->bindValue(':state_of_posting_id', $searchStateOfPosting);
            }
            if ($searchFccDepartment) {
                $statement->bindValue(':fcc_department_id', $searchFccDepartment);
            }
            if ($searchFccCommittee) {
                $statement->bindValue(':fcc_committee_id', $searchFccCommittee);
            }
            if ($searchOrganization) {
                $statement->bindValue(':organization_id', $searchOrganization);
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
            $query = "SELECT d.id,d.profile_type,d.user_login,d.first_name,d.last_name,d.middle_name,d.email_address 
                ,d.primary_phone,d.secondary_phone,d.organization_id,d.state_of_posting_id,d.fcc_department_id 
                ,d.fcc_committee_id,d.fcc_supervisor_id,d.primary_role,d.record_status,d.first_login,d.guid 
                ,sp.state_code as state_of_posting_code,sp.state_name as state_of_posting_name
                ,dept.department_code,dept.department_name,c.committee_name 
                ,o.establishment_code,o.establishment_mnemonic,o.organization_name, o.establishment_type_id 
                ,concat_ws(' ',u.first_name, u.last_name) as supervisor_name, u.primary_phone as supervisor_phone
                ,u.email_address as supervisor_email,r.role_description 
                FROM user_profile d 
                LEFT JOIN states sp on d.state_of_posting_id=sp.id  
                LEFT JOIN departments dept on d.fcc_department_id=dept.id  
                LEFT JOIN committees c on d.fcc_committee_id=c.id  
                LEFT JOIN organization o on d.organization_id=o.id  
                LEFT JOIN system_roles r on d.primary_role=r.id  
                LEFT JOIN user_profile u on d.fcc_supervisor_id=u.id  
                WHERE $where order by d.first_name LIMIT $limitStartRow ,$this->rows_per_page ";
            $statement = $this->connection->prepare($query);

            if ($searchUsername) {
                $statement->bindValue(':user_login', $searchUsername);
            }
            if ($searchFirstName) {
                $statement->bindValue(':first_name', "%" . $searchFirstName . "%");
            }
            if ($searchLastName) {
                $statement->bindValue(':last_name', "%" . $searchLastName . "%");
            }
            if ($searchEmail) {
                $statement->bindValue(':email_address', $searchEmail);
            }
            if ($searchPhone) {
                $statement->bindValue(':primary_phone', $searchPhone);
                $statement->bindValue(':secondary_phone', $searchPhone);
            }
            if ($searchPrimaryRole) {
                $statement->bindValue(':primary_role', $searchPrimaryRole);
            }
            if ($searchStateOfPosting) {
                $statement->bindValue(':state_of_posting_id', $searchStateOfPosting);
            }
            if ($searchFccDepartment) {
                $statement->bindValue(':fcc_department_id', $searchFccDepartment);
            }
            if ($searchFccCommittee) {
                $statement->bindValue(':fcc_committee_id', $searchFccCommittee);
            }
            if ($searchOrganization) {
                $statement->bindValue(':organization_id', $searchOrganization);
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

                    $userProfile = new UserProfile();
                    $userProfile->setId($record['id']);
                    $userProfile->setProfileType($record['profile_type']);
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
                    $userProfile->setFccCommitteeName($record['committee_name']);

                    $userProfile->setFccSupervisorId($record['fcc_supervisor_id']);
                    $userProfile->setFccSupervisorName($record['supervisor_name']);
                    $userProfile->setFccSupervisorPhone($record['supervisor_phone']);
                    $userProfile->setFccSupervisorEmail($record['supervisor_email']);

                    $userProfile->setPrimaryRole($record['primary_role']);
                    $userProfile->setPrimaryRoleDescription($record['role_description']);
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

    public function addUserProfile(UserProfile $userProfile): bool
    {
        $outcome = false;
        $statement = null;

        try {

            //check for duplicate username
            $query = "SELECT id FROM user_profile WHERE user_login=:user_login LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':user_login', $userProfile->getUsername());
            $statement->execute();
            $existingUser = $statement->fetch();

            if ($existingUser) {
                throw new AppException(AppConstants::DUPLICATE_USERNAME);
            }

            //check duplicate email
            $query = "SELECT id FROM user_profile WHERE email_address=:email_address LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':email_address', $userProfile->getEmailAddress());
            $statement->execute();
            $existingUser = $statement->fetch();

            if ($existingUser) {
                throw new AppException(AppConstants::DUPLICATE_EMAIL);
            }

            //check duplicate prim phone
            $query = "SELECT id FROM user_profile WHERE primary_phone=:primary_phone LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':primary_phone', $userProfile->getPrimaryPhone());
            $statement->execute();
            $existingUser = $statement->fetch();

            if ($existingUser) {
                throw new AppException(AppConstants::DUPLICATE_PRIMARY_PHONE);
            }

            //check duplicate sec phone
            if ($userProfile->getSecondaryPhone()) {
                $query = "SELECT id FROM user_profile WHERE secondary_phone=:secondary_phone LIMIT 1";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':secondary_phone', $userProfile->getSecondaryPhone());
                $statement->execute();
                $existingUser = $statement->fetch();

                if ($existingUser) {
                    throw new AppException(AppConstants::DUPLICATE_SECONDARY_PHONE);
                }
            }

            //check duplicate guid
            $query = "SELECT id FROM user_profile WHERE guid=:guid LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':guid', $userProfile->getGuid());
            $statement->execute();
            $existingUser = $statement->fetch();

            if ($existingUser) {
                throw new AppException(AppConstants::DUPLICATE_GUID);
            }

            //if this is FCC Desk officer check for duplicate committee
            /*if($userProfile->getFccCommitteeId()){
                if($userProfile->getPrimaryRole() && $userProfile->getPrimaryRole() == AppConstants::ROLE_FCC_DESK_OFFICER_FEDERAL){

                    $query = "SELECT id FROM user_profile 
                      WHERE primary_role=:primary_role
                      AND fcc_committee_id=:fcc_committee_id LIMIT 1";
                    $statement = $this->connection->prepare($query);
                    $statement->bindValue(':primary_role', $userProfile->getPrimaryRole());
                    $statement->bindValue(':fcc_committee_id', $userProfile->getFccCommitteeId());
                    $statement->execute();
                    $existingUser = $statement->fetch();

                    if ($existingUser) {
                        throw new AppException(AppConstants::DUPLICATE_FCC_DESK_OFFICER_COMMITTEE);
                    }

                }
            }*/

            $this->connection->beginTransaction();

            //now insert
            $query = "INSERT INTO user_profile "
                . "(profile_type,user_login,user_pass,first_name,last_name,middle_name,email_address,primary_phone "
                . ",secondary_phone,organization_id,state_of_posting_id,fcc_department_id,fcc_committee_id,fcc_supervisor_id"
                . ",primary_role,record_status,first_login,created,created_by,last_mod,last_mod_by,guid)"
                . "VALUES (:profile_type,:user_login,:user_pass,:first_name,:last_name,:middle_name,:email_address,:primary_phone"
                . ",:secondary_phone,:organization_id,:state_of_posting_id,:fcc_department_id,:fcc_committee_id,:fcc_supervisor_id"
                . ",:primary_role,:record_status,:first_login,:created,:created_by,:last_mod,:last_mod_by,:guid)";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':profile_type', $this->getValueOrNull($userProfile->getProfileType()));
            $statement->bindValue(':user_login', $this->getValueOrNull($userProfile->getUsername()));
            $statement->bindValue(':user_pass', $this->getValueOrNull($userProfile->getPassword()));
            $statement->bindValue(':first_name', $this->getValueOrNull($userProfile->getFirstName()));
            $statement->bindValue(':last_name', $this->getValueOrNull($userProfile->getLastName()));
            $statement->bindValue(':middle_name', $this->getValueOrNull($userProfile->getMiddleName()));
            $statement->bindValue(':email_address', $this->getValueOrNull($userProfile->getEmailAddress()));
            $statement->bindValue(':primary_phone', $this->getValueOrNull($userProfile->getPrimaryPhone()));
            $statement->bindValue(':secondary_phone', $this->getValueOrNull($userProfile->getSecondaryPhone()));
            $statement->bindValue(':organization_id', $this->getValueOrNull($userProfile->getOrganizationId()));
            $statement->bindValue(':state_of_posting_id', $this->getValueOrNull($userProfile->getStateOfPostingId()));
            $statement->bindValue(':fcc_department_id', $this->getValueOrNull($userProfile->getFccDepartmentId()));
            $statement->bindValue(':fcc_committee_id', $this->getValueOrNull($userProfile->getFccCommitteeId()));
            $statement->bindValue(':fcc_supervisor_id', $this->getValueOrNull($userProfile->getFccSupervisorId()));
            $statement->bindValue(':primary_role', $this->getValueOrNull($userProfile->getPrimaryRole()));
            $statement->bindValue(':record_status', $this->getValueOrNull($userProfile->getStatus()));
            $statement->bindValue(':first_login', $this->getValueOrNull($userProfile->getFirstLogin()));
            $statement->bindValue(':created', $this->getValueOrNull($userProfile->getLastModified()));
            $statement->bindValue(':created_by', $this->getValueOrNull($userProfile->getLastModifiedByUserId()));
            $statement->bindValue(':last_mod', $this->getValueOrNull($userProfile->getLastModified()));
            $statement->bindValue(':last_mod_by', $this->getValueOrNull($userProfile->getLastModifiedByUserId()));
            $statement->bindValue(':guid', $this->getValueOrNull($userProfile->getGuid()));

            $statement->execute();

            //insert default user role
            //get the id just inserted
            $query = "SELECT id FROM user_profile WHERE user_login=:user_login";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':user_login', $this->getValueOrNull($userProfile->getUsername()));
            $statement->execute();

            $newUserId = $statement->fetchColumn(0);

            /*//$query = "insert into user_roles (user_id,role_id) VALUES (:user_id,:role_id)";
            //$statement = $this->connection->prepare($query);
            //$statement->bindValue(':user_id', $this->getValueOrNull($newUserId));
            //$statement->bindValue(':role_id', AppConstants::ROLE_USER);
            //$statement->execute();*/

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

    public function getUserProfile($guid): UserProfile
    {
        $userProfile = null;
        $statement = null;

        try {
            $query = "SELECT d.id,d.profile_type,d.user_login,d.first_name,d.last_name,d.middle_name,d.email_address 
                ,d.primary_phone,d.secondary_phone,d.organization_id,d.state_of_posting_id,d.fcc_department_id 
                ,d.fcc_committee_id,d.fcc_supervisor_id,d.primary_role,d.record_status,d.first_login,d.guid 
                ,sp.state_code AS state_of_posting_code,sp.state_name AS state_of_posting_name 
                ,dept.department_code,dept.department_name,c.committee_name
                ,o.establishment_code,o.establishment_mnemonic,o.organization_name, o.establishment_type_id 
                ,concat_ws(' ',u.first_name, u.last_name) AS supervisor_name, u.primary_phone AS supervisor_phone
                ,u.email_address AS supervisor_email,r.role_description 
                FROM user_profile d 
                LEFT JOIN states sp ON d.state_of_posting_id=sp.id  
                LEFT JOIN departments dept ON d.fcc_department_id=dept.id  
                LEFT JOIN committees c ON d.fcc_department_id=c.id  
                LEFT JOIN organization o ON d.organization_id=o.id  
                LEFT JOIN system_roles r ON d.primary_role=r.id  
                LEFT JOIN user_profile u ON d.fcc_supervisor_id=u.id  
                WHERE d.guid=:guid ";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':guid', $guid);
            $statement->execute();

            $record = $statement->fetch(PDO::FETCH_ASSOC);
            if ($record) {
                $userProfile = new UserProfile();
                $userProfile->setId($record['id']);
                $userProfile->setProfileType($record['profile_type']);
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
                $userProfile->setFccCommitteeName($record['committee_name']);

                $userProfile->setFccSupervisorId($record['fcc_supervisor_id']);
                $userProfile->setFccSupervisorName($record['supervisor_name']);
                $userProfile->setFccSupervisorPhone($record['supervisor_phone']);
                $userProfile->setFccSupervisorEmail($record['supervisor_email']);

                $userProfile->setPrimaryRole($record['primary_role']);
                $userProfile->setPrimaryRoleDescription($record['role_description']);
                $userProfile->setStatus($record['record_status']);
                $userProfile->setFirstLogin($record['first_login']);
                $userProfile->setGuid($record['guid']);
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $userProfile;
    }

    public function editUserProfile(UserProfile $userProfile): bool
    {
        $outcome = false;
        $statement = null;

        try {

            //check for duplicate username
            $query = "SELECT id FROM user_profile WHERE user_login=:user_login AND guid<>:guid LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':user_login', $userProfile->getUsername());
            $statement->bindValue(':guid', $userProfile->getGuid());
            $statement->execute();
            $existingUser = $statement->fetch();

            if ($existingUser) {
                throw new AppException(AppConstants::DUPLICATE_USERNAME);
            }

            //check duplicate email
            $query = "SELECT id FROM user_profile WHERE email_address=:email_address AND guid<>:guid LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':email_address', $userProfile->getEmailAddress());
            $statement->bindValue(':guid', $userProfile->getGuid());
            $statement->execute();
            $existingUser = $statement->fetch();

            if ($existingUser) {
                throw new AppException(AppConstants::DUPLICATE_EMAIL);
            }

            //check duplicate prim phone
            $query = "SELECT id FROM user_profile WHERE primary_phone=:primary_phone AND guid<>:guid LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':primary_phone', $userProfile->getPrimaryPhone());
            $statement->bindValue(':guid', $userProfile->getGuid());
            $statement->execute();
            $existingUser = $statement->fetch();

            if ($existingUser) {
                throw new AppException(AppConstants::DUPLICATE_PRIMARY_PHONE);
            }

            //check duplicate sec phone
            if ($userProfile->getSecondaryPhone()) {
                $query = "SELECT id FROM user_profile WHERE secondary_phone=:secondary_phone AND guid<>:guid LIMIT 1";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':secondary_phone', $userProfile->getSecondaryPhone());
                $statement->bindValue(':guid', $userProfile->getGuid());
                $statement->execute();
                $existingUser = $statement->fetch();

                if ($existingUser) {
                    throw new AppException(AppConstants::DUPLICATE_SECONDARY_PHONE);
                }
            }

            //if this is FCC Desk officer check for duplicate committee
            /*if($userProfile->getFccCommitteeId()){
                if($userProfile->getPrimaryRole() && $userProfile->getPrimaryRole() == AppConstants::ROLE_FCC_DESK_OFFICER_FEDERAL){

                    $query = "SELECT id FROM user_profile 
                      WHERE primary_role=:primary_role
                      AND fcc_committee_id=:fcc_committee_id 
                      AND guid<>:guid LIMIT 1";
                    $statement = $this->connection->prepare($query);
                    $statement->bindValue(':primary_role', $userProfile->getPrimaryRole());
                    $statement->bindValue(':fcc_committee_id', $userProfile->getFccCommitteeId());
                    $statement->bindValue(':guid', $userProfile->getGuid());
                    $statement->execute();
                    $existingUser = $statement->fetch();

                    if ($existingUser) {
                        throw new AppException(AppConstants::DUPLICATE_FCC_DESK_OFFICER_COMMITTEE);
                    }

                }
            }*/

            //now update
            $query = "UPDATE user_profile "
                . "SET first_name=:first_name, last_name=:last_name, middle_name=:middle_name, email_address=:email_address "
                . ",primary_phone=:primary_phone, secondary_phone=:secondary_phone "
                . ", state_of_posting_id=:state_of_posting_id ,fcc_department_id=:fcc_department_id, fcc_committee_id=:fcc_committee_id"
                . ",fcc_supervisor_id=:fcc_supervisor_id,primary_role=:primary_role"
                . ",last_mod=:last_mod, last_mod_by=:last_mod_by  "
                . "WHERE guid=:guid";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':first_name', $this->getValueOrNull($userProfile->getFirstName()));
            $statement->bindValue(':last_name', $this->getValueOrNull($userProfile->getLastName()));
            $statement->bindValue(':middle_name', $this->getValueOrNull($userProfile->getMiddleName()));
            $statement->bindValue(':email_address', $this->getValueOrNull($userProfile->getEmailAddress()));
            $statement->bindValue(':primary_phone', $this->getValueOrNull($userProfile->getPrimaryPhone()));
            $statement->bindValue(':secondary_phone', $this->getValueOrNull($userProfile->getSecondaryPhone()));
            $statement->bindValue(':state_of_posting_id', $this->getValueOrNull($userProfile->getStateOfPostingId()));
            $statement->bindValue(':fcc_department_id', $this->getValueOrNull($userProfile->getFccDepartmentId()));
            $statement->bindValue(':fcc_committee_id', $this->getValueOrNull($userProfile->getFccCommitteeId()));
            $statement->bindValue(':fcc_supervisor_id', $this->getValueOrNull($userProfile->getFccSupervisorId()));
            $statement->bindValue(':primary_role', $this->getValueOrNull($userProfile->getPrimaryRole()));
            $statement->bindValue(':last_mod', $this->getValueOrNull($userProfile->getLastModified()));
            $statement->bindValue(':last_mod_by', $this->getValueOrNull($userProfile->getLastModifiedByUserId()));
            $statement->bindValue(':guid', $userProfile->getGuid());

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

    public function editBioData(UserProfile $userProfile): bool
    {
        $outcome = false;
        $statement = null;

        try {

            //check for duplicate username
            $query = "SELECT id FROM user_profile WHERE user_login=:user_login AND guid<>:guid LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':user_login', $userProfile->getUsername());
            $statement->bindValue(':guid', $userProfile->getGuid());
            $statement->execute();
            $existingUser = $statement->fetch();

            if ($existingUser) {
                throw new AppException(AppConstants::DUPLICATE_USERNAME);
            }

            //check duplicate email
            $query = "SELECT id FROM user_profile WHERE email_address=:email_address AND guid<>:guid LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':email_address', $userProfile->getEmailAddress());
            $statement->bindValue(':guid', $userProfile->getGuid());
            $statement->execute();
            $existingUser = $statement->fetch();

            if ($existingUser) {
                throw new AppException(AppConstants::DUPLICATE_EMAIL);
            }

            //check duplicate prim phone
            $query = "SELECT id FROM user_profile WHERE primary_phone=:primary_phone AND guid<>:guid LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':primary_phone', $userProfile->getPrimaryPhone());
            $statement->bindValue(':guid', $userProfile->getGuid());
            $statement->execute();
            $existingUser = $statement->fetch();

            if ($existingUser) {
                throw new AppException(AppConstants::DUPLICATE_PRIMARY_PHONE);
            }

            //check duplicate sec phone
            if ($userProfile->getSecondaryPhone()) {
                $query = "SELECT id FROM user_profile WHERE secondary_phone=:secondary_phone AND guid<>:guid LIMIT 1";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':secondary_phone', $userProfile->getSecondaryPhone());
                $statement->bindValue(':guid', $userProfile->getGuid());
                $statement->execute();
                $existingUser = $statement->fetch();

                if ($existingUser) {
                    throw new AppException(AppConstants::DUPLICATE_SECONDARY_PHONE);
                }
            }

            //now update
            $query = "UPDATE user_profile "
                . "SET first_name=:first_name, last_name=:last_name, middle_name=:middle_name, email_address=:email_address "
                . ",primary_phone=:primary_phone "
                . ",last_mod=:last_mod, last_mod_by=:last_mod_by  "
                . "WHERE guid=:guid";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':first_name', $this->getValueOrNull($userProfile->getFirstName()));
            $statement->bindValue(':last_name', $this->getValueOrNull($userProfile->getLastName()));
            $statement->bindValue(':middle_name', $this->getValueOrNull($userProfile->getMiddleName()));
            $statement->bindValue(':email_address', $this->getValueOrNull($userProfile->getEmailAddress()));
            $statement->bindValue(':primary_phone', $this->getValueOrNull($userProfile->getPrimaryPhone()));
            $statement->bindValue(':last_mod', $this->getValueOrNull($userProfile->getLastModified()));
            $statement->bindValue(':last_mod_by', $this->getValueOrNull($userProfile->getLastModifiedByUserId()));
            $statement->bindValue(':guid', $userProfile->getGuid());

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

    public function resetPassword(UserProfile $userProfile): bool
    {
        $outcome = false;
        $statement = null;

        try {

            $query = "UPDATE user_profile 
                SET user_pass=:user_pass 
                ,last_mod=:last_mod,last_mod_by=:last_mod_by  
                WHERE guid=:guid";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':user_pass', $userProfile->getPassword());
            $statement->bindValue(':last_mod', $userProfile->getLastModified());
            $statement->bindValue(':last_mod_by', $userProfile->getLastModifiedByUserId());
            $statement->bindValue(':guid', $userProfile->getGuid());

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

    public function updateProfilePhoto(UserProfile $userProfile): bool
    {
        $outcome = false;
        $statement = null;

        try {

            if ($userProfile->getUploadedPhotoFileName()) {

                $query = "UPDATE user_profile 
                SET profile_picture_file_name=:profile_picture_file_name 
                ,last_mod=:last_mod,last_mod_by=:last_mod_by  
                WHERE guid=:guid";

                $statement = $this->connection->prepare($query);
                $statement->bindValue(':profile_picture_file_name', $userProfile->getUploadedPhotoFileName());
                $statement->bindValue(':last_mod', $userProfile->getLastModified());
                $statement->bindValue(':last_mod_by', $userProfile->getLastModifiedByUserId());
                $statement->bindValue(':guid', $userProfile->getGuid());

                $statement->execute();

            }

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

    public function deleteUserProfile(UserProfile $userProfile): bool
    {
        $outcome = false;
        $statement = null;

        try {

            $this->connection->beginTransaction();

            //now delete
            $query = "UPDATE user_profile "
                . "SET record_status=:record_status "
                . ",last_mod=:last_mod,last_mod_by=:last_mod_by  "
                . "WHERE guid=:guid";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':record_status', $userProfile->getStatus());
            $statement->bindValue(':last_mod', $userProfile->getLastModified());
            $statement->bindValue(':last_mod_by', $userProfile->getLastModifiedByUserId());
            $statement->bindValue(':guid', $userProfile->getGuid());
            $statement->execute();

            //delete this user from other tables like commitees, etc
            $query = "SELECT id FROM user_profile WHERE guid=:guid";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':guid', $userProfile->getGuid());
            $statement->execute();

            $user_id = $statement->fetchColumn(0);

            if ($user_id) {
                $query = "DELETE FROM committee_members WHERE staff_user_profile_id=:staff_user_profile_id";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':staff_user_profile_id', $user_id);
                $statement->execute();

                $query = "UPDATE committees SET secretary_user_profile_id=NULL WHERE secretary_user_profile_id=:secretary_user_profile_id";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':secretary_user_profile_id', $user_id);
                $statement->execute();

                $query = "UPDATE committees SET chairman_user_profile_id=NULL WHERE chairman_user_profile_id=:chairman_user_profile_id";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':chairman_user_profile_id', $user_id);
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

    public function getMDAExtraUserProfiles($organizationId): array
    {
        $mdaExtraUsers = array();
        $statement = null;

        try {
            $query = "SELECT d.id,d.profile_type,d.user_login,d.first_name,d.last_name,d.middle_name,d.email_address 
                ,d.primary_phone,d.secondary_phone,d.organization_id,d.state_of_posting_id,d.fcc_department_id 
                ,d.fcc_committee_id,d.fcc_supervisor_id,d.primary_role,d.record_status,d.first_login,d.guid 
                ,sp.state_code AS state_of_posting_code,sp.state_name AS state_of_posting_name 
                ,dept.department_code,dept.department_name,c.committee_name
                ,o.establishment_code,o.establishment_mnemonic,o.organization_name, o.establishment_type_id 
                ,concat_ws(' ',u.first_name, u.last_name) AS supervisor_name, u.primary_phone AS supervisor_phone
                ,u.email_address AS supervisor_email,r.role_description 
                FROM user_profile d 
                LEFT JOIN states sp ON d.state_of_posting_id=sp.id  
                LEFT JOIN departments dept ON d.fcc_department_id=dept.id  
                LEFT JOIN committees c ON d.fcc_department_id=c.id  
                LEFT JOIN organization o ON d.organization_id=o.id  
                LEFT JOIN system_roles r ON d.primary_role=r.id  
                LEFT JOIN user_profile u ON d.fcc_supervisor_id=u.id  
                WHERE d.organization_id=:organization_id AND d.user_login<>o.establishment_code";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':organization_id', $organizationId);
            $statement->execute();

            $records = $statement->fetchAll();
            if ($records) {
                foreach ($records as $record) {

                    $userProfile = new UserProfile();
                    $userProfile->setId($record['id']);
                    $userProfile->setProfileType($record['profile_type']);
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
                    $userProfile->setFccCommitteeName($record['committee_name']);

                    $userProfile->setFccSupervisorId($record['fcc_supervisor_id']);
                    $userProfile->setFccSupervisorName($record['supervisor_name']);
                    $userProfile->setFccSupervisorPhone($record['supervisor_phone']);
                    $userProfile->setFccSupervisorEmail($record['supervisor_email']);

                    $userProfile->setPrimaryRole($record['primary_role']);
                    $userProfile->setPrimaryRoleDescription($record['role_description']);
                    $userProfile->setStatus($record['record_status']);
                    $userProfile->setFirstLogin($record['first_login']);
                    $userProfile->setGuid($record['guid']);

                    $mdaExtraUsers[] = $userProfile;
                }
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $mdaExtraUsers;
    }

    public function fixUserProfile($encoder, $logger): bool
    {
        $outcome = false;
        $statement = null;

        try {

            $query = "SELECT user_login,plain_password FROM _fcc_stuffs_from_old_portal._user_profile WHERE is_new IS NULL";
            $statement = $this->connection->prepare($query);
            $statement->execute();

            $oldUsers = $statement->fetchAll();

            $update_query = "UPDATE _user_profile "
                . "SET new_password=:new_password "
                . "WHERE user_login=:user_login";

            if ($oldUsers) {
                foreach ($oldUsers as $oldUser) {

                    $userProfile = new UserProfile();
                    $encryptedPassword = $encoder->encodePassword($userProfile, $oldUser['plain_password']);

                    $statement = $this->connection->prepare($update_query);

                    $statement->bindValue(':new_password', $encryptedPassword);
                    $statement->bindValue(':user_login', $oldUser['user_login']);

                    $statement->execute();

                }
            }

            $logger->info('COMPLETED');

            $outcome = true;

        } catch (Throwable $e) {
            $logger->info($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $outcome;

    }

}