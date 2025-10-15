<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/25/2017
 * Time: 7:10 AM
 */

namespace AppBundle\Model\Reporting\FederalLevel;


class NumberOfPositionOverallYearTotalEntry
{

    private $year;

    private $overallTotalMale = 0;
    private $overallTotalFemale = 0;

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
    public function getOverallTotalMale(): int
    {
        return $this->overallTotalMale;
    }

    /**
     * @param int $overallTotalMale
     */
    public function setOverallTotalMale(int $overallTotalMale)
    {
        $this->overallTotalMale = $overallTotalMale;
    }

    /**
     * @return int
     */
    public function getOverallTotalFemale(): int
    {
        return $this->overallTotalFemale;
    }

    /**
     * @param int $overallTotalFemale
     */
    public function setOverallTotalFemale(int $overallTotalFemale)
    {
        $this->overallTotalFemale = $overallTotalFemale;
    }

}