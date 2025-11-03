<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:27 PM
 */

namespace AppBundle\Model\Reporting\FederalLevel;


class POLOHDistStage3Entry
{
    private $zoneId;
    private $zoneCode;
    private $zoneName;
    private $totalMinisters;
    private $totalMinistersOfState;
    private $totalPermSecs;
    private $totalChairmen;
    private $totalMembers;
    private $totalAmbassadors;
    private $totalSpecialAdviserToPresident;
    private $totalSpecialAssistantsToThePresident;
    private $totalSpecialAssistantsToTheVP;
    private $totalChiefExecutives;

    private $total;
    private $percentage;

    private $geoPoliticalZoneId, $geoPoliticalZoneName;


    /**
     * CareerDistributionStage1 constructor.
     */
    public function __construct()
    {
    }

    public function calculateTotal()
    {
        $this->total = $this->totalMinisters + $this->totalMinistersOfState + $this->totalPermSecs + $this->totalChairmen
            + $this->totalMembers + $this->totalAmbassadors + $this->totalSpecialAdviserToPresident
            + $this->totalSpecialAssistantsToThePresident + $this->totalSpecialAssistantsToTheVP + $this->totalChiefExecutives;
    }

    public function calculatePercentage($overallTotal)
    {
        if ($overallTotal) {
            $this->percentage = number_format(($this->getTotal() / $overallTotal) * 100, 1, '.', ',');
        }
    }

    public function calculateMinistersCategorySubTotal()
    {
        return $this->totalMinisters + $this->totalMinistersOfState;
    }

    public function calculateSpecAdviserCategory()
    {
        return $this->totalSpecialAdviserToPresident;
    }

    public function calculateSpecAssistantsCategory()
    {
        return $this->totalSpecialAssistantsToThePresident + $this->totalSpecialAssistantsToTheVP;
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
    public function getZoneId()
    {
        return $this->zoneId;
    }

    /**
     * @param mixed $zoneId
     */
    public function setZoneId($zoneId)
    {
        $this->zoneId = $zoneId;
    }

    /**
     * @return mixed
     */
    public function getZoneCode()
    {
        return $this->zoneCode;
    }

    /**
     * @param mixed $zoneCode
     */
    public function setZoneCode($zoneCode): void
    {
        $this->zoneCode = $zoneCode;
    }

    /**
     * @return mixed
     */
    public function getZoneName()
    {
        return $this->zoneName;
    }

    /**
     * @param mixed $zoneName
     */
    public function setZoneName($zoneName)
    {
        $this->zoneName = $zoneName;
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
    public function getTotalChiefExecutives()
    {
        return $this->totalChiefExecutives;
    }

    /**
     * @param mixed $totalChiefExecutives
     */
    public function setTotalChiefExecutives($totalChiefExecutives)
    {
        $this->totalChiefExecutives = $totalChiefExecutives;
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
    public function getTotalChairmen()
    {
        return $this->totalChairmen;
    }

    /**
     * @param mixed $totalChairmen
     */
    public function setTotalChairmen($totalChairmen)
    {
        $this->totalChairmen = $totalChairmen;
    }

    /**
     * @return mixed
     */
    public function getTotalMembers()
    {
        return $this->totalMembers;
    }

    /**
     * @param mixed $totalMembers
     */
    public function setTotalMembers($totalMembers)
    {
        $this->totalMembers = $totalMembers;
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
    public function getTotalSpecialAdviserToPresident()
    {
        return $this->totalSpecialAdviserToPresident;
    }

    /**
     * @param mixed $totalSpecialAdviserToPresident
     */
    public function setTotalSpecialAdviserToPresident($totalSpecialAdviserToPresident)
    {
        $this->totalSpecialAdviserToPresident = $totalSpecialAdviserToPresident;
    }

    /**
     * @return mixed
     */
    public function getTotalSpecialAssistantsToThePresident()
    {
        return $this->totalSpecialAssistantsToThePresident;
    }

    /**
     * @param mixed $totalSpecialAssistantsToThePresident
     */
    public function setTotalSpecialAssistantsToThePresident($totalSpecialAssistantsToThePresident)
    {
        $this->totalSpecialAssistantsToThePresident = $totalSpecialAssistantsToThePresident;
    }

    /**
     * @return mixed
     */
    public function getTotalSpecialAssistantsToTheVP()
    {
        return $this->totalSpecialAssistantsToTheVP;
    }

    /**
     * @param mixed $totalSpecialAssistantsToTheVP
     */
    public function setTotalSpecialAssistantsToTheVP($totalSpecialAssistantsToTheVP)
    {
        $this->totalSpecialAssistantsToTheVP = $totalSpecialAssistantsToTheVP;
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

}