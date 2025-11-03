<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/1/2018
 * Time: 1:34 PM
 */

namespace AppBundle\Model\Formula;


class LGAFormula
{

    private $formula;

    /**
     * LGAFormula constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getFormula()
    {
        return $this->formula;
    }

    /**
     * @param mixed $formula
     */
    public function setFormula($formula)
    {
        $this->formula = $formula;
    }

}