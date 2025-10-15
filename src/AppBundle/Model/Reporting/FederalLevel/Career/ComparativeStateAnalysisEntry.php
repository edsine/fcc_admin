<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/25/2017
 * Time: 7:01 AM
 */

namespace AppBundle\Model\Reporting\FederalLevel\Career;


class ComparativeStateAnalysisEntry
{

    private $stateId;
    private $stateCode;
    private $stateName;

    private $yearEntries; //[year => yearEntry]

    /**
     * ComparativeStateAnalysisEntry constructor.
     */
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
    public function getStateId()
    {
        return $this->stateId;
    }

    /**
     * @param mixed $stateId
     */
    public function setStateId($stateId)
    {
        $this->stateId = $stateId;
    }

    /**
     * @return mixed
     */
    public function getStateCode()
    {
        return $this->stateCode;
    }

    /**
     * @param mixed $stateCode
     */
    public function setStateCode($stateCode)
    {
        $this->stateCode = $stateCode;
    }

    /**
     * @return mixed
     */
    public function getStateName()
    {
        return $this->stateName;
    }

    /**
     * @param mixed $stateName
     */
    public function setStateName($stateName)
    {
        $this->stateName = $stateName;
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