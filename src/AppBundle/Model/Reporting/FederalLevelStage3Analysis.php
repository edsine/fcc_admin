<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:38 PM
 */

namespace AppBundle\Model\Reporting;


class FederalLevelStage3Analysis
{
    private $stage3Data;
    private $xAxisLabels;

        /**
     * @return mixed
     */
    public function getStage3Data()
    {
        return $this->stage3Data;
    }

    /**
     * @param mixed $stage3Data
     */
    public function setStage3Data($stage3Data)
    {
        $this->stage3Data = $stage3Data;
    }

    /**
     * @return mixed
     */
    public function getXAxisLabels()
    {
        return $this->xAxisLabels;
    }

    /**
     * @param mixed $xAxisLabels
     */
    public function setXAxisLabels($xAxisLabels)
    {
        $this->xAxisLabels = $xAxisLabels;
    }


}