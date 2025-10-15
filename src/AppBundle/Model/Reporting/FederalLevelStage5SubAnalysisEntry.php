<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:27 PM
 */

namespace AppBundle\Model\Reporting;


class FederalLevelStage5SubAnalysisEntry
{
    private $stateCode;
    private $stateName;
    private $totalGL7to10;
    private $percentageGL7to10;
    private $totalGL12to14;
    private $percentageGL12to14;
    private $totalGL15AndAbove;
    private $percentageGL15AndAbove;


    private $total;
    private $percentage;

    /**
     * CareerDistributionStage1 constructor.
     */
    public function __construct()
    {
    }

    public function calculateTotal(){
        $this->total = $this->totalGL7to10 + $this->totalGL12to14 + $this->totalGL15AndAbove;
    }

    public function calculatePercentage($overallTotal){
        if($overallTotal){
            $this->percentage = number_format(($this->getTotal()/$overallTotal) * 100,1,'.',',');
        }
    }

    public function calculateCategoryPercentages($categoryOverallTotals){
        if($categoryOverallTotals['overall_GL_7_to_10']){
            $this->percentageGL7to10 = number_format(($this->getTotalGL7To10()/$categoryOverallTotals['overall_GL_7_to_10']) * 100,1,'.',',');
        }

        if($categoryOverallTotals['overall_GL_12_to_14']){
            $this->percentageGL12to14 = number_format(($this->getTotalGL12To14()/$categoryOverallTotals['overall_GL_12_to_14']) * 100,1,'.',',');
        }

        if($categoryOverallTotals['overall_GL_15_And_Above']){
            $this->percentageGL15AndAbove = number_format(($this->getTotalGL15AndAbove()/$categoryOverallTotals['overall_GL_15_And_Above']) * 100,1,'.',',');
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
    public function getTotalGL12To14()
    {
        return $this->totalGL12to14;
    }

    /**
     * @param mixed $totalGL12to14
     */
    public function setTotalGL12To14($totalGL12to14)
    {
        $this->totalGL12to14 = $totalGL12to14;
    }

    /**
     * @return mixed
     */
    public function getPercentageGL12To14()
    {
        return $this->percentageGL12to14;
    }

    /**
     * @param mixed $percentageGL12to14
     */
    public function setPercentageGL12To14($percentageGL12to14)
    {
        $this->percentageGL12to14 = $percentageGL12to14;
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