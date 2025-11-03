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
use \Throwable;

class OrganizationService extends ServiceHelper
{
    private $connection;
    private $rows_per_page;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->rows_per_page = AppConstants::ROWS_PER_PAGE;
    }


    public function searchRecords(OrganizationSearchCriteria $searchCriteria, Paginator $paginator, $pageDirection): array
    {
        $organizations = array();
        $statement = null;

        try {

            $searchOrganizationName = $searchCriteria->getOrganizationName();
            $searchEstablishmentCode = $searchCriteria->getEstablishmentCode();
            $searchEstablishmentMnemonic = $searchCriteria->getEstablishmentMnemonic();
            $searchStateOwnedEstablishmentState = $searchCriteria->getStateOwnedEstablishmentState();
            $searchStateOfLocation = $searchCriteria->getStateOfLocation();
            $searchStatus = $searchCriteria->getStatus();

            $establishmentType = $searchCriteria->getEstablishmentType();
            $where = array("d.id > 0", "d.establishment_type_id='$establishmentType'");

            if ($searchOrganizationName) {
                $where[] = "d.organization_name LIKE :organization_name";
            }
            if ($searchEstablishmentCode) {
                $where[] = "d.establishment_code = :establishment_code";
            }
            if ($searchEstablishmentMnemonic) {
                $where[] = "d.establishment_mnemonic LIKE :establishment_mnemonic";
            }
            if ($searchStateOwnedEstablishmentState) {
                $where[] = "d.state_owned_establishment_state_id = :state_owned_establishment_state_id";
            }
            if ($searchStateOfLocation) {
                $where[] = "d.state_of_location_id = :state_of_location_id";
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
            $countQuery = "SELECT COUNT(d.id) AS totalRows FROM organization d WHERE $where";
            $statement = $this->connection->prepare($countQuery);

            if ($searchOrganizationName) {
                $statement->bindValue(':organization_name', "%" . $searchOrganizationName . "%");
            }
            if ($searchEstablishmentCode) {
                $statement->bindValue(':establishment_code', $searchEstablishmentCode);
            }
            if ($searchEstablishmentMnemonic) {
                $statement->bindValue(':establishment_mnemonic', $searchEstablishmentMnemonic);
            }
            if ($searchStateOwnedEstablishmentState) {
                $statement->bindValue(':state_owned_establishment_state_id', $searchStateOwnedEstablishmentState);
            }
            if ($searchStateOfLocation) {
                $statement->bindValue(':state_of_location_id', $searchStateOfLocation);
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
            $query = "SELECT d.id,d.establishment_code,d.establishment_mnemonic,d.organization_name 
                ,d.establishment_type_id,d.state_owned_establishment_state_id,d.state_of_location_id 
                ,d.contact_address,d.website_address,d.email_address,d.primary_phone,d.record_status,d.guid 
                ,d.parent_organization_id, d.fcc_committee_id, d.fcc_desk_officer_id
                ,e.description as _establishment_type_name
                ,s.state_code as _state_owned_establishment_state_code,s.state_name as _state_owned_establishment_state_name 
                ,sl.state_name as _state_of_location_name
                ,o.organization_name as _parent_organization_name, c.committee_name as _fcc_committee_name
                ,concat_ws(' ', u.first_name,u.last_name) as _fcc_desk_officer_name 
                ,u.email_address as _fcc_desk_officer_email,u.primary_phone as _fcc_desk_officer_phone 
                ,m.id as _mda_desk_officer_id,concat_ws(' ', m.first_name,m.last_name) as _mda_desk_officer_name 
                ,m.email_address as _mda_desk_officer_email,m.primary_phone as _mda_desk_officer_phone 
                ,(select group_concat(organization_category_type.description)
                    from organization_category_types organization_category_type
                    join organization_categories organization_category on organization_category_type.id = organization_category.organization_category_type_id
                    where organization_category.organization_id = d.id group by organization_category.organization_id ) as organization_category_names
                FROM organization d 
                LEFT JOIN establishment_types e on d.establishment_type_id=e.id 
                LEFT JOIN states s on d.state_owned_establishment_state_id=s.id 
                LEFT JOIN states sl on d.state_of_location_id=sl.id  
                LEFT JOIN organization o on d.parent_organization_id=o.id 
                LEFT JOIN committees c on d.fcc_committee_id=c.id 
                LEFT JOIN user_profile u on (d.fcc_committee_id = u.fcc_committee_id 
                                              and u.primary_role=:fcc_desk_officer_role)
                LEFT JOIN user_profile m on d.establishment_code=m.user_login 
                WHERE $where order by d.organization_name LIMIT $limitStartRow ,$this->rows_per_page ";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':fcc_desk_officer_role', AppConstants::ROLE_FCC_DESK_OFFICER_FEDERAL);

            if ($searchOrganizationName) {
                $statement->bindValue(':organization_name', "%" . $searchOrganizationName . "%");
            }
            if ($searchEstablishmentCode) {
                $statement->bindValue(':establishment_code', $searchEstablishmentCode);
            }
            if ($searchEstablishmentMnemonic) {
                $statement->bindValue(':establishment_mnemonic', $searchEstablishmentMnemonic);
            }
            if ($searchStateOwnedEstablishmentState) {
                $statement->bindValue(':state_owned_establishment_state_id', $searchStateOwnedEstablishmentState);
            }
            if ($searchStateOfLocation) {
                $statement->bindValue(':state_of_location_id', $searchStateOfLocation);
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

                    $organization = new Organization();
                    $organization->setId($record['id']);
                    $organization->setEstablishmentCode($record['establishment_code']);
                    $organization->setEstablishmentMnemonic($record['establishment_mnemonic']);
                    $organization->setOrganizationName($record['organization_name']);

                    $organization->setEstablishmentTypeId($record['establishment_type_id']);
                    $organization->setEstablishmentTypeName($record['_establishment_type_name']);
                    $organization->setEstablishmentCategoryNames($record['organization_category_names']);

                    $organization->setStateOwnedEstablishmentStateId($record['state_owned_establishment_state_id']);
                    $organization->setStateOwnedEstablishmentStateCode($record['_state_owned_establishment_state_code']);
                    $organization->setStateOwnedEstablishmentStateName($record['_state_owned_establishment_state_name']);

                    $organization->setStateOfLocationId($record['state_of_location_id']);
                    $organization->setStateOfLocationName($record['_state_of_location_name']);

                    $organization->setContactAddress($record['contact_address']);
                    $organization->setWebsiteAddress($record['website_address']);
                    $organization->setEmailAddress($record['email_address']);
                    $organization->setPrimaryPhone($record['primary_phone']);

                    $organization->setParentOrganizationId($record['parent_organization_id']);
                    $organization->setParentOrganizationName($record['_parent_organization_name']);

                    $organization->setFccCommitteeId($record['fcc_committee_id']);
                    $organization->setFccCommitteeName($record['_fcc_committee_name']);

                    $organization->setFccDeskOfficerId($record['fcc_desk_officer_id']);
                    $organization->setFccDeskOfficerName($record['_fcc_desk_officer_name']);
                    $organization->setFccDeskOfficerEmail($record['_fcc_desk_officer_email']);
                    $organization->setFccDeskOfficerPhone($record['_fcc_desk_officer_phone']);

                    $organization->setMdaDeskOfficerId($record['_mda_desk_officer_id']);
                    $organization->setMdaDeskOfficerName($record['_mda_desk_officer_name']);
                    $organization->setMdaDeskOfficerEmail($record['_mda_desk_officer_email']);
                    $organization->setMdaDeskOfficerPhone($record['_mda_desk_officer_phone']);

                    $organization->setStatus($record['record_status']);
                    $organization->setGuid($record['guid']);

                    $organization->setDisplaySerialNo($displaySerialNo);
                    $displaySerialNo++;

                    $organizations[] = $organization;
                }
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $organizations;
    }

    public function addOrganization(Organization $organization, UserProfile $userProfile): bool
    {
        $outcome = false;
        $statement = null;

        try {

            //check for duplicate code
            $query = "SELECT id FROM organization WHERE establishment_code=:establishment_code LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':establishment_code', $organization->getEstablishmentCode());
            $statement->execute();
            $existingOrganization = $statement->fetch();

            if ($existingOrganization) {
                throw new AppException(AppConstants::DUPLICATE_CODE);
            }

            //check for duplicate name
            $query = "SELECT id FROM organization WHERE organization_name=:organization_name LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':organization_name', $organization->getOrganizationName());
            $statement->execute();
            $existingOrganization = $statement->fetch();

            if ($existingOrganization) {
                throw new AppException(AppConstants::DUPLICATE_DESC);
            }

            //check duplicate email
            if ($organization->getEmailAddress()) {
                $query = "SELECT id FROM organization WHERE email_address=:email_address LIMIT 1";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':email_address', $organization->getEmailAddress());
                $statement->execute();
                $existingOrganization = $statement->fetch();

                if ($existingOrganization) {
                    throw new AppException(AppConstants::DUPLICATE_EMAIL);
                }
            }

            //check duplicate prim phone
            if ($organization->getPrimaryPhone()) {
                $query = "SELECT id FROM organization WHERE primary_phone=:primary_phone LIMIT 1";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':primary_phone', $organization->getPrimaryPhone());
                $statement->execute();
                $existingOrganization = $statement->fetch();

                if ($existingOrganization) {
                    throw new AppException(AppConstants::DUPLICATE_PRIMARY_PHONE);
                }
            }

            //check duplicate sec phone
            /*if ($organization->getSecondaryPhone()) {
                $query = "SELECT id FROM organization WHERE secondary_phone=:secondary_phone LIMIT 1";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':secondary_phone', $organization->getSecondaryPhone());
                $statement->execute();
                $existingUser = $statement->fetch();

                if ($existingUser) {
                    throw new AppException(AppConstants::DUPLICATE_SECONDARY_PHONE);
                }
            }*/

            //check duplicate guid
            $query = "SELECT id FROM organization WHERE guid=:guid LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':guid', $organization->getGuid());
            $statement->execute();
            $existingOrganization = $statement->fetch();

            if ($existingOrganization) {
                throw new AppException(AppConstants::DUPLICATE_GUID);
            }

            //begin a transaction
            $this->connection->beginTransaction();

            //now insert
            $query = "INSERT INTO organization 
                (
                establishment_code,establishment_mnemonic,organization_name,level_of_government,establishment_type_id 
                ,state_owned_establishment_state_id,state_of_location_id,contact_address,website_address,email_address
                ,primary_phone,parent_organization_id
                ,record_status,created,created_by,last_mod,last_mod_by,guid
                )
                VALUES 
                (
                :establishment_code,:establishment_mnemonic,:organization_name,:level_of_government,:establishment_type_id 
                ,:state_owned_establishment_state_id,:state_of_location_id,:contact_address,:website_address,:email_address
                ,:primary_phone,:parent_organization_id
                ,:record_status,:created,:created_by,:last_mod,:last_mod_by,:guid
                )";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':establishment_code', $this->getValueOrNull($organization->getEstablishmentCode()));
            $statement->bindValue(':establishment_mnemonic', $this->getValueOrNull($organization->getEstablishmentMnemonic()));
            $statement->bindValue(':organization_name', $this->getValueOrNull($organization->getOrganizationName()));
            $statement->bindValue(':level_of_government', $this->getValueOrNull($organization->getLevelOfGovernment()));
            $statement->bindValue(':establishment_type_id', $this->getValueOrNull($organization->getEstablishmentTypeId()));
            $statement->bindValue(':state_owned_establishment_state_id', $this->getValueOrNull($organization->getStateOwnedEstablishmentStateId()));
            $statement->bindValue(':state_of_location_id', $this->getValueOrNull($organization->getStateOfLocationId()));
            $statement->bindValue(':contact_address', $this->getValueOrNull($organization->getContactAddress()));
            $statement->bindValue(':website_address', $this->getValueOrNull($organization->getWebsiteAddress()));
            $statement->bindValue(':email_address', $this->getValueOrNull($organization->getEmailAddress()));
            $statement->bindValue(':primary_phone', $this->getValueOrNull($organization->getPrimaryPhone()));
            $statement->bindValue(':parent_organization_id', $this->getValueOrNull($organization->getParentOrganizationId()));
            $statement->bindValue(':record_status', $this->getValueOrNull($organization->getStatus()));
            $statement->bindValue(':created', $this->getValueOrNull($organization->getLastModified()));
            $statement->bindValue(':created_by', $this->getValueOrNull($organization->getLastModifiedByUserId()));
            $statement->bindValue(':last_mod', $this->getValueOrNull($organization->getLastModified()));
            $statement->bindValue(':last_mod_by', $this->getValueOrNull($organization->getLastModifiedByUserId()));
            $statement->bindValue(':guid', $this->getValueOrNull($organization->getGuid()));
            $statement->execute();

            //get the id of the organization
            $query = "SELECT id FROM organization WHERE guid=:guid LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':guid', $organization->getGuid());
            $statement->execute();
            $organizationId = $statement->fetchColumn(0);

            //insert the organization categories
            $query = "DELETE FROM organization_categories WHERE organization_id=:organization_id ";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':organization_id', $organizationId);
            $statement->execute();

            if ($organization->getEstablishmentCategoryIds()) {
                $batchRecords = array();
                foreach ($organization->getEstablishmentCategoryIds() as $establishmentCategoryId) {

                    $batchRecord = array();

                    $batchRecord['organization_id'] = $organizationId;
                    $batchRecord['organization_category_type_id'] = $establishmentCategoryId;

                    $batchRecords[] = $batchRecord;
                }
                $this->pdoMultiInsert('organization_categories', $batchRecords);
            }

            //create the MDA Admin account. Will update bio-data on first login
            //now insert
            $query = "INSERT INTO user_profile 
                (
                profile_type,user_login,user_pass,organization_id,primary_role
                ,record_status,first_login,created,created_by,last_mod,last_mod_by,guid
                )
                VALUES 
                (
                :profile_type,:user_login,:user_pass,:organization_id,:primary_role
                ,:record_status,:first_login,:created,:created_by,:last_mod,:last_mod_by,:guid
                )";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':profile_type', $this->getValueOrNull($userProfile->getProfileType()));
            $statement->bindValue(':user_login', $this->getValueOrNull($userProfile->getUsername()));
            $statement->bindValue(':user_pass', $this->getValueOrNull($userProfile->getPassword()));
            $statement->bindValue(':organization_id', $this->getValueOrNull($organizationId));
            $statement->bindValue(':primary_role', $this->getValueOrNull($userProfile->getPrimaryRole()));
            $statement->bindValue(':record_status', $this->getValueOrNull($userProfile->getStatus()));
            $statement->bindValue(':first_login', $this->getValueOrNull($userProfile->getFirstLogin()));
            $statement->bindValue(':created', $this->getValueOrNull($userProfile->getLastModified()));
            $statement->bindValue(':created_by', $this->getValueOrNull($userProfile->getLastModifiedByUserId()));
            $statement->bindValue(':last_mod', $this->getValueOrNull($userProfile->getLastModified()));
            $statement->bindValue(':last_mod_by', $this->getValueOrNull($userProfile->getLastModifiedByUserId()));
            $statement->bindValue(':guid', $this->getValueOrNull($userProfile->getGuid()));
            $statement->execute();

            //commit
            $this->connection->commit();

            $outcome = true;

        } catch (Throwable $e) {
            //roll back
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

    public function getOrganization($guid):?Organization
    {
        $organization = null;
        $statement = null;

        try {
            $query = "SELECT d.id,d.establishment_code,d.establishment_mnemonic,d.organization_name 
                ,d.establishment_type_id,d.state_owned_establishment_state_id,d.state_of_location_id 
                ,d.contact_address,d.website_address,d.email_address,d.primary_phone,d.record_status,d.guid 
                ,d.parent_organization_id, d.fcc_committee_id, d.fcc_desk_officer_id
                ,e.description as _establishment_type_name
                ,s.state_code as _state_owned_establishment_state_code,s.state_name as _state_owned_establishment_state_name 
                ,sl.state_name as _state_of_location_name 
                ,o.organization_name as _parent_organization_name, c.committee_name as _fcc_committee_name 
                ,concat_ws(' ', u.first_name,u.last_name) as _fcc_desk_officer_name 
                ,u.primary_phone as _fcc_desk_officer_phone,u.email_address as _fcc_desk_officer_email 
                ,m.id as _mda_desk_officer_id,concat_ws(' ', m.first_name,m.last_name) as _mda_desk_officer_name 
                ,m.email_address as _mda_desk_officer_email,m.primary_phone as _mda_desk_officer_phone 
                FROM organization d 
                LEFT JOIN establishment_types e on d.establishment_type_id=e.id 
                LEFT JOIN states s on d.state_owned_establishment_state_id=s.id 
                LEFT JOIN states sl on d.state_of_location_id=sl.id  
                LEFT JOIN organization o on d.parent_organization_id=o.id 
                LEFT JOIN committees c on d.fcc_committee_id=c.id 
                LEFT JOIN user_profile u on (d.fcc_committee_id = u.fcc_committee_id 
                                                  and u.primary_role=:fcc_desk_officer_role)
                LEFT JOIN user_profile m on d.establishment_code=m.user_login 
                WHERE d.guid=:guid ";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':fcc_desk_officer_role', AppConstants::ROLE_FCC_DESK_OFFICER_FEDERAL);
            $statement->bindValue(':guid', $guid);
            $statement->execute();

            $record = $statement->fetch(PDO::FETCH_ASSOC);
            if ($record) {
                $organization = new Organization();
                $organization->setId($record['id']);
                $organization->setEstablishmentCode($record['establishment_code']);
                $organization->setEstablishmentMnemonic($record['establishment_mnemonic']);
                $organization->setOrganizationName($record['organization_name']);

                $organization->setEstablishmentTypeId($record['establishment_type_id']);
                $organization->setEstablishmentTypeName($record['_establishment_type_name']);

                $organization->setStateOwnedEstablishmentStateId($record['state_owned_establishment_state_id']);
                $organization->setStateOwnedEstablishmentStateCode($record['_state_owned_establishment_state_code']);
                $organization->setStateOwnedEstablishmentStateName($record['_state_owned_establishment_state_name']);

                $organization->setStateOfLocationId($record['state_of_location_id']);
                $organization->setStateOfLocationName($record['_state_of_location_name']);

                $organization->setContactAddress($record['contact_address']);
                $organization->setWebsiteAddress($record['website_address']);
                $organization->setEmailAddress($record['email_address']);
                $organization->setPrimaryPhone($record['primary_phone']);

                $organization->setParentOrganizationId($record['parent_organization_id']);
                $organization->setParentOrganizationName($record['_parent_organization_name']);

                $organization->setFccCommitteeId($record['fcc_committee_id']);
                $organization->setFccCommitteeName($record['_fcc_committee_name']);

                $organization->setFccDeskOfficerId($record['fcc_desk_officer_id']);
                $organization->setFccDeskOfficerName($record['_fcc_desk_officer_name']);
                $organization->setFccDeskOfficerPhone($record['_fcc_desk_officer_phone']);
                $organization->setFccDeskOfficerEmail($record['_fcc_desk_officer_email']);

                $organization->setMdaDeskOfficerId($record['_mda_desk_officer_id']);
                $organization->setMdaDeskOfficerName($record['_mda_desk_officer_name']);
                $organization->setMdaDeskOfficerEmail($record['_mda_desk_officer_email']);
                $organization->setMdaDeskOfficerPhone($record['_mda_desk_officer_phone']);

                $organization->setStatus($record['record_status']);
                $organization->setGuid($record['guid']);

                //get the categories
                $query = "select organization_category_type_id from organization_categories where organization_id=:organization_id";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':organization_id', $organization->getId());
                $statement->execute();

                $organizationCategories = $statement->fetchAll();

                $organizationCategoryIds = array();
                if($organizationCategories){
                    foreach ($organizationCategories as $organizationCategory){
                        $organizationCategoryIds[] = $organizationCategory['organization_category_type_id'];
                    }
                }

                $organization->setEstablishmentCategoryIds($organizationCategoryIds);
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $organization;
    }

    public function editOrganization(Organization $organization): bool
    {
        $outcome = false;
        $statement = null;

        try {

            //check for duplicate code
            $query = "SELECT id FROM organization WHERE establishment_code=:establishment_code AND guid<>:guid LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':establishment_code', $organization->getEstablishmentCode());
            $statement->bindValue(':guid', $organization->getGuid());
            $statement->execute();
            $existingUser = $statement->fetch();

            if ($existingUser) {
                throw new AppException(AppConstants::DUPLICATE_USERNAME);
            }

            //check duplicate email
            $query = "SELECT id FROM organization WHERE email_address=:email_address AND guid<>:guid LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':email_address', $organization->getEmailAddress());
            $statement->bindValue(':guid', $organization->getGuid());
            $statement->execute();
            $existingUser = $statement->fetch();

            if ($existingUser) {
                throw new AppException(AppConstants::DUPLICATE_EMAIL);
            }

            //check duplicate prim phone
            $query = "SELECT id FROM organization WHERE primary_phone=:primary_phone AND guid<>:guid LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':primary_phone', $organization->getPrimaryPhone());
            $statement->bindValue(':guid', $organization->getGuid());
            $statement->execute();
            $existingUser = $statement->fetch();

            if ($existingUser) {
                throw new AppException(AppConstants::DUPLICATE_PRIMARY_PHONE);
            }

            //check duplicate sec phone
            /*if ($organization->getSecondaryPhone()) {
                $query = "SELECT id FROM organization WHERE secondary_phone=:secondary_phone AND guid<>:guid LIMIT 1";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':secondary_phone', $organization->getSecondaryPhone());
                $statement->bindValue(':guid', $organization->getGuid());
                $statement->execute();
                $existingUser = $statement->fetch();

                if ($existingUser) {
                    throw new AppException(AppConstants::DUPLICATE_SECONDARY_PHONE);
                }
            }*/

            $this->connection->beginTransaction();

            //now update
            $query = "UPDATE organization 
                SET establishment_code=:establishment_code, establishment_mnemonic=:establishment_mnemonic
                , organization_name=:organization_name, establishment_type_id=:establishment_type_id
                ,state_owned_establishment_state_id=:state_owned_establishment_state_id, state_of_location_id=:state_of_location_id
                ,contact_address=:contact_address, website_address=:website_address, email_address=:email_address
                ,primary_phone=:primary_phone
                ,parent_organization_id=:parent_organization_id
                , last_mod=:last_mod, last_mod_by=:last_mod_by 
                WHERE guid=:guid";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':establishment_code', $this->getValueOrNull($organization->getEstablishmentCode()));
            $statement->bindValue(':establishment_mnemonic', $this->getValueOrNull($organization->getEstablishmentMnemonic()));
            $statement->bindValue(':organization_name', $this->getValueOrNull($organization->getOrganizationName()));
            $statement->bindValue(':establishment_type_id', $this->getValueOrNull($organization->getEstablishmentTypeId()));
            $statement->bindValue(':state_owned_establishment_state_id', $this->getValueOrNull($organization->getStateOwnedEstablishmentStateId()));
            $statement->bindValue(':state_of_location_id', $this->getValueOrNull($organization->getStateOfLocationId()));
            $statement->bindValue(':contact_address', $this->getValueOrNull($organization->getContactAddress()));
            $statement->bindValue(':website_address', $this->getValueOrNull($organization->getWebsiteAddress()));
            $statement->bindValue(':email_address', $this->getValueOrNull($organization->getEmailAddress()));
            $statement->bindValue(':primary_phone', $this->getValueOrNull($organization->getPrimaryPhone()));
            $statement->bindValue(':parent_organization_id', $this->getValueOrNull($organization->getParentOrganizationId()));
            $statement->bindValue(':last_mod', $this->getValueOrNull($organization->getLastModified()));
            $statement->bindValue(':last_mod_by', $this->getValueOrNull($organization->getLastModifiedByUserId()));
            $statement->bindValue(':guid', $this->getValueOrNull($organization->getGuid()));
            $statement->execute();

            //get the id of the organization
            $query = "SELECT id FROM organization WHERE guid=:guid LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':guid', $organization->getGuid());
            $statement->execute();
            $organizationId = $statement->fetchColumn(0);

            //insert the organization categories
            $query = "DELETE FROM organization_categories WHERE organization_id=:organization_id ";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':organization_id', $organizationId);
            $statement->execute();

            if ($organization->getEstablishmentCategoryIds()) {
                $batchRecords = array();
                foreach ($organization->getEstablishmentCategoryIds() as $establishmentCategoryId) {

                    $batchRecord = array();

                    $batchRecord['organization_id'] = $organizationId;
                    $batchRecord['organization_category_type_id'] = $establishmentCategoryId;

                    $batchRecords[] = $batchRecord;
                }
                $this->pdoMultiInsert('organization_categories', $batchRecords);
            }

            $this->connection->commit();
            $outcome = true;

        } catch (Throwable $e) {
            //roll back
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

    public function deleteOrganization(Organization $organization): bool
    {
        $outcome = false;
        $statement = null;

        try {

            //now delete
            $query = "UPDATE organization "
                . "SET record_status=:record_status "
                . ",last_mod=:last_mod,last_mod_by=:last_mod_by  "
                . "WHERE guid=:guid";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':record_status', $organization->getStatus());
            $statement->bindValue(':last_mod', $organization->getLastModified());
            $statement->bindValue(':last_mod_by', $organization->getLastModifiedByUserId());
            $statement->bindValue(':guid', $organization->getGuid());

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