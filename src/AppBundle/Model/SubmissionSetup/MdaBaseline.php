<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/1/2017
 * Time: 12:58 PM
 */

namespace AppBundle\Model\SubmissionSetup;


class MdaBaseline
{

    private $organizationId, $organizationName;
    private $yearOfEstablishment;
    private $confirmYearOfEstablishment;
    private $baselineYear;
    private $confirmBaselineYear;
    private $created;
    private $createdByUserId;
    private $lastModified;
    private $lastModifiedByUserId;
    private $selector;
    private $displaySerialNo;

    /**
     * MdaBaseline constructor.
     */
    public function __construct()
    {
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
    public function getYearOfEstablishment()
    {
        return $this->yearOfEstablishment;
    }

    /**
     * @param mixed $yearOfEstablishment
     */
    public function setYearOfEstablishment($yearOfEstablishment)
    {
        $this->yearOfEstablishment = $yearOfEstablishment;
    }

    /**
     * @return mixed
     */
    public function getConfirmYearOfEstablishment()
    {
        return $this->confirmYearOfEstablishment;
    }

    /**
     * @param mixed $confirmYearOfEstablishment
     */
    public function setConfirmYearOfEstablishment($confirmYearOfEstablishment)
    {
        $this->confirmYearOfEstablishment = $confirmYearOfEstablishment;
    }

    /**
     * @return mixed
     */
    public function getBaselineYear()
    {
        return $this->baselineYear;
    }

    /**
     * @param mixed $baselineYear
     */
    public function setBaselineYear($baselineYear)
    {
        $this->baselineYear = $baselineYear;
    }

    /**
     * @return mixed
     */
    public function getConfirmBaselineYear()
    {
        return $this->confirmBaselineYear;
    }

    /**
     * @param mixed $confirmBaselineYear
     */
    public function setConfirmBaselineYear($confirmBaselineYear)
    {
        $this->confirmBaselineYear = $confirmBaselineYear;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getCreatedByUserId()
    {
        return $this->createdByUserId;
    }

    /**
     * @param mixed $createdByUserId
     */
    public function setCreatedByUserId($createdByUserId)
    {
        $this->createdByUserId = $createdByUserId;
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
    public function getSelector()
    {
        return $this->selector;
    }

    /**
     * @param mixed $selector
     */
    public function setSelector($selector)
    {
        $this->selector = $selector;
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

}