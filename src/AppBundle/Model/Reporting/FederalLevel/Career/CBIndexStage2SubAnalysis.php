<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/28/2017
 * Time: 8:40 PM
 */

namespace AppBundle\Model\Reporting\FederalLevel\Career;


class CBIndexStage2SubAnalysis
{

    private $subAnalysisData;

    private $zoneId;
    private $zoneCode;
    private $zoneName;

    private $overallTotal;
    private $overallVariation;
    private $overallPercentBFactor;
    private $overallCBIndex;
    private $overallProportion;
    private $overallPercentage;

    /**
     * CBIndexStage2SubAnalysis constructor.
     */
    public function __construct()
    {
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
    public function getOverallVariation()
    {
        return $this->overallVariation;
    }

    /**
     * @param mixed $overallVariation
     */
    public function setOverallVariation($overallVariation)
    {
        $this->overallVariation = $overallVariation;
    }

    /**
     * @return mixed
     */
    public function getOverallPercentBFactor()
    {
        return $this->overallPercentBFactor;
    }

    /**
     * @param mixed $overallPercentBFactor
     */
    public function setOverallPercentBFactor($overallPercentBFactor)
    {
        $this->overallPercentBFactor = $overallPercentBFactor;
    }

    /**
     * @return mixed
     */
    public function getOverallCBIndex()
    {
        return $this->overallCBIndex;
    }

    /**
     * @param mixed $overallCBIndex
     */
    public function setOverallCBIndex($overallCBIndex)
    {
        $this->overallCBIndex = $overallCBIndex;
    }

    /**
     * @return mixed
     */
    public function getOverallProportion()
    {
        return $this->overallProportion;
    }

    /**
     * @param mixed $overallProportion
     */
    public function setOverallProportion($overallProportion)
    {
        $this->overallProportion = $overallProportion;
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