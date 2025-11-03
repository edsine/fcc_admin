<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/28/2017
 * Time: 8:39 PM
 */

namespace AppBundle\Model\Reporting\FederalLevel\Career;


class CBIndexStage1Analysis
{

    private $stage1Data;

    private $overallTotal;
    private $overallVariation;
    private $overallPercentBFactor;
    private $overallCBIndex;
    private $overallProportion;
    private $overallPercentage;

    private $xAxisLabels;
    private $chartSeries;

    /**
     * CBIndexStage1Analysis constructor.
     */
    public function __construct()
    {
    }

    public function formatValues()
    {
        $this->overallTotal = number_format($this->overallTotal);
        $this->overallVariation = number_format($this->overallVariation);
        $this->overallPercentBFactor = number_format($this->overallPercentBFactor);
        $this->overallCBIndex = number_format($this->overallCBIndex);
        $this->overallProportion = number_format($this->overallProportion);
        $this->overallPercentage = number_format($this->overallPercentage);
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

    /**
     * @return mixed
     */
    public function getXAxisLabels()
    {
        return $this->xAxisLabels;
    }

    /**
     * @param mixed $xAxisLabels
     */
    public function setXAxisLabels($xAxisLabels)
    {
        $this->xAxisLabels = $xAxisLabels;
    }

    /**
     * @return mixed
     */
    public function getChartSeries()
    {
        return $this->chartSeries;
    }

    /**
     * @param mixed $chartSeries
     */
    public function setChartSeries($chartSeries)
    {
        $this->chartSeries = $chartSeries;
    }

}