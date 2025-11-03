<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/25/2017
 * Time: 7:10 AM
 */

namespace AppBundle\Model\Reporting\Shared;


class MDAComparativeOverallYearTotalEntry
{

    private $year;
    private $overallTotal = 0;
    private $overallPercentage = 0;

    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param mixed $year
     */
    public function setYear($year)
    {
        $this->year = $year;
    }

    /**
     * @return int
     */
    public function getOverallTotal(): int
    {
        return $this->overallTotal;
    }

    /**
     * @param int $overallTotal
     */
    public function setOverallTotal(int $overallTotal)
    {
        $this->overallTotal = $overallTotal;
    }

    /**
     * @return int
     */
    public function getOverallPercentage(): int
    {
        return $this->overallPercentage;
    }

    /**
     * @param int $overallPercentage
     */
    public function setOverallPercentage(int $overallPercentage)
    {
        $this->overallPercentage = $overallPercentage;
    }


}