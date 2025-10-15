<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:27 PM
 */

namespace AppBundle\Model\Reporting;


class FederalLevelStage2Entry
{
    private $stateCode;
    private $stateName;
    private $totalGL1to4;
    private $percentageGL1to4;
    private $totalGL5to6;
    private $percentageGL5to6;
    private $totalGL1to6;
    private $percentageGL1to6;
    private $totalGL7to10;
    private $percentageGL7to10;
    private $totalGL11to14;
    private $percentageGL11to14;
    private $totalGL15AndAbove;
    private $percentageGL15AndAbove;
    private $totalGL7to15AndAbove;
    private $percentageGL7to15AndAbove;


    private $total;
    private $percentage;

    /**
     * CareerDistributionStage1 constructor.
     */
    public function __construct()
    {
    }

    public function calculateTotal(){
        $this->total = $this->totalGL1to4 + $this->totalGL5to6 + $this->totalGL7to10 + $this->totalGL11to14 + $this->totalGL15AndAbove;
    }

    public function calculatePercentage($overallTotal){
        if($overallTotal){
            $this->percentage = number_format(($this->getTotal()/$overallTotal) * 100,1,'.',',');
        }
    }

    public function calculateCategoryPercentages($categoryOverallTotals){
        if($categoryOverallTotals['overall_GL_1_to_4']){
            $this->percentageGL1to4 = number_format(($this->getTotalGL1To4()/$categoryOverallTotals['overall_GL_1_to_4']) * 100,1,'.',',');
        }


        if($categoryOverallTotals['overall_GL_5_to_6']){
            $this->percentageGL5to6 = number_format(($this->getTotalGL5To6()/$categoryOverallTotals['overall_GL_5_to_6']) * 100,1,'.',',');
        }


        if($categoryOverallTotals['overall_GL_1_to_6']){
            $this->percentageGL1to6 = number_format(($this->getTotalGL1To6()/$categoryOverallTotals['overall_GL_1_to_6']) * 100,1,'.',',');
        }


        if($categoryOverallTotals['overall_GL_7_to_10']){
            $this->percentageGL7to10 = number_format(($this->getTotalGL7To10()/$categoryOverallTotals['overall_GL_7_to_10']) * 100,1,'.',',');
        }


        if($categoryOverallTotals['overall_GL_11_to_14']){
            $this->percentageGL11to14 = number_format(($this->getTotalGL11To14()/$categoryOverallTotals['overall_GL_11_to_14']) * 100,1,'.',',');
        }


        if($categoryOverallTotals['overall_GL_15_And_Above']){
            $this->percentageGL15AndAbove = number_format(($this->getTotalGL15AndAbove()/$categoryOverallTotals['overall_GL_15_And_Above']) * 100,1,'.',',');
        }


        if($categoryOverallTotals['overall_GL_7_to_15_And_Above']){
            $this->percentageGL7to15AndAbove = number_format(($this->getTotalGL7To15AndAbove()/$categoryOverallTotals['overall_GL_7_to_15_And_Above']) * 100,1,'.',',');
        }

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
    public function getTotalGL1To4()
    {
        return $this->totalGL1to4;
    }

    /**
     * @param mixed $totalGL1to4
     */
    public function setTotalGL1To4($totalGL1to4)
    {
        $this->totalGL1to4 = $totalGL1to4;
    }

    /**
     * @return mixed
     */
    public function getPercentageGL1To4()
    {
        return $this->percentageGL1to4;
    }

    /**
     * @param mixed $percentageGL1to4
     */
    public function setPercentageGL1To4($percentageGL1to4)
    {
        $this->percentageGL1to4 = $percentageGL1to4;
    }

    /**
     * @return mixed
     */
    public function getTotalGL5To6()
    {
        return $this->totalGL5to6;
    }

    /**
     * @param mixed $totalGL5to6
     */
    public function setTotalGL5To6($totalGL5to6)
    {
        $this->totalGL5to6 = $totalGL5to6;
    }

    /**
     * @return mixed
     */
    public function getPercentageGL5To6()
    {
        return $this->percentageGL5to6;
    }

    /**
     * @param mixed $percentageGL5to6
     */
    public function setPercentageGL5To6($percentageGL5to6)
    {
        $this->percentageGL5to6 = $percentageGL5to6;
    }

    /**
     * @return mixed
     */
    public function getTotalGL1To6()
    {
        return $this->totalGL1to6;
    }

    /**
     * @param mixed $totalGL1to6
     */
    public function setTotalGL1To6($totalGL1to6)
    {
        $this->totalGL1to6 = $totalGL1to6;
    }

    /**
     * @return mixed
     */
    public function getPercentageGL1To6()
    {
        return $this->percentageGL1to6;
    }

    /**
     * @param mixed $percentageGL1to6
     */
    public function setPercentageGL1To6($percentageGL1to6)
    {
        $this->percentageGL1to6 = $percentageGL1to6;
    }

    /**
     * @return mixed
     */
    public function getTotalGL7To10()
    {
        return $this->totalGL7to10;
    }

    /**
     * @param mixed $totalGL7to10
     */
    public function setTotalGL7To10($totalGL7to10)
    {
        $this->totalGL7to10 = $totalGL7to10;
    }

    /**
     * @return mixed
     */
    public function getPercentageGL7To10()
    {
        return $this->percentageGL7to10;
    }

    /**
     * @param mixed $percentageGL7to10
     */
    public function setPercentageGL7To10($percentageGL7to10)
    {
        $this->percentageGL7to10 = $percentageGL7to10;
    }

    /**
     * @return mixed
     */
    public function getTotalGL11To14()
    {
        return $this->totalGL11to14;
    }

    /**
     * @param mixed $totalGL11to14
     */
    public function setTotalGL11To14($totalGL11to14)
    {
        $this->totalGL11to14 = $totalGL11to14;
    }

    /**
     * @return mixed
     */
    public function getPercentageGL11To14()
    {
        return $this->percentageGL11to14;
    }

    /**
     * @param mixed $percentageGL11to14
     */
    public function setPercentageGL11To14($percentageGL11to14)
    {
        $this->percentageGL11to14 = $percentageGL11to14;
    }

    /**
     * @return mixed
     */
    public function getTotalGL15AndAbove()
    {
        return $this->totalGL15AndAbove;
    }

    /**
     * @param mixed $totalGL15AndAbove
     */
    public function setTotalGL15AndAbove($totalGL15AndAbove)
    {
        $this->totalGL15AndAbove = $totalGL15AndAbove;
    }

    /**
     * @return mixed
     */
    public function getPercentageGL15AndAbove()
    {
        return $this->percentageGL15AndAbove;
    }

    /**
     * @param mixed $percentageGL15AndAbove
     */
    public function setPercentageGL15AndAbove($percentageGL15AndAbove)
    {
        $this->percentageGL15AndAbove = $percentageGL15AndAbove;
    }

    /**
     * @return mixed
     */
    public function getTotalGL7To15AndAbove()
    {
        return $this->totalGL7to15AndAbove;
    }

    /**
     * @param mixed $totalGL7to15AndAbove
     */
    public function setTotalGL7To15AndAbove($totalGL7to15AndAbove)
    {
        $this->totalGL7to15AndAbove = $totalGL7to15AndAbove;
    }

    /**
     * @return mixed
     */
    public function getPercentageGL7To15AndAbove()
    {
        return $this->percentageGL7to15AndAbove;
    }

    /**
     * @param mixed $percentageGL7to15AndAbove
     */
    public function setPercentageGL7To15AndAbove($percentageGL7to15AndAbove)
    {
        $this->percentageGL7to15AndAbove = $percentageGL7to15AndAbove;
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

}