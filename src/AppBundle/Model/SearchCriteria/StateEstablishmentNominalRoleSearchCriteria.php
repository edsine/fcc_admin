<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 2:04 PM
 */

namespace AppBundle\Model\SearchCriteria;


class StateEstablishmentNominalRoleSearchCriteria
{
    private $recordId;
    private $submissionId;
    private $submissionYear;
    private $serialNo;
    private $employeeNumber;
    private $name;
    private $lgaOfOrigin;
    private $senatorialDistrict;
    private $dateOfBirth, $dateOfEmployment;
    private $gradeLevel;
    private $designation;
    private $lgaOfDeployment;
    private $gender;
    private $maritalStatus;

    public function __construct()
    {

    }

    /**
     * @return mixed
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * @param mixed $dateOfBirth
     */
    public function setDateOfBirth($dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;
    }

    /**
     * @return mixed
     */
    public function getDateOfEmployment()
    {
        return $this->dateOfEmployment;
    }

    /**
     * @param mixed $dateOfEmployment
     */
    public function setDateOfEmployment($dateOfEmployment)
    {
        $this->dateOfEmployment = $dateOfEmployment;
    }

    /**
     * @return mixed
     */
    public function getDesignation()
    {
        return $this->designation;
    }

    /**
     * @param mixed $designation
     */
    public function setDesignation($designation)
    {
        $this->designation = $designation;
    }

    /**
     * @return mixed
     */
    public function getEmployeeNumber()
    {
        return $this->employeeNumber;
    }

    /**
     * @param mixed $employeeNumber
     */
    public function setEmployeeNumber($employeeNumber)
    {
        $this->employeeNumber = $employeeNumber;
    }

    /**
     * @return mixed
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param mixed $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return mixed
     */
    public function getGradeLevel()
    {
        return $this->gradeLevel;
    }

    /**
     * @param mixed $gradeLevel
     */
    public function setGradeLevel($gradeLevel)
    {
        $this->gradeLevel = $gradeLevel;
    }

    /**
     * @return mixed
     */
    public function getMaritalStatus()
    {
        return $this->maritalStatus;
    }

    /**
     * @param mixed $maritalStatus
     */
    public function setMaritalStatus($maritalStatus)
    {
        $this->maritalStatus = $maritalStatus;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getLgaOfOrigin()
    {
        return $this->lgaOfOrigin;
    }

    /**
     * @param mixed $lgaOfOrigin
     */
    public function setLgaOfOrigin($lgaOfOrigin)
    {
        $this->lgaOfOrigin = $lgaOfOrigin;
    }

    /**
     * @return mixed
     */
    public function getRecordId()
    {
        return $this->recordId;
    }

    /**
     * @param mixed $recordId
     */
    public function setRecordId($recordId)
    {
        $this->recordId = $recordId;
    }

    /**
     * @return mixed
     */
    public function getSerialNo()
    {
        return $this->serialNo;
    }

    /**
     * @param mixed $serialNo
     */
    public function setSerialNo($serialNo)
    {
        $this->serialNo = $serialNo;
    }

    /**
     * @return mixed
     */
    public function getLgaOfDeployment()
    {
        return $this->lgaOfDeployment;
    }

    /**
     * @param mixed $lgaOfDeployment
     */
    public function setLgaOfDeployment($lgaOfDeployment)
    {
        $this->lgaOfDeployment = $lgaOfDeployment;
    }

    /**
     * @return mixed
     */
    public function getSenatorialDistrict()
    {
        return $this->senatorialDistrict;
    }

    /**
     * @param mixed $senatorialDistrict
     */
    public function setSenatorialDistrict($senatorialDistrict)
    {
        $this->senatorialDistrict = $senatorialDistrict;
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


}