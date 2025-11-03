<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 2/21/2017
 * Time: 3:02 PM
 */

namespace AppBundle\Model\Recruitment;


use AppBundle\Model\Document\AttachedDocument;

class RecruitmentCBIRequest
{

    private $id;
    private $recruitmentId, $recruitmentSelector;
    private $recruitmentYear;
    private $recruitmentCategoryId,$recruitmentCategoryName;
    private $organizationId, $organizationName, $organizationEstablishmentCode, $organizationSelector;
    private $recruitmentValue;
    private $cbiGLCategoryId, $cbiGLCategoryName;
    private $submissionYearUsed;
    private $remarks;
    private $approvalStatus, $dateOfApproval, $approvalByUserId, $approvalByUserName;
    private $recordStatus;
    private $created, $createdByUserId;
    private $lastModified, $lastModifiedByUserId;
    private $selector;

    private $displaySerialNo;

    public function __construct()
    {
    }

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
    public function getRecruitmentId()
    {
        return $this->recruitmentId;
    }

    /**
     * @param mixed $recruitmentId
     */
    public function setRecruitmentId($recruitmentId)
    {
        $this->recruitmentId = $recruitmentId;
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
    public function setRecruitmentSelector($recruitmentSelector)
    {
        $this->recruitmentSelector = $recruitmentSelector;
    }

    /**
     * @return mixed
     */
    public function getRecruitmentYear()
    {
        return $this->recruitmentYear;
    }

    /**
     * @param mixed $recruitmentYear
     */
    public function setRecruitmentYear($recruitmentYear)
    {
        $this->recruitmentYear = $recruitmentYear;
    }

    /**
     * @return mixed
     */
    public function getRecruitmentCategoryId()
    {
        return $this->recruitmentCategoryId;
    }

    /**
     * @param mixed $recruitmentCategoryId
     */
    public function setRecruitmentCategoryId($recruitmentCategoryId)
    {
        $this->recruitmentCategoryId = $recruitmentCategoryId;
    }

    /**
     * @return mixed
     */
    public function getRecruitmentCategoryName()
    {
        return $this->recruitmentCategoryName;
    }

    /**
     * @param mixed $recruitmentCategoryName
     */
    public function setRecruitmentCategoryName($recruitmentCategoryName)
    {
        $this->recruitmentCategoryName = $recruitmentCategoryName;
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
    public function getOrganizationSelector()
    {
        return $this->organizationSelector;
    }

    /**
     * @param mixed $organizationSelector
     */
    public function setOrganizationSelector($organizationSelector)
    {
        $this->organizationSelector = $organizationSelector;
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
    public function getCbiGLCategoryId()
    {
        return $this->cbiGLCategoryId;
    }

    /**
     * @param mixed $cbiGLCategoryId
     */
    public function setCbiGLCategoryId($cbiGLCategoryId)
    {
        $this->cbiGLCategoryId = $cbiGLCategoryId;
    }

    /**
     * @return mixed
     */
    public function getCbiGLCategoryName()
    {
        return $this->cbiGLCategoryName;
    }

    /**
     * @param mixed $cbiGLCategoryName
     */
    public function setCbiGLCategoryName($cbiGLCategoryName)
    {
        $this->cbiGLCategoryName = $cbiGLCategoryName;
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
    public function getApprovalByUserId()
    {
        return $this->approvalByUserId;
    }

    /**
     * @param mixed $approvalByUserId
     */
    public function setApprovalByUserId($approvalByUserId)
    {
        $this->approvalByUserId = $approvalByUserId;
    }

    /**
     * @return mixed
     */
    public function getApprovalByUserName()
    {
        return $this->approvalByUserName;
    }

    /**
     * @param mixed $approvalByUserName
     */
    public function setApprovalByUserName($approvalByUserName)
    {
        $this->approvalByUserName = $approvalByUserName;
    }

    /**
     * @return mixed
     */
    public function getRecordStatus()
    {
        return $this->recordStatus;
    }

    /**
     * @param mixed $recordStatus
     */
    public function setRecordStatus($recordStatus)
    {
        $this->recordStatus = $recordStatus;
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