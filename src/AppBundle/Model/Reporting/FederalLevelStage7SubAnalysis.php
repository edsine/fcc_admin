<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:38 PM
 */

namespace AppBundle\Model\Reporting;


class FederalLevelStage7SubAnalysis
{
    private $subAnalysisData;

    private $subAnalysisKey;
    private $subAnalysisTitle;

    private $overallGL15AndAbove;
    private $overallPercentageGL15AndAbove;

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
    public function getOverallGL15AndAbove()
    {
        return $this->overallGL15AndAbove;
    }

    /**
     * @param mixed $overallGL15AndAbove
     */
    public function setOverallGL15AndAbove($overallGL15AndAbove)
    {
        $this->overallGL15AndAbove = $overallGL15AndAbove;
    }

    /**
     * @return mixed
     */
    public function getOverallPercentageGL15AndAbove()
    {
        return $this->overallPercentageGL15AndAbove;
    }

    /**
     * @param mixed $overallPercentageGL15AndAbove
     */
    public function setOverallPercentageGL15AndAbove($overallPercentageGL15AndAbove)
    {
        $this->overallPercentageGL15AndAbove = $overallPercentageGL15AndAbove;
    }



}