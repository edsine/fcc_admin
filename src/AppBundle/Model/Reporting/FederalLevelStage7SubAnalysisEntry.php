<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:27 PM
 */

namespace AppBundle\Model\Reporting;


class FederalLevelStage7SubAnalysisEntry
{
    private $stateCode;
    private $stateName;
    private $totalGL15AndAbove;
    private $percentageGL15AndAbove = 0;

    /**
     * CareerDistributionStage1 constructor.
     */
    public function __construct()
    {
    }

    public function calculatePercentage($stage2OverallGL15AndAbove){
        if($stage2OverallGL15AndAbove){
            $this->percentageGL15AndAbove = number_format(($this->getTotalGL15AndAbove()/$stage2OverallGL15AndAbove) * 100,1,'.',',');
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

    /* This is the static comparing function: */
    static function cmp_obj($a, $b)
    {
        $a_total = $a->totalGL15AndAbove;
        $b_total = $b->totalGL15AndAbove;

        if ($a_total === $b_total) {
            //compare the names
            $a_name = strtolower($a->stateName);
            $b_name = strtolower($b->stateName);

            return strcmp($a_name, $b_name);
        }

        return ($a_total > $b_total) ? -1 : +1;
    }
}