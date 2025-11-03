<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 2/21/2017
 * Time: 3:02 PM
 */

namespace AppBundle\Model\Recruitment;


use AppBundle\Model\Document\AttachedDocument;

class RecruitmentMonitoring
{

    private $id;
    private $recruitmentId, $recruitmentSelector;
    private $recruitmentYear;
    private $recruitmentCategoryId,$recruitmentCategoryName;
    private $organizationId, $organizationName, $organizationEstablishmentCode, $organizationSelector;
    private $invitationDate, $invitationRemarks;
    private $dmeConfirmationStatus, $dateOfDmeConfirmation, $dmeConfirmationByUserId, $dmeConfirmationByUserName, $dmeConfirmationRemarks;
    private $completionStatus, $dateOfCompletion, $completionByUserId, $completionByUserName, $completionRemarks;
    private $recordStatus;
    private $created, $createdByUserId;
    private $lastModified, $lastModifiedByUserId;
    private $selector;

    private $displaySerialNo;

    /**
     * @var AttachedDocument
     */
    private $invitationAttachment;

    private $completed = false;
    private $confirmed = false;

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
    public function getInvitationDate()
    {
        return $this->invitationDate;
    }

    /**
     * @param mixed $invitationDate
     */
    public function setInvitationDate($invitationDate)
    {
        $this->invitationDate = $invitationDate;
    }

    /**
     * @return mixed
     */
    public function getInvitationRemarks()
    {
        return $this->invitationRemarks;
    }

    /**
     * @param mixed $invitationRemarks
     */
    public function setInvitationRemarks($invitationRemarks)
    {
        $this->invitationRemarks = $invitationRemarks;
    }

    /**
     * @return mixed
     */
    public function getDmeConfirmationStatus()
    {
        return $this->dmeConfirmationStatus;
    }

    /**
     * @param mixed $dmeConfirmationStatus
     */
    public function setDmeConfirmationStatus($dmeConfirmationStatus)
    {
        $this->dmeConfirmationStatus = $dmeConfirmationStatus;
    }

    /**
     * @return mixed
     */
    public function getDateOfDmeConfirmation()
    {
        return $this->dateOfDmeConfirmation;
    }

    /**
     * @param mixed $dateOfDmeConfirmation
     */
    public function setDateOfDmeConfirmation($dateOfDmeConfirmation)
    {
        $this->dateOfDmeConfirmation = $dateOfDmeConfirmation;
    }

    /**
     * @return mixed
     */
    public function getDmeConfirmationByUserId()
    {
        return $this->dmeConfirmationByUserId;
    }

    /**
     * @param mixed $dmeConfirmationByUserId
     */
    public function setDmeConfirmationByUserId($dmeConfirmationByUserId)
    {
        $this->dmeConfirmationByUserId = $dmeConfirmationByUserId;
    }

    /**
     * @return mixed
     */
    public function getDmeConfirmationByUserName()
    {
        return $this->dmeConfirmationByUserName;
    }

    /**
     * @param mixed $dmeConfirmationByUserName
     */
    public function setDmeConfirmationByUserName($dmeConfirmationByUserName)
    {
        $this->dmeConfirmationByUserName = $dmeConfirmationByUserName;
    }

    /**
     * @return mixed
     */
    public function getDmeConfirmationRemarks()
    {
        return $this->dmeConfirmationRemarks;
    }

    /**
     * @param mixed $dmeConfirmationRemarks
     */
    public function setDmeConfirmationRemarks($dmeConfirmationRemarks)
    {
        $this->dmeConfirmationRemarks = $dmeConfirmationRemarks;
    }

    /**
     * @return mixed
     */
    public function getCompletionStatus()
    {
        return $this->completionStatus;
    }

    /**
     * @param mixed $completionStatus
     */
    public function setCompletionStatus($completionStatus)
    {
        $this->completionStatus = $completionStatus;
    }

    /**
     * @return mixed
     */
    public function getDateOfCompletion()
    {
        return $this->dateOfCompletion;
    }

    /**
     * @param mixed $dateOfCompletion
     */
    public function setDateOfCompletion($dateOfCompletion)
    {
        $this->dateOfCompletion = $dateOfCompletion;
    }

    /**
     * @return mixed
     */
    public function getCompletionByUserId()
    {
        return $this->completionByUserId;
    }

    /**
     * @param mixed $completionByUserId
     */
    public function setCompletionByUserId($completionByUserId)
    {
        $this->completionByUserId = $completionByUserId;
    }

    /**
     * @return mixed
     */
    public function getCompletionByUserName()
    {
        return $this->completionByUserName;
    }

    /**
     * @param mixed $completionByUserName
     */
    public function setCompletionByUserName($completionByUserName)
    {
        $this->completionByUserName = $completionByUserName;
    }

    /**
     * @return mixed
     */
    public function getCompletionRemarks()
    {
        return $this->completionRemarks;
    }

    /**
     * @param mixed $completionRemarks
     */
    public function setCompletionRemarks($completionRemarks)
    {
        $this->completionRemarks = $completionRemarks;
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

    /**
     * @return AttachedDocument
     */
    public function getInvitationAttachment(): AttachedDocument
    {
        return $this->invitationAttachment;
    }

    /**
     * @param AttachedDocument $invitationAttachment
     */
    public function setInvitationAttachment(AttachedDocument $invitationAttachment)
    {
        $this->invitationAttachment = $invitationAttachment;
    }

    /**
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->completed;
    }

    /**
     * @param bool $completed
     */
    public function setCompleted(bool $completed)
    {
        $this->completed = $completed;
    }

    /**
     * @return bool
     */
    public function isConfirmed(): bool
    {
        return $this->confirmed;
    }

    /**
     * @param bool $confirmed
     */
    public function setConfirmed(bool $confirmed)
    {
        $this->confirmed = $confirmed;
    }

}