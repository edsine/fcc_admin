<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:27 PM
 */

namespace AppBundle\Model\Reporting;


class FederalLevelStage4SubAnalysisEntry
{
    private $stateCode;
    private $stateName;
    private $totalGL1to3;
    private $percentageGL1to3;
    private $totalGL4to5;
    private $percentageGL4to5;
    private $totalGL6;
    private $percentageGL6;


    private $total;
    private $percentage;

    /**
     * CareerDistributionStage1 constructor.
     */
    public function __construct()
    {
    }

    public function calculateTotal(){
        $this->total = $this->totalGL1to3 + $this->totalGL4to5 + $this->totalGL6;
    }

    public function calculatePercentage($overallTotal){
        if($overallTotal){
            $this->percentage = number_format(($this->getTotal()/$overallTotal) * 100,1,'.',',');
        }
    }

    public function calculateCategoryPercentages($categoryOverallTotals){
        if($categoryOverallTotals['overall_GL_1_to_3']){
            $this->percentageGL1to3 = number_format(($this->getTotalGL1To3()/$categoryOverallTotals['overall_GL_1_to_3']) * 100,1,'.',',');
        }

        if($categoryOverallTotals['overall_GL_4_to_5']){
            $this->percentageGL4to5 = number_format(($this->getTotalGL4To5()/$categoryOverallTotals['overall_GL_4_to_5']) * 100,1,'.',',');
        }

        if($categoryOverallTotals['overall_GL_6']){
            $this->percentageGL6 = number_format(($this->getTotalGL6()/$categoryOverallTotals['overall_GL_6']) * 100,1,'.',',');
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
    public function getTotalGL1To3()
    {
        return $this->totalGL1to3;
    }

    /**
     * @param mixed $totalGL1to3
     */
    public function setTotalGL1To3($totalGL1to3)
    {
        $this->totalGL1to3 = $totalGL1to3;
    }

    /**
     * @return mixed
     */
    public function getPercentageGL1To3()
    {
        return $this->percentageGL1to3;
    }

    /**
     * @param mixed $percentageGL1to3
     */
    public function setPercentageGL1To3($percentageGL1to3)
    {
        $this->percentageGL1to3 = $percentageGL1to3;
    }

    /**
     * @return mixed
     */
    public function getTotalGL4To5()
    {
        return $this->totalGL4to5;
    }

    /**
     * @param mixed $totalGL4to5
     */
    public function setTotalGL4To5($totalGL4to5)
    {
        $this->totalGL4to5 = $totalGL4to5;
    }

    /**
     * @return mixed
     */
    public function getPercentageGL4To5()
    {
        return $this->percentageGL4to5;
    }

    /**
     * @param mixed $percentageGL4to5
     */
    public function setPercentageGL4To5($percentageGL4to5)
    {
        $this->percentageGL4to5 = $percentageGL4to5;
    }

    /**
     * @return mixed
     */
    public function getTotalGL6()
    {
        return $this->totalGL6;
    }

    /**
     * @param mixed $totalGL6
     */
    public function setTotalGL6($totalGL6)
    {
        $this->totalGL6 = $totalGL6;
    }

    /**
     * @return mixed
     */
    public function getPercentageGL6()
    {
        return $this->percentageGL6;
    }

    /**
     * @param mixed $percentageGL6
     */
    public function setPercentageGL6($percentageGL6)
    {
        $this->percentageGL6 = $percentageGL6;
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