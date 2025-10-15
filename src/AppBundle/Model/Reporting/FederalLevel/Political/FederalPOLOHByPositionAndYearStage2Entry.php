<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/9/2017
 * Time: 3:46 PM
 */

namespace AppBundle\Model\Reporting\FederalLevel\Political;


class FederalPOLOHByPositionAndYearStage2Entry
{

    private $geoPoliticalZoneId;
    private $geoPoliticalZoneCode;
    private $geoPoliticalZoneName;

    private $total = 0;
    private $percentage = 0;

    /**
     * FederalPOLOHByPositionAndYearStage2Entry constructor.
     */
    public function __construct()
    {
    }

    public function calculatePercentage($overallTotal)
    {
        if ($overallTotal) {
            $this->percentage = number_format(($this->getTotal() / $overallTotal) * 100, 1, '.', ',');
        }
    }

    public function addTotal($stateTotal)
    {
        if ($stateTotal) {
            $this->total += $stateTotal;
        }
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
    public function getGeoPoliticalZoneCode()
    {
        return $this->geoPoliticalZoneCode;
    }

    /**
     * @param mixed $geoPoliticalZoneCode
     */
    public function setGeoPoliticalZoneCode($geoPoliticalZoneCode)
    {
        $this->geoPoliticalZoneCode = $geoPoliticalZoneCode;
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