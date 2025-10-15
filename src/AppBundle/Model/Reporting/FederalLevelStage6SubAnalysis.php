<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:38 PM
 */

namespace AppBundle\Model\Reporting;


class FederalLevelStage6SubAnalysis
{
    private $subAnalysisData;

    private $subAnalysisKey;
    private $subAnalysisTitle;

    private $overallGL7AndAbove;
    private $overallPercentageGL7AndAbove;

    /**
     * FederalLevelStage6SubAnalysis constructor.
     * @param $subAnalysisTitle
     */
    public function __construct($subAnalysisTitle)
    {
        $this->subAnalysisTitle = $subAnalysisTitle;
    }


    /**
     * @return mixed
     */
    public function getSubAnalysisKey()
    {
        return $this->subAnalysisKey;
    }

    /**
     * @param mixed $subAnalysisKey
     */
    public function setSubAnalysisKey($subAnalysisKey)
    {
        $this->subAnalysisKey = $subAnalysisKey;
    }

    /**
     * @return mixed
     */
    public function getSubAnalysisTitle()
    {
        return $this->subAnalysisTitle;
    }

    /**
     * @param mixed $subAnalysisTitle
     */
    public function setSubAnalysisTitle($subAnalysisTitle)
    {
        $this->subAnalysisTitle = $subAnalysisTitle;
    }

    /**
     * @return mixed
     */
    public function getSubAnalysisData()
    {
        return $this->subAnalysisData;
    }

    /**
     * @param mixed $subAnalysisData
     */
    public function setSubAnalysisData($subAnalysisData)
    {
        $this->subAnalysisData = $subAnalysisData;
    }

    /**
     * @return mixed
     */
    public function getOverallGL7AndAbove()
    {
        return $this->overallGL7AndAbove;
    }

    /**
     * @param mixed $overallGL7AndAbove
     */
    public function setOverallGL7AndAbove($overallGL7AndAbove)
    {
        $this->overallGL7AndAbove = $overallGL7AndAbove;
    }

    /**
     * @return mixed
     */
    public function getOverallPercentageGL7AndAbove()
    {
        return $this->overallPercentageGL7AndAbove;
    }

    /**
     * @param mixed $overallPercentageGL7AndAbove
     */
    public function setOverallPercentageGL7AndAbove($overallPercentageGL7AndAbove)
    {
        $this->overallPercentageGL7AndAbove = $overallPercentageGL7AndAbove;
    }



}