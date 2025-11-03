<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 2/21/2017
 * Time: 3:02 PM
 */

namespace AppBundle\Model\Recruitment;


use AppBundle\Model\Document\AttachedDocument;

class RecruitmentAdvert
{

    private $id;
    private $recruitmentId, $recruitmentSelector;
    private $recruitmentYear;
    private $recruitmentCategoryId,$recruitmentCategoryName;
    private $organizationId, $organizationName, $organizationEstablishmentCode, $organizationSelector;
    private $title;
    private $vacancyPost;
    private $startDate;
    private $endDate;
    private $confirmationStatus, $confirmationStatusRemarks, $confirmationByUserId, $confirmationByUserName, $dateOfConfirmation;
    private $recordStatus;
    private $created, $createdByUserId;
    private $lastModified, $lastModifiedByUserId;
    private $selector;

    private $displaySerialNo;

    /**
     * @var AttachedDocument
     */
    private $attachment;

    private $expired = false;
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
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param mixed $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return mixed
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param mixed $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return mixed
     */
    public function getConfirmationStatus()
    {
        return $this->confirmationStatus;
    }

    /**
     * @param mixed $confirmationStatus
     */
    public function setConfirmationStatus($confirmationStatus)
    {
        $this->confirmationStatus = $confirmationStatus;
    }

    /**
     * @return mixed
     */
    public function getConfirmationStatusRemarks()
    {
        return $this->confirmationStatusRemarks;
    }

    /**
     * @param mixed $confirmationStatusRemarks
     */
    public function setConfirmationStatusRemarks($confirmationStatusRemarks)
    {
        $this->confirmationStatusRemarks = $confirmationStatusRemarks;
    }

    /**
     * @return mixed
     */
    public function getConfirmationByUserId()
    {
        return $this->confirmationByUserId;
    }

    /**
     * @param mixed $confirmationByUserId
     */
    public function setConfirmationByUserId($confirmationByUserId)
    {
        $this->confirmationByUserId = $confirmationByUserId;
    }

    /**
     * @return mixed
     */
    public function getConfirmationByUserName()
    {
        return $this->confirmationByUserName;
    }

    /**
     * @param mixed $confirmationByUserName
     */
    public function setConfirmationByUserName($confirmationByUserName)
    {
        $this->confirmationByUserName = $confirmationByUserName;
    }

    /**
     * @return mixed
     */
    public function getDateOfConfirmation()
    {
        return $this->dateOfConfirmation;
    }

    /**
     * @param mixed $dateOfConfirmation
     */
    public function setDateOfConfirmation($dateOfConfirmation)
    {
        $this->dateOfConfirmation = $dateOfConfirmation;
    }

    /**
     * @return mixed
     */
    public function getVacancyPost()
    {
        return $this->vacancyPost;
    }

    /**
     * @param mixed $vacancyPost
     */
    public function setVacancyPost($vacancyPost)
    {
        $this->vacancyPost = $vacancyPost;
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
     * @return boolean
     */
    public function isExpired(): bool
    {
        return $this->expired;
    }

    /**
     * @param boolean $expired
     */
    public function setExpired(bool $expired)
    {
        $this->expired = $expired;
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
     * @return AttachedDocument
     */
    public function getAttachment(): AttachedDocument
    {
        return $this->attachment;
    }

    /**
     * @param AttachedDocument $attachment
     */
    public function setAttachment(AttachedDocument $attachment)
    {
        $this->attachment = $attachment;
    }

}