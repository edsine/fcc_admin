<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 2:04 PM
 */

namespace AppBundle\Model\SearchCriteria;


class UserProfileSearchCriteria
{
    private $username;
    private $firstName;
    private $lastName;
    private $emailAddress;
    private $phoneNumber;
    private $profileType = 'UNDEFINED';
    private $primaryRole;
    private $fccLocation;
    private $fccDepartment;
    private $fccCommittee;
    private $organization;
    private $status;

    public function __construct(string $profileType)
    {
        $this->profileType = $profileType;
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
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param mixed $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return string
     */
    public function getProfileType()
    {
        return $this->profileType;
    }

    /**
     * @param string $profileType
     */
    public function setProfileType($profileType)
    {
        $this->profileType = $profileType;
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
    public function getFccLocation()
    {
        return $this->fccLocation;
    }

    /**
     * @param mixed $fccLocation
     */
    public function setFccLocation($fccLocation)
    {
        $this->fccLocation = $fccLocation;
    }

    /**
     * @return mixed
     */
    public function getFccDepartment()
    {
        return $this->fccDepartment;
    }

    /**
     * @param mixed $fccDepartment
     */
    public function setFccDepartment($fccDepartment)
    {
        $this->fccDepartment = $fccDepartment;
    }

    /**
     * @return mixed
     */
    public function getFccCommittee()
    {
        return $this->fccCommittee;
    }

    /**
     * @param mixed $fccCommittee
     */
    public function setFccCommittee($fccCommittee)
    {
        $this->fccCommittee = $fccCommittee;
    }

    /**
     * @return mixed
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param mixed $organization
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
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
}