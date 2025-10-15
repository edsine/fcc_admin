<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/9/2017
 * Time: 3:45 PM
 */

namespace AppBundle\Model\Reporting\FederalLevel\Political;


class FederalPOLOHByPositionAndYearStage1Analysis
{

    private $stage1Data;

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
    public function getStage1Data()
    {
        return $this->stage1Data;
    }

    /**
     * @param mixed $stage1Data
     */
    public function setStage1Data($stage1Data)
    {
        $this->stage1Data = $stage1Data;
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