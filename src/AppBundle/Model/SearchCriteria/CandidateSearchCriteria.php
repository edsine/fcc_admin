<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 2:04 PM
 */

namespace AppBundle\Model\SearchCriteria;


class CandidateSearchCriteria
{
    private $listId;
    private $recruitmentId;
    private $cocId;
    private $surname, $firstName, $lastName;
    private $centerState;
    private $phoneNumber;
    private $postApplied;

    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getListId()
    {
        return $this->listId;
    }

    /**
     * @param mixed $listId
     */
    public function setListId($listId): void
    {
        $this->listId = $listId;
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
    public function getCocId()
    {
        return $this->cocId;
    }

    /**
     * @param mixed $cocId
     */
    public function setCocId($cocId)
    {
        $this->cocId = $cocId;
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
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
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

}