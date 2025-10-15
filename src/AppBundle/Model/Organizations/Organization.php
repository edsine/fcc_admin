<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 12:30 PM
 */

namespace AppBundle\Model\Organizations;


class Organization
{
    private $id;
    private $establishmentCode;
    private $establishmentMnemonic;
    private $organizationName;
    private $levelOfGovernment;
    private $establishmentTypeId, $establishmentTypeName;

    private $establishmentCategoryIds;
    private $establishmentCategoryNames;
    private $stateOwnedEstablishmentStateId, $stateOwnedEstablishmentStateCode, $stateOwnedEstablishmentStateName;
    private $stateOfLocationId, $stateOfLocationName;
    private $parentOrganizationId, $parentOrganizationName;
    private $contactAddress, $websiteAddress, $emailAddress, $primaryPhone;
    private $fccCommitteeId,$fccCommitteeName , $fccDeskOfficerId, $fccDeskOfficerName, $fccDeskOfficerPhone, $fccDeskOfficerEmail;
    private $mdaDeskOfficerId, $mdaDeskOfficerName, $mdaDeskOfficerPhone, $mdaDeskOfficerEmail;
    private $status;
    private $lastModified;
    private $lastModifiedByUserId;
    private $guid;


    private $displaySerialNo;

    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getContactAddress()
    {
        return $this->contactAddress;
    }

    /**
     * @param mixed $contactAddress
     */
    public function setContactAddress($contactAddress)
    {
        $this->contactAddress = $contactAddress;
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
    public function getEstablishmentTypeName()
    {
        return $this->establishmentTypeName;
    }

    /**
     * @param mixed $establishmentTypeName
     */
    public function setEstablishmentTypeName($establishmentTypeName)
    {
        $this->establishmentTypeName = $establishmentTypeName;
    }

    /**
     * @return mixed
     */
    public function getEstablishmentCategoryIds()
    {
        return $this->establishmentCategoryIds;
    }

    /**
     * @param mixed $establishmentCategoryIds
     */
    public function setEstablishmentCategoryIds($establishmentCategoryIds)
    {
        $this->establishmentCategoryIds = $establishmentCategoryIds;
    }

    /**
     * @return mixed
     */
    public function getEstablishmentCategoryNames()
    {
        return $this->establishmentCategoryNames;
    }

    /**
     * @param mixed $establishmentCategoryNames
     */
    public function setEstablishmentCategoryNames($establishmentCategoryNames)
    {
        $this->establishmentCategoryNames = $establishmentCategoryNames;
    }

    /**
     * @return mixed
     */
    public function getGuid()
    {
        return $this->guid;
    }

    /**
     * @param mixed $guid
     */
    public function setGuid($guid)
    {
        $this->guid = $guid;
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
    public function getLevelOfGovernment()
    {
        return $this->levelOfGovernment;
    }

    /**
     * @param mixed $levelOfGovernment
     */
    public function setLevelOfGovernment($levelOfGovernment)
    {
        $this->levelOfGovernment = $levelOfGovernment;
    }

    /**
     * @return mixed
     */
    public function getPrimaryPhone()
    {
        return $this->primaryPhone;
    }

    /**
     * @param mixed $primaryPhone
     */
    public function setPrimaryPhone($primaryPhone)
    {
        $this->primaryPhone = $primaryPhone;
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
     * @return mixed
     */
    public function getStateOfLocationId()
    {
        return $this->stateOfLocationId;
    }

    /**
     * @param mixed $stateOfLocationId
     */
    public function setStateOfLocationId($stateOfLocationId)
    {
        $this->stateOfLocationId = $stateOfLocationId;
    }

    /**
     * @return mixed
     */
    public function getStateOfLocationName()
    {
        return $this->stateOfLocationName;
    }

    /**
     * @param mixed $stateOfLocationName
     */
    public function setStateOfLocationName($stateOfLocationName)
    {
        $this->stateOfLocationName = $stateOfLocationName;
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
    public function getWebsiteAddress()
    {
        return $this->websiteAddress;
    }

    /**
     * @param mixed $websiteAddress
     */
    public function setWebsiteAddress($websiteAddress)
    {
        $this->websiteAddress = $websiteAddress;
    }

    /**
     * @return mixed
     */
    public function getFccCommitteeId()
    {
        return $this->fccCommitteeId;
    }

    /**
     * @param mixed $fccCommitteeId
     */
    public function setFccCommitteeId($fccCommitteeId)
    {
        $this->fccCommitteeId = $fccCommitteeId;
    }

    /**
     * @return mixed
     */
    public function getFccCommitteeName()
    {
        return $this->fccCommitteeName;
    }

    /**
     * @param mixed $fccCommitteeName
     */
    public function setFccCommitteeName($fccCommitteeName)
    {
        $this->fccCommitteeName = $fccCommitteeName;
    }

    /**
     * @return mixed
     */
    public function getFccDeskOfficerId()
    {
        return $this->fccDeskOfficerId;
    }

    /**
     * @param mixed $fccDeskOfficerId
     */
    public function setFccDeskOfficerId($fccDeskOfficerId)
    {
        $this->fccDeskOfficerId = $fccDeskOfficerId;
    }

    /**
     * @return mixed
     */
    public function getFccDeskOfficerName()
    {
        return $this->fccDeskOfficerName;
    }

    /**
     * @param mixed $fccDeskOfficerName
     */
    public function setFccDeskOfficerName($fccDeskOfficerName)
    {
        $this->fccDeskOfficerName = $fccDeskOfficerName;
    }

    /**
     * @return mixed
     */
    public function getParentOrganizationId()
    {
        return $this->parentOrganizationId;
    }

    /**
     * @param mixed $parentOrganizationId
     */
    public function setParentOrganizationId($parentOrganizationId)
    {
        $this->parentOrganizationId = $parentOrganizationId;
    }

    /**
     * @return mixed
     */
    public function getParentOrganizationName()
    {
        return $this->parentOrganizationName;
    }

    /**
     * @param mixed $parentOrganizationName
     */
    public function setParentOrganizationName($parentOrganizationName)
    {
        $this->parentOrganizationName = $parentOrganizationName;
    }

    /**
     * @return mixed
     */
    public function getFccDeskOfficerPhone()
    {
        return $this->fccDeskOfficerPhone;
    }

    /**
     * @param mixed $fccDeskOfficerPhone
     */
    public function setFccDeskOfficerPhone($fccDeskOfficerPhone)
    {
        $this->fccDeskOfficerPhone = $fccDeskOfficerPhone;
    }

    /**
     * @return mixed
     */
    public function getFccDeskOfficerEmail()
    {
        return $this->fccDeskOfficerEmail;
    }

    /**
     * @param mixed $fccDeskOfficerEmail
     */
    public function setFccDeskOfficerEmail($fccDeskOfficerEmail)
    {
        $this->fccDeskOfficerEmail = $fccDeskOfficerEmail;
    }

    /**
     * @return mixed
     */
    public function getMdaDeskOfficerId()
    {
        return $this->mdaDeskOfficerId;
    }

    /**
     * @param mixed $mdaDeskOfficerId
     */
    public function setMdaDeskOfficerId($mdaDeskOfficerId)
    {
        $this->mdaDeskOfficerId = $mdaDeskOfficerId;
    }

    /**
     * @return mixed
     */
    public function getMdaDeskOfficerName()
    {
        return $this->mdaDeskOfficerName;
    }

    /**
     * @param mixed $mdaDeskOfficerName
     */
    public function setMdaDeskOfficerName($mdaDeskOfficerName)
    {
        $this->mdaDeskOfficerName = $mdaDeskOfficerName;
    }

    /**
     * @return mixed
     */
    public function getMdaDeskOfficerPhone()
    {
        return $this->mdaDeskOfficerPhone;
    }

    /**
     * @param mixed $mdaDeskOfficerPhone
     */
    public function setMdaDeskOfficerPhone($mdaDeskOfficerPhone)
    {
        $this->mdaDeskOfficerPhone = $mdaDeskOfficerPhone;
    }

    /**
     * @return mixed
     */
    public function getMdaDeskOfficerEmail()
    {
        return $this->mdaDeskOfficerEmail;
    }

    /**
     * @param mixed $mdaDeskOfficerEmail
     */
    public function setMdaDeskOfficerEmail($mdaDeskOfficerEmail)
    {
        $this->mdaDeskOfficerEmail = $mdaDeskOfficerEmail;
    }

}