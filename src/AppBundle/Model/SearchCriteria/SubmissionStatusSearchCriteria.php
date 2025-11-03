<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/6/2017
 * Time: 3:49 PM
 */

namespace AppBundle\Model\SearchCriteria;


class SubmissionStatusSearchCriteria
{
    private $submissionYear;
    private $organizationId;
    private $validationStatus;
    private $fccDeskOfficerConfirmationStatus;
    private $misHeadApprovalStatus;
    private $processingStatus;

    private $submissionStatus;

    /**
     * SubmissionStatusSearchCritera constructor.
     */
    public function __construct()
    {
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
    public function getSubmissionStatus()
    {
        return $this->submissionStatus;
    }

    /**
     * @param mixed $submissionStatus
     */
    public function setSubmissionStatus($submissionStatus)
    {
        $this->submissionStatus = $submissionStatus;
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


}