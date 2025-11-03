<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:36 PM
 */

namespace AppBundle\Model\Reporting\FederalLevel;


class POLOHDistributionAnalysis
{

    private $organizationName;
    private $submissionYear;

    //for consolidated report only
    private $totalEstablishments;

    //report stages
    private $stage1;
    private $stage2;
    private $stage3;
    private $stage4;
    private $stage5;
    private $stage6;
    private $stage7;
    private $stage8;




    /**
     * CareerDistributionReport constructor.
     */
    public function __construct()
    {
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
    public function getTotalEstablishments()
    {
        return $this->totalEstablishments;
    }

    /**
     * @param mixed $totalEstablishments
     */
    public function setTotalEstablishments($totalEstablishments)
    {
        $this->totalEstablishments = $totalEstablishments;
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

    /**
     * @return mixed
     */
    public function getStage3()
    {
        return $this->stage3;
    }

    /**
     * @param mixed $stage3
     */
    public function setStage3($stage3)
    {
        $this->stage3 = $stage3;
    }

    /**
     * @return mixed
     */
    public function getStage4()
    {
        return $this->stage4;
    }

    /**
     * @param mixed $stage4
     */
    public function setStage4($stage4)
    {
        $this->stage4 = $stage4;
    }

    /**
     * @return mixed
     */
    public function getStage5()
    {
        return $this->stage5;
    }

    /**
     * @param mixed $stage5
     */
    public function setStage5($stage5)
    {
        $this->stage5 = $stage5;
    }

    /**
     * @return mixed
     */
    public function getStage6()
    {
        return $this->stage6;
    }

    /**
     * @param mixed $stage6
     */
    public function setStage6($stage6)
    {
        $this->stage6 = $stage6;
    }

    /**
     * @return mixed
     */
    public function getStage7()
    {
        return $this->stage7;
    }

    /**
     * @param mixed $stage7
     */
    public function setStage7($stage7)
    {
        $this->stage7 = $stage7;
    }

    /**
     * @return mixed
     */
    public function getStage8()
    {
        return $this->stage8;
    }

    /**
     * @param mixed $stage8
     */
    public function setStage8($stage8)
    {
        $this->stage8 = $stage8;
    }

}