<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 2/21/2017
 * Time: 3:02 PM
 */

namespace AppBundle\Model\Recruitment;


use AppBundle\Model\Document\AttachedDocument;

class CandidateValidation
{

    private $id;
    private $whichListId;
    private $recruitmentId, $recruitmentSelector;
    private $recruitmentYear;
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
    private $appointmentStatus;
    private $recordStatus;
    private $created, $createdByUserId;
    private $lastModified, $lastModifiedByUserId;
    private $selector;

    private $displaySerialNo;

    private $failedSurname = 0;
    private $failedFirstName = 0;
    private $failedOtherNames = 0;
    private $failedDateOfBirth = 0;
    private $failedAddress = 0;
    private $failedCenter = 0;
    private $failedPhoneNumber = 0;
    private $failedEmailAddress = 0;
    private $failedLga = 0;
    private $failedGender = 0;
    private $failedPostApplied = 0;
    private $failedUniversity = 0;
    private $failedCourse = 0;
    private $failedStateOfOrigin = 0;
    private $failedClassOfDegree = 0;
    private $failedAppointmentStatus = 0;
    private $failedNotInLongList = 0;

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
    public function getWhichListId()
    {
        return $this->whichListId;
    }

    /**
     * @param mixed $whichListId
     */
    public function setWhichListId($whichListId): void
    {
        $this->whichListId = $whichListId;
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
    public function getAppointmentStatus()
    {
        return $this->appointmentStatus;
    }

    /**
     * @param mixed $appointmentStatus
     */
    public function setAppointmentStatus($appointmentStatus): void
    {
        $this->appointmentStatus = $appointmentStatus;
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
     * @return int
     */
    public function getFailedSurname(): int
    {
        return $this->failedSurname;
    }

    /**
     * @param int $failedSurname
     */
    public function setFailedSurname(int $failedSurname): void
    {
        $this->failedSurname = $failedSurname;
    }

    /**
     * @return int
     */
    public function getFailedFirstName(): int
    {
        return $this->failedFirstName;
    }

    /**
     * @param int $failedFirstName
     */
    public function setFailedFirstName(int $failedFirstName): void
    {
        $this->failedFirstName = $failedFirstName;
    }

    /**
     * @return int
     */
    public function getFailedOtherNames(): int
    {
        return $this->failedOtherNames;
    }

    /**
     * @param int $failedOtherNames
     */
    public function setFailedOtherNames(int $failedOtherNames): void
    {
        $this->failedOtherNames = $failedOtherNames;
    }

    /**
     * @return int
     */
    public function getFailedDateOfBirth(): int
    {
        return $this->failedDateOfBirth;
    }

    /**
     * @param int $failedDateOfBirth
     */
    public function setFailedDateOfBirth(int $failedDateOfBirth): void
    {
        $this->failedDateOfBirth = $failedDateOfBirth;
    }

    /**
     * @return int
     */
    public function getFailedAddress(): int
    {
        return $this->failedAddress;
    }

    /**
     * @param int $failedAddress
     */
    public function setFailedAddress(int $failedAddress): void
    {
        $this->failedAddress = $failedAddress;
    }

    /**
     * @return int
     */
    public function getFailedCenter(): int
    {
        return $this->failedCenter;
    }

    /**
     * @param int $failedCenter
     */
    public function setFailedCenter(int $failedCenter): void
    {
        $this->failedCenter = $failedCenter;
    }

    /**
     * @return int
     */
    public function getFailedPhoneNumber(): int
    {
        return $this->failedPhoneNumber;
    }

    /**
     * @param int $failedPhoneNumber
     */
    public function setFailedPhoneNumber(int $failedPhoneNumber): void
    {
        $this->failedPhoneNumber = $failedPhoneNumber;
    }

    /**
     * @return int
     */
    public function getFailedEmailAddress(): int
    {
        return $this->failedEmailAddress;
    }

    /**
     * @param int $failedEmailAddress
     */
    public function setFailedEmailAddress(int $failedEmailAddress): void
    {
        $this->failedEmailAddress = $failedEmailAddress;
    }

    /**
     * @return int
     */
    public function getFailedLga(): int
    {
        return $this->failedLga;
    }

    /**
     * @param int $failedLga
     */
    public function setFailedLga(int $failedLga): void
    {
        $this->failedLga = $failedLga;
    }

    /**
     * @return int
     */
    public function getFailedGender(): int
    {
        return $this->failedGender;
    }

    /**
     * @param int $failedGender
     */
    public function setFailedGender(int $failedGender): void
    {
        $this->failedGender = $failedGender;
    }

    /**
     * @return int
     */
    public function getFailedPostApplied(): int
    {
        return $this->failedPostApplied;
    }

    /**
     * @param int $failedPostApplied
     */
    public function setFailedPostApplied(int $failedPostApplied): void
    {
        $this->failedPostApplied = $failedPostApplied;
    }

    /**
     * @return int
     */
    public function getFailedUniversity(): int
    {
        return $this->failedUniversity;
    }

    /**
     * @param int $failedUniversity
     */
    public function setFailedUniversity(int $failedUniversity): void
    {
        $this->failedUniversity = $failedUniversity;
    }

    /**
     * @return int
     */
    public function getFailedCourse(): int
    {
        return $this->failedCourse;
    }

    /**
     * @param int $failedCourse
     */
    public function setFailedCourse(int $failedCourse): void
    {
        $this->failedCourse = $failedCourse;
    }

    /**
     * @return int
     */
    public function getFailedStateOfOrigin(): int
    {
        return $this->failedStateOfOrigin;
    }

    /**
     * @param int $failedStateOfOrigin
     */
    public function setFailedStateOfOrigin(int $failedStateOfOrigin): void
    {
        $this->failedStateOfOrigin = $failedStateOfOrigin;
    }

    /**
     * @return int
     */
    public function getFailedClassOfDegree(): int
    {
        return $this->failedClassOfDegree;
    }

    /**
     * @param int $failedClassOfDegree
     */
    public function setFailedClassOfDegree(int $failedClassOfDegree): void
    {
        $this->failedClassOfDegree = $failedClassOfDegree;
    }

    /**
     * @return int
     */
    public function getFailedAppointmentStatus(): int
    {
        return $this->failedAppointmentStatus;
    }

    /**
     * @param int $failedAppointmentStatus
     */
    public function setFailedAppointmentStatus(int $failedAppointmentStatus): void
    {
        $this->failedAppointmentStatus = $failedAppointmentStatus;
    }

    /**
     * @return int
     */
    public function getFailedNotInLongList(): int
    {
        return $this->failedNotInLongList;
    }

    /**
     * @param int $failedNotInLongList
     */
    public function setFailedNotInLongList(int $failedNotInLongList): void
    {
        $this->failedNotInLongList = $failedNotInLongList;
    }
}