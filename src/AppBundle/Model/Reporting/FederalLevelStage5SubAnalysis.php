<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:38 PM
 */

namespace AppBundle\Model\Reporting;


class FederalLevelStage5SubAnalysis
{
    private $subAnalysisData;

    private $zoneId;
    private $zoneCode;
    private $zoneName;

    private $overallGL7to10;
    private $overallPercentageGL7to10;
    private $overallGL12to14;
    private $overallPercentageGL12to14;
    private $overallGL15AndAbove;
    private $overallPercentageGL15AndAbove;

    private $overallTotal;
    private $overallPercentage;

    //var $comparisonTotal;

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
    public function getOverallGL7To10()
    {
        return $this->overallGL7to10;
    }

    /**
     * @param mixed $overallGL7to10
     */
    public function setOverallGL7To10($overallGL7to10)
    {
        $this->overallGL7to10 = $overallGL7to10;
    }

    /**
     * @return mixed
     */
    public function getOverallPercentageGL7To10()
    {
        return $this->overallPercentageGL7to10;
    }

    /**
     * @param mixed $overallPercentageGL7to10
     */
    public function setOverallPercentageGL7To10($overallPercentageGL7to10)
    {
        $this->overallPercentageGL7to10 = $overallPercentageGL7to10;
    }

    /**
     * @return mixed
     */
    public function getOverallGL12To14()
    {
        return $this->overallGL12to14;
    }

    /**
     * @param mixed $overallGL12to14
     */
    public function setOverallGL12To14($overallGL12to14)
    {
        $this->overallGL12to14 = $overallGL12to14;
    }

    /**
     * @return mixed
     */
    public function getOverallPercentageGL12To14()
    {
        return $this->overallPercentageGL12to14;
    }

    /**
     * @param mixed $overallPercentageGL12to14
     */
    public function setOverallPercentageGL12To14($overallPercentageGL12to14)
    {
        $this->overallPercentageGL12to14 = $overallPercentageGL12to14;
    }

    /**
     * @return mixed
     */
    public function getOverallGL15AndAbove()
    {
        return $this->overallGL15AndAbove;
    }

    /**
     * @param mixed $overallGL15AndAbove
     */
    public function setOverallGL15AndAbove($overallGL15AndAbove)
    {
        $this->overallGL15AndAbove = $overallGL15AndAbove;
    }

    /**
     * @return mixed
     */
    public function getOverallPercentageGL15AndAbove()
    {
        return $this->overallPercentageGL15AndAbove;
    }

    /**
     * @param mixed $overallPercentageGL15AndAbove
     */
    public function setOverallPercentageGL15AndAbove($overallPercentageGL15AndAbove)
    {
        $this->overallPercentageGL15AndAbove = $overallPercentageGL15AndAbove;
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
        //$this->comparisonTotal = $this->overallTotal;
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