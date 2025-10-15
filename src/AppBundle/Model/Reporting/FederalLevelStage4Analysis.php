<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:38 PM
 */

namespace AppBundle\Model\Reporting;


class FederalLevelStage4Analysis
{
    private $stage4Data;

    /**
     * @return mixed
     */
    public function getStage4Data()
    {
        return $this->stage4Data;
    }

    /**
     * @param mixed $stage4Data
     */
    public function setStage4Data($stage4Data)
    {
        $this->stage4Data = $stage4Data;
    }
}