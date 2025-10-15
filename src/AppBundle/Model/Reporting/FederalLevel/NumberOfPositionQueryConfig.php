<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 7/12/2017
 * Time: 8:22 AM
 */

namespace AppBundle\Model\Reporting\FederalLevel;


class NumberOfPositionQueryConfig
{
    private $cadre;
    private $queryCriteria;

    public function __construct($cadre)
    {
        $this->cadre = $cadre;
    }


    /**
     * @return mixed
     */
    public function getCadre()
    {
        return $this->cadre;
    }

    /**
     * @param mixed $cadre
     */
    public function setCadre($cadre)
    {
        $this->cadre = $cadre;
    }

    /**
     * @return mixed
     */
    public function getQueryCriteria()
    {
        return $this->queryCriteria;
    }

    /**
     * @param mixed $queryCriteria
     */
    public function setQueryCriteria($queryCriteria)
    {
        $this->queryCriteria = $queryCriteria;
    }

}