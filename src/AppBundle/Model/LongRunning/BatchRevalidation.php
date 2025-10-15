<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 4/13/2017
 * Time: 3:58 PM
 */

namespace AppBundle\Model\LongRunning;


class BatchRevalidation
{
    private $submissionId;
    private $validationStatus;
    private $establishmentType;

    /**
     * BatchRevalidation constructor.
     */
    public function __construct()
    {
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
    public function getEstablishmentType()
    {
        return $this->establishmentType;
    }

    /**
     * @param mixed $establishmentType
     */
    public function setEstablishmentType($establishmentType)
    {
        $this->establishmentType = $establishmentType;
    }


}