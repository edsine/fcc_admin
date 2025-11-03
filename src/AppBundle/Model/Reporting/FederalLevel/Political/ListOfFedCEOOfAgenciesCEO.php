<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/25/2017
 * Time: 10:31 AM
 */

namespace AppBundle\Model\Reporting\FederalLevel\Political;


class ListOfFedCEOOfAgenciesCEO
{

    private $organizationId;
    private $organizationName;
    private $committeeId;
    private $nameOfCEO;
    private $stateOfOrigin;
    private $displaySerialNo;

    /**
     * ListOfCEOOfAgenciesCEO constructor.
     */
    public function __construct()
    {
    }

    public function updateDetails($employeeName, $state){
        $this->nameOfCEO = $employeeName;
        $this->stateOfOrigin = $state;
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
    public function getCommitteeId()
    {
        return $this->committeeId;
    }

    /**
     * @param mixed $committeeId
     */
    public function setCommitteeId($committeeId)
    {
        $this->committeeId = $committeeId;
    }

    /**
     * @return mixed
     */
    public function getNameOfCEO()
    {
        return $this->nameOfCEO;
    }

    /**
     * @param mixed $nameOfCEO
     */
    public function setNameOfCEO($nameOfCEO)
    {
        $this->nameOfCEO = $nameOfCEO;
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
     * The __toString method allows a class to decide how it will react when it is converted to a string.
     *
     * @return string
     * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
     */
    function __toString()
    {
        return $this->committeeId . "," . $this->organizationName;
    }


}