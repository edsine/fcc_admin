<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/25/2017
 * Time: 7:01 AM
 */

namespace AppBundle\Model\Reporting\FederalLevel;


class ProportionOfPositionDataAnalysis
{

    private $startYear, $endYear;
    private $stateEntries;
    private $totalOrganizationsConsidered;

    /**
     * @var ProportionOfPositionOverallYearTotalEntry
     */
    private $overallYearTotals; //[year => yearEntriesTotal]

    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getStartYear()
    {
        return $this->startYear;
    }

    /**
     * @param mixed $startYear
     */
    public function setStartYear($startYear)
    {
        $this->startYear = $startYear;
    }

    /**
     * @return mixed
     */
    public function getEndYear()
    {
        return $this->endYear;
    }

    /**
     * @param mixed $endYear
     */
    public function setEndYear($endYear)
    {
        $this->endYear = $endYear;
    }

    /**
     * @return mixed
     */
    public function getStateEntries()
    {
        return $this->stateEntries;
    }

    /**
     * @param mixed $stateEntries
     */
    public function setStateEntries($stateEntries)
    {
        $this->stateEntries = $stateEntries;
    }

    /**
     * @return mixed
     */
    public function getOverallYearTotals()
    {
        return $this->overallYearTotals;
    }

    /**
     * @param mixed $overallYearTotals
     */
    public function setOverallYearTotals($overallYearTotals)
    {
        $this->overallYearTotals = $overallYearTotals;
    }

    /**
     * @return mixed
     */
    public function getTotalOrganizationsConsidered()
    {
        return $this->totalOrganizationsConsidered;
    }

    /**
     * @param mixed $totalOrganizationsConsidered
     */
    public function setTotalOrganizationsConsidered($totalOrganizationsConsidered)
    {
        $this->totalOrganizationsConsidered = $totalOrganizationsConsidered;
    }


}