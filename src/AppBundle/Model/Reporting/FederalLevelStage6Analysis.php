<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:38 PM
 */

namespace AppBundle\Model\Reporting;


class FederalLevelStage6Analysis
{
    private $stage6Data;

    private $overallSubAnalysisTotal;

    /**
     * @return mixed
     */
    public function getStage6Data()
    {
        return $this->stage6Data;
    }

    /**
     * @param mixed $stage6Data
     */
    public function setStage6Data($stage6Data)
    {
        $this->stage6Data = $stage6Data;
    }

    /**
     * @return mixed
     */
    public function getOverallSubAnalysisTotal()
    {
        return $this->overallSubAnalysisTotal;
    }

    /**
     * @param mixed $overallSubAnalysisTotal
     */
    public function setOverallSubAnalysisTotal($overallSubAnalysisTotal)
    {
        $this->overallSubAnalysisTotal = $overallSubAnalysisTotal;
    }


}