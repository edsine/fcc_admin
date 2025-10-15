<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/3/2017
 * Time: 2:10 PM
 */

namespace AppBundle\Model\ApprovalRequests;


class SubmissionPermissionRequest
{
    private $organizationId, $organizationName;
    private $submissionYear;
    private $remarks;
    private $approvalStatus;
    private $dateOfApproval;
    private $approvedByUserId, $approvedByName;
    private $created,$createdByUserId, $lastModified, $lastModifiedByUserId;
    private $selector;
    private $displaySerialNo;
    private $expired;
    private $hasExpired = false;
    private $approved = false;

    /**
     * SkipSubmissionYearRequest constructor.
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
    public function getSubmissionYear()
    {
        return $this->submissionYear;
    }

    /**
     * @param mixed $submissionYear
     */
    public function setSubmissionYear($submissionYear)
    {
        $this->submissionYear = $submissionYear;
    }

    /**
     * @return mixed
     */
    public function getRemarks()
    {
        return $this->remarks;
    }

    /**
     * @param mixed $remarks
     */
    public function setRemarks($remarks)
    {
        $this->remarks = $remarks;
    }

    /**
     * @return mixed
     */
    public function getApprovalStatus()
    {
        return $this->approvalStatus;
    }

    /**
     * @param mixed $approvalStatus
     */
    public function setApprovalStatus($approvalStatus)
    {
        $this->approvalStatus = $approvalStatus;
    }

    /**
     * @return mixed
     */
    public function getDateOfApproval()
    {
        return $this->dateOfApproval;
    }

    /**
     * @param mixed $dateOfApproval
     */
    public function setDateOfApproval($dateOfApproval)
    {
        $this->dateOfApproval = $dateOfApproval;
    }

    /**
     * @return mixed
     */
    public function getApprovedByUserId()
    {
        return $this->approvedByUserId;
    }

    /**
     * @param mixed $approvedByUserId
     */
    public function setApprovedByUserId($approvedByUserId)
    {
        $this->approvedByUserId = $approvedByUserId;
    }

    /**
     * @return mixed
     */
    public function getApprovedByName()
    {
        return $this->approvedByName;
    }

    /**
     * @param mixed $approvedByName
     */
    public function setApprovedByName($approvedByName)
    {
        $this->approvedByName = $approvedByName;
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

    /**
     * @return mixed
     */
    public function getExpired()
    {
        return $this->expired;
    }

    /**
     * @param mixed $expired
     */
    public function setExpired($expired)
    {
        $this->expired = $expired;
    }

    /**
     * @return bool
     */
    public function isHasExpired(): bool
    {
        return $this->hasExpired;
    }

    /**
     * @param bool $hasExpired
     */
    public function setHasExpired(bool $hasExpired)
    {
        $this->hasExpired = $hasExpired;
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
     * @return bool
     */
    public function isApproved(): bool
    {
        return $this->approved;
    }

    /**
     * @param bool $approved
     */
    public function setApproved(bool $approved)
    {
        $this->approved = $approved;
    }


}