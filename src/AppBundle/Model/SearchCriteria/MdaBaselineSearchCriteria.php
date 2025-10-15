<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/1/2017
 * Time: 12:58 PM
 */

namespace AppBundle\Model\SearchCriteria;


class MdaBaselineSearchCriteria
{

    private $organizationId;
    private $baselineYear;

    /**
     * MdaBaseline constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getOrganizationId()
    {
        return $this->organizationId;
    }

    /**
     * @param mixed $organizationId
     */
    public function setOrganizationId($organizationId)
    {
        $this->organizationId = $organizationId;
    }

    /**
     * @return mixed
     */
    public function getBaselineYear()
    {
        return $this->baselineYear;
    }

    /**
     * @param mixed $baselineYear
     */
    public function setBaselineYear($baselineYear)
    {
        $this->baselineYear = $baselineYear;
    }

}