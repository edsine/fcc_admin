<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/28/2017
 * Time: 8:39 PM
 */

namespace AppBundle\Model\Reporting\FederalLevel\Career;


class CBIndexStage1Entry
{

    private $stateId;
    private $stateCode;
    private $stateName;
    private $totalGL1;
    private $totalGL2;
    private $totalGL3;
    private $totalGL4;
    private $totalGL5;
    private $totalGL6;
    private $totalGL7;
    private $totalGL8;
    private $totalGL9;
    private $totalGL10;
    private $totalGL11;
    private $totalGL12;
    private $totalGL13;
    private $totalGL14;
    private $totalGL15;
    private $totalGL16;
    private $totalGL17;
    private $totalConsolidated;

    private $total = 0;
    private $variation = 0;
    private $percentBFactor = 0.0;
    private $cbIndex = 0;
    private $proportion = 0;
    private $percentage = 0.0;

    private $geoPoliticalZoneId, $geoPoliticalZoneName;


    /**
     * CareerDistributionStage1 constructor.
     */
    public function __construct()
    {
    }

    public function calculateTotal($whichCategory)
    {
        switch ($whichCategory){
            case 'gl1_and_Above':
                $this->total = $this->totalGL1 + $this->totalGL2 + $this->totalGL3 + $this->totalGL4 + $this->totalGL5 + $this->totalGL6 + $this->totalGL7
                    + $this->totalGL8 + $this->totalGL9 + $this->totalGL10 + $this->totalGL11 + $this->totalGL12 + $this->totalGL13 + $this->totalGL14
                    + $this->totalGL15 + $this->totalGL16 + $this->totalGL17 + $this->totalConsolidated;
                break;

            case 'gl1_to_6':
                $this->total = $this->totalGL1 + $this->totalGL2 + $this->totalGL3 + $this->totalGL4
                    + $this->totalGL5 + $this->totalGL6 + $this->totalGL7 + $this->totalConsolidated;
                break;

            case 'gl_7_and_above':
                $this->total = $this->totalGL7 + $this->totalGL8 + $this->totalGL9 + $this->totalGL10
                    + $this->totalGL11 + $this->totalGL12 + $this->totalGL13 + $this->totalGL14
                    + $this->totalGL15 + $this->totalGL16 + $this->totalGL17 + $this->totalConsolidated;
                break;

            case 'gl_15_and_above':
                $this->total = $this->totalGL15 + $this->totalGL16 + $this->totalGL17 + $this->totalConsolidated;
                break;
        }
    }

    public function calculateVariation($highestCategoryValue){
        $this->variation = $highestCategoryValue - $this->total;
    }

    public function calculatePercentBFactor($overallVariation){
        if ($overallVariation) {
            $this->percentBFactor = ($this->variation / $overallVariation) * 100 ;
        }
    }

    public function calculateCBIndex($recruitmentValue){
        $this->cbIndex = ($this->percentBFactor / 100) * $recruitmentValue;
    }

    public function calculateProportion(){
        $this->proportion = $this->total + $this->cbIndex;
    }

    public function calculatePercentage($totalNoAfterRecruitment)
    {
        if ($totalNoAfterRecruitment) {
            $this->percentage = ($this->proportion / $totalNoAfterRecruitment) * 100;
        }
    }

    public function formatValues(){
        /*$this->percentBFactor = number_format($this->percentBFactor);
        $this->cbIndex = number_format($this->cbIndex);
        $this->proportion = number_format($this->proportion);
        $this->percentage = number_format($this->percentage);*/

        $this->percentBFactor = number_format($this->percentBFactor,1);
        $this->cbIndex = round($this->cbIndex);
        $this->proportion = round($this->proportion);
        $this->percentage = number_format($this->percentage,1);
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
    public function getTotalGL1()
    {
        return $this->totalGL1;
    }

    /**
     * @param mixed $totalGL1
     */
    public function setTotalGL1($totalGL1)
    {
        $this->totalGL1 = $totalGL1;
    }

    /**
     * @return mixed
     */
    public function getTotalGL10()
    {
        return $this->totalGL10;
    }

    /**
     * @param mixed $totalGL10
     */
    public function setTotalGL10($totalGL10)
    {
        $this->totalGL10 = $totalGL10;
    }

    /**
     * @return mixed
     */
    public function getTotalGL11()
    {
        return $this->totalGL11;
    }

    /**
     * @param mixed $totalGL11
     */
    public function setTotalGL11($totalGL11)
    {
        $this->totalGL11 = $totalGL11;
    }

    /**
     * @return mixed
     */
    public function getTotalGL12()
    {
        return $this->totalGL12;
    }

    /**
     * @param mixed $totalGL12
     */
    public function setTotalGL12($totalGL12)
    {
        $this->totalGL12 = $totalGL12;
    }

    /**
     * @return mixed
     */
    public function getTotalGL13()
    {
        return $this->totalGL13;
    }

    /**
     * @param mixed $totalGL13
     */
    public function setTotalGL13($totalGL13)
    {
        $this->totalGL13 = $totalGL13;
    }

    /**
     * @return mixed
     */
    public function getTotalGL14()
    {
        return $this->totalGL14;
    }

    /**
     * @param mixed $totalGL14
     */
    public function setTotalGL14($totalGL14)
    {
        $this->totalGL14 = $totalGL14;
    }

    /**
     * @return mixed
     */
    public function getTotalGL15()
    {
        return $this->totalGL15;
    }

    /**
     * @param mixed $totalGL15
     */
    public function setTotalGL15($totalGL15)
    {
        $this->totalGL15 = $totalGL15;
    }

    /**
     * @return mixed
     */
    public function getTotalGL16()
    {
        return $this->totalGL16;
    }

    /**
     * @param mixed $totalGL16
     */
    public function setTotalGL16($totalGL16)
    {
        $this->totalGL16 = $totalGL16;
    }

    /**
     * @return mixed
     */
    public function getTotalGL17()
    {
        return $this->totalGL17;
    }

    /**
     * @param mixed $totalGL17
     */
    public function setTotalGL17($totalGL17)
    {
        $this->totalGL17 = $totalGL17;
    }

    /**
     * @return mixed
     */
    public function getTotalConsolidated()
    {
        return $this->totalConsolidated;
    }

    /**
     * @param mixed $totalConsolidated
     */
    public function setTotalConsolidated($totalConsolidated)
    {
        $this->totalConsolidated = $totalConsolidated;
    }

    /**
     * @return mixed
     */
    public function getTotalGL2()
    {
        return $this->totalGL2;
    }

    /**
     * @param mixed $totalGL2
     */
    public function setTotalGL2($totalGL2)
    {
        $this->totalGL2 = $totalGL2;
    }

    /**
     * @return mixed
     */
    public function getTotalGL3()
    {
        return $this->totalGL3;
    }

    /**
     * @param mixed $totalGL3
     */
    public function setTotalGL3($totalGL3)
    {
        $this->totalGL3 = $totalGL3;
    }

    /**
     * @return mixed
     */
    public function getTotalGL4()
    {
        return $this->totalGL4;
    }

    /**
     * @param mixed $totalGL4
     */
    public function setTotalGL4($totalGL4)
    {
        $this->totalGL4 = $totalGL4;
    }

    /**
     * @return mixed
     */
    public function getTotalGL5()
    {
        return $this->totalGL5;
    }

    /**
     * @param mixed $totalGL5
     */
    public function setTotalGL5($totalGL5)
    {
        $this->totalGL5 = $totalGL5;
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
    public function getTotalGL7()
    {
        return $this->totalGL7;
    }

    /**
     * @param mixed $totalGL7
     */
    public function setTotalGL7($totalGL7)
    {
        $this->totalGL7 = $totalGL7;
    }

    /**
     * @return mixed
     */
    public function getTotalGL8()
    {
        return $this->totalGL8;
    }

    /**
     * @param mixed $totalGL8
     */
    public function setTotalGL8($totalGL8)
    {
        $this->totalGL8 = $totalGL8;
    }

    /**
     * @return mixed
     */
    public function getTotalGL9()
    {
        return $this->totalGL9;
    }

    /**
     * @param mixed $totalGL9
     */
    public function setTotalGL9($totalGL9)
    {
        $this->totalGL9 = $totalGL9;
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