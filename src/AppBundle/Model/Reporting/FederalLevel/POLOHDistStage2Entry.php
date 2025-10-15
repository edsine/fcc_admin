<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:27 PM
 */

namespace AppBundle\Model\Reporting\FederalLevel;


class POLOHDistStage2Entry
{
    private $stateCode;
    private $stateName;
    private $totalMinisters;
    private $percentageMinisters;
    private $totalMinistersOfState;
    private $percentageMinistersOfState;
    private $totalMinistersCategory;
    private $percentageMinistersCategory;
    private $totalSpecialAdvisers;
    private $percentageSpecialAdvisers;
    private $totalSpecialAssistants;
    private $percentageSpecialAssistants;
    private $totalSpecialAdvAssistCategory;
    private $percentageSpecialAdvAssistCategory;
    private $totalPermSecs;
    private $percentagePermSecs;
    private $totalAmbassadors;
    private $percentageAmbassadors;


    private $total;
    private $percentage;

    /**
     * CareerDistributionStage1 constructor.
     */
    public function __construct()
    {
    }

    public function calculateTotal(){
        $this->total = $this->totalMinisters + $this->totalMinistersOfState + $this->totalSpecialAssistants + $this->totalSpecialAdvisers + $this->totalPermSecs;
    }

    public function calculatePercentage($overallTotal){
        if($overallTotal){
            $this->percentage = number_format(($this->getTotal()/$overallTotal) * 100,1,'.',',');
        }
    }

    public function calculateCategoryPercentages($categoryOverallTotals){
        if($categoryOverallTotals['overall_ministers']){
            $this->percentageMinisters = number_format(($this->getTotalMinisters()/$categoryOverallTotals['overall_ministers']) * 100,1,'.',',');
        }

        if($categoryOverallTotals['overall_ministers_of_state']){
            $this->percentageMinistersOfState = number_format(($this->getTotalMinistersOfState()/$categoryOverallTotals['overall_ministers_of_state']) * 100,1,'.',',');
        }

        if($categoryOverallTotals['overall_ministers_category']){
            $this->percentageMinistersCategory = number_format(($this->getTotalMinistersCategory()/$categoryOverallTotals['overall_ministers_category']) * 100,1,'.',',');
        }

        if($categoryOverallTotals['overall_special_advisers']){
            $this->percentageSpecialAdvisers = number_format(($this->getTotalSpecialAdvisers()/$categoryOverallTotals['overall_special_advisers']) * 100,1,'.',',');
        }

        if($categoryOverallTotals['overall_special_assistants']){
            $this->percentageSpecialAssistants = number_format(($this->getTotalSpecialAssistants()/$categoryOverallTotals['overall_special_assistants']) * 100,1,'.',',');
        }

        if($categoryOverallTotals['overall_special_adv_assist_category']){
            $this->percentageSpecialAdvAssistCategory = number_format(($this->getTotalSpecialAdvAssistCategory()/$categoryOverallTotals['overall_special_adv_assist_category']) * 100,1,'.',',');
        }

        if($categoryOverallTotals['overall_perm_secs']){
            $this->percentagePermSecs = number_format(($this->getTotalPermSecs()/$categoryOverallTotals['overall_perm_secs']) * 100,1,'.',',');
        }

        if($categoryOverallTotals['overall_ambassadors']){
            $this->percentageAmbassadors = number_format(($this->getTotalAmbassadors()/$categoryOverallTotals['overall_ambassadors']) * 100,1,'.',',');
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
    public function getTotalMinisters()
    {
        return $this->totalMinisters;
    }

    /**
     * @param mixed $totalMinisters
     */
    public function setTotalMinisters($totalMinisters)
    {
        $this->totalMinisters = $totalMinisters;
    }

    /**
     * @return mixed
     */
    public function getPercentageMinisters()
    {
        return $this->percentageMinisters;
    }

    /**
     * @param mixed $percentageMinisters
     */
    public function setPercentageMinisters($percentageMinisters)
    {
        $this->percentageMinisters = $percentageMinisters;
    }

    /**
     * @return mixed
     */
    public function getTotalMinistersOfState()
    {
        return $this->totalMinistersOfState;
    }

    /**
     * @param mixed $totalMinistersOfState
     */
    public function setTotalMinistersOfState($totalMinistersOfState)
    {
        $this->totalMinistersOfState = $totalMinistersOfState;
    }

    /**
     * @return mixed
     */
    public function getPercentageMinistersOfState()
    {
        return $this->percentageMinistersOfState;
    }

    /**
     * @param mixed $percentageMinistersOfState
     */
    public function setPercentageMinistersOfState($percentageMinistersOfState)
    {
        $this->percentageMinistersOfState = $percentageMinistersOfState;
    }

    /**
     * @return mixed
     */
    public function getTotalMinistersCategory()
    {
        return $this->totalMinistersCategory;
    }

    /**
     * @param mixed $totalMinistersCategory
     */
    public function setTotalMinistersCategory($totalMinistersCategory)
    {
        $this->totalMinistersCategory = $totalMinistersCategory;
    }

    /**
     * @return mixed
     */
    public function getPercentageMinistersCategory()
    {
        return $this->percentageMinistersCategory;
    }

    /**
     * @param mixed $percentageMinistersCategory
     */
    public function setPercentageMinistersCategory($percentageMinistersCategory)
    {
        $this->percentageMinistersCategory = $percentageMinistersCategory;
    }

    /**
     * @return mixed
     */
    public function getTotalSpecialAssistants()
    {
        return $this->totalSpecialAssistants;
    }

    /**
     * @param mixed $totalSpecialAssistants
     */
    public function setTotalSpecialAssistants($totalSpecialAssistants)
    {
        $this->totalSpecialAssistants = $totalSpecialAssistants;
    }

    /**
     * @return mixed
     */
    public function getPercentageSpecialAssistants()
    {
        return $this->percentageSpecialAssistants;
    }

    /**
     * @param mixed $percentageSpecialAssistants
     */
    public function setPercentageSpecialAssistants($percentageSpecialAssistants)
    {
        $this->percentageSpecialAssistants = $percentageSpecialAssistants;
    }

    /**
     * @return mixed
     */
    public function getTotalSpecialAdvisers()
    {
        return $this->totalSpecialAdvisers;
    }

    /**
     * @param mixed $totalSpecialAdvisers
     */
    public function setTotalSpecialAdvisers($totalSpecialAdvisers)
    {
        $this->totalSpecialAdvisers = $totalSpecialAdvisers;
    }

    /**
     * @return mixed
     */
    public function getPercentageSpecialAdvisers()
    {
        return $this->percentageSpecialAdvisers;
    }

    /**
     * @param mixed $percentageSpecialAdvisers
     */
    public function setPercentageSpecialAdvisers($percentageSpecialAdvisers)
    {
        $this->percentageSpecialAdvisers = $percentageSpecialAdvisers;
    }

    /**
     * @return mixed
     */
    public function getTotalSpecialAdvAssistCategory()
    {
        return $this->totalSpecialAdvAssistCategory;
    }

    /**
     * @param mixed $totalSpecialAdvAssistCategory
     */
    public function setTotalSpecialAdvAssistCategory($totalSpecialAdvAssistCategory)
    {
        $this->totalSpecialAdvAssistCategory = $totalSpecialAdvAssistCategory;
    }

    /**
     * @return mixed
     */
    public function getPercentageSpecialAdvAssistCategory()
    {
        return $this->percentageSpecialAdvAssistCategory;
    }

    /**
     * @param mixed $percentageSpecialAdvAssistCategory
     */
    public function setPercentageSpecialAdvAssistCategory($percentageSpecialAdvAssistCategory)
    {
        $this->percentageSpecialAdvAssistCategory = $percentageSpecialAdvAssistCategory;
    }

    /**
     * @return mixed
     */
    public function getTotalPermSecs()
    {
        return $this->totalPermSecs;
    }

    /**
     * @param mixed $totalPermSecs
     */
    public function setTotalPermSecs($totalPermSecs)
    {
        $this->totalPermSecs = $totalPermSecs;
    }

    /**
     * @return mixed
     */
    public function getPercentagePermSecs()
    {
        return $this->percentagePermSecs;
    }

    /**
     * @param mixed $percentagePermSecs
     */
    public function setPercentagePermSecs($percentagePermSecs)
    {
        $this->percentagePermSecs = $percentagePermSecs;
    }

    /**
     * @return mixed
     */
    public function getTotalAmbassadors()
    {
        return $this->totalAmbassadors;
    }

    /**
     * @param mixed $totalAmbassadors
     */
    public function setTotalAmbassadors($totalAmbassadors)
    {
        $this->totalAmbassadors = $totalAmbassadors;
    }

    /**
     * @return mixed
     */
    public function getPercentageAmbassadors()
    {
        return $this->percentageAmbassadors;
    }

    /**
     * @param mixed $percentageAmbassadors
     */
    public function setPercentageAmbassadors($percentageAmbassadors)
    {
        $this->percentageAmbassadors = $percentageAmbassadors;
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