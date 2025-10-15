<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/9/2017
 * Time: 3:45 PM
 */

namespace AppBundle\Model\Reporting\FederalLevel\Political;


class FederalPOLOHByPositionAndYearStage2Analysis
{

    private $stage2Data;

    private $overallTotal;
    private $overallPercentage;

    /**
     * FederalPOLOHByPositionAndYearStage1Analysis constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getStage2Data()
    {
        return $this->stage2Data;
    }

    /**
     * @param mixed $stage2Data
     */
    public function setStage2Data($stage2Data)
    {
        $this->stage2Data = $stage2Data;
    }

    /**
     * @return mixed
     */
    public function getOverallTotal()
    {
        return $this->overallTotal;
    }

    /**
     * @param mixed $overallTotal
     */
    public function setOverallTotal($overallTotal)
    {
        $this->overallTotal = $overallTotal;
    }

    /**
     * @return mixed
     */
    public function getOverallPercentage()
    {
        return $this->overallPercentage;
    }

    /**
     * @param mixed $overallPercentage
     */
    public function setOverallPercentage($overallPercentage)
    {
        $this->overallPercentage = $overallPercentage;
    }


}