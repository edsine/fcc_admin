<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/28/2017
 * Time: 10:53 AM
 */

namespace AppBundle\Model\Recruitment;


use AppBundle\Model\Document\AttachedDocument;

class Recruitment
{
    private $id;
    private $title;
    private $organizationId, $organizationName, $organizationSelector;
    private $recruitmentYear;
    private $recruitmentCategoryId, $recruitmentCategoryName;
    private $remarks;
    private $recordStatus;
    private $created, $createdByUserId;
    private $lastModified, $lastModifiedByUserId;
    private $selector;
    private $displaySerialNo;

    /**
     * @var AttachedDocument
     */
    private $attachment;

    /**
     * @var AttachedDocument
     */
    private $longListAttachment;
    private $longListLastUploaded, $totalLongListCandidates;

    /**
     * @var AttachedDocument
     */
    private $shortListAttachment;
    private $shortListLastUploaded, $totalShortListCandidates;

    /**
     * Recruitment constructor.
     */
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
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
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
    public function getLongListAttachment(): AttachedDocument
    {
        return $this->longListAttachment;
    }

    /**
     * @param AttachedDocument $longListAttachment
     */
    public function setLongListAttachment(AttachedDocument $longListAttachment)
    {
        $this->longListAttachment = $longListAttachment;
    }

    /**
     * @return AttachedDocument
     */
    public function getShortListAttachment(): AttachedDocument
    {
        return $this->shortListAttachment;
    }

    /**
     * @param AttachedDocument $shortListAttachment
     */
    public function setShortListAttachment(AttachedDocument $shortListAttachment)
    {
        $this->shortListAttachment = $shortListAttachment;
    }

    /**
     * @return mixed
     */
    public function getLongListLastUploaded()
    {
        return $this->longListLastUploaded;
    }

    /**
     * @param mixed $longListLastUploaded
     */
    public function setLongListLastUploaded($longListLastUploaded)
    {
        $this->longListLastUploaded = $longListLastUploaded;
    }

    /**
     * @return mixed
     */
    public function getTotalLongListCandidates()
    {
        return $this->totalLongListCandidates;
    }

    /**
     * @param mixed $totalLongListCandidates
     */
    public function setTotalLongListCandidates($totalLongListCandidates)
    {
        $this->totalLongListCandidates = $totalLongListCandidates;
    }

    /**
     * @return mixed
     */
    public function getShortListLastUploaded()
    {
        return $this->shortListLastUploaded;
    }

    /**
     * @param mixed $shortListLastUploaded
     */
    public function setShortListLastUploaded($shortListLastUploaded)
    {
        $this->shortListLastUploaded = $shortListLastUploaded;
    }

    /**
     * @return mixed
     */
    public function getTotalShortListCandidates()
    {
        return $this->totalShortListCandidates;
    }

    /**
     * @param mixed $totalShortListCandidates
     */
    public function setTotalShortListCandidates($totalShortListCandidates)
    {
        $this->totalShortListCandidates = $totalShortListCandidates;
    }

}