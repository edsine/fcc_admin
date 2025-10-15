<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 12:30 PM
 */

namespace AppBundle\Model\Organizations;


class CommitteeMember
{
    private $committeeId,$committeeName;
    private $userProfileId,$firstName, $lastName;

    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getCommitteeId()
    {
        return $this->committeeId;
    }

    /**
     * @param mixed $committeeId
     */
    public function setCommitteeId($committeeId)
    {
        $this->committeeId = $committeeId;
    }

    /**
     * @return mixed
     */
    public function getCommitteeName()
    {
        return $this->committeeName;
    }

    /**
     * @param mixed $committeeName
     */
    public function setCommitteeName($committeeName)
    {
        $this->committeeName = $committeeName;
    }

    /**
     * @return mixed
     */
    public function getUserProfileId()
    {
        return $this->userProfileId;
    }

    /**
     * @param mixed $userProfileId
     */
    public function setUserProfileId($userProfileId)
    {
        $this->userProfileId = $userProfileId;
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

}