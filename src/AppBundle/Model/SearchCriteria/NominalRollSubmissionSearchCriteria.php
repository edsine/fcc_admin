<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/6/2017
 * Time: 12:51 AM
 */

namespace AppBundle\Model\SearchCriteria;


class NominalRollSubmissionSearchCriteria
{
    private $submissionId;
    private $submissionYear;
    private $submissionType;
    private $organizationId, $organizationMnemonic, $organizationEstablishmentCode;
    private $uploadedFileName;
    private $totalRowsImported;
    private $validationStatus;
    private $fccDeskOfficerConfirmationStatus, $fccMisHeadApprovalStatus;
    private $processingStatus;
    private $activeStatus;

    /**
     * NominalRoleUploadSearchCriteria constructor.
     */
    public function __construct()
    {
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


}