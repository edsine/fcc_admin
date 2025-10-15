<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/25/2017
 * Time: 7:01 AM
 */

namespace AppBundle\Model\Reporting\Shared;


class MDAComparativeAnalysisEntry
{

    private $organizationId;
    private $organizationCode;
    private $organizationName;

    /**
     * @var MDAYearEntry[]
     */
    private $yearEntries; //[year => yearEntry]

    public function __construct()
    {
    }

    public function updateYearEntryTotal($year, $total){
        $this->yearEntries[$year]->setTotal($total);
    }

    public function fetchYearEntryTotal($year){
        return $this->yearEntries[$year]->getTotal();
    }

    public function updateYearEntryPercentage($year,$yearOverallTotal){
        $this->yearEntries[$year]->calculatePercentage($yearOverallTotal);
    }

    public function fetchYearEntryPercentage($year){
        return $this->yearEntries[$year]->getPercentage();
    }

    public function formatValues(){
        foreach ($this->yearEntries as $yearEntry){
            $yearEntry->setPercentage(number_format($yearEntry->getPercentage(),1));
        }
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
    public function getOrganizationCode()
    {
        return $this->organizationCode;
    }

    /**
     * @param mixed $organizationCode
     */
    public function setOrganizationCode($organizationCode)
    {
        $this->organizationCode = $organizationCode;
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
    public function getYearEntries()
    {
        return $this->yearEntries;
    }

    /**
     * @param mixed $yearEntries
     */
    public function setYearEntries($yearEntries)
    {
        $this->yearEntries = $yearEntries;
    }


}