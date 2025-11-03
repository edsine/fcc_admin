<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 6/28/2017
 * Time: 11:34 PM
 */

namespace AppBundle\Model\Reporting\FederalLevel;


class NumberOfPositionYearEntry
{

    private $year;
    private $totalMale = 0;
    private $totalFemale = 0;

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
    public function getTotalMale(): int
    {
        return $this->totalMale;
    }

    /**
     * @param int $totalMale
     */
    public function setTotalMale(int $totalMale)
    {
        $this->totalMale = $totalMale;
    }

    /**
     * @return int
     */
    public function getTotalFemale(): int
    {
        return $this->totalFemale;
    }

    /**
     * @param int $totalFemale
     */
    public function setTotalFemale(int $totalFemale)
    {
        $this->totalFemale = $totalFemale;
    }

}