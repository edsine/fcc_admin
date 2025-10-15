<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/9/2017
 * Time: 3:46 PM
 */

namespace AppBundle\Model\Reporting\FederalLevel\Political;


class FederalPOLOHByPositionAndYearStage1Entry
{

    private $stateId;
    private $stateCode;
    private $stateName;

    private $total = 0;
    private $percentage = 0.0;

    private $geoPoliticalZoneId, $geoPoliticalZoneName;

    /**
     * FederalPOLOHByPositionAndYearStage1Entry constructor.
     */
    public function __construct()
    {
    }

    public function updateTotal($totalValue){
        if($totalValue){
            $this->total += $totalValue;
        }
    }

    public function calculatePercentage($overallTotal)
    {
        if ($overallTotal) {
            $this->percentage = number_format(($this->getTotal() / $overallTotal) * 100, 1, '.', ',');
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
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param mixed $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return mixed
     */
    public function getPercentage()
    {
        return $this->percentage;
    }

    /**
     * @param mixed $percentage
     */
    public function setPercentage($percentage)
    {
        $this->percentage = $percentage;
    }

    /**
     * @return mixed
     */
    public function getGeoPoliticalZoneId()
    {
        return $this->geoPoliticalZoneId;
    }

    /**
     * @param mixed $geoPoliticalZoneId
     */
    public function setGeoPoliticalZoneId($geoPoliticalZoneId)
    {
        $this->geoPoliticalZoneId = $geoPoliticalZoneId;
    }

    /**
     * @return mixed
     */
    public function getGeoPoliticalZoneName()
    {
        return $this->geoPoliticalZoneName;
    }

    /**
     * @param mixed $geoPoliticalZoneName
     */
    public function setGeoPoliticalZoneName($geoPoliticalZoneName)
    {
        $this->geoPoliticalZoneName = $geoPoliticalZoneName;
    }


}