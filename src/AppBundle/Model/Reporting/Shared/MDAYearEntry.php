<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/25/2017
 * Time: 7:06 AM
 */

namespace AppBundle\Model\Reporting\Shared;


class MDAYearEntry
{

    private $year;
    private $total = 0;
    private $percentage = 0;

    public function __construct()
    {
    }

    public function calculatePercentage($overallTotal)
    {
        if ($overallTotal) {
            //$this->percentage = number_format(($this->getTotal() / $overallTotal) * 100, 1, '.', ',');
            $this->percentage = ($this->getTotal() / $overallTotal) * 100;
        }
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