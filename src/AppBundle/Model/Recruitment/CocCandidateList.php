<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 2/21/2017
 * Time: 3:02 PM
 */

namespace AppBundle\Model\Recruitment;


use AppBundle\Model\Document\AttachedDocument;

class CocCandidateList
{

    private $id;
    private $recruitmentId, $recruitmentSelector;
    private $recruitmentYear;
    private $totalCandidates;
    private $validationStatus;
    private $dateLastValidated;
    private $dataTransferStatus;
    private $dateDataTransferred;
    private $recordStatus;
    private $created, $createdByUserId;
    private $lastModified, $lastModifiedByUserId;
    private $selector;

    private $displaySerialNo;

    /**
     * @var AttachedDocument
     */
    private $attachment;

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
    public function setId($id): void
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
    public function setRecruitmentId($recruitmentId): void
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
    public function setRecruitmentSelector($recruitmentSelector): void
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
    public function setRecruitmentYear($recruitmentYear): void
    {
        $this->recruitmentYear = $recruitmentYear;
    }

    /**
     * @return mixed
     */
    public function getTotalCandidates()
    {
        return $this->totalCandidates;
    }

    /**
     * @param mixed $totalCandidates
     */
    public function setTotalCandidates($totalCandidates): void
    {
        $this->totalCandidates = $totalCandidates;
    }

    /**
     * @return mixed
     */
    public function getValidationStatus()
    {
        return $this->validationStatus;
    }

    /**
     * @param mixed $validationStatus
     */
    public function setValidationStatus($validationStatus): void
    {
        $this->validationStatus = $validationStatus;
    }

    /**
     * @return mixed
     */
    public function getDateLastValidated()
    {
        return $this->dateLastValidated;
    }

    /**
     * @param mixed $dateLastValidated
     */
    public function setDateLastValidated($dateLastValidated): void
    {
        $this->dateLastValidated = $dateLastValidated;
    }

    /**
     * @return mixed
     */
    public function getDataTransferStatus()
    {
        return $this->dataTransferStatus;
    }

    /**
     * @param mixed $dataTransferStatus
     */
    public function setDataTransferStatus($dataTransferStatus): void
    {
        $this->dataTransferStatus = $dataTransferStatus;
    }

    /**
     * @return mixed
     */
    public function getDateDataTransferred()
    {
        return $this->dateDataTransferred;
    }

    /**
     * @param mixed $dateDataTransferred
     */
    public function setDateDataTransferred($dateDataTransferred): void
    {
        $this->dateDataTransferred = $dateDataTransferred;
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
    public function setRecordStatus($recordStatus): void
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
    public function setCreated($created): void
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
    public function setCreatedByUserId($createdByUserId): void
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
    public function setLastModified($lastModified): void
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
    public function setLastModifiedByUserId($lastModifiedByUserId): void
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
    public function setSelector($selector): void
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
    public function setDisplaySerialNo($displaySerialNo): void
    {
        $this->displaySerialNo = $displaySerialNo;
    }

    /**
     * @return AttachedDocument
     */
    public function getAttachment(): ?AttachedDocument
    {
        return $this->attachment;
    }

    /**
     * @param AttachedDocument $attachment
     */
    public function setAttachment(AttachedDocument $attachment): void
    {
        $this->attachment = $attachment;
    }

}