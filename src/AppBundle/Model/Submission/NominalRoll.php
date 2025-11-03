<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/3/2017
 * Time: 11:25 AM
 */

namespace AppBundle\Model\Submission;


class NominalRoll
{
    private $id;
    private $organizationName;
    private $submissionId;
    private $submissionYear;
    private $serialNo;
    private $employeeStatus;
    private $employeeNumber;
    private $name;
    private $nationality;
    private $stateOfOrigin;
    private $dateOfBirth, $dateOfEmployment, $dateOfPresentAppointment;
    private $gradeLevel;
    private $designation;
    private $stateOfLocation;
    private $gender;
    private $maritalStatus;
    private $lga;
    private $geoPoliticalZone;
    private $physicallyChallengedStatus;
    private $quarterlyReturnEmploymentStatus;

    /**
     * FailedNominalRoleValidation constructor.
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
    public function getGeoPoliticalZone()
    {
        return $this->geoPoliticalZone;
    }

    /**
     * @param mixed $geoPoliticalZone
     */
    public function setGeoPoliticalZone($geoPoliticalZone)
    {
        $this->geoPoliticalZone = $geoPoliticalZone;
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
    public function getLga()
    {
        return $this->lga;
    }

    /**
     * @param mixed $lga
     */
    public function setLga($lga)
    {
        $this->lga = $lga;
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
    public function getNationality()
    {
        return $this->nationality;
    }

    /**
     * @param mixed $nationality
     */
    public function setNationality($nationality)
    {
        $this->nationality = $nationality;
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
    public function getStateOfLocation()
    {
        return $this->stateOfLocation;
    }

    /**
     * @param mixed $stateOfLocation
     */
    public function setStateOfLocation($stateOfLocation)
    {
        $this->stateOfLocation = $stateOfLocation;
    }

    /**
     * @return mixed
     */
    public function getStateOfOrigin()
    {
        return $this->stateOfOrigin;
    }

    /**
     * @param mixed $stateOfOrigin
     */
    public function setStateOfOrigin($stateOfOrigin)
    {
        $this->stateOfOrigin = $stateOfOrigin;
    }

    /**
     * @return mixed
     */
    public function getEmployeeStatus()
    {
        return $this->employeeStatus;
    }

    /**
     * @param mixed $employeeStatus
     */
    public function setEmployeeStatus($employeeStatus)
    {
        $this->employeeStatus = $employeeStatus;
    }

    /**
     * @return mixed
     */
    public function getDateOfPresentAppointment()
    {
        return $this->dateOfPresentAppointment;
    }

    /**
     * @param mixed $dateOfPresentAppointment
     */
    public function setDateOfPresentAppointment($dateOfPresentAppointment)
    {
        $this->dateOfPresentAppointment = $dateOfPresentAppointment;
    }

    /**
     * @return mixed
     */
    public function getPhysicallyChallengedStatus()
    {
        return $this->physicallyChallengedStatus;
    }

    /**
     * @param mixed $physicallyChallengedStatus
     */
    public function setPhysicallyChallengedStatus($physicallyChallengedStatus)
    {
        $this->physicallyChallengedStatus = $physicallyChallengedStatus;
    }

    /**
     * @return mixed
     */
    public function getQuarterlyReturnEmploymentStatus()
    {
        return $this->quarterlyReturnEmploymentStatus;
    }

    /**
     * @param mixed $quarterlyReturnEmploymentStatus
     */
    public function setQuarterlyReturnEmploymentStatus($quarterlyReturnEmploymentStatus)
    {
        $this->quarterlyReturnEmploymentStatus = $quarterlyReturnEmploymentStatus;
    }

}