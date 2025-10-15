<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:38 PM
 */

namespace AppBundle\Model\Reporting;


class FederalLevelStage8CategoryAnalysis
{
    private $stage8CategoryData;
    private $overallCategorySubAnalysisTotal;

    /**
     * @return mixed
     */
    public function getStage8CategoryData()
    {
        return $this->stage8CategoryData;
    }

    /**
     * @param mixed $stage8CategoryData
     */
    public function setStage8CategoryData($stage8CategoryData)
    {
        $this->stage8CategoryData = $stage8CategoryData;
    }

    /**
     * @return mixed
     */
    public function getOverallCategorySubAnalysisTotal()
    {
        return $this->overallCategorySubAnalysisTotal;
    }

    /**
     * @param mixed $overallCategorySubAnalysisTotal
     */
    public function setOverallCategorySubAnalysisTotal($overallCategorySubAnalysisTotal)
    {
        $this->overallCategorySubAnalysisTotal = $overallCategorySubAnalysisTotal;
    }
}