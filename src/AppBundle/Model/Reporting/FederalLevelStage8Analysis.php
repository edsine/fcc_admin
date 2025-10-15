<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:38 PM
 */

namespace AppBundle\Model\Reporting;


class FederalLevelStage8Analysis
{
    private $stage8GL7AndAboveData;
    private $stage8GL15AndAboveData;

    private $overallGL7AndAboveSubAnalysisTotal;
    private $overallGL15AndAboveSubAnalysisTotal;

    /**
     * @return mixed
     */
    public function getStage8GL7AndAboveData()
    {
        return $this->stage8GL7AndAboveData;
    }

    /**
     * @param mixed $stage8GL7AndAboveData
     */
    public function setStage8GL7AndAboveData($stage8GL7AndAboveData)
    {
        $this->stage8GL7AndAboveData = $stage8GL7AndAboveData;
    }

    /**
     * @return mixed
     */
    public function getOverallGL7AndAboveSubAnalysisTotal()
    {
        return $this->overallGL7AndAboveSubAnalysisTotal;
    }

    /**
     * @param mixed $overallGL7AndAboveSubAnalysisTotal
     */
    public function setOverallGL7AndAboveSubAnalysisTotal($overallGL7AndAboveSubAnalysisTotal)
    {
        $this->overallGL7AndAboveSubAnalysisTotal = $overallGL7AndAboveSubAnalysisTotal;
    }

    /**
     * @return mixed
     */
    public function getStage8GL15AndAboveData()
    {
        return $this->stage8GL15AndAboveData;
    }

    /**
     * @param mixed $stage8GL15AndAboveData
     */
    public function setStage8GL15AndAboveData($stage8GL15AndAboveData)
    {
        $this->stage8GL15AndAboveData = $stage8GL15AndAboveData;
    }

    /**
     * @return mixed
     */
    public function getOverallGL15AndAboveSubAnalysisTotal()
    {
        return $this->overallGL15AndAboveSubAnalysisTotal;
    }

    /**
     * @param mixed $overallGL15AndAboveSubAnalysisTotal
     */
    public function setOverallGL15AndAboveSubAnalysisTotal($overallGL15AndAboveSubAnalysisTotal)
    {
        $this->overallGL15AndAboveSubAnalysisTotal = $overallGL15AndAboveSubAnalysisTotal;
    }


}