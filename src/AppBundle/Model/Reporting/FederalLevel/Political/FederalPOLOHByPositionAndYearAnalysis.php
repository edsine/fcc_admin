<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/9/2017
 * Time: 3:08 PM
 */

namespace AppBundle\Model\Reporting\FederalLevel\Political;


class FederalPOLOHByPositionAndYearAnalysis
{

    private $positionDescription;
    private $submissionYear;
    private $stage1;
    private $stage2;

    /**
     * FederalPOLOHByPositionAndYearAnalysis constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getPositionDescription()
    {
        return $this->positionDescription;
    }

    /**
     * @param mixed $positionDescription
     */
    public function setPositionDescription($positionDescription)
    {
        $this->positionDescription = $positionDescription;
    }

    /**
     * @return mixed
     */
    public function getSubmissionYear()
    {
        return $this->submissionYear;
    }

    /**
     * @param mixed $submissionYear
     */
    public function setSubmissionYear($submissionYear)
    {
        $this->submissionYear = $submissionYear;
    }

    /**
     * @return mixed
     */
    public function getStage1()
    {
        return $this->stage1;
    }

    /**
     * @param mixed $stage1
     */
    public function setStage1($stage1)
    {
        $this->stage1 = $stage1;
    }

    /**
     * @return mixed
     */
    public function getStage2()
    {
        return $this->stage2;
    }

    /**
     * @param mixed $stage2
     */
    public function setStage2($stage2)
    {
        $this->stage2 = $stage2;
    }


}