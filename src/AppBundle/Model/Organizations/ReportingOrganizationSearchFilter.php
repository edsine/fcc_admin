<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 2:04 PM
 */

namespace AppBundle\Model\Organizations;


class ReportingOrganizationSearchFilter
{
    private $searchDescription;
    private $searchCode;
    private $searchEstablishmentType = "NONE";

    /**
     * ReportingOrganizationSearchFilter constructor.
     * @param string $searchEstablishmentType
     */
    public function __construct($searchEstablishmentType)
    {
        $this->searchEstablishmentType = $searchEstablishmentType;
    }

    /**
     * @return mixed
     */
    public function getSearchDescription()
    {
        return $this->searchDescription;
    }

    /**
     * @param mixed $searchDescription
     */
    public function setSearchDescription($searchDescription)
    {
        $this->searchDescription = $searchDescription;
    }

    /**
     * @return mixed
     */
    public function getSearchCode()
    {
        return $this->searchCode;
    }

    /**
     * @param mixed $searchCode
     */
    public function setSearchCode($searchCode)
    {
        $this->searchCode = $searchCode;
    }

    /**
     * @return string
     */
    public function getSearchEstablishmentType()
    {
        return $this->searchEstablishmentType;
    }

    /**
     * @param string $searchEstablishmentType
     */
    public function setSearchEstablishmentType($searchEstablishmentType)
    {
        $this->searchEstablishmentType = $searchEstablishmentType;
    }


}