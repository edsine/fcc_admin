<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 7/12/2017
 * Time: 2:48 PM
 */

namespace AppBundle\Model\Reporting\Submission;


class SubmissionSummaryAnalysis
{

    private $startYear, $endYear;
    private $yearEntries;

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