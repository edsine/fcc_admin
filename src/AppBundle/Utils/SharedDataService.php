<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 9/18/2016
 * Time: 10:10 AM
 */

namespace AppBundle\Utils;


use AppBundle\Model\Organizations\Organization;
use AppBundle\Model\Security\SystemPrivilege;
use Doctrine\DBAL\Connection;
use \Throwable;

class SharedDataService
{

    private $connection;

    /**
     * SharedDataService constructor.
     * @param $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getNationalityCode()
    {
        $nationality = array();
        $stmt = null;
        try {
            $query = "SELECT d.id,d.description FROM nationality_code d WHERE d.record_status = :record_status";
            $stmt = $this->connection->prepare($query);
            $stmt->bindValue(':record_status', 'ACTIVE');

            $stmt->execute();

            $nationality = $stmt->fetchAll();
        } catch (Throwable $t) {
        } finally {
            if($stmt){
                $stmt->closeCursor();
            }
        }
        return $nationality;
    }

    public function getGeoPoliticalZones()
    {
        $regions = array();
        $stmt = null;
        try {
            $query = "SELECT d.id,d.zone_code,d.zone_name FROM geo_political_zone d WHERE d.record_status = :record_status";
            $stmt = $this->connection->prepare($query);
            $stmt->bindValue(':record_status', 'ACTIVE');

            $stmt->execute();

            $regions = $stmt->fetchAll();
        } catch (Throwable $t) {
        } finally {
            if($stmt){
                $stmt->closeCursor();
            }
        }
        return $regions;
    }

    public function getNigerianStates()
    {

        $states = array();
        $stmt = null;
        try {
            $query = "SELECT d.id,d.state_code,d.state_name FROM states d WHERE d.record_status = :record_status";
            $stmt = $this->connection->prepare($query);
            $stmt->bindValue(':record_status', 'ACTIVE');

            $stmt->execute();

            $states = $stmt->fetchAll();
        } catch (Throwable $t) {
        } finally {
            if($stmt){
                $stmt->closeCursor();
            }
        }
        return $states;
    }

    public function getStateLgas($stateId)
    {

        $stateLgas = array();
        $stmt = null;
        try {
            $query = "SELECT d.id,d.plate_code,d.lga_code,d.lga_name FROM lga d WHERE d.state_id = :state_id AND d.record_status = :record_status";
            $stmt = $this->connection->prepare($query);
            $stmt->bindValue(':state_id', $stateId);
            $stmt->bindValue(':record_status', 'ACTIVE');

            $stmt->execute();

            $stateLgas = $stmt->fetchAll();
        } catch (Throwable $t) {
        } finally {
            if($stmt){
                $stmt->closeCursor();
            }
        }
        return $stateLgas;
    }

    public function loadStateLgas()
    {

        $allStateLgas = array();
        $stmt = null;
        try {
            $query = "SELECT d.id,d.plate_code,d.lga_code,d.lga_name,d.state_id FROM lga d WHERE d.record_status = :record_status ORDER BY d.state_id, d.lga_name";
            $stmt = $this->connection->prepare($query);
            $stmt->bindValue(':record_status', 'ACTIVE');

            $stmt->execute();

            $records = $stmt->fetchAll();

            $allStateLgas = array();

            foreach ($records as $lga) {
                if (array_key_exists($lga["state_id"], $allStateLgas)) {
                    $allStateLgas[$lga["state_id"]][] = $lga;
                } else {
                    $allStateLgas[$lga["state_id"]] = array($lga); //state_id => array(of lga rows)
                }
            }
        } catch (Throwable $t) {
        } finally {
            if($stmt){
                $stmt->closeCursor();
            }
        }
        return $allStateLgas;
    }

    public function getCommittees()
    {
        $committees = array();
        $stmt = null;
        try {
            $query = "SELECT d.id,d.committee_name FROM committees d WHERE d.record_status = :record_status";
            $stmt = $this->connection->prepare($query);
            $stmt->bindValue(':record_status', 'ACTIVE');

            $stmt->execute();

            $committees = $stmt->fetchAll();
        } catch (Throwable $t) {
        } finally {
            if($stmt){
                $stmt->closeCursor();
            }
        }
        return $committees;
    }

    public function getOrganizations()
    {
        $organizations = array();
        $stmt = null;
        try {
            $query = "select d.id,d.organization_name,d.level_of_government,d.establishment_code,d.establishment_type_id
                    ,d.state_owned_establishment_state_id,d.fcc_committee_id,desk_officer.id as fcc_desk_officer_id 
                    ,
                    (
                    select count(*) from federal_level_nominal_roll_submissions s 
                      where s.organization_id = d.id and s.processing_status=:processing_status
                    ) as total_submissions_processed
                    from organization d 
                    LEFT JOIN committees c on d.fcc_committee_id=c.id 
                    LEFT JOIN user_profile desk_officer on (d.fcc_committee_id = desk_officer.fcc_committee_id 
                                              and desk_officer.primary_role=:fcc_desk_officer_role)
                    WHERE d.record_status = :record_status 
                    order by d.establishment_type_id, d.organization_name, total_submissions_processed desc ";
            $stmt = $this->connection->prepare($query);
            $stmt->bindValue(':processing_status', AppConstants::COMPLETED);
            $stmt->bindValue(':fcc_desk_officer_role', AppConstants::ROLE_FCC_DESK_OFFICER_FEDERAL);
            $stmt->bindValue(':record_status', 'ACTIVE');

            $stmt->execute();

            $organizations = $stmt->fetchAll();
        } catch (Throwable $t) {

        } finally {
            if($stmt){
                $stmt->closeCursor();
            }
        }
        return $organizations;
    }

    public function getSubmissionYearsProcessedForOrganization($organizationId)
    {
        $processedYears = array();
        $stmt = null;
        try {
            $query = "SELECT distinct d.submission_year
                      FROM federal_level_nominal_roll_submissions d 
                      WHERE d.organization_id = :organization_id 
                      and d.processing_status = :processing_status 
                      ORDER BY d.submission_year desc";
            $stmt = $this->connection->prepare($query);
            $stmt->bindValue(':organization_id', $organizationId);
            $stmt->bindValue(':processing_status', AppConstants::COMPLETED);

            $stmt->execute();

            $processedYears = $stmt->fetchAll();
        } catch (Throwable $t) {
        } finally {
            if($stmt){
                $stmt->closeCursor();
            }
        }
        return $processedYears;
    }

    public function getDepartments()
    {
        $committees = array();
        $stmt = null;
        try {
            $query = "SELECT d.id,d.department_name FROM departments d WHERE d.record_status = :record_status";
            $stmt = $this->connection->prepare($query);
            $stmt->bindValue(':record_status', 'ACTIVE');

            $stmt->execute();

            $committees = $stmt->fetchAll();
        } catch (Throwable $t) {
        } finally {
            if($stmt){
                $stmt->closeCursor();
            }
        }
        return $committees;
    }

    public function getFCCRoles()
    {
        $roles = array();
        $stmt = null;
        try {
            $query = "SELECT d.id,d.role_description FROM system_roles d WHERE d.role_category_id=:role_category_id AND d.record_status = :record_status AND d.is_reserved='N'";
            $stmt = $this->connection->prepare($query);

            $stmt->bindValue(':role_category_id', AppConstants::FCC);
            $stmt->bindValue(':record_status', 'ACTIVE');

            $stmt->execute();

            $roles = $stmt->fetchAll();
        } catch (Throwable $t) {
        } finally {
            if($stmt){
                $stmt->closeCursor();
            }
        }
        return $roles;
    }

    public function getMDARoles()
    {
        $roles = array();
        $stmt = null;
        try {
            $query = "SELECT d.id,d.role_description FROM system_roles d WHERE d.role_category_id=:role_category_id AND d.record_status = :record_status AND d.is_reserved='N'";
            $stmt = $this->connection->prepare($query);

            $stmt->bindValue(':role_category_id', AppConstants::MDA);
            $stmt->bindValue(':record_status', 'ACTIVE');

            $stmt->execute();

            $roles = $stmt->fetchAll();
        } catch (Throwable $t) {
        } finally {
            if($stmt){
                $stmt->closeCursor();
            }
        }
        return $roles;
    }

    public function getFCCUsers()
    {
        $roles = array();
        $stmt = null;
        try {
            $query = "select d.id,concat(d.first_name,' ',d.last_name) as user_info, d.primary_phone, d.email_address, d.primary_role 
                ,d.state_of_posting_id 
                 from user_profile d 
                 WHERE d.profile_type=:profile_type and d.record_status = :record_status order by d.first_name";
            $stmt = $this->connection->prepare($query);

            $stmt->bindValue(':profile_type', AppConstants::FCC_USER);
            $stmt->bindValue(':record_status', 'ACTIVE');

            $stmt->execute();

            $roles = $stmt->fetchAll();
        } catch (Throwable $t) {
        } finally {
            if($stmt){
                $stmt->closeCursor();
            }
        }
        return $roles;
    }

    public function getClientOrganization(): ?Organization
    {
        $clientOrganization = new Organization();
        $stmt = null;
        try {
            $query = "select d.id,d.establishment_code,d.establishment_mnemonic,d.organization_name,d.guid 
                FROM organization d 
                WHERE d.is_client=:is_client AND d.record_status = :record_status";
            $stmt = $this->connection->prepare($query);
            $stmt->bindValue(':is_client', AppConstants::Y);
            $stmt->bindValue(':record_status', 'ACTIVE');

            $stmt->execute();

            $record = $stmt->fetch();
            if ($record) {
                $clientOrganization->setId($record['id']);
                $clientOrganization->setEstablishmentCode($record['establishment_code']);
                $clientOrganization->setEstablishmentMnemonic($record['establishment_mnemonic']);
                $clientOrganization->setOrganizationName($record['organization_name']);
                $clientOrganization->setGuid($record['guid']);
            }
        } catch (Throwable $t) {
        } finally {
            if($stmt){
                $stmt->closeCursor();
            }
        }
        return $clientOrganization;
    }

    public function getSubmissionYears()
    {
        $organizations = array();
        $stmt = null;
        try {
            $query = "SELECT d.submission_year FROM submission_demand_schedule d ORDER BY d.submission_year DESC";
            $stmt = $this->connection->prepare($query);

            $stmt->execute();

            $organizations = $stmt->fetchAll();
        } catch (Throwable $t) {
        } finally {
            if($stmt){
                $stmt->closeCursor();
            }
        }
        return $organizations;
    }

    public function getDownloadCategories()
    {
        $downloadCategories = array();
        $stmt = null;
        try {
            $query = "SELECT d.id, d.title FROM download_categories d ORDER BY d.id DESC";
            $stmt = $this->connection->prepare($query);
            $stmt->execute();

            $downloadCategories = $stmt->fetchAll();
        } catch (Throwable $t) {
        } finally {
            if($stmt){
                $stmt->closeCursor();
            }
        }
        return $downloadCategories;
    }

    public function getSystemPrivileges()
    {
        $systemPrivileges = array();
        $stmt = null;
        try {
            $query = "select d.id as privilege_id,d.description as privilege_description,d.category_id "
                . ",c.description as category_name "
                . "from system_privileges d "
                . "JOIN system_privilege_categories c on d.category_id=c.id "
                . "ORDER BY d.category_id asc";
            $stmt = $this->connection->prepare($query);

            $stmt->execute();

            $privileges = $stmt->fetchAll();

            if ($privileges) {
                foreach ($privileges as $privilege) {
                    $category = $privilege['category_id'];

                    $systemPrivilege = new SystemPrivilege();
                    $systemPrivilege->setId($privilege['privilege_id']);
                    $systemPrivilege->setDescription($privilege['privilege_description']);

                    if (array_key_exists($category, $systemPrivileges)) {
                        $systemPrivileges[$category][] = $systemPrivilege;
                    } else {
                        $systemPrivileges[$category] = array();
                        $systemPrivileges[$category][] = $systemPrivilege;
                    }
                }
            }

        } catch (Throwable $t) {
        } finally {
            if($stmt){
                $stmt->closeCursor();
            }
        }
        return $systemPrivileges;
    }

    public function getSystemRolePrivileges()
    {
        $rolePrivileges = array();
        $stmt = null;
        try {

            $query = "SELECT id FROM system_roles WHERE record_status='ACTIVE'";
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            $systemRoles = $stmt->fetchAll();

            if ($systemRoles) {
                foreach ($systemRoles as $systemRole) {
                    $rolePrivileges[$systemRole['id']] = array();
                }

                //now select role privileges and sort
                $query = "SELECT d.role_id,d.privilege_id FROM system_role_privileges d "
                    . "ORDER BY d.role_id ASC";
                $stmt = $this->connection->prepare($query);
                $stmt->execute();
                $rolePrivilegeRecords = $stmt->fetchAll();

                if ($rolePrivilegeRecords) {
                    foreach ($rolePrivilegeRecords as $rolePrivilegeRecord) {
                        $rolePrivileges[$rolePrivilegeRecord['role_id']][] = $rolePrivilegeRecord['privilege_id'];
                    }
                }

            }


        } catch (Throwable $t) {
        } finally {
            if($stmt){
                $stmt->closeCursor();
            }
        }
        return $rolePrivileges;
    }

    public function getRoleCategories()
    {
        $roleCategories = array();
        $stmt = null;
        try {
            $query = "SELECT d.id,d.description FROM system_role_categories d";
            $stmt = $this->connection->prepare($query);

            $stmt->execute();

            $roleCategories = $stmt->fetchAll();
        } catch (Throwable $t) {
        } finally {
            if($stmt){
                $stmt->closeCursor();
            }
        }
        return $roleCategories;
    }

    public function getStaticContent()
    {
        $staticContents = array();
        $stmt = null;
        try {
            $query = "SELECT d.content_code,d.content FROM static_text_cms d";
            $stmt = $this->connection->prepare($query);

            $stmt->execute();

            $records = $stmt->fetchAll();

            if ($records) {
                foreach ($records as $record) {
                    $staticContents[$record['content_code']] = html_entity_decode($record['content'], ENT_QUOTES);
                }
            }
        } catch (Throwable $t) {
        } finally {
            if($stmt){
                $stmt->closeCursor();
            }
        }
        return $staticContents;
    }

    public function getCareerGradeLevels()
    {
        $careerOfficeGradeLevels = array();
        $stmt = null;
        try {
            $query = "SELECT d.grade_level_code,d.description FROM grade_level_codes d WHERE d.post_category=:post_category";
            $stmt = $this->connection->prepare($query);
            $stmt->bindValue(":post_category", 'CAREER_CIVIL_SERVANT');

            $stmt->execute();

            $careerOfficeGradeLevels = $stmt->fetchAll();
        } catch (Throwable $t) {
        } finally {
            if($stmt){
                $stmt->closeCursor();
            }
        }
        return $careerOfficeGradeLevels;
    }

    public function getPoliticalGradeLevels()
    {
        $politicalOfficeGradeLevels = array();
        $stmt = null;
        try {
            $query = "SELECT d.grade_level_code,d.description FROM grade_level_codes d WHERE d.post_category=:post_category";
            $stmt = $this->connection->prepare($query);
            $stmt->bindValue(":post_category", 'POLITICAL_OFFICE_HOLDER');

            $stmt->execute();

            $politicalOfficeGradeLevels = $stmt->fetchAll();
        } catch (Throwable $t) {
        } finally {
            if($stmt){
                $stmt->closeCursor();
            }
        }
        return $politicalOfficeGradeLevels;
    }

    public function getMaritalStatus()
    {
        $maritalStatus = array();
        $stmt = null;
        try {
            $query = "SELECT d.id,d.description FROM marital_status d WHERE d.record_status=:record_status";
            $stmt = $this->connection->prepare($query);
            $stmt->bindValue(":record_status", AppConstants::ACTIVE);

            $stmt->execute();

            $maritalStatus = $stmt->fetchAll();
        } catch (Throwable $t) {
        } finally {
            if($stmt){
                $stmt->closeCursor();
            }
        }
        return $maritalStatus;
    }

    public function getOrganizationCategoryTypes()
    {
        $organizationCategoryTypes = array();
        $stmt = null;
        try {
            $query = "SELECT d.id,d.description FROM organization_category_types d WHERE d.record_status=:record_status";
            $stmt = $this->connection->prepare($query);
            $stmt->bindValue(":record_status", AppConstants::ACTIVE);

            $stmt->execute();

            $organizationCategoryTypes = $stmt->fetchAll();
        } catch (Throwable $t) {
        } finally {
            if($stmt){
                $stmt->closeCursor();
            }
        }
        return $organizationCategoryTypes;
    }

    public function getCBIRecruitmentCategories()
    {
        $cbiRecruitmentCategories = array();
        $stmt = null;
        try {
            $query = "SELECT d.id,d.description FROM cbi_report_recruitment_categories d WHERE d.record_status=:record_status";
            $stmt = $this->connection->prepare($query);
            $stmt->bindValue(":record_status", AppConstants::ACTIVE);

            $stmt->execute();

            $cbiRecruitmentCategories = $stmt->fetchAll();
        } catch (Throwable $t) {
        } finally {
            if($stmt){
                $stmt->closeCursor();
            }
        }
        return $cbiRecruitmentCategories;
    }

    /*public function getAvailableMembersAndMdaForCommittee($committeeId)
    {
        $availableMembersAndMdas = array();

        $statement = null;

        try {
            //get the members
            $query = "SELECT user_profile.id AS user_profile_id,user_profile.first_name,user_profile.last_name
                      FROM user_profile AS user_profile
                      WHERE user_profile.fcc_committee_id IS NULL OR user_profile.fcc_committee_id <> :fcc_committee_id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':fcc_committee_id', $committeeId);
            $statement->execute();

            $availableMemberRecords = $statement->fetchAll();
            $availableMembersAndMdas['available_members'] = $availableMemberRecords;

            //fetch the mdas
            $query = "SELECT organization.id AS organization_id, organization.organization_name
                      FROM organization AS organization
                      WHERE organization.fcc_committee_id IS NULL";
            $statement = $this->connection->prepare($query);
            $statement->execute();

            $availableMdas = $statement->fetchAll();
            $availableMembersAndMdas['available_mdas'] = $availableMdas;

        } catch (Throwable $e) {
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $availableMembersAndMdas;
    }*/

}