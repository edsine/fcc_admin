<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 6/28/2017
 * Time: 11:34 PM
 */

namespace AppBundle\Model\Reporting\FederalLevel;


class ProportionOfPositionYearEntry
{

    private $year;
    private $totalMale_14_to_29 = 0;
    private $totalFemale_14_to_29 = 0;
    private $totalMale_30_to_45 = 0;
    private $totalFemale_30_to_45 = 0;
    private $totalMale_46_And_Above = 0;
    private $totalFemale_46_And_Above = 0;

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
    public function getTotalMale14To29(): int
    {
        return $this->totalMale_14_to_29;
    }

    /**
     * @param int $totalMale_14_to_29
     */
    public function setTotalMale14To29(int $totalMale_14_to_29)
    {
        $this->totalMale_14_to_29 = $totalMale_14_to_29;
    }

    /**
     * @return int
     */
    public function getTotalFemale14To29(): int
    {
        return $this->totalFemale_14_to_29;
    }

    /**
     * @param int $totalFemale_14_to_29
     */
    public function setTotalFemale14To29(int $totalFemale_14_to_29)
    {
        $this->totalFemale_14_to_29 = $totalFemale_14_to_29;
    }

    /**
     * @return int
     */
    public function getTotalMale30To45(): int
    {
        return $this->totalMale_30_to_45;
    }

    /**
     * @param int $totalMale_30_to_45
     */
    public function setTotalMale30To45(int $totalMale_30_to_45)
    {
        $this->totalMale_30_to_45 = $totalMale_30_to_45;
    }

    /**
     * @return int
     */
    public function getTotalFemale30To45(): int
    {
        return $this->totalFemale_30_to_45;
    }

    /**
     * @param int $totalFemale_30_to_45
     */
    public function setTotalFemale30To45(int $totalFemale_30_to_45)
    {
        $this->totalFemale_30_to_45 = $totalFemale_30_to_45;
    }

    /**
     * @return int
     */
    public function getTotalMale46AndAbove(): int
    {
        return $this->totalMale_46_And_Above;
    }

    /**
     * @param int $totalMale_46_And_Above
     */
    public function setTotalMale46AndAbove(int $totalMale_46_And_Above)
    {
        $this->totalMale_46_And_Above = $totalMale_46_And_Above;
    }

    /**
     * @return int
     */
    public function getTotalFemale46AndAbove(): int
    {
        return $this->totalFemale_46_And_Above;
    }

    /**
     * @param int $totalFemale_46_And_Above
     */
    public function setTotalFemale46AndAbove(int $totalFemale_46_And_Above)
    {
        $this->totalFemale_46_And_Above = $totalFemale_46_And_Above;
    }

}