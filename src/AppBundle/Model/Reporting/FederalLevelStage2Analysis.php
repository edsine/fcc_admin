<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:38 PM
 */

namespace AppBundle\Model\Reporting;


class FederalLevelStage2Analysis
{
    private $stage2Data;

    private $overallGL1to4;
    private $overallPercentageGL1to4;
    private $overallGL5to6;
    private $overallPercentageGL5to6;
    private $overallGL1to6;
    private $overallPercentageGL1to6;
    private $overallGL7to10;
    private $overallPercentageGL7to10;
    private $overallGL11to14;
    private $overallPercentageGL11to14;
    private $overallGL15AndAbove;
    private $overallPercentageGL15AndAbove;
    private $overallGL7to15AndAbove;
    private $overallPercentageGL7to15AndAbove;

    private $overallTotal;
    private $overallPercentage;

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
    public function getOverallGL1To4()
    {
        return $this->overallGL1to4;
    }

    /**
     * @param mixed $overallGL1to4
     */
    public function setOverallGL1To4($overallGL1to4)
    {
        $this->overallGL1to4 = $overallGL1to4;
    }

    /**
     * @return mixed
     */
    public function getOverallPercentageGL1To4()
    {
        return $this->overallPercentageGL1to4;
    }

    /**
     * @param mixed $overallPercentageGL1to4
     */
    public function setOverallPercentageGL1To4($overallPercentageGL1to4)
    {
        $this->overallPercentageGL1to4 = $overallPercentageGL1to4;
    }

    /**
     * @return mixed
     */
    public function getOverallGL5To6()
    {
        return $this->overallGL5to6;
    }

    /**
     * @param mixed $overallGL5to6
     */
    public function setOverallGL5To6($overallGL5to6)
    {
        $this->overallGL5to6 = $overallGL5to6;
    }

    /**
     * @return mixed
     */
    public function getOverallPercentageGL5To6()
    {
        return $this->overallPercentageGL5to6;
    }

    /**
     * @param mixed $overallPercentageGL5to6
     */
    public function setOverallPercentageGL5To6($overallPercentageGL5to6)
    {
        $this->overallPercentageGL5to6 = $overallPercentageGL5to6;
    }

    /**
     * @return mixed
     */
    public function getOverallGL1To6()
    {
        return $this->overallGL1to6;
    }

    /**
     * @param mixed $overallGL1to6
     */
    public function setOverallGL1To6($overallGL1to6)
    {
        $this->overallGL1to6 = $overallGL1to6;
    }

    /**
     * @return mixed
     */
    public function getOverallPercentageGL1To6()
    {
        return $this->overallPercentageGL1to6;
    }

    /**
     * @param mixed $overallPercentageGL1to6
     */
    public function setOverallPercentageGL1To6($overallPercentageGL1to6)
    {
        $this->overallPercentageGL1to6 = $overallPercentageGL1to6;
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
    public function getOverallGL11To14()
    {
        return $this->overallGL11to14;
    }

    /**
     * @param mixed $overallGL11to14
     */
    public function setOverallGL11To14($overallGL11to14)
    {
        $this->overallGL11to14 = $overallGL11to14;
    }

    /**
     * @return mixed
     */
    public function getOverallPercentageGL11To14()
    {
        return $this->overallPercentageGL11to14;
    }

    /**
     * @param mixed $overallPercentageGL11to14
     */
    public function setOverallPercentageGL11To14($overallPercentageGL11to14)
    {
        $this->overallPercentageGL11to14 = $overallPercentageGL11to14;
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
    public function getOverallGL7To15AndAbove()
    {
        return $this->overallGL7to15AndAbove;
    }

    /**
     * @param mixed $overallGL7to15AndAbove
     */
    public function setOverallGL7To15AndAbove($overallGL7to15AndAbove)
    {
        $this->overallGL7to15AndAbove = $overallGL7to15AndAbove;
    }

    /**
     * @return mixed
     */
    public function getOverallPercentageGL7To15AndAbove()
    {
        return $this->overallPercentageGL7to15AndAbove;
    }

    /**
     * @param mixed $overallPercentageGL7to15AndAbove
     */
    public function setOverallPercentageGL7To15AndAbove($overallPercentageGL7to15AndAbove)
    {
        $this->overallPercentageGL7to15AndAbove = $overallPercentageGL7to15AndAbove;
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