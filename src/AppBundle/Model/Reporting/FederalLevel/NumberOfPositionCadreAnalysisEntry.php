<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 6/29/2017
 * Time: 12:06 AM
 */

namespace AppBundle\Model\Reporting\FederalLevel;


class NumberOfPositionCadreAnalysisEntry
{

    private $cadreId;
    private $cadreCode;
    private $cadreName;

    /**
     * @var NumberOfPositionYearEntry[]
     */
    private $yearEntries; //[year => yearEntry]

    public function __construct()
    {
    }

    public function updateYearEntry($year, $totalMale, $totalFemale){
        $this->yearEntries[$year]->setTotalMale($this->yearEntries[$year]->getTotalMale() + $totalMale);
        $this->yearEntries[$year]->setTotalFemale($this->yearEntries[$year]->getTotalFemale() + $totalFemale);
    }

    public function fetchYearEntry($year, $gender){
        return ($gender=='M') ? $this->yearEntries[$year]->getTotalMale() : $this->yearEntries[$year]->getTotalFemale();
    }


    /**
     * @return mixed
     */
    public function getCadreId()
    {
        return $this->cadreId;
    }

    /**
     * @param mixed $cadreId
     */
    public function setCadreId($cadreId)
    {
        $this->cadreId = $cadreId;
    }

    /**
     * @return mixed
     */
    public function getCadreCode()
    {
        return $this->cadreCode;
    }

    /**
     * @param mixed $cadreCode
     */
    public function setCadreCode($cadreCode)
    {
        $this->cadreCode = $cadreCode;
    }

    /**
     * @return mixed
     */
    public function getCadreName()
    {
        return $this->cadreName;
    }

    /**
     * @param mixed $cadreName
     */
    public function setCadreName($cadreName)
    {
        $this->cadreName = $cadreName;
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