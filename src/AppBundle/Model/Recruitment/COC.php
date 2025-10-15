<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 2/21/2017
 * Time: 3:02 PM
 */

namespace AppBundle\Model\Recruitment;


use AppBundle\Model\Document\AttachedDocument;

class COC
{

    private $id;
    private $recruitmentId, $recruitmentSelector;
    private $recruitmentYear;
    private $recruitmentCategoryId, $recruitmentCategoryName;
    private $organizationId, $organizationName, $organizationEstablishmentCode, $organizationSelector;
    private $typeOfCocId, $typeOfCocName;
    private $dateOfSelectedCandidatesAttachment;

    private $committeeChairRecommendation, $committeeChairRecommendationRemarks, $dateOfCommitteeChairRecommendation
    , $committeeChairRecommendationByUserId, $committeeChairRecommendationByUserName;

    private $committeeSecConfirmation, $committeeSecConfirmationRemarks, $dateOfCommitteeSecConfirmation
    , $committeeSecConfirmationByUserId, $committeeSecConfirmationByUserName;

    private $execChairmanApproval, $execChairmanApprovalRemarks, $dateOfExecChairmanApproval
    , $execChairmanApprovalByUserId, $execChairmanApprovalByUserName;

    private $dmeConfirmationStatus, $dmeConfirmationRemarks, $dateOfDmeConfirmation, $dmeConfirmationByUserId, $dmeConfirmationByUserName;
    private $durationInMonths, $durationInWeeks, $expectedDateOfExpiration;

    private $dateOfAppointedCandidatesAttachment;
    private $recordStatus;
    private $created, $createdByUserId;
    private $lastModified, $lastModifiedByUserId;
    private $selector;

    private $displaySerialNo;

    /**
     * @var AttachedDocument
     */
    private $selectedCandidatesAttachment;

    /**
     * @var AttachedDocument
     */
    private $appointedCandidatesAttachment;

    private $committeeChairRecommended = false;
    private $committeeSecConfirmed = false;
    private $execChairmanApproved = false;
    private $dmeConfirmed = false;
    private $selectedCandidatesUploaded = false;

    private $expired = false;

    /**
     * @var ApprovalComment[]
     */
    private $approvalComments;

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
    public function getTypeOfCocId()
    {
        return $this->typeOfCocId;
    }

    /**
     * @param mixed $typeOfCocId
     */
    public function setTypeOfCocId($typeOfCocId)
    {
        $this->typeOfCocId = $typeOfCocId;
    }

    /**
     * @return mixed
     */
    public function getTypeOfCocName()
    {
        return $this->typeOfCocName;
    }

    /**
     * @param mixed $typeOfCocName
     */
    public function setTypeOfCocName($typeOfCocName)
    {
        $this->typeOfCocName = $typeOfCocName;
    }

    /**
     * @return mixed
     */
    public function getDateOfSelectedCandidatesAttachment()
    {
        return $this->dateOfSelectedCandidatesAttachment;
    }

    /**
     * @param mixed $dateOfSelectedCandidatesAttachment
     */
    public function setDateOfSelectedCandidatesAttachment($dateOfSelectedCandidatesAttachment)
    {
        $this->dateOfSelectedCandidatesAttachment = $dateOfSelectedCandidatesAttachment;
    }

    /**
     * @return mixed
     */
    public function getCommitteeChairRecommendation()
    {
        return $this->committeeChairRecommendation;
    }

    /**
     * @param mixed $committeeChairRecommendation
     */
    public function setCommitteeChairRecommendation($committeeChairRecommendation)
    {
        $this->committeeChairRecommendation = $committeeChairRecommendation;
    }

    /**
     * @return mixed
     */
    public function getCommitteeChairRecommendationRemarks()
    {
        return $this->committeeChairRecommendationRemarks;
    }

    /**
     * @param mixed $committeeChairRecommendationRemarks
     */
    public function setCommitteeChairRecommendationRemarks($committeeChairRecommendationRemarks)
    {
        $this->committeeChairRecommendationRemarks = $committeeChairRecommendationRemarks;
    }

    /**
     * @return mixed
     */
    public function getDateOfCommitteeChairRecommendation()
    {
        return $this->dateOfCommitteeChairRecommendation;
    }

    /**
     * @param mixed $dateOfCommitteeChairRecommendation
     */
    public function setDateOfCommitteeChairRecommendation($dateOfCommitteeChairRecommendation)
    {
        $this->dateOfCommitteeChairRecommendation = $dateOfCommitteeChairRecommendation;
    }

    /**
     * @return mixed
     */
    public function getCommitteeChairRecommendationByUserId()
    {
        return $this->committeeChairRecommendationByUserId;
    }

    /**
     * @param mixed $committeeChairRecommendationByUserId
     */
    public function setCommitteeChairRecommendationByUserId($committeeChairRecommendationByUserId)
    {
        $this->committeeChairRecommendationByUserId = $committeeChairRecommendationByUserId;
    }

    /**
     * @return mixed
     */
    public function getCommitteeChairRecommendationByUserName()
    {
        return $this->committeeChairRecommendationByUserName;
    }

    /**
     * @param mixed $committeeChairRecommendationByUserName
     */
    public function setCommitteeChairRecommendationByUserName($committeeChairRecommendationByUserName)
    {
        $this->committeeChairRecommendationByUserName = $committeeChairRecommendationByUserName;
    }

    /**
     * @return mixed
     */
    public function getCommitteeSecConfirmation()
    {
        return $this->committeeSecConfirmation;
    }

    /**
     * @param mixed $committeeSecConfirmation
     */
    public function setCommitteeSecConfirmation($committeeSecConfirmation)
    {
        $this->committeeSecConfirmation = $committeeSecConfirmation;
    }

    /**
     * @return mixed
     */
    public function getCommitteeSecConfirmationRemarks()
    {
        return $this->committeeSecConfirmationRemarks;
    }

    /**
     * @param mixed $committeeSecConfirmationRemarks
     */
    public function setCommitteeSecConfirmationRemarks($committeeSecConfirmationRemarks)
    {
        $this->committeeSecConfirmationRemarks = $committeeSecConfirmationRemarks;
    }

    /**
     * @return mixed
     */
    public function getDateOfCommitteeSecConfirmation()
    {
        return $this->dateOfCommitteeSecConfirmation;
    }

    /**
     * @param mixed $dateOfCommitteeSecConfirmation
     */
    public function setDateOfCommitteeSecConfirmation($dateOfCommitteeSecConfirmation)
    {
        $this->dateOfCommitteeSecConfirmation = $dateOfCommitteeSecConfirmation;
    }

    /**
     * @return mixed
     */
    public function getCommitteeSecConfirmationByUserId()
    {
        return $this->committeeSecConfirmationByUserId;
    }

    /**
     * @param mixed $committeeSecConfirmationByUserId
     */
    public function setCommitteeSecConfirmationByUserId($committeeSecConfirmationByUserId)
    {
        $this->committeeSecConfirmationByUserId = $committeeSecConfirmationByUserId;
    }

    /**
     * @return mixed
     */
    public function getCommitteeSecConfirmationByUserName()
    {
        return $this->committeeSecConfirmationByUserName;
    }

    /**
     * @param mixed $committeeSecConfirmationByUserName
     */
    public function setCommitteeSecConfirmationByUserName($committeeSecConfirmationByUserName)
    {
        $this->committeeSecConfirmationByUserName = $committeeSecConfirmationByUserName;
    }

    /**
     * @return mixed
     */
    public function getExecChairmanApproval()
    {
        return $this->execChairmanApproval;
    }

    /**
     * @param mixed $execChairmanApproval
     */
    public function setExecChairmanApproval($execChairmanApproval)
    {
        $this->execChairmanApproval = $execChairmanApproval;
    }

    /**
     * @return mixed
     */
    public function getExecChairmanApprovalRemarks()
    {
        return $this->execChairmanApprovalRemarks;
    }

    /**
     * @param mixed $execChairmanApprovalRemarks
     */
    public function setExecChairmanApprovalRemarks($execChairmanApprovalRemarks)
    {
        $this->execChairmanApprovalRemarks = $execChairmanApprovalRemarks;
    }

    /**
     * @return mixed
     */
    public function getDateOfExecChairmanApproval()
    {
        return $this->dateOfExecChairmanApproval;
    }

    /**
     * @param mixed $dateOfExecChairmanApproval
     */
    public function setDateOfExecChairmanApproval($dateOfExecChairmanApproval)
    {
        $this->dateOfExecChairmanApproval = $dateOfExecChairmanApproval;
    }

    /**
     * @return mixed
     */
    public function getExecChairmanApprovalByUserId()
    {
        return $this->execChairmanApprovalByUserId;
    }

    /**
     * @param mixed $execChairmanApprovalByUserId
     */
    public function setExecChairmanApprovalByUserId($execChairmanApprovalByUserId)
    {
        $this->execChairmanApprovalByUserId = $execChairmanApprovalByUserId;
    }

    /**
     * @return mixed
     */
    public function getExecChairmanApprovalByUserName()
    {
        return $this->execChairmanApprovalByUserName;
    }

    /**
     * @param mixed $execChairmanApprovalByUserName
     */
    public function setExecChairmanApprovalByUserName($execChairmanApprovalByUserName)
    {
        $this->execChairmanApprovalByUserName = $execChairmanApprovalByUserName;
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
    public function getDurationInMonths()
    {
        return $this->durationInMonths;
    }

    /**
     * @param mixed $durationInMonths
     */
    public function setDurationInMonths($durationInMonths)
    {
        $this->durationInMonths = $durationInMonths;
    }

    /**
     * @return mixed
     */
    public function getDurationInWeeks()
    {
        return $this->durationInWeeks;
    }

    /**
     * @param mixed $durationInWeeks
     */
    public function setDurationInWeeks($durationInWeeks)
    {
        $this->durationInWeeks = $durationInWeeks;
    }

    /**
     * @return mixed
     */
    public function getExpectedDateOfExpiration()
    {
        return $this->expectedDateOfExpiration;
    }

    /**
     * @param mixed $expectedDateOfExpiration
     */
    public function setExpectedDateOfExpiration($expectedDateOfExpiration)
    {
        $this->expectedDateOfExpiration = $expectedDateOfExpiration;
    }

    /**
     * @return mixed
     */
    public function getDateOfAppointedCandidatesAttachment()
    {
        return $this->dateOfAppointedCandidatesAttachment;
    }

    /**
     * @param mixed $dateOfAppointedCandidatesAttachment
     */
    public function setDateOfAppointedCandidatesAttachment($dateOfAppointedCandidatesAttachment)
    {
        $this->dateOfAppointedCandidatesAttachment = $dateOfAppointedCandidatesAttachment;
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
    public function getSelectedCandidatesAttachment(): AttachedDocument
    {
        return $this->selectedCandidatesAttachment;
    }

    /**
     * @param AttachedDocument $selectedCandidatesAttachment
     */
    public function setSelectedCandidatesAttachment(AttachedDocument $selectedCandidatesAttachment)
    {
        $this->selectedCandidatesAttachment = $selectedCandidatesAttachment;
    }

    /**
     * @return AttachedDocument
     */
    public function getAppointedCandidatesAttachment(): AttachedDocument
    {
        return $this->appointedCandidatesAttachment;
    }

    /**
     * @param AttachedDocument $appointedCandidatesAttachment
     */
    public function setAppointedCandidatesAttachment(AttachedDocument $appointedCandidatesAttachment)
    {
        $this->appointedCandidatesAttachment = $appointedCandidatesAttachment;
    }

    /**
     * @return bool
     */
    public function isCommitteeChairRecommended(): bool
    {
        return $this->committeeChairRecommended;
    }

    /**
     * @param bool $committeeChairRecommended
     */
    public function setCommitteeChairRecommended(bool $committeeChairRecommended)
    {
        $this->committeeChairRecommended = $committeeChairRecommended;
    }

    /**
     * @return bool
     */
    public function isCommitteeSecConfirmed(): bool
    {
        return $this->committeeSecConfirmed;
    }

    /**
     * @param bool $committeeSecConfirmed
     */
    public function setCommitteeSecConfirmed(bool $committeeSecConfirmed)
    {
        $this->committeeSecConfirmed = $committeeSecConfirmed;
    }

    /**
     * @return bool
     */
    public function isExecChairmanApproved(): bool
    {
        return $this->execChairmanApproved;
    }

    /**
     * @param bool $execChairmanApproved
     */
    public function setExecChairmanApproved(bool $execChairmanApproved)
    {
        $this->execChairmanApproved = $execChairmanApproved;
    }

    /**
     * @return bool
     */
    public function isDmeConfirmed(): bool
    {
        return $this->dmeConfirmed;
    }

    /**
     * @param bool $dmeConfirmed
     */
    public function setDmeConfirmed(bool $dmeConfirmed)
    {
        $this->dmeConfirmed = $dmeConfirmed;
    }

    /**
     * @return bool
     */
    public function isSelectedCandidatesUploaded(): bool
    {
        return $this->selectedCandidatesUploaded;
    }

    /**
     * @param bool $selectedCandidatesUploaded
     */
    public function setSelectedCandidatesUploaded(bool $selectedCandidatesUploaded)
    {
        $this->selectedCandidatesUploaded = $selectedCandidatesUploaded;
    }

    /**
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expired;
    }

    /**
     * @param bool $expired
     */
    public function setExpired(bool $expired)
    {
        $this->expired = $expired;
    }

    /**
     * @return ApprovalComment[]
     */
    public function getApprovalComments(): array
    {
        return $this->approvalComments;
    }

    /**
     * @param ApprovalComment[] $approvalComments
     */
    public function setApprovalComments(array $approvalComments)
    {
        $this->approvalComments = $approvalComments;
    }

}