<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 11/15/2016
 * Time: 11:39 PM
 */

namespace AppBundle\Model\Security;


use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SecurityUser implements UserInterface, EquatableInterface
{

    private $id;
    private $username;
    private $password;
    private $plainPassword;
    private $salt;
    private $roles;

    private $profileType;
    private $displayName;
    private $firstName;
    private $lastName;
    private $middleName;
    private $emailAddress;
    private $primaryPhone;
    private $secondaryPhone;
    private $stateOfPostingId, $stateOfPostingCode, $stateOfPostingName;
    private $fccDepartmentId, $fccDepartmentCode, $fccDepartmentName;
    private $fccCommitteeId, $fccCommitteeName;
    private $fccSupervisorId, $fccSupervisorName, $fccSupervisorPhone, $fccSupervisorEmail;
    private $organizationId, $organizationEstablishmentCode, $organizationMnemonic, $organizationName, $organizationEstablishmentType;
    private $organizationBaseLineYear, $organizationLevelOfGovernment;
    private $primaryRole, $primaryRoleDescription;
    private $status;
    private $firstLogin;
    private $profilePictureFileName, $profilePictureDisplayUrl;
    private $lastModified;
    private $lastModifiedByUserId;
    private $guid;

    private $uploadedPhotoFile,$uploadedPhotoFileName;

    private $displaySerialNo;

    private $privilegeChecker;

    public function __construct($username, $password, $salt, array $roles)
    {
        $this->username = $username;
        $this->password = $password;
        $this->salt = $salt;
        $this->roles = $roles;
    }


    /*public function initialize($primaryRole)
    {
        switch ($primaryRole) {
            //FCC ROLES
            case AppConstants::ROLE_SUPER_ADMIN:
                $this->isSuperAdmin = true;
                break;

            case AppConstants::ROLE_MIS_HEAD:
                $this->isMISHead = true;
                break;
        }

        if(in_array(AppConstants::ROLE_USER, $this->roles)){
            $this->isAppUser = true;
        }
    }*/

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @param mixed $salt
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
    }

    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof SecurityUser) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->salt !== $user->getSalt()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        return true;
    }

    public function eraseCredentials()
    {

    }

    /**
     * @return mixed
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param mixed $plainPassword
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * @return mixed
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * @param mixed $emailAddress
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    /**
     * @return mixed
     */
    public function getFccCommitteeId()
    {
        return $this->fccCommitteeId;
    }

    /**
     * @param mixed $fccCommitteeId
     */
    public function setFccCommitteeId($fccCommitteeId)
    {
        $this->fccCommitteeId = $fccCommitteeId;
    }

    /**
     * @return mixed
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @param mixed $displayName
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
    }

    /**
     * @return mixed
     */
    public function getFccCommitteeName()
    {
        return $this->fccCommitteeName;
    }

    /**
     * @param mixed $fccCommitteeName
     */
    public function setFccCommitteeName($fccCommitteeName)
    {
        $this->fccCommitteeName = $fccCommitteeName;
    }

    /**
     * @return mixed
     */
    public function getFccDepartmentId()
    {
        return $this->fccDepartmentId;
    }

    /**
     * @param mixed $fccDepartmentId
     */
    public function setFccDepartmentId($fccDepartmentId)
    {
        $this->fccDepartmentId = $fccDepartmentId;
    }

    /**
     * @return mixed
     */
    public function getFccDepartmentName()
    {
        return $this->fccDepartmentName;
    }

    /**
     * @param mixed $fccDepartmentName
     */
    public function setFccDepartmentName($fccDepartmentName)
    {
        $this->fccDepartmentName = $fccDepartmentName;
    }

    /**
     * @return mixed
     */
    public function getFirstLogin()
    {
        return $this->firstLogin;
    }

    /**
     * @param mixed $firstLogin
     */
    public function setFirstLogin($firstLogin)
    {
        $this->firstLogin = $firstLogin;
    }

    /**
     * @return mixed
     */
    public function getProfilePictureFileName()
    {
        return $this->profilePictureFileName;
    }

    /**
     * @param mixed $profilePictureFileName
     */
    public function setProfilePictureFileName($profilePictureFileName)
    {
        $this->profilePictureFileName = $profilePictureFileName;
    }

    /**
     * @return mixed
     */
    public function getProfilePictureDisplayUrl()
    {
        return $this->profilePictureDisplayUrl;
    }

    /**
     * @param mixed $profilePictureDisplayUrl
     */
    public function setProfilePictureDisplayUrl($profilePictureDisplayUrl)
    {
        $this->profilePictureDisplayUrl = $profilePictureDisplayUrl;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * @param mixed $lastModified
     */
    public function setLastModified($lastModified)
    {
        $this->lastModified = $lastModified;
    }

    /**
     * @return mixed
     */
    public function getLastModifiedByUserId()
    {
        return $this->lastModifiedByUserId;
    }

    /**
     * @param mixed $lastModifiedByUserId
     */
    public function setLastModifiedByUserId($lastModifiedByUserId)
    {
        $this->lastModifiedByUserId = $lastModifiedByUserId;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return mixed
     */
    public function getMiddleName()
    {
        return $this->middleName;
    }

    /**
     * @param mixed $middleName
     */
    public function setMiddleName($middleName)
    {
        $this->middleName = $middleName;
    }

    /**
     * @return mixed
     */
    public function getStateOfPostingId()
    {
        return $this->stateOfPostingId;
    }

    /**
     * @param mixed $stateOfPostingId
     */
    public function setStateOfPostingId($stateOfPostingId)
    {
        $this->stateOfPostingId = $stateOfPostingId;
    }

    /**
     * @return mixed
     */
    public function getStateOfPostingName()
    {
        return $this->stateOfPostingName;
    }

    /**
     * @param mixed $stateOfPostingName
     */
    public function setStateOfPostingName($stateOfPostingName)
    {
        $this->stateOfPostingName = $stateOfPostingName;
    }

    /**
     * @return mixed
     */
    public function getPrimaryPhone()
    {
        return $this->primaryPhone;
    }

    /**
     * @param mixed $primaryPhone
     */
    public function setPrimaryPhone($primaryPhone)
    {
        $this->primaryPhone = $primaryPhone;
    }

    /**
     * @return mixed
     */
    public function getPrimaryRole()
    {
        return $this->primaryRole;
    }

    /**
     * @param mixed $primaryRole
     */
    public function setPrimaryRole($primaryRole)
    {
        $this->primaryRole = $primaryRole;
    }

    /**
     * @return mixed
     */
    public function getProfileType()
    {
        return $this->profileType;
    }

    /**
     * @param mixed $profileType
     */
    public function setProfileType($profileType)
    {
        $this->profileType = $profileType;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getOrganizationId()
    {
        return $this->organizationId;
    }

    /**
     * @param mixed $organizationId
     */
    public function setOrganizationId($organizationId)
    {
        $this->organizationId = $organizationId;
    }

    /**
     * @return mixed
     */
    public function getOrganizationName()
    {
        return $this->organizationName;
    }

    /**
     * @param mixed $organizationName
     */
    public function setOrganizationName($organizationName)
    {
        $this->organizationName = $organizationName;
    }

    /**
     * @return mixed
     */
    public function getSecondaryPhone()
    {
        return $this->secondaryPhone;
    }

    /**
     * @param mixed $secondaryPhone
     */
    public function setSecondaryPhone($secondaryPhone)
    {
        $this->secondaryPhone = $secondaryPhone;
    }

    /**
     * @return mixed
     */
    public function getFccDepartmentCode()
    {
        return $this->fccDepartmentCode;
    }

    /**
     * @param mixed $fccDepartmentCode
     */
    public function setFccDepartmentCode($fccDepartmentCode)
    {
        $this->fccDepartmentCode = $fccDepartmentCode;
    }

    /**
     * @return mixed
     */
    public function getStateOfPostingCode()
    {
        return $this->stateOfPostingCode;
    }

    /**
     * @param mixed $stateOfPostingCode
     */
    public function setStateOfPostingCode($stateOfPostingCode)
    {
        $this->stateOfPostingCode = $stateOfPostingCode;
    }

    /**
     * @return mixed
     */
    public function getOrganizationEstablishmentCode()
    {
        return $this->organizationEstablishmentCode;
    }

    /**
     * @param mixed $organizationEstablishmentCode
     */
    public function setOrganizationEstablishmentCode($organizationEstablishmentCode)
    {
        $this->organizationEstablishmentCode = $organizationEstablishmentCode;
    }

    /**
     * @return mixed
     */
    public function getOrganizationEstablishmentType()
    {
        return $this->organizationEstablishmentType;
    }

    /**
     * @param mixed $organizationEstablishmentType
     */
    public function setOrganizationEstablishmentType($organizationEstablishmentType)
    {
        $this->organizationEstablishmentType = $organizationEstablishmentType;
    }

    /**
     * @return mixed
     */
    public function getOrganizationMnemonic()
    {
        return $this->organizationMnemonic;
    }

    /**
     * @param mixed $organizationMnemonic
     */
    public function setOrganizationMnemonic($organizationMnemonic)
    {
        $this->organizationMnemonic = $organizationMnemonic;
    }

    /**
     * @return mixed
     */
    public function getOrganizationBaseLineYear()
    {
        return $this->organizationBaseLineYear;
    }

    /**
     * @param mixed $organizationBaseLineYear
     */
    public function setOrganizationBaseLineYear($organizationBaseLineYear)
    {
        $this->organizationBaseLineYear = $organizationBaseLineYear;
    }

    /**
     * @return mixed
     */
    public function getOrganizationLevelOfGovernment()
    {
        return $this->organizationLevelOfGovernment;
    }

    /**
     * @param mixed $organizationLevelOfGovernment
     */
    public function setOrganizationLevelOfGovernment($organizationLevelOfGovernment)
    {
        $this->organizationLevelOfGovernment = $organizationLevelOfGovernment;
    }

    /**
     * @return mixed
     */
    public function getGuid()
    {
        return $this->guid;
    }

    /**
     * @param mixed $guid
     */
    public function setGuid($guid)
    {
        $this->guid = $guid;
    }

    /**
     * @return mixed
     */
    public function getDisplaySerialNo()
    {
        return $this->displaySerialNo;
    }

    /**
     * @param mixed $displaySerialNo
     */
    public function setDisplaySerialNo($displaySerialNo)
    {
        $this->displaySerialNo = $displaySerialNo;
    }

    /**
     * @return mixed
     */
    public function getFccSupervisorId()
    {
        return $this->fccSupervisorId;
    }

    /**
     * @param mixed $fccSupervisorId
     */
    public function setFccSupervisorId($fccSupervisorId)
    {
        $this->fccSupervisorId = $fccSupervisorId;
    }

    /**
     * @return mixed
     */
    public function getFccSupervisorName()
    {
        return $this->fccSupervisorName;
    }

    /**
     * @param mixed $fccSupervisorName
     */
    public function setFccSupervisorName($fccSupervisorName)
    {
        $this->fccSupervisorName = $fccSupervisorName;
    }

    /**
     * @return mixed
     */
    public function getFccSupervisorPhone()
    {
        return $this->fccSupervisorPhone;
    }

    /**
     * @param mixed $fccSupervisorPhone
     */
    public function setFccSupervisorPhone($fccSupervisorPhone)
    {
        $this->fccSupervisorPhone = $fccSupervisorPhone;
    }

    /**
     * @return mixed
     */
    public function getFccSupervisorEmail()
    {
        return $this->fccSupervisorEmail;
    }

    /**
     * @param mixed $fccSupervisorEmail
     */
    public function setFccSupervisorEmail($fccSupervisorEmail)
    {
        $this->fccSupervisorEmail = $fccSupervisorEmail;
    }

    /**
     * @return mixed
     */
    public function getPrimaryRoleDescription()
    {
        return $this->primaryRoleDescription;
    }

    /**
     * @param mixed $primaryRoleDescription
     */
    public function setPrimaryRoleDescription($primaryRoleDescription)
    {
        $this->primaryRoleDescription = $primaryRoleDescription;
    }

    /**
     * @return mixed
     */
    public function getPrivilegeChecker() : PrivilegeChecker
    {
        return $this->privilegeChecker;
    }

    /**
     * @param mixed $privilegeChecker
     */
    public function setPrivilegeChecker($privilegeChecker)
    {
        $this->privilegeChecker = $privilegeChecker;
    }

    /**
     * @return mixed
     */
    public function getUploadedPhotoFile()
    {
        return $this->uploadedPhotoFile;
    }

    /**
     * @param mixed $uploadedPhotoFile
     */
    public function setUploadedPhotoFile($uploadedPhotoFile)
    {
        $this->uploadedPhotoFile = $uploadedPhotoFile;
    }

    /**
     * @return mixed
     */
    public function getUploadedPhotoFileName()
    {
        return $this->uploadedPhotoFileName;
    }

    /**
     * @param mixed $uploadedPhotoFileName
     */
    public function setUploadedPhotoFileName($uploadedPhotoFileName)
    {
        $this->uploadedPhotoFileName = $uploadedPhotoFileName;
    }

}