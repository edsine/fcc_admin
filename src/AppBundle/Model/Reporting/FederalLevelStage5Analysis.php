<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:38 PM
 */

namespace AppBundle\Model\Reporting;


class FederalLevelStage5Analysis
{
    private $stage5Data;

    /**
     * @return mixed
     */
    public function getStage5Data()
    {
        return $this->stage5Data;
    }

    /**
     * @param mixed $stage5Data
     */
    public function setStage5Data($stage5Data)
    {
        $this->stage5Data = $stage5Data;
    }
}