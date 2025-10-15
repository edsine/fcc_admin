<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/25/2017
 * Time: 10:31 AM
 */

namespace AppBundle\Model\Reporting\FederalLevel\Political;


class ListOfFedCEOOfAgenciesCommittee
{

    private $committeeId;
    private $committeeName;

    private $agencyEntries = array();

    /**
     * ListOfCEOOfAgenciesCommittee constructor.
     */
    public function __construct()
    {
    }

    public function addAgency(ListOfFedCEOOfAgenciesCEO $agency){
        $this->agencyEntries[] = $agency;
    }

    /**
     * @return mixed
     */
    public function getCommitteeId()
    {
        return $this->committeeId;
    }

    /**
     * @param mixed $committeeId
     */
    public function setCommitteeId($committeeId)
    {
        $this->committeeId = $committeeId;
    }

    /**
     * @return mixed
     */
    public function getCommitteeName()
    {
        return $this->committeeName;
    }

    /**
     * @param mixed $committeeName
     */
    public function setCommitteeName($committeeName)
    {
        $this->committeeName = $committeeName;
    }

    /**
     * @return mixed
     */
    public function getAgencyEntries()
    {
        return $this->agencyEntries;
    }

    /**
     * @param mixed $agencyEntries
     */
    public function setAgencyEntries($agencyEntries)
    {
        $this->agencyEntries = $agencyEntries;
    }

}