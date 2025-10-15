<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 12:30 PM
 */

namespace AppBundle\Model\Organizations;


class Committee
{
    private $id;
    private $name;
    private $status;
    private $chairmanUserId, $chairmanUserName;
    private $secretaryUserId, $secretaryUserName;
    private $lastModified;
    private $lastModifiedByUserId;
    private $guid;

    private $committeeMemberIds;
    private $committeeMembers;
    private $committeeMdaIds;
    private $committeeMdas;

    private $displaySerialNo;

    public function __construct()
    {
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getChairmanUserId()
    {
        return $this->chairmanUserId;
    }

    /**
     * @param mixed $chairmanUserId
     */
    public function setChairmanUserId($chairmanUserId)
    {
        $this->chairmanUserId = $chairmanUserId;
    }

    /**
     * @return mixed
     */
    public function getChairmanUserName()
    {
        return $this->chairmanUserName;
    }

    /**
     * @param mixed $chairmanUserName
     */
    public function setChairmanUserName($chairmanUserName)
    {
        $this->chairmanUserName = $chairmanUserName;
    }

    /**
     * @return mixed
     */
    public function getSecretaryUserId()
    {
        return $this->secretaryUserId;
    }

    /**
     * @param mixed $secretaryUserId
     */
    public function setSecretaryUserId($secretaryUserId)
    {
        $this->secretaryUserId = $secretaryUserId;
    }

    /**
     * @return mixed
     */
    public function getSecretaryUserName()
    {
        return $this->secretaryUserName;
    }

    /**
     * @param mixed $secretaryUserName
     */
    public function setSecretaryUserName($secretaryUserName)
    {
        $this->secretaryUserName = $secretaryUserName;
    }

    public function getLastModified()
    {
        return $this->lastModified;
    }

    public function setLastModified($lastModified)
    {
        $this->lastModified = $lastModified;
    }

    public function getGuid()
    {
        return $this->guid;
    }

    public function setGuid($guid)
    {
        $this->guid = $guid;
    }

    public function getDisplaySerialNo()
    {
        return $this->displaySerialNo;
    }

    public function setDisplaySerialNo($displaySerialNo)
    {
        $this->displaySerialNo = $displaySerialNo;
    }

    public function getLastModifiedByUserId()
    {
        return $this->lastModifiedByUserId;
    }

    public function setLastModifiedByUserId($lastModifiedByUserId)
    {
        $this->lastModifiedByUserId = $lastModifiedByUserId;
    }

    /**
     * @return mixed
     */
    public function getCommitteeMemberIds()
    {
        return $this->committeeMemberIds;
    }

    /**
     * @param mixed $committeeMemberIds
     */
    public function setCommitteeMemberIds($committeeMemberIds)
    {
        $this->committeeMemberIds = $committeeMemberIds;
    }

    /**
     * @return mixed
     */
    public function getCommitteeMdaIds()
    {
        return $this->committeeMdaIds;
    }

    /**
     * @param mixed $committeeMdaIds
     */
    public function setCommitteeMdaIds($committeeMdaIds)
    {
        $this->committeeMdaIds = $committeeMdaIds;
    }

    /**
     * @return mixed
     */
    public function getCommitteeMembers()
    {
        return $this->committeeMembers;
    }

    /**
     * @param mixed $committeeMembers
     */
    public function setCommitteeMembers($committeeMembers)
    {
        $this->committeeMembers = $committeeMembers;
    }

    /**
     * @return mixed
     */
    public function getCommitteeMdas()
    {
        return $this->committeeMdas;
    }

    /**
     * @param mixed $committeeMdas
     */
    public function setCommitteeMdas($committeeMdas)
    {
        $this->committeeMdas = $committeeMdas;
    }

}