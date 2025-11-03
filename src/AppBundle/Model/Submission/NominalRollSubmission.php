<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/1/2017
 * Time: 2:55 PM
 */

namespace AppBundle\Model\Submission;


class NominalRollSubmission
{

    private $submissionId;
    private $submissionYear;
    private $organizationId, $organizationMnemonic, $organizationEstablishmentCode, $organizationName;
    private $stateOwnedEstablishmentStateId, $stateOwnedEstablishmentStateName;
    private $fccDeskOfficerUserId, $fccDeskOfficerName, $fccDeskOfficerEmail, $fccDeskOfficerPhone;
    private $uploadedFileName, $simpleFileName;
    private $totalRowsImported, $totalRowsImportedFormatted;
    private $validationStatus, $dateValidationPassed, $dateValidationPassedFormatted ;
    private $submissionType;
    private $fccDeskOfficerConfirmationStatus, $fccMisHeadApprovalStatus;
    private $dateFccDeskOfficerConfirmed, $dateFccDeskOfficerConfirmedFormatted;
    private $dateFccMisHeadApproved, $dateFccMisHeadApprovedFormatted;
    private $processingStatus;
    private $activeStatus;
    private $created, $createdByUserId;
    private $lastModified;
    private $lastModifiedByUserId;
    private $mdaAdminUserId, $mdaAdminName, $mdaAdminEmail, $mdaAdminPhone;

    private $permissionCode;

    private $displaySerialNo;

    private $isFederalLevelSubmission = false;
    private $isStateLevelSubmission = false;

    private $validationPending = false;
    private $passedValidation = false;
    private $failedValidation = false;
    private $failedValidationWithFatalError = false;

    private $fccDeskOfficerConfirmed = false;
    private $misHeadApproved = false;
    private $processed = false;

    private $mainSubmission = false;
    private $quarterlyReturn = false;

    /**
     * NominalRoleSubmission constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getTotalRowsImported()
    {
        return $this->totalRowsImported;
    }

    /**
     * @param mixed $totalRowsImported
     */
    public function setTotalRowsImported($totalRowsImported)
    {
        $this->totalRowsImported = $totalRowsImported;
    }

    /**
     * @return mixed
     */
    public function getActiveStatus()
    {
        return $this->activeStatus;
    }

    /**
     * @param mixed $activeStatus
     */
    public function setActiveStatus($activeStatus)
    {
        $this->activeStatus = $activeStatus;
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
    public function getOrganizationMnemonic()
    {
        return $this->organizationMnemonic;
    }

    /**
     * @param mixed $organizationMnemonic
     */
    public function setOrganizationMnemonic($organizationMnemonic)
    {
        $this->organizationMnemonic = $organizationMnemonic;
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
    public function getStateOwnedEstablishmentStateId()
    {
        return $this->stateOwnedEstablishmentStateId;
    }

    /**
     * @param mixed $stateOwnedEstablishmentStateId
     */
    public function setStateOwnedEstablishmentStateId($stateOwnedEstablishmentStateId)
    {
        $this->stateOwnedEstablishmentStateId = $stateOwnedEstablishmentStateId;
    }

    /**
     * @return mixed
     */
    public function getStateOwnedEstablishmentStateName()
    {
        return $this->stateOwnedEstablishmentStateName;
    }

    /**
     * @param mixed $stateOwnedEstablishmentStateName
     */
    public function setStateOwnedEstablishmentStateName($stateOwnedEstablishmentStateName)
    {
        $this->stateOwnedEstablishmentStateName = $stateOwnedEstablishmentStateName;
    }

    /**
     * @return mixed
     */
    public function getProcessingStatus()
    {
        return $this->processingStatus;
    }

    /**
     * @param mixed $processingStatus
     */
    public function setProcessingStatus($processingStatus)
    {
        $this->processingStatus = $processingStatus;
    }

    /**
     * @return mixed
     */
    public function getSubmissionId()
    {
        return $this->submissionId;
    }

    /**
     * @param mixed $submissionId
     */
    public function setSubmissionId($submissionId)
    {
        $this->submissionId = $submissionId;
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
    public function getUploadedFileName()
    {
        return $this->uploadedFileName;
    }

    /**
     * @param mixed $uploadedFileName
     */
    public function setUploadedFileName($uploadedFileName)
    {
        $this->uploadedFileName = $uploadedFileName;
    }

    /**
     * @return mixed
     */
    public function getSimpleFileName()
    {
        return $this->simpleFileName;
    }

    /**
     * @param mixed $simpleFileName
     */
    public function setSimpleFileName($simpleFileName)
    {
        $this->simpleFileName = $simpleFileName;
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
    public function setValidationStatus($validationStatus)
    {
        $this->validationStatus = $validationStatus;
    }

    /**
     * @return mixed
     */
    public function getFccDeskOfficerConfirmationStatus()
    {
        return $this->fccDeskOfficerConfirmationStatus;
    }

    /**
     * @param mixed $fccDeskOfficerConfirmationStatus
     */
    public function setFccDeskOfficerConfirmationStatus($fccDeskOfficerConfirmationStatus)
    {
        $this->fccDeskOfficerConfirmationStatus = $fccDeskOfficerConfirmationStatus;
    }

    /**
     * @return mixed
     */
    public function getFccMisHeadApprovalStatus()
    {
        return $this->fccMisHeadApprovalStatus;
    }

    /**
     * @param mixed $fccMisHeadApprovalStatus
     */
    public function setFccMisHeadApprovalStatus($fccMisHeadApprovalStatus)
    {
        $this->fccMisHeadApprovalStatus = $fccMisHeadApprovalStatus;
    }

    /**
     * @return mixed
     */
    public function getTotalRowsImportedFormatted()
    {
        return $this->totalRowsImportedFormatted;
    }

    /**
     * @param mixed $totalRowsImportedFormatted
     */
    public function setTotalRowsImportedFormatted($totalRowsImportedFormatted)
    {
        $this->totalRowsImportedFormatted = $totalRowsImportedFormatted;
    }

    /**
     * @return mixed
     */
    public function getDateFccDeskOfficerConfirmed()
    {
        return $this->dateFccDeskOfficerConfirmed;
    }

    /**
     * @param mixed $dateFccDeskOfficerConfirmed
     */
    public function setDateFccDeskOfficerConfirmed($dateFccDeskOfficerConfirmed)
    {
        $this->dateFccDeskOfficerConfirmed = $dateFccDeskOfficerConfirmed;
    }

    /**
     * @return mixed
     */
    public function getDateFccDeskOfficerConfirmedFormatted()
    {
        return $this->dateFccDeskOfficerConfirmedFormatted;
    }

    /**
     * @param mixed $dateFccDeskOfficerConfirmedFormatted
     */
    public function setDateFccDeskOfficerConfirmedFormatted($dateFccDeskOfficerConfirmedFormatted)
    {
        $this->dateFccDeskOfficerConfirmedFormatted = $dateFccDeskOfficerConfirmedFormatted;
    }

    /**
     * @return mixed
     */
    public function getDateFccMisHeadApproved()
    {
        return $this->dateFccMisHeadApproved;
    }

    /**
     * @param mixed $dateFccMisHeadApproved
     */
    public function setDateFccMisHeadApproved($dateFccMisHeadApproved)
    {
        $this->dateFccMisHeadApproved = $dateFccMisHeadApproved;
    }

    /**
     * @return mixed
     */
    public function getDateFccMisHeadApprovedFormatted()
    {
        return $this->dateFccMisHeadApprovedFormatted;
    }

    /**
     * @param mixed $dateFccMisHeadApprovedFormatted
     */
    public function setDateFccMisHeadApprovedFormatted($dateFccMisHeadApprovedFormatted)
    {
        $this->dateFccMisHeadApprovedFormatted = $dateFccMisHeadApprovedFormatted;
    }

    /**
     * @return mixed
     */
    public function getDateValidationPassed()
    {
        return $this->dateValidationPassed;
    }

    /**
     * @param mixed $dateValidationPassed
     */
    public function setDateValidationPassed($dateValidationPassed)
    {
        $this->dateValidationPassed = $dateValidationPassed;
    }

    /**
     * @return mixed
     */
    public function getDateValidationPassedFormatted()
    {
        return $this->dateValidationPassedFormatted;
    }

    /**
     * @param mixed $dateValidationPassedFormatted
     */
    public function setDateValidationPassedFormatted($dateValidationPassedFormatted)
    {
        $this->dateValidationPassedFormatted = $dateValidationPassedFormatted;
    }

    /**
     * @return mixed
     */
    public function getSubmissionType()
    {
        return $this->submissionType;
    }

    /**
     * @param mixed $submissionType
     */
    public function setSubmissionType($submissionType)
    {
        $this->submissionType = $submissionType;
    }

    /**
     * @return mixed
     */
    public function getFccDeskOfficerEmail()
    {
        return $this->fccDeskOfficerEmail;
    }

    /**
     * @param mixed $fccDeskOfficerEmail
     */
    public function setFccDeskOfficerEmail($fccDeskOfficerEmail)
    {
        $this->fccDeskOfficerEmail = $fccDeskOfficerEmail;
    }

    /**
     * @return mixed
     */
    public function getFccDeskOfficerName()
    {
        return $this->fccDeskOfficerName;
    }

    /**
     * @param mixed $fccDeskOfficerName
     */
    public function setFccDeskOfficerName($fccDeskOfficerName)
    {
        $this->fccDeskOfficerName = $fccDeskOfficerName;
    }

    /**
     * @return mixed
     */
    public function getFccDeskOfficerPhone()
    {
        return $this->fccDeskOfficerPhone;
    }

    /**
     * @param mixed $fccDeskOfficerPhone
     */
    public function setFccDeskOfficerPhone($fccDeskOfficerPhone)
    {
        $this->fccDeskOfficerPhone = $fccDeskOfficerPhone;
    }

    /**
     * @return mixed
     */
    public function getFccDeskOfficerUserId()
    {
        return $this->fccDeskOfficerUserId;
    }

    /**
     * @param mixed $fccDeskOfficerUserId
     */
    public function setFccDeskOfficerUserId($fccDeskOfficerUserId)
    {
        $this->fccDeskOfficerUserId = $fccDeskOfficerUserId;
    }

    /**
     * @return mixed
     */
    public function getMdaAdminEmail()
    {
        return $this->mdaAdminEmail;
    }

    /**
     * @param mixed $mdaAdminEmail
     */
    public function setMdaAdminEmail($mdaAdminEmail)
    {
        $this->mdaAdminEmail = $mdaAdminEmail;
    }

    /**
     * @return mixed
     */
    public function getMdaAdminName()
    {
        return $this->mdaAdminName;
    }

    /**
     * @param mixed $mdaAdminName
     */
    public function setMdaAdminName($mdaAdminName)
    {
        $this->mdaAdminName = $mdaAdminName;
    }

    /**
     * @return mixed
     */
    public function getMdaAdminPhone()
    {
        return $this->mdaAdminPhone;
    }

    /**
     * @param mixed $mdaAdminPhone
     */
    public function setMdaAdminPhone($mdaAdminPhone)
    {
        $this->mdaAdminPhone = $mdaAdminPhone;
    }

    /**
     * @return mixed
     */
    public function getMdaAdminUserId()
    {
        return $this->mdaAdminUserId;
    }

    /**
     * @param mixed $mdaAdminUserId
     */
    public function setMdaAdminUserId($mdaAdminUserId)
    {
        $this->mdaAdminUserId = $mdaAdminUserId;
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
     * @return boolean
     */
    public function isIsFederalLevelSubmission(): bool
    {
        return $this->isFederalLevelSubmission;
    }

    /**
     * @param boolean $isFederalLevelSubmission
     */
    public function setIsFederalLevelSubmission(bool $isFederalLevelSubmission)
    {
        $this->isFederalLevelSubmission = $isFederalLevelSubmission;
    }

    /**
     * @return boolean
     */
    public function isIsStateLevelSubmission(): bool
    {
        return $this->isStateLevelSubmission;
    }

    /**
     * @param boolean $isStateLevelSubmission
     */
    public function setIsStateLevelSubmission(bool $isStateLevelSubmission)
    {
        $this->isStateLevelSubmission = $isStateLevelSubmission;
    }

    /**
     * @return mixed
     */
    public function getPermissionCode()
    {
        return $this->permissionCode;
    }

    /**
     * @param mixed $permissionCode
     */
    public function setPermissionCode($permissionCode)
    {
        $this->permissionCode = $permissionCode;
    }

    /**
     * @return bool
     */
    public function isPassedValidation(): bool
    {
        return $this->passedValidation;
    }

    /**
     * @param bool $passedValidation
     */
    public function setPassedValidation(bool $passedValidation)
    {
        $this->passedValidation = $passedValidation;
    }

    /**
     * @return bool
     */
    public function isFailedValidation(): bool
    {
        return $this->failedValidation;
    }

    /**
     * @param bool $failedValidation
     */
    public function setFailedValidation(bool $failedValidation)
    {
        $this->failedValidation = $failedValidation;
    }

    /**
     * @return bool
     */
    public function isFccDeskOfficerConfirmed(): bool
    {
        return $this->fccDeskOfficerConfirmed;
    }

    /**
     * @param bool $fccDeskOfficerConfirmed
     */
    public function setFccDeskOfficerConfirmed(bool $fccDeskOfficerConfirmed)
    {
        $this->fccDeskOfficerConfirmed = $fccDeskOfficerConfirmed;
    }

    /**
     * @return bool
     */
    public function isMisHeadApproved(): bool
    {
        return $this->misHeadApproved;
    }

    /**
     * @param bool $misHeadApproved
     */
    public function setMisHeadApproved(bool $misHeadApproved)
    {
        $this->misHeadApproved = $misHeadApproved;
    }

    /**
     * @return bool
     */
    public function isValidationPending(): bool
    {
        return $this->validationPending;
    }

    /**
     * @param bool $validationPending
     */
    public function setValidationPending(bool $validationPending)
    {
        $this->validationPending = $validationPending;
    }

    /**
     * @return bool
     */
    public function isProcessed(): bool
    {
        return $this->processed;
    }

    /**
     * @param bool $processed
     */
    public function setProcessed(bool $processed)
    {
        $this->processed = $processed;
    }

    /**
     * @return bool
     */
    public function isFailedValidationWithFatalError(): bool
    {
        return $this->failedValidationWithFatalError;
    }

    /**
     * @param bool $failedValidationWithFatalError
     */
    public function setFailedValidationWithFatalError(bool $failedValidationWithFatalError)
    {
        $this->failedValidationWithFatalError = $failedValidationWithFatalError;
    }

    /**
     * @return bool
     */
    public function isMainSubmission(): bool
    {
        return $this->mainSubmission;
    }

    /**
     * @param bool $mainSubmission
     */
    public function setMainSubmission(bool $mainSubmission)
    {
        $this->mainSubmission = $mainSubmission;
    }

    /**
     * @return bool
     */
    public function isQuarterlyReturn(): bool
    {
        return $this->quarterlyReturn;
    }

    /**
     * @param bool $quarterlyReturn
     */
    public function setQuarterlyReturn(bool $quarterlyReturn)
    {
        $this->quarterlyReturn = $quarterlyReturn;
    }

}