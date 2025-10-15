<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:38 PM
 */

namespace AppBundle\Model\Reporting;


class FederalLevelStage4SubAnalysis
{
    private $subAnalysisData;

    private $zoneId;
    private $zoneCode;
    private $zoneName;

    private $overallGL1to3;
    private $overallPercentageGL1to3;
    private $overallGL4to5;
    private $overallPercentageGL4to5;
    private $overallGL6;
    private $overallPercentageGL6;

    private $overallTotal;
    private $overallPercentage;

    /**
     * @return mixed
     */
    public function getZoneId()
    {
        return $this->zoneId;
    }

    /**
     * @param mixed $zoneId
     */
    public function setZoneId($zoneId)
    {
        $this->zoneId = $zoneId;
    }

    /**
     * @return mixed
     */
    public function getZoneCode()
    {
        return $this->zoneCode;
    }

    /**
     * @param mixed $zoneCode
     */
    public function setZoneCode($zoneCode)
    {
        $this->zoneCode = $zoneCode;
    }

    /**
     * @return mixed
     */
    public function getZoneName()
    {
        return $this->zoneName;
    }

    /**
     * @param mixed $zoneName
     */
    public function setZoneName($zoneName)
    {
        $this->zoneName = $zoneName;
    }

    /**
     * @return mixed
     */
    public function getSubAnalysisData()
    {
        return $this->subAnalysisData;
    }

    /**
     * @param mixed $subAnalysisData
     */
    public function setSubAnalysisData($subAnalysisData)
    {
        $this->subAnalysisData = $subAnalysisData;
    }

    /**
     * @return mixed
     */
    public function getOverallGL1To3()
    {
        return $this->overallGL1to3;
    }

    /**
     * @param mixed $overallGL1to3
     */
    public function setOverallGL1To3($overallGL1to3)
    {
        $this->overallGL1to3 = $overallGL1to3;
    }

    /**
     * @return mixed
     */
    public function getOverallPercentageGL1To3()
    {
        return $this->overallPercentageGL1to3;
    }

    /**
     * @param mixed $overallPercentageGL1to3
     */
    public function setOverallPercentageGL1To3($overallPercentageGL1to3)
    {
        $this->overallPercentageGL1to3 = $overallPercentageGL1to3;
    }

    /**
     * @return mixed
     */
    public function getOverallGL4To5()
    {
        return $this->overallGL4to5;
    }

    /**
     * @param mixed $overallGL4to5
     */
    public function setOverallGL4To5($overallGL4to5)
    {
        $this->overallGL4to5 = $overallGL4to5;
    }

    /**
     * @return mixed
     */
    public function getOverallPercentageGL4To5()
    {
        return $this->overallPercentageGL4to5;
    }

    /**
     * @param mixed $overallPercentageGL4to5
     */
    public function setOverallPercentageGL4To5($overallPercentageGL4to5)
    {
        $this->overallPercentageGL4to5 = $overallPercentageGL4to5;
    }

    /**
     * @return mixed
     */
    public function getOverallGL6()
    {
        return $this->overallGL6;
    }

    /**
     * @param mixed $overallGL6
     */
    public function setOverallGL6($overallGL6)
    {
        $this->overallGL6 = $overallGL6;
    }

    /**
     * @return mixed
     */
    public function getOverallPercentageGL6()
    {
        return $this->overallPercentageGL6;
    }

    /**
     * @param mixed $overallPercentageGL6
     */
    public function setOverallPercentageGL6($overallPercentageGL6)
    {
        $this->overallPercentageGL6 = $overallPercentageGL6;
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

    /* This is the static comparing function: */
    static function cmp_obj($a, $b)
    {
        $a_total = $a->overallTotal;
        $b_total = $b->overallTotal;

        if ($a_total === $b_total) {
            //compare the names
            $a_name = strtolower($a->zoneName);
            $b_name = strtolower($b->zoneName);

            return strcmp($a_name, $b_name);
        }

        return ($a_total > $b_total) ? -1 : +1;
    }

}