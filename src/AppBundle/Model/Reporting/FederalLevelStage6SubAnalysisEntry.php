<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:27 PM
 */

namespace AppBundle\Model\Reporting;


class FederalLevelStage6SubAnalysisEntry
{
    private $stateCode;
    private $stateName;
    private $totalGL7AndAbove;
    private $percentageGL7AndAbove = 0;

    /**
     * CareerDistributionStage1 constructor.
     */
    public function __construct()
    {
    }

    public function calculatePercentage($stage2OverallGL7to15AndAbove){
        if($stage2OverallGL7to15AndAbove){
            $this->percentageGL7AndAbove = number_format(($this->getTotalGL7AndAbove()/$stage2OverallGL7to15AndAbove) * 100,1,'.',',');
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
    public function getTotalGL7AndAbove()
    {
        return $this->totalGL7AndAbove;
    }

    /**
     * @param mixed $totalGL7AndAbove
     */
    public function setTotalGL7AndAbove($totalGL7AndAbove)
    {
        $this->totalGL7AndAbove = $totalGL7AndAbove;
    }

    /**
     * @return mixed
     */
    public function getPercentageGL7AndAbove()
    {
        return $this->percentageGL7AndAbove;
    }

    /**
     * @param mixed $percentageGL7AndAbove
     */
    public function setPercentageGL7AndAbove($percentageGL7AndAbove)
    {
        $this->percentageGL7AndAbove = $percentageGL7AndAbove;
    }

    /* This is the static comparing function: */
    static function cmp_obj($a, $b)
    {
        $a_total = $a->totalGL7AndAbove;
        $b_total = $b->totalGL7AndAbove;

        if ($a_total === $b_total) {
            //compare the names
            $a_name = strtolower($a->stateName);
            $b_name = strtolower($b->stateName);

            return strcmp($a_name, $b_name);
        }

        return ($a_total > $b_total) ? -1 : +1;
    }
}