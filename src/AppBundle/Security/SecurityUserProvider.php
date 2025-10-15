<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 11/15/2016
 * Time: 11:45 PM
 */

namespace AppBundle\Security;


use AppBundle\Model\Security\PrivilegeChecker;
use AppBundle\Model\Security\SecurityUser;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\FileUploadHelper;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class SecurityUserProvider implements UserProviderInterface
{

    private $connection;

    /**
     * SecurityUserProvider constructor.
     * @param $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function loadUserByUsername($username)
    {
        $stmt = null;
        $userProfile = null;
        try {
            //$query = "select * from user_profile d WHERE d.user_login = :user_login";
            $query = "SELECT d.id,d.profile_type,d.user_login,d.user_pass,d.first_name,d.last_name,d.middle_name,d.email_address 
                ,d.primary_phone,d.secondary_phone,d.organization_id,d.state_of_posting_id,d.fcc_department_id 
                ,d.fcc_supervisor_id,d.primary_role,d.record_status,d.first_login,d.profile_picture_file_name,d.guid 
                ,IFNULL(concat(d.first_name,' ',d.last_name),d.user_login) AS display_name 
                ,sp.state_code AS state_of_posting_code,sp.state_name AS state_of_posting_name 
                ,dept.department_code,dept.department_name
                ,fcc_desk_officer_committee.id AS fcc_desk_officer_committee_id
                ,fcc_desk_officer_committee.committee_name AS fcc_desk_officer_committee_name
                ,committee_member_committee.id AS committee_member_committee_id
                ,committee_member_committee.committee_name AS committee_member_committee_name
                ,committee_secretary_committee.id as committee_secretary_committee_id
                ,committee_secretary_committee.committee_name as committee_secretary_committee_name
                ,committee_chairman_committee.id as committee_chairman_committee_id
                ,committee_chairman_committee.committee_name as committee_chairman_committee_name
                ,o.establishment_code,o.establishment_mnemonic,o.organization_name,o.establishment_type_id , o.level_of_government
                ,baseline.baseline_year
                ,concat_ws(' ',u.first_name, u.last_name) AS supervisor_name, u.primary_phone AS supervisor_phone 
                ,u.email_address AS supervisor_email 
                ,r.role_description 
                FROM user_profile d 
                LEFT JOIN states sp ON d.state_of_posting_id=sp.id  
                LEFT JOIN departments dept ON d.fcc_department_id=dept.id  
                LEFT JOIN committees fcc_desk_officer_committee ON d.fcc_committee_id=fcc_desk_officer_committee.id  
                LEFT JOIN committee_members committee_member ON d.id=committee_member.staff_user_profile_id  
                LEFT JOIN committees committee_member_committee ON committee_member.committee_id=committee_member_committee.id  
                LEFT JOIN committees committee_secretary_committee ON d.id=committee_secretary_committee.secretary_user_profile_id  
                LEFT JOIN committees committee_chairman_committee ON d.id=committee_chairman_committee.chairman_user_profile_id  
                JOIN organization o ON d.organization_id=o.id  
                LEFT JOIN mda_baseline_year AS baseline ON o.id = baseline.organization_id
                LEFT JOIN user_profile u ON d.fcc_supervisor_id=u.id  
                JOIN system_roles r ON d.primary_role=r.id  
                WHERE d.user_login = :user_login AND d.record_status = :record_status";

            $stmt = $this->connection->prepare($query);
            $stmt->bindValue(':user_login', $username);
            $stmt->bindValue(':record_status', AppConstants::ACTIVE);

            $stmt->execute();

            $userRecord = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($userRecord) {
                $userId = $userRecord['id'];
                $userPass = $userRecord['user_pass'];
                $salt = null;

                //fetch the roles
                /*$query = "select role_id from user_roles d WHERE d.user_id = :user_id";
                $stmt = $this->connection->prepare($query);
                $stmt->bindValue(':user_id', $userId);
                $stmt->execute();
                $roles = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);*/

                $roles = array('ROLE_USER'); //fix later
                $userRoles = array_merge(array($userRecord['primary_role']), $roles);

                //fetch the role privileges
                $userRoleInString = implode("','", $userRoles);
                $query = "select distinct(privilege_id) as _privilege from system_role_privileges d WHERE d.role_id in ('$userRoleInString')";
                $stmt = $this->connection->prepare($query);
                $stmt->execute();

                $userPrivileges = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);

                $userProfile = new SecurityUser($username, $userPass, $salt, $userRoles); //username and password is set after this line

                $userProfile->setId($userRecord['id']);
                $userProfile->setProfileType($userRecord['profile_type']);
                $userProfile->setDisplayName($userRecord['display_name']);
                $userProfile->setFirstName($userRecord['first_name']);
                $userProfile->setLastName($userRecord['last_name']);
                $userProfile->setEmailAddress($userRecord['email_address']);
                $userProfile->setPrimaryPhone($userRecord['primary_phone']);
                $userProfile->setSecondaryPhone($userRecord['secondary_phone']);

                $userProfile->setOrganizationId($userRecord['organization_id']);
                $userProfile->setOrganizationName($userRecord['organization_name']);
                $userProfile->setOrganizationEstablishmentCode($userRecord['establishment_code']);
                $userProfile->setOrganizationMnemonic($userRecord['establishment_mnemonic']);
                $userProfile->setOrganizationEstablishmentType($userRecord['establishment_type_id']);
                $userProfile->setOrganizationBaseLineYear($userRecord['baseline_year']);
                $userProfile->setOrganizationLevelOfGovernment($userRecord['level_of_government']);

                $userProfile->setStateOfPostingId($userRecord['state_of_posting_id']);
                $userProfile->setStateOfPostingCode($userRecord['state_of_posting_code']);
                $userProfile->setStateOfPostingName($userRecord['state_of_posting_name']);

                $userProfile->setFccDepartmentId($userRecord['fcc_department_id']);
                $userProfile->setFccDepartmentCode($userRecord['department_code']);
                $userProfile->setFccDepartmentName($userRecord['department_name']);

                $userProfile->setFccSupervisorId($userRecord['fcc_supervisor_id']);
                $userProfile->setFccSupervisorName($userRecord['supervisor_name']);
                $userProfile->setFccSupervisorPhone($userRecord['supervisor_phone']);
                $userProfile->setFccSupervisorEmail($userRecord['supervisor_email']);

                $userProfile->setPrimaryRole($userRecord['primary_role']);
                $userProfile->setPrimaryRoleDescription($userRecord['role_description']);

                switch ($userProfile->getPrimaryRole()) {
                    case AppConstants::ROLE_FCC_DESK_OFFICER_FEDERAL:
                        $userProfile->setFccCommitteeId($userRecord['fcc_desk_officer_committee_id']);
                        $userProfile->setFccCommitteeName($userRecord['fcc_desk_officer_committee_name']);
                        break;

                    case AppConstants::ROLE_FCC_COMMISSIONER:
                        $userProfile->setFccCommitteeId($userRecord['committee_member_committee_id']);
                        $userProfile->setFccCommitteeName($userRecord['committee_member_committee_name']);
                        break;

                    case AppConstants::ROLE_FCC_COMMITTEE_SECRETARY:
                        $userProfile->setFccCommitteeId($userRecord['committee_secretary_committee_id']);
                        $userProfile->setFccCommitteeName($userRecord['committee_secretary_committee_name']);
                        break;

                    case AppConstants::ROLE_FCC_COMMITTEE_CHAIRMAN:
                        $userProfile->setFccCommitteeId($userRecord['committee_chairman_committee_id']);
                        $userProfile->setFccCommitteeName($userRecord['committee_chairman_committee_id_name']);
                        break;
                }

                $userProfile->setStatus($userRecord['record_status']);
                $userProfile->setFirstLogin($userRecord['first_login']);
                $userProfile->setProfilePictureFileName($userRecord['profile_picture_file_name']);
                $userProfile->setGuid($userRecord['guid']);

                if ($userProfile->getProfilePictureFileName()) {
                    $fileUploadHelper = new FileUploadHelper();
                    $mdaEstablishmentCode = $userProfile->getOrganizationEstablishmentCode();
                    $picturePreviewUrl = $fileUploadHelper->getProfileUrl() . $mdaEstablishmentCode . "/" . $userProfile->getProfilePictureFileName();

                    $userProfile->setProfilePictureDisplayUrl($picturePreviewUrl);
                }

                //$userProfile->initialize($userProfile->getPrimaryRole());

                $privilegeChecker = new PrivilegeChecker($userProfile->getPrimaryRole()
                    , $userRoles
                    , $userPrivileges
                    , $userProfile->getUsername()
                    , $userProfile->getOrganizationEstablishmentCode());
                $privilegeChecker->initRoles();

                $userProfile->setPrivilegeChecker($privilegeChecker);

            }
        } catch (\Exception $e) {
            throw new UsernameNotFoundException(
                sprintf('An exception occured: Username "%s" could not be retrieved.', $username)
            );
        } finally {
            if ($stmt) {
                $stmt->closeCursor();
            }
        }

        if ($userProfile) {
            return $userProfile;
        } else {
            throw new UsernameNotFoundException(
                sprintf('Username "%s" does not exist.', $username)
            );
        }
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof SecurityUser) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'AppBundle\Model\Security\SecurityUser';
    }

}