<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 2:04 PM
 */

namespace AppBundle\Model\SearchCriteria;


class RecruitmentMonitoringSearchCriteria
{
    private $recruitment;
    private $organizationId;

    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getRecruitment()
    {
        return $this->recruitment;
    }

    /**
     * @param mixed $recruitment
     */
    public function setRecruitment($recruitment)
    {
        $this->recruitment = $recruitment;
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
}