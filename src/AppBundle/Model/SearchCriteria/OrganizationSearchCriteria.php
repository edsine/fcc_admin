<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 2:04 PM
 */

namespace AppBundle\Model\SearchCriteria;


class OrganizationSearchCriteria
{
    private $organizationName;
    private $establishmentCode;
    private $establishmentMnemonic;
    private $establishmentType = 'UNDEFINED';
    private $stateOwnedEstablishmentState;
    private $stateOfLocation;
    private $fccCommittee;
    private $status;

    private $fccDeskOfficer;

    public function __construct(string $establishmentType)
    {
        $this->establishmentType = $establishmentType;
    }

    /**
     * @return mixed
     */
    public function getEstablishmentCode()
    {
        return $this->establishmentCode;
    }

    /**
     * @param mixed $establishmentCode
     */
    public function setEstablishmentCode($establishmentCode)
    {
        $this->establishmentCode = $establishmentCode;
    }

    /**
     * @return mixed
     */
    public function getEstablishmentMnemonic()
    {
        return $this->establishmentMnemonic;
    }

    /**
     * @param mixed $establishmentMnemonic
     */
    public function setEstablishmentMnemonic($establishmentMnemonic)
    {
        $this->establishmentMnemonic = $establishmentMnemonic;
    }

    /**
     * @return string|string
     */
    public function getEstablishmentType()
    {
        return $this->establishmentType;
    }

    /**
     * @param string|string $establishmentType
     */
    public function setEstablishmentType($establishmentType)
    {
        $this->establishmentType = $establishmentType;
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
    public function getStateOwnedEstablishmentState()
    {
        return $this->stateOwnedEstablishmentState;
    }

    /**
     * @param mixed $stateOwnedEstablishmentState
     */
    public function setStateOwnedEstablishmentState($stateOwnedEstablishmentState)
    {
        $this->stateOwnedEstablishmentState = $stateOwnedEstablishmentState;
    }

    /**
     * @return mixed
     */
    public function getFccCommittee()
    {
        return $this->fccCommittee;
    }

    /**
     * @param mixed $fccCommittee
     */
    public function setFccCommittee($fccCommittee)
    {
        $this->fccCommittee = $fccCommittee;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getFccDeskOfficer()
    {
        return $this->fccDeskOfficer;
    }

    /**
     * @param mixed $fccDeskOfficer
     */
    public function setFccDeskOfficer($fccDeskOfficer)
    {
        $this->fccDeskOfficer = $fccDeskOfficer;
    }

}