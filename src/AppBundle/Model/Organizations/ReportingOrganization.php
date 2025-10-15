<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 1:01 PM
 */

namespace AppBundle\Model\Organizations;


class ReportingOrganization
{
    private $id;
    private $code;
    private $mnemonic;
    private $description;
    private $establishmentTypeId;
    private $establishmentTypeDesc;
    private $stateOwnedEstablishmentStateId;
    private $stateOwnedEstablishmentStateCode;
    private $stateOwnedEstablishmentStateName;
    private $status;
    private $lastModified;
    private $lastModifiedById;

    /**
     * ReportingOrganization constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getMnemonic()
    {
        return $this->mnemonic;
    }

    /**
     * @param mixed $mnemonic
     */
    public function setMnemonic($mnemonic)
    {
        $this->mnemonic = $mnemonic;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getEstablishmentTypeId()
    {
        return $this->establishmentTypeId;
    }

    /**
     * @param mixed $establishmentTypeId
     */
    public function setEstablishmentTypeId($establishmentTypeId)
    {
        $this->establishmentTypeId = $establishmentTypeId;
    }

    /**
     * @return mixed
     */
    public function getEstablishmentTypeDesc()
    {
        return $this->establishmentTypeDesc;
    }

    /**
     * @param mixed $establishmentTypeDesc
     */
    public function setEstablishmentTypeDesc($establishmentTypeDesc)
    {
        $this->establishmentTypeDesc = $establishmentTypeDesc;
    }

    /**
     * @return mixed
     */
    public function getStateOwnedEstablishmentStateId()
    {
        return $this->stateOwnedEstablishmentStateId;
    }

    /**
     * @param mixed $stateOwnedEstablishmentStateId
     */
    public function setStateOwnedEstablishmentStateId($stateOwnedEstablishmentStateId)
    {
        $this->stateOwnedEstablishmentStateId = $stateOwnedEstablishmentStateId;
    }

    /**
     * @return mixed
     */
    public function getStateOwnedEstablishmentStateCode()
    {
        return $this->stateOwnedEstablishmentStateCode;
    }

    /**
     * @param mixed $stateOwnedEstablishmentStateCode
     */
    public function setStateOwnedEstablishmentStateCode($stateOwnedEstablishmentStateCode)
    {
        $this->stateOwnedEstablishmentStateCode = $stateOwnedEstablishmentStateCode;
    }

    /**
     * @return mixed
     */
    public function getStateOwnedEstablishmentStateName()
    {
        return $this->stateOwnedEstablishmentStateName;
    }

    /**
     * @param mixed $stateOwnedEstablishmentStateName
     */
    public function setStateOwnedEstablishmentStateName($stateOwnedEstablishmentStateName)
    {
        $this->stateOwnedEstablishmentStateName = $stateOwnedEstablishmentStateName;
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
    public function getLastModifiedById()
    {
        return $this->lastModifiedById;
    }

    /**
     * @param mixed $lastModifiedById
     */
    public function setLastModifiedById($lastModifiedById)
    {
        $this->lastModifiedById = $lastModifiedById;
    }




}