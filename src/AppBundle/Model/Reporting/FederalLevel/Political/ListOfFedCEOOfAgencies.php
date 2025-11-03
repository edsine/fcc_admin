<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/25/2017
 * Time: 10:31 AM
 */

namespace AppBundle\Model\Reporting\FederalLevel\Political;


class ListOfFedCEOOfAgencies
{

    private $committeeEntries;

    /**
     * ListOfCEOOfAgencies constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getCommitteeEntries()
    {
        return $this->committeeEntries;
    }

    /**
     * @param mixed $committeeEntries
     */
    public function setCommitteeEntries($committeeEntries)
    {
        $this->committeeEntries = $committeeEntries;
    }

}