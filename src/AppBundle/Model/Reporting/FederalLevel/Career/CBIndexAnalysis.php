<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/28/2017
 * Time: 8:37 PM
 */

namespace AppBundle\Model\Reporting\FederalLevel\Career;


class CBIndexAnalysis
{

    private $organizationName;
    private $submissionYear;

    private $distributionCategory;

    private $numberToBeRecruited; //same as rv
    private $numberRequiredToBalance; //same as total variations of stage 1
    private $totalNumberAfterRecruitment; // rv + stage1 overall total
    private $percentBalanced; // (rv/numberReqToBalance) * 100

    //report stages
    private $stage1;
    private $stage2;

    /**
     * CBIndexAnalysis constructor.
     */
    public function __construct()
    {
    }

    public function formatValues(){
        $this->numberToBeRecruited = number_format($this->numberToBeRecruited);
        $this->numberRequiredToBalance = number_format($this->numberRequiredToBalance);
        $this->totalNumberAfterRecruitment = number_format($this->totalNumberAfterRecruitment);
        $this->percentBalanced = number_format($this->percentBalanced, 1, '.', ',');
    }

    /**
     * @return mixed
     */
    public function getOrganizationName()
    {
        return $this->organizationName;
    }

    /**
     * @param mixed $organizationName
     */
    public function setOrganizationName($organizationName)
    {
        $this->organizationName = $organizationName;
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
    public function getDistributionCategory()
    {
        return $this->distributionCategory;
    }

    /**
     * @param mixed $distributionCategory
     */
    public function setDistributionCategory($distributionCategory)
    {
        $this->distributionCategory = $distributionCategory;
    }

    /**
     * @return mixed
     */
    public function getNumberToBeRecruited()
    {
        return $this->numberToBeRecruited;
    }

    /**
     * @param mixed $numberToBeRecruited
     */
    public function setNumberToBeRecruited($numberToBeRecruited)
    {
        $this->numberToBeRecruited = $numberToBeRecruited;
    }

    /**
     * @return mixed
     */
    public function getNumberRequiredToBalance()
    {
        return $this->numberRequiredToBalance;
    }

    /**
     * @param mixed $numberRequiredToBalance
     */
    public function setNumberRequiredToBalance($numberRequiredToBalance)
    {
        $this->numberRequiredToBalance = $numberRequiredToBalance;
    }

    /**
     * @return mixed
     */
    public function getTotalNumberAfterRecruitment()
    {
        return $this->totalNumberAfterRecruitment;
    }

    /**
     * @param mixed $totalNumberAfterRecruitment
     */
    public function setTotalNumberAfterRecruitment($totalNumberAfterRecruitment)
    {
        $this->totalNumberAfterRecruitment = $totalNumberAfterRecruitment;
    }

    /**
     * @return mixed
     */
    public function getPercentBalanced()
    {
        return $this->percentBalanced;
    }

    /**
     * @param mixed $percentBalanced
     */
    public function setPercentBalanced($percentBalanced)
    {
        $this->percentBalanced = $percentBalanced;
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