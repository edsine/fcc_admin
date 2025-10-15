<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/3/2017
 * Time: 2:10 PM
 */

namespace AppBundle\Model\ReportRequests;


class CBIReportRequest
{
    private $organizationId, $organizationName;
    private $requestType;
    private $recruitmentId, $recruitmentTitle, $recruitmentSelector;
    private $recruitmentValue;
    private $cbiGradeLevelCategory, $cbiGradeLevelCategoryName;
    private $submissionYearUsed;
    private $remarks;
    private $approvalStatus;
    private $dateOfApproval;
    private $approvedByUserId, $approvedByName;
    private $created,$createdByUserId, $lastModified, $lastModifiedByUserId;
    private $selector;
    private $displaySerialNo;
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
    public function getRequestType()
    {
        return $this->requestType;
    }

    /**
     * @param mixed $requestType
     */
    public function setRequestType($requestType): void
    {
        $this->requestType = $requestType;
    }

    /**
     * @return mixed
     */
    public function getRecruitmentId()
    {
        return $this->recruitmentId;
    }

    /**
     * @param mixed $recruitmentId
     */
    public function setRecruitmentId($recruitmentId): void
    {
        $this->recruitmentId = $recruitmentId;
    }

    /**
     * @return mixed
     */
    public function getRecruitmentTitle()
    {
        return $this->recruitmentTitle;
    }

    /**
     * @param mixed $recruitmentTitle
     */
    public function setRecruitmentTitle($recruitmentTitle): void
    {
        $this->recruitmentTitle = $recruitmentTitle;
    }

    /**
     * @return mixed
     */
    public function getRecruitmentSelector()
    {
        return $this->recruitmentSelector;
    }

    /**
     * @param mixed $recruitmentSelector
     */
    public function setRecruitmentSelector($recruitmentSelector): void
    {
        $this->recruitmentSelector = $recruitmentSelector;
    }

    /**
     * @return mixed
     */
    public function getRecruitmentValue()
    {
        return $this->recruitmentValue;
    }

    /**
     * @param mixed $recruitmentValue
     */
    public function setRecruitmentValue($recruitmentValue)
    {
        $this->recruitmentValue = $recruitmentValue;
    }

    /**
     * @return mixed
     */
    public function getCbiGradeLevelCategory()
    {
        return $this->cbiGradeLevelCategory;
    }

    /**
     * @param mixed $cbiGradeLevelCategory
     */
    public function setCbiGradeLevelCategory($cbiGradeLevelCategory)
    {
        $this->cbiGradeLevelCategory = $cbiGradeLevelCategory;
    }

    /**
     * @return mixed
     */
    public function getCbiGradeLevelCategoryName()
    {
        return $this->cbiGradeLevelCategoryName;
    }

    /**
     * @param mixed $cbiGradeLevelCategoryName
     */
    public function setCbiGradeLevelCategoryName($cbiGradeLevelCategoryName)
    {
        $this->cbiGradeLevelCategoryName = $cbiGradeLevelCategoryName;
    }

    /**
     * @return mixed
     */
    public function getSubmissionYearUsed()
    {
        return $this->submissionYearUsed;
    }

    /**
     * @param mixed $submissionYearUsed
     */
    public function setSubmissionYearUsed($submissionYearUsed)
    {
        $this->submissionYearUsed = $submissionYearUsed;
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