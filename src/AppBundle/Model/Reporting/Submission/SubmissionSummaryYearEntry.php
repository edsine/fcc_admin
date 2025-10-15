<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 7/12/2017
 * Time: 2:49 PM
 */

namespace AppBundle\Model\Reporting\Submission;


class SubmissionSummaryYearEntry
{

    private $year;
    private $totalQualifiedOrganizations = 0;
    private $totalActualSubmissions = 0;
    private $totalNotSubmitted = 0;
    private $totalFailedSubmissions = 0;
    private $totalPassedSubmissions = 0;
    private $totalProcessedSubmissions = 0;
    private $totalNotProcessed = 0;

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
    public function getTotalQualifiedOrganizations()
    {
        return $this->totalQualifiedOrganizations;
    }

    /**
     * @param mixed $totalQualifiedOrganizations
     */
    public function setTotalQualifiedOrganizations($totalQualifiedOrganizations)
    {
        $this->totalQualifiedOrganizations = $totalQualifiedOrganizations;
    }

    /**
     * @return mixed
     */
    public function getTotalActualSubmissions()
    {
        return $this->totalActualSubmissions;
    }

    /**
     * @param mixed $totalActualSubmissions
     */
    public function setTotalActualSubmissions($totalActualSubmissions)
    {
        $this->totalActualSubmissions = $totalActualSubmissions;
    }

    /**
     * @return mixed
     */
    public function getTotalNotSubmitted()
    {
        return $this->totalNotSubmitted;
    }

    /**
     * @param mixed $totalNotSubmitted
     */
    public function setTotalNotSubmitted($totalNotSubmitted)
    {
        $this->totalNotSubmitted = $totalNotSubmitted;
    }

    /**
     * @return mixed
     */
    public function getTotalFailedSubmissions()
    {
        return $this->totalFailedSubmissions;
    }

    /**
     * @param mixed $totalFailedSubmissions
     */
    public function setTotalFailedSubmissions($totalFailedSubmissions)
    {
        $this->totalFailedSubmissions = $totalFailedSubmissions;
    }

    /**
     * @return mixed
     */
    public function getTotalPassedSubmissions()
    {
        return $this->totalPassedSubmissions;
    }

    /**
     * @param mixed $totalPassedSubmissions
     */
    public function setTotalPassedSubmissions($totalPassedSubmissions)
    {
        $this->totalPassedSubmissions = $totalPassedSubmissions;
    }

    /**
     * @return mixed
     */
    public function getTotalProcessedSubmissions()
    {
        return $this->totalProcessedSubmissions;
    }

    /**
     * @param mixed $totalProcessedSubmissions
     */
    public function setTotalProcessedSubmissions($totalProcessedSubmissions)
    {
        $this->totalProcessedSubmissions = $totalProcessedSubmissions;
    }

    /**
     * @return int
     */
    public function getTotalNotProcessed(): int
    {
        return $this->totalNotProcessed;
    }

    /**
     * @param int $totalNotProcessed
     */
    public function setTotalNotProcessed(int $totalNotProcessed)
    {
        $this->totalNotProcessed = $totalNotProcessed;
    }



}