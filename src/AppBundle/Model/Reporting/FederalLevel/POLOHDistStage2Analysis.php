<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:38 PM
 */

namespace AppBundle\Model\Reporting\FederalLevel;


class POLOHDistStage2Analysis
{
    private $stage2Data;

    private $overallMinisters;
    private $overallPercentageMinisters;
    private $overallMinistersOfState;
    private $overallPercentageMinistersOfState;
    private $overallMinistersCategory;
    private $overallPercentageMinistersCategory;
    private $overallSpecialAdvisers;
    private $overallPercentageSpecialAdvisers;
    private $overallSpecialAssistants;
    private $overallPercentageSpecialAssistants;
    private $overallSpecialAdvAssistCategory;
    private $overallPercentageSpecialAdvAssistCategory;
    private $overallPermSecs;
    private $overallPercentagePermSecs;
    private $overallAmbassadors;
    private $overallPercentageAmbassadors;

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
    public function getOverallMinisters()
    {
        return $this->overallMinisters;
    }

    /**
     * @param mixed $overallMinisters
     */
    public function setOverallMinisters($overallMinisters)
    {
        $this->overallMinisters = $overallMinisters;
    }

    /**
     * @return mixed
     */
    public function getOverallPercentageMinisters()
    {
        return $this->overallPercentageMinisters;
    }

    /**
     * @param mixed $overallPercentageMinisters
     */
    public function setOverallPercentageMinisters($overallPercentageMinisters)
    {
        $this->overallPercentageMinisters = $overallPercentageMinisters;
    }

    /**
     * @return mixed
     */
    public function getOverallMinistersOfState()
    {
        return $this->overallMinistersOfState;
    }

    /**
     * @param mixed $overallMinistersOfState
     */
    public function setOverallMinistersOfState($overallMinistersOfState)
    {
        $this->overallMinistersOfState = $overallMinistersOfState;
    }

    /**
     * @return mixed
     */
    public function getOverallPercentageMinistersOfState()
    {
        return $this->overallPercentageMinistersOfState;
    }

    /**
     * @param mixed $overallPercentageMinistersOfState
     */
    public function setOverallPercentageMinistersOfState($overallPercentageMinistersOfState)
    {
        $this->overallPercentageMinistersOfState = $overallPercentageMinistersOfState;
    }

    /**
     * @return mixed
     */
    public function getOverallMinistersCategory()
    {
        return $this->overallMinistersCategory;
    }

    /**
     * @param mixed $overallMinistersCategory
     */
    public function setOverallMinistersCategory($overallMinistersCategory)
    {
        $this->overallMinistersCategory = $overallMinistersCategory;
    }

    /**
     * @return mixed
     */
    public function getOverallPercentageMinistersCategory()
    {
        return $this->overallPercentageMinistersCategory;
    }

    /**
     * @param mixed $overallPercentageMinistersCategory
     */
    public function setOverallPercentageMinistersCategory($overallPercentageMinistersCategory)
    {
        $this->overallPercentageMinistersCategory = $overallPercentageMinistersCategory;
    }

    /**
     * @return mixed
     */
    public function getOverallSpecialAdvisers()
    {
        return $this->overallSpecialAdvisers;
    }

    /**
     * @param mixed $overallSpecialAdvisers
     */
    public function setOverallSpecialAdvisers($overallSpecialAdvisers)
    {
        $this->overallSpecialAdvisers = $overallSpecialAdvisers;
    }

    /**
     * @return mixed
     */
    public function getOverallPercentageSpecialAdvisers()
    {
        return $this->overallPercentageSpecialAdvisers;
    }

    /**
     * @param mixed $overallPercentageSpecialAdvisers
     */
    public function setOverallPercentageSpecialAdvisers($overallPercentageSpecialAdvisers)
    {
        $this->overallPercentageSpecialAdvisers = $overallPercentageSpecialAdvisers;
    }

    /**
     * @return mixed
     */
    public function getOverallSpecialAssistants()
    {
        return $this->overallSpecialAssistants;
    }

    /**
     * @param mixed $overallSpecialAssistants
     */
    public function setOverallSpecialAssistants($overallSpecialAssistants)
    {
        $this->overallSpecialAssistants = $overallSpecialAssistants;
    }

    /**
     * @return mixed
     */
    public function getOverallPercentageSpecialAssistants()
    {
        return $this->overallPercentageSpecialAssistants;
    }

    /**
     * @param mixed $overallPercentageSpecialAssistants
     */
    public function setOverallPercentageSpecialAssistants($overallPercentageSpecialAssistants)
    {
        $this->overallPercentageSpecialAssistants = $overallPercentageSpecialAssistants;
    }

    /**
     * @return mixed
     */
    public function getOverallSpecialAdvAssistCategory()
    {
        return $this->overallSpecialAdvAssistCategory;
    }

    /**
     * @param mixed $overallSpecialAdvAssistCategory
     */
    public function setOverallSpecialAdvAssistCategory($overallSpecialAdvAssistCategory)
    {
        $this->overallSpecialAdvAssistCategory = $overallSpecialAdvAssistCategory;
    }

    /**
     * @return mixed
     */
    public function getOverallPercentageSpecialAdvAssistCategory()
    {
        return $this->overallPercentageSpecialAdvAssistCategory;
    }

    /**
     * @param mixed $overallPercentageSpecialAdvAssistCategory
     */
    public function setOverallPercentageSpecialAdvAssistCategory($overallPercentageSpecialAdvAssistCategory)
    {
        $this->overallPercentageSpecialAdvAssistCategory = $overallPercentageSpecialAdvAssistCategory;
    }

    /**
     * @return mixed
     */
    public function getOverallPermSecs()
    {
        return $this->overallPermSecs;
    }

    /**
     * @param mixed $overallPermSecs
     */
    public function setOverallPermSecs($overallPermSecs)
    {
        $this->overallPermSecs = $overallPermSecs;
    }

    /**
     * @return mixed
     */
    public function getOverallPercentagePermSecs()
    {
        return $this->overallPercentagePermSecs;
    }

    /**
     * @param mixed $overallPercentagePermSecs
     */
    public function setOverallPercentagePermSecs($overallPercentagePermSecs)
    {
        $this->overallPercentagePermSecs = $overallPercentagePermSecs;
    }

    /**
     * @return mixed
     */
    public function getOverallAmbassadors()
    {
        return $this->overallAmbassadors;
    }

    /**
     * @param mixed $overallAmbassadors
     */
    public function setOverallAmbassadors($overallAmbassadors)
    {
        $this->overallAmbassadors = $overallAmbassadors;
    }

    /**
     * @return mixed
     */
    public function getOverallPercentageAmbassadors()
    {
        return $this->overallPercentageAmbassadors;
    }

    /**
     * @param mixed $overallPercentageAmbassadors
     */
    public function setOverallPercentageAmbassadors($overallPercentageAmbassadors)
    {
        $this->overallPercentageAmbassadors = $overallPercentageAmbassadors;
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