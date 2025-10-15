<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/3/2017
 * Time: 11:25 AM
 */

namespace AppBundle\Model\Submission;


class NominalRollValidation
{
    private $id;
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

    private $failedEmployeeStatus = 0;
    private $failedEmployeeNumber = 0;
    private $failedName = 0;
    private $failedNationality = 0;
    private $failedStateOfOrigin = 0;
    private $failedDateOfBirth = 0;
    private $failedDateOfEmployment = 0;
    private $failedDateOfPresentAppointment = 0;
    private $failedGradeLevel = 0;
    private $failedDesignation = 0;
    private $failedStateOfLocation = 0;
    private $failedGender = 0;
    private $failedMaritalStatus = 0;
    private $failedLga = 0;
    private $failedGeopoliticalZone = 0;
    private $failedPhysicallyChallenged = 0;
    private $failedQuarterlyReturnStatus = 0;

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
     * @return int
     */
    public function getFailedDateOfBirth()
    {
        return $this->failedDateOfBirth;
    }

    /**
     * @param int $failedDateOfBirth
     */
    public function setFailedDateOfBirth($failedDateOfBirth)
    {
        $this->failedDateOfBirth = $failedDateOfBirth;
    }

    /**
     * @return int
     */
    public function getFailedDateOfEmployment()
    {
        return $this->failedDateOfEmployment;
    }

    /**
     * @param int $failedDateOfEmployment
     */
    public function setFailedDateOfEmployment($failedDateOfEmployment)
    {
        $this->failedDateOfEmployment = $failedDateOfEmployment;
    }

    /**
     * @return int
     */
    public function getFailedDesignation()
    {
        return $this->failedDesignation;
    }

    /**
     * @param int $failedDesignation
     */
    public function setFailedDesignation($failedDesignation)
    {
        $this->failedDesignation = $failedDesignation;
    }

    /**
     * @return int
     */
    public function getFailedEmployeeNumber()
    {
        return $this->failedEmployeeNumber;
    }

    /**
     * @param int $failedEmployeeNumber
     */
    public function setFailedEmployeeNumber($failedEmployeeNumber)
    {
        $this->failedEmployeeNumber = $failedEmployeeNumber;
    }

    /**
     * @return int
     */
    public function getFailedGender()
    {
        return $this->failedGender;
    }

    /**
     * @param int $failedGender
     */
    public function setFailedGender($failedGender)
    {
        $this->failedGender = $failedGender;
    }

    /**
     * @return int
     */
    public function getFailedGeopoliticalZone()
    {
        return $this->failedGeopoliticalZone;
    }

    /**
     * @param int $failedGeopoliticalZone
     */
    public function setFailedGeopoliticalZone($failedGeopoliticalZone)
    {
        $this->failedGeopoliticalZone = $failedGeopoliticalZone;
    }

    /**
     * @return int
     */
    public function getFailedGradeLevel()
    {
        return $this->failedGradeLevel;
    }

    /**
     * @param int $failedGradeLevel
     */
    public function setFailedGradeLevel($failedGradeLevel)
    {
        $this->failedGradeLevel = $failedGradeLevel;
    }

    /**
     * @return int
     */
    public function getFailedLga()
    {
        return $this->failedLga;
    }

    /**
     * @param int $failedLga
     */
    public function setFailedLga($failedLga)
    {
        $this->failedLga = $failedLga;
    }

    /**
     * @return int
     */
    public function getFailedMaritalStatus()
    {
        return $this->failedMaritalStatus;
    }

    /**
     * @param int $failedMaritalStatus
     */
    public function setFailedMaritalStatus($failedMaritalStatus)
    {
        $this->failedMaritalStatus = $failedMaritalStatus;
    }

    /**
     * @return int
     */
    public function getFailedName()
    {
        return $this->failedName;
    }

    /**
     * @param int $failedName
     */
    public function setFailedName($failedName)
    {
        $this->failedName = $failedName;
    }

    /**
     * @return int
     */
    public function getFailedNationality()
    {
        return $this->failedNationality;
    }

    /**
     * @param int $failedNationality
     */
    public function setFailedNationality($failedNationality)
    {
        $this->failedNationality = $failedNationality;
    }

    /**
     * @return int
     */
    public function getFailedStateOfLocation()
    {
        return $this->failedStateOfLocation;
    }

    /**
     * @param int $failedStateOfLocation
     */
    public function setFailedStateOfLocation($failedStateOfLocation)
    {
        $this->failedStateOfLocation = $failedStateOfLocation;
    }

    /**
     * @return int
     */
    public function getFailedStateOfOrigin()
    {
        return $this->failedStateOfOrigin;
    }

    /**
     * @param int $failedStateOfOrigin
     */
    public function setFailedStateOfOrigin($failedStateOfOrigin)
    {
        $this->failedStateOfOrigin = $failedStateOfOrigin;
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

    /**
     * @return int
     */
    public function getFailedEmployeeStatus(): int
    {
        return $this->failedEmployeeStatus;
    }

    /**
     * @param int $failedEmployeeStatus
     */
    public function setFailedEmployeeStatus(int $failedEmployeeStatus)
    {
        $this->failedEmployeeStatus = $failedEmployeeStatus;
    }

    /**
     * @return int
     */
    public function getFailedDateOfPresentAppointment(): int
    {
        return $this->failedDateOfPresentAppointment;
    }

    /**
     * @param int $failedDateOfPresentAppointment
     */
    public function setFailedDateOfPresentAppointment(int $failedDateOfPresentAppointment)
    {
        $this->failedDateOfPresentAppointment = $failedDateOfPresentAppointment;
    }

    /**
     * @return int
     */
    public function getFailedPhysicallyChallenged(): int
    {
        return $this->failedPhysicallyChallenged;
    }

    /**
     * @param int $failedPhysicallyChallenged
     */
    public function setFailedPhysicallyChallenged(int $failedPhysicallyChallenged)
    {
        $this->failedPhysicallyChallenged = $failedPhysicallyChallenged;
    }

    /**
     * @return int
     */
    public function getFailedQuarterlyReturnStatus(): int
    {
        return $this->failedQuarterlyReturnStatus;
    }

    /**
     * @param int $failedQuarterlyReturnStatus
     */
    public function setFailedQuarterlyReturnStatus(int $failedQuarterlyReturnStatus)
    {
        $this->failedQuarterlyReturnStatus = $failedQuarterlyReturnStatus;
    }

}