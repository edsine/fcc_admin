<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/25/2017
 * Time: 7:10 AM
 */

namespace AppBundle\Model\Reporting\FederalLevel;


class ProportionOfPositionOverallYearTotalEntry
{

    private $year;

    private $overallTotalMale_14_to_29 = 0;
    private $overallTotalFemale_14_to_29 = 0;
    private $overallTotalMale_30_to_45 = 0;
    private $overallTotalFemale_30_to_45 = 0;
    private $overallTotalMale_46_And_Above = 0;
    private $overallTotalFemale_46_And_Above = 0;

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
    public function getOverallTotalMale14To29(): int
    {
        return $this->overallTotalMale_14_to_29;
    }

    /**
     * @param int $overallTotalMale_14_to_29
     */
    public function setOverallTotalMale14To29(int $overallTotalMale_14_to_29)
    {
        $this->overallTotalMale_14_to_29 = $overallTotalMale_14_to_29;
    }

    /**
     * @return int
     */
    public function getOverallTotalFemale14To29(): int
    {
        return $this->overallTotalFemale_14_to_29;
    }

    /**
     * @param int $overallTotalFemale_14_to_29
     */
    public function setOverallTotalFemale14To29(int $overallTotalFemale_14_to_29)
    {
        $this->overallTotalFemale_14_to_29 = $overallTotalFemale_14_to_29;
    }

    /**
     * @return int
     */
    public function getOverallTotalMale30To45(): int
    {
        return $this->overallTotalMale_30_to_45;
    }

    /**
     * @param int $overallTotalMale_30_to_45
     */
    public function setOverallTotalMale30To45(int $overallTotalMale_30_to_45)
    {
        $this->overallTotalMale_30_to_45 = $overallTotalMale_30_to_45;
    }

    /**
     * @return int
     */
    public function getOverallTotalFemale30To45(): int
    {
        return $this->overallTotalFemale_30_to_45;
    }

    /**
     * @param int $overallTotalFemale_30_to_45
     */
    public function setOverallTotalFemale30To45(int $overallTotalFemale_30_to_45)
    {
        $this->overallTotalFemale_30_to_45 = $overallTotalFemale_30_to_45;
    }

    /**
     * @return int
     */
    public function getOverallTotalMale46AndAbove(): int
    {
        return $this->overallTotalMale_46_And_Above;
    }

    /**
     * @param int $overallTotalMale_46_And_Above
     */
    public function setOverallTotalMale46AndAbove(int $overallTotalMale_46_And_Above)
    {
        $this->overallTotalMale_46_And_Above = $overallTotalMale_46_And_Above;
    }

    /**
     * @return int
     */
    public function getOverallTotalFemale46AndAbove(): int
    {
        return $this->overallTotalFemale_46_And_Above;
    }

    /**
     * @param int $overallTotalFemale_46_And_Above
     */
    public function setOverallTotalFemale46AndAbove(int $overallTotalFemale_46_And_Above)
    {
        $this->overallTotalFemale_46_And_Above = $overallTotalFemale_46_And_Above;
    }

}