<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:38 PM
 */

namespace AppBundle\Model\Reporting;


class FederalLevelStage8SubAnalysis
{
    private $subAnalysisData;

    private $zoneId;
    private $zoneCode;
    private $zoneName;

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