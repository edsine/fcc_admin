<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/28/2017
 * Time: 8:40 PM
 */

namespace AppBundle\Model\Reporting\FederalLevel\Career;


class CBIndexStage2SubAnalysisEntry
{

    private $stateId;
    private $stateCode;
    private $stateName;

    private $total;
    private $variation;
    private $percentBFactor;
    private $cbIndex;
    private $proportion;
    private $percentage;

    private $geoPoliticalZoneId, $geoPoliticalZoneName;

    /**
     * CBIndexStage2SubAnalysisEntry constructor.
     */
    public function __construct()
    {
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
    public function getVariation()
    {
        return $this->variation;
    }

    /**
     * @param mixed $variation
     */
    public function setVariation($variation)
    {
        $this->variation = $variation;
    }

    /**
     * @return mixed
     */
    public function getPercentBFactor()
    {
        return $this->percentBFactor;
    }

    /**
     * @param mixed $percentBFactor
     */
    public function setPercentBFactor($percentBFactor)
    {
        $this->percentBFactor = $percentBFactor;
    }

    /**
     * @return mixed
     */
    public function getCbIndex()
    {
        return $this->cbIndex;
    }

    /**
     * @param mixed $cbIndex
     */
    public function setCbIndex($cbIndex)
    {
        $this->cbIndex = $cbIndex;
    }

    /**
     * @return mixed
     */
    public function getProportion()
    {
        return $this->proportion;
    }

    /**
     * @param mixed $proportion
     */
    public function setProportion($proportion)
    {
        $this->proportion = $proportion;
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

    /* This is the static comparing function: */
    static function cmp_obj($a, $b)
    {
        $a_total = $a->total;
        $b_total = $b->total;

        if ($a_total === $b_total) {
            //compare the names
            $a_name = strtolower($a->stateName);
            $b_name = strtolower($b->stateName);

            return strcmp($a_name, $b_name);
        }

        return ($a_total > $b_total) ? -1 : +1;
    }

}