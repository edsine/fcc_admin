<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/6/2017
 * Time: 3:14 PM
 */

namespace AppBundle\Model\Reporting\Submission;


use AppBundle\Utils\AppConstants;

class SubmissionStatus
{
    private $submissionId;
    private $submissionYear;
    private $totalRowsImportedFormatted;
    private $organizationId, $organizationName, $organizationEstablishmentCode;
    private $mdaDeskOfficerName, $mdaDeskOfficerPhone, $mdaDeskOfficerEmail;
    private $fccDeskOfficerName, $fccDeskOfficerPhone, $fccDeskOfficerEmail;
    private $dateOfSubmission;
    private $uploadedFileName;
    private $validationStatus, $dateValidated;
    private $fccDeskOfficerConfirmationStatus, $datefccDeskOfficerConfirmed;
    private $misHeadApprovalStatus, $dateMisHeadApproved;
    private $processingStatus, $dateProcessed;
    private $displaySerialNo;

    private $validationStepHtml;
    private $confirmationStepHtml;
    private $approvalStepHtml;
    private $processingStepHtml;

    /**
     * SubmissionStatus constructor.
     */
    public function __construct()
    {
    }

    public function initializeStepHtml()
    {
        if ($this->validationStatus === AppConstants::PENDING) {
            $this->validationStepHtml = '<a class="step" href="#">Validation</a>';
        }else if ($this->validationStatus === AppConstants::FAILED){
            $this->validationStepHtml = '<a class="step failed" href="#">Validation</a>';
        }else{
            $this->validationStepHtml = '<a class="step completed" href="#">Validation</a>';
        }

        if ($this->fccDeskOfficerConfirmationStatus === AppConstants::PENDING) {
            $this->confirmationStepHtml = '<a class="step" href="#">Confirmation</a>';
        }else if ($this->fccDeskOfficerConfirmationStatus === AppConstants::CONFIRMED){
            $this->confirmationStepHtml = '<a class="step completed" href="#">Confirmation</a>';
        }

        if ($this->misHeadApprovalStatus === AppConstants::PENDING) {
            $this->approvalStepHtml = '<a class="step" href="#">Approval</a>';
        }else if ($this->misHeadApprovalStatus === AppConstants::APPROVED){
            $this->approvalStepHtml = '<a class="step completed" href="#">Approval</a>';
        }

        if ($this->processingStatus === AppConstants::PENDING) {
            $this->processingStepHtml = '<a class="step" href="#">Processing</a>';
        }else if ($this->processingStatus === AppConstants::COMPLETED){
            $this->processingStepHtml = '<a class="step completed" href="#">Processing</a>';
        }
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
    public function getMdaDeskOfficerName()
    {
        return $this->mdaDeskOfficerName;
    }

    /**
     * @param mixed $mdaDeskOfficerName
     */
    public function setMdaDeskOfficerName($mdaDeskOfficerName)
    {
        $this->mdaDeskOfficerName = $mdaDeskOfficerName;
    }

    /**
     * @return mixed
     */
    public function getMdaDeskOfficerPhone()
    {
        return $this->mdaDeskOfficerPhone;
    }

    /**
     * @param mixed $mdaDeskOfficerPhone
     */
    public function setMdaDeskOfficerPhone($mdaDeskOfficerPhone)
    {
        $this->mdaDeskOfficerPhone = $mdaDeskOfficerPhone;
    }

    /**
     * @return mixed
     */
    public function getMdaDeskOfficerEmail()
    {
        return $this->mdaDeskOfficerEmail;
    }

    /**
     * @param mixed $mdaDeskOfficerEmail
     */
    public function setMdaDeskOfficerEmail($mdaDeskOfficerEmail)
    {
        $this->mdaDeskOfficerEmail = $mdaDeskOfficerEmail;
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
    public function getDateOfSubmission()
    {
        return $this->dateOfSubmission;
    }

    /**
     * @param mixed $dateOfSubmission
     */
    public function setDateOfSubmission($dateOfSubmission)
    {
        $this->dateOfSubmission = $dateOfSubmission;
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
    public function getDateValidated()
    {
        return $this->dateValidated;
    }

    /**
     * @param mixed $dateValidated
     */
    public function setDateValidated($dateValidated)
    {
        $this->dateValidated = $dateValidated;
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
    public function getDatefccDeskOfficerConfirmed()
    {
        return $this->datefccDeskOfficerConfirmed;
    }

    /**
     * @param mixed $datefccDeskOfficerConfirmed
     */
    public function setDatefccDeskOfficerConfirmed($datefccDeskOfficerConfirmed)
    {
        $this->datefccDeskOfficerConfirmed = $datefccDeskOfficerConfirmed;
    }

    /**
     * @return mixed
     */
    public function getMisHeadApprovalStatus()
    {
        return $this->misHeadApprovalStatus;
    }

    /**
     * @param mixed $misHeadApprovalStatus
     */
    public function setMisHeadApprovalStatus($misHeadApprovalStatus)
    {
        $this->misHeadApprovalStatus = $misHeadApprovalStatus;
    }

    /**
     * @return mixed
     */
    public function getDateMisHeadApproved()
    {
        return $this->dateMisHeadApproved;
    }

    /**
     * @param mixed $dateMisHeadApproved
     */
    public function setDateMisHeadApproved($dateMisHeadApproved)
    {
        $this->dateMisHeadApproved = $dateMisHeadApproved;
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
    public function getDateProcessed()
    {
        return $this->dateProcessed;
    }

    /**
     * @param mixed $dateProcessed
     */
    public function setDateProcessed($dateProcessed)
    {
        $this->dateProcessed = $dateProcessed;
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
    public function getValidationStepHtml()
    {
        return $this->validationStepHtml;
    }

    /**
     * @param mixed $validationStepHtml
     */
    public function setValidationStepHtml($validationStepHtml)
    {
        $this->validationStepHtml = $validationStepHtml;
    }

    /**
     * @return mixed
     */
    public function getConfirmationStepHtml()
    {
        return $this->confirmationStepHtml;
    }

    /**
     * @param mixed $confirmationStepHtml
     */
    public function setConfirmationStepHtml($confirmationStepHtml)
    {
        $this->confirmationStepHtml = $confirmationStepHtml;
    }

    /**
     * @return mixed
     */
    public function getApprovalStepHtml()
    {
        return $this->approvalStepHtml;
    }

    /**
     * @param mixed $approvalStepHtml
     */
    public function setApprovalStepHtml($approvalStepHtml)
    {
        $this->approvalStepHtml = $approvalStepHtml;
    }

    /**
     * @return mixed
     */
    public function getProcessingStepHtml()
    {
        return $this->processingStepHtml;
    }

    /**
     * @param mixed $processingStepHtml
     */
    public function setProcessingStepHtml($processingStepHtml)
    {
        $this->processingStepHtml = $processingStepHtml;
    }

}