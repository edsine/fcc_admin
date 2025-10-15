<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 2/21/2017
 * Time: 3:02 PM
 */

namespace AppBundle\Model\Recruitment;


class Candidate
{

    private $id;
    private $recruitmentId, $recruitmentSelector;
    private $recruitmentYear;
    private $contextId, $contextSelector;
    private $serialNo;
    private $surname, $firstName, $otherNames;
    private $dateOfBirth;
    private $address;
    private $centerState;
    private $phoneNumber;
    private $emailAddress;
    private $lga;
    private $gender;
    private $postApplied;
    private $university;
    private $course;
    private $stateOfOrigin;
    private $classOfDegree;
    private $recordStatus;
    private $created, $createdByUserId;
    private $lastModified, $lastModifiedByUserId;
    private $appointmentStatus;
    private $appointmentStatusLastMod, $appointmentStatusLastModByUserId, $appointmentStatusLastModByUserName;

    private $displaySerialNo;

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
    public function getContextId()
    {
        return $this->contextId;
    }

    /**
     * @param mixed $contextId
     */
    public function setContextId($contextId)
    {
        $this->contextId = $contextId;
    }

    /**
     * @return mixed
     */
    public function getContextSelector()
    {
        return $this->contextSelector;
    }

    /**
     * @param mixed $contextSelector
     */
    public function setContextSelector($contextSelector)
    {
        $this->contextSelector = $contextSelector;
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
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * @param mixed $surname
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getOtherNames()
    {
        return $this->otherNames;
    }

    /**
     * @param mixed $otherNames
     */
    public function setOtherNames($otherNames)
    {
        $this->otherNames = $otherNames;
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
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getCenterState()
    {
        return $this->centerState;
    }

    /**
     * @param mixed $centerState
     */
    public function setCenterState($centerState)
    {
        $this->centerState = $centerState;
    }

    /**
     * @return mixed
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param mixed $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return mixed
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * @param mixed $emailAddress
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
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
    public function getPostApplied()
    {
        return $this->postApplied;
    }

    /**
     * @param mixed $postApplied
     */
    public function setPostApplied($postApplied)
    {
        $this->postApplied = $postApplied;
    }

    /**
     * @return mixed
     */
    public function getUniversity()
    {
        return $this->university;
    }

    /**
     * @param mixed $university
     */
    public function setUniversity($university)
    {
        $this->university = $university;
    }

    /**
     * @return mixed
     */
    public function getCourse()
    {
        return $this->course;
    }

    /**
     * @param mixed $course
     */
    public function setCourse($course)
    {
        $this->course = $course;
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
    public function getClassOfDegree()
    {
        return $this->classOfDegree;
    }

    /**
     * @param mixed $classOfDegree
     */
    public function setClassOfDegree($classOfDegree)
    {
        $this->classOfDegree = $classOfDegree;
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
    public function getAppointmentStatus()
    {
        return $this->appointmentStatus;
    }

    /**
     * @param mixed $appointmentStatus
     */
    public function setAppointmentStatus($appointmentStatus)
    {
        $this->appointmentStatus = $appointmentStatus;
    }

    /**
     * @return mixed
     */
    public function getAppointmentStatusLastMod()
    {
        return $this->appointmentStatusLastMod;
    }

    /**
     * @param mixed $appointmentStatusLastMod
     */
    public function setAppointmentStatusLastMod($appointmentStatusLastMod)
    {
        $this->appointmentStatusLastMod = $appointmentStatusLastMod;
    }

    /**
     * @return mixed
     */
    public function getAppointmentStatusLastModByUserId()
    {
        return $this->appointmentStatusLastModByUserId;
    }

    /**
     * @param mixed $appointmentStatusLastModByUserId
     */
    public function setAppointmentStatusLastModByUserId($appointmentStatusLastModByUserId)
    {
        $this->appointmentStatusLastModByUserId = $appointmentStatusLastModByUserId;
    }

    /**
     * @return mixed
     */
    public function getAppointmentStatusLastModByUserName()
    {
        return $this->appointmentStatusLastModByUserName;
    }

    /**
     * @param mixed $appointmentStatusLastModByUserName
     */
    public function setAppointmentStatusLastModByUserName($appointmentStatusLastModByUserName)
    {
        $this->appointmentStatusLastModByUserName = $appointmentStatusLastModByUserName;
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


}