<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/3/2017
 * Time: 2:10 PM
 */

namespace AppBundle\Model\SearchCriteria;


class CBIReportRequestSearchCriteria
{
    private $organizationId;
    private $approvalStatus;

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
    public function getApprovalStatus()
    {
        return $this->approvalStatus;
    }

    /**
     * @param mixed $approvalStatus
     */
    public function setApprovalStatus($approvalStatus)
    {
        $this->approvalStatus = $approvalStatus;
    }

}