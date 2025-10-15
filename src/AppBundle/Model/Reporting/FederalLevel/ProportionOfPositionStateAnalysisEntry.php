<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 6/29/2017
 * Time: 12:06 AM
 */

namespace AppBundle\Model\Reporting\FederalLevel;


class ProportionOfPositionStateAnalysisEntry
{

    private $stateId;
    private $stateCode;
    private $stateName;

    /**
     * @var ProportionOfPositionYearEntry[]
     */
    private $yearEntries; //[year => yearEntry]

    public function __construct()
    {
    }

    public function updateYearEntry($year,$category, $totalMale, $totalFemale){
        switch ($category){
            case '14_to_29':
                $this->yearEntries[$year]->setTotalMale14To29($totalMale);
                $this->yearEntries[$year]->setTotalFemale14To29($totalFemale);
                break;

            case '30_to_45':
                $this->yearEntries[$year]->setTotalMale30To45($totalMale);
                $this->yearEntries[$year]->setTotalFemale30To45($totalFemale);
                break;

            case '46_And_Above':
                $this->yearEntries[$year]->setTotalMale46AndAbove($totalMale);
                $this->yearEntries[$year]->setTotalFemale46AndAbove($totalFemale);
                break;
        }

    }

    public function fetchYearEntry($year, $category, $gender){
        switch ($category){
            case '14_to_29':
                return ($gender=='M') ? $this->yearEntries[$year]->getTotalMale14To29() : $this->yearEntries[$year]->getTotalFemale14To29();
                break;

            case '30_to_45':
                return ($gender=='M') ? $this->yearEntries[$year]->getTotalMale30To45() : $this->yearEntries[$year]->getTotalFemale30To45();
                break;

            case '46_And_Above':
                return ($gender=='M') ? $this->yearEntries[$year]->getTotalMale46AndAbove() : $this->yearEntries[$year]->getTotalFemale46AndAbove();
                break;

            default:
                return 0;
                break;
        }
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
    public function getYearEntries()
    {
        return $this->yearEntries;
    }

    /**
     * @param mixed $yearEntries
     */
    public function setYearEntries($yearEntries)
    {
        $this->yearEntries = $yearEntries;
    }
}