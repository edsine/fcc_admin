<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/28/2017
 * Time: 8:40 PM
 */

namespace AppBundle\Model\Reporting\FederalLevel\Career;


class CBIndexStage2Analysis
{

    private $stage2Data;

    /**
     * CBIndexStage2Analysis constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getStage2Data()
    {
        return $this->stage2Data;
    }

    /**
     * @param mixed $stage2Data
     */
    public function setStage2Data($stage2Data)
    {
        $this->stage2Data = $stage2Data;
    }


}