<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:38 PM
 */

namespace AppBundle\Model\Reporting\FederalLevel;


class POLOHDistStage1Analysis
{
    private $stage1Data;

    private $overallMinisters;
    private $overallMinistersOfState;
    private $overallPermSecs;
    private $overallChairmen;
    private $overallMembers;
    private $overallAmbassadors;
    private $overallSpecialAdviserToThePresident;
    private $overallSpecialAssistantToThePresident;
    private $overallSpecialAssistantToTheVP;
    private $overallChiefExecutives;

    private $overallTotal;
    private $overallPercentage;

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
    public function getOverallChairmen()
    {
        return $this->overallChairmen;
    }

    /**
     * @param mixed $overallChairmen
     */
    public function setOverallChairmen($overallChairmen)
    {
        $this->overallChairmen = $overallChairmen;
    }



    /**
     * @return mixed
     */
    public function getOverallMembers()
    {
        return $this->overallMembers;
    }

    /**
     * @param mixed $overallMembers
     */
    public function setOverallMembers($overallMembers)
    {
        $this->overallMembers = $overallMembers;
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
    public function getOverallSpecialAdviserToThePresident()
    {
        return $this->overallSpecialAdviserToThePresident;
    }

    /**
     * @param mixed $overallSpecialAdviserToThePresident
     */
    public function setOverallSpecialAdviserToThePresident($overallSpecialAdviserToThePresident)
    {
        $this->overallSpecialAdviserToThePresident = $overallSpecialAdviserToThePresident;
    }

    /**
     * @return mixed
     */
    public function getOverallSpecialAssistantToThePresident()
    {
        return $this->overallSpecialAssistantToThePresident;
    }

    /**
     * @param mixed $overallSpecialAssistantToThePresident
     */
    public function setOverallSpecialAssistantToThePresident($overallSpecialAssistantToThePresident)
    {
        $this->overallSpecialAssistantToThePresident = $overallSpecialAssistantToThePresident;
    }

    /**
     * @return mixed
     */
    public function getOverallSpecialAssistantToTheVP()
    {
        return $this->overallSpecialAssistantToTheVP;
    }

    /**
     * @param mixed $overallSpecialAssistantToTheVP
     */
    public function setOverallSpecialAssistantToTheVP($overallSpecialAssistantToTheVP)
    {
        $this->overallSpecialAssistantToTheVP = $overallSpecialAssistantToTheVP;
    }

    /**
     * @return mixed
     */
    public function getOverallChiefExecutives()
    {
        return $this->overallChiefExecutives;
    }

    /**
     * @param mixed $overallChiefExecutives
     */
    public function setOverallChiefExecutives($overallChiefExecutives)
    {
        $this->overallChiefExecutives = $overallChiefExecutives;
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