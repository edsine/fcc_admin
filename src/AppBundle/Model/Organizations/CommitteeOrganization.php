<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 12:30 PM
 */

namespace AppBundle\Model\Organizations;


class CommitteeOrganization
{
    private $committeeId,$committeeName;
    private $organizationId,$organizationName;

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
}