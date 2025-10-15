<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:38 PM
 */

namespace AppBundle\Model\Reporting;


class FederalLevelStage7Analysis
{
    private $stage7Data;

    private $overallSubAnalysisTotal;

    /**
     * @return mixed
     */
    public function getStage7Data()
    {
        return $this->stage7Data;
    }

    /**
     * @param mixed $stage7Data
     */
    public function setStage7Data($stage7Data)
    {
        $this->stage7Data = $stage7Data;
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