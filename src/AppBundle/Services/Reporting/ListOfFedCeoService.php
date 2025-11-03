<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/6/2017
 * Time: 3:47 PM
 */

namespace AppBundle\Services\Reporting;


use AppBundle\AppException\AppException;
use AppBundle\Model\Reporting\FederalLevel\Political\ListOfFedCEOOfAgencies;
use AppBundle\Model\Reporting\FederalLevel\Political\ListOfFedCEOOfAgenciesCEO;
use AppBundle\Model\Reporting\FederalLevel\Political\ListOfFedCEOOfAgenciesCommittee;
use AppBundle\Utils\AppConstants;
use Doctrine\DBAL\Driver\Connection;

class ListOfFedCeoService
{

    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }


    public function getFederalCeos($logger)
    {
        $report = null;
        $statement = null;

        try {

            //fetch the committees and initialize
            $query = "SELECT id,upper(committee_name) as _committee_name FROM committees WHERE record_status=:record_status";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':record_status', AppConstants::ACTIVE);
            $statement->execute();
            $committees = $statement->fetchAll();

            if ($committees) {

                $tempCommittees = array();
                foreach ($committees as $committee) {
                    $committeeEntry = new ListOfFedCEOOfAgenciesCommittee();
                    $committeeEntry->setCommitteeId($committee['id']);
                    $committeeEntry->setCommitteeName($committee['_committee_name']);

                    $tempCommittees[$committeeEntry->getCommitteeId()] = $committeeEntry;
                }

                //now fill each committees array with organizations
                $query = "SELECT d.id,upper(d.organization_name) as agency_name,a.fcc_committee_id as agency_committee_id "
                    . "FROM organization d "
                    . "LEFT JOIN organization a on d.parent_organization_id = a.id "
                    . "WHERE d.establishment_type_id=:establishment_type_id AND d.record_status=:record_status ORDER BY d.organization_name";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':establishment_type_id', AppConstants::FEDERAL_PARASTATAL_ESTABLISHMENT);
                $statement->bindValue(':record_status', AppConstants::ACTIVE);
                $statement->execute();
                $organizations = $statement->fetchAll();

                $tempCommitteeAgencies = array();
                foreach ($organizations as $organization) {
                    $committeeAgency = new ListOfFedCEOOfAgenciesCEO();
                    $committeeAgency->setOrganizationId($organization['id']);
                    $committeeAgency->setOrganizationName($organization['agency_name']);
                    $committeeAgency->setCommitteeId($organization['agency_committee_id']);

                    $tempCommitteeAgencies[$committeeAgency->getOrganizationId()] = $committeeAgency;
                }

                //now select the ceos and sort
                $query = "SELECT d.submission_id, d.employee_name, d.state_of_origin_code,nom_role.organization_id,s.state_name "
                    . "FROM confirmed_federal_level_nominal_roll_submission d "
                    . "LEFT JOIN federal_level_nominal_roll_submissions nom_role on d.submission_id = nom_role.submission_id "
                    . "LEFT JOIN states s on d.state_of_origin_code = s.state_code "
                    . "WHERE d.grade_level=:grade_level and d.submission_year IN ( "
                    . "     SELECT MAX(submission_year) "
                    . "     FROM confirmed_federal_level_nominal_roll_submission "
                    . "     GROUP BY submission_id "
                    . ") "
                    . "ORDER BY nom_role.organization_id ";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':grade_level', AppConstants::GRADE_LEVEL_CHIEF_EXECUTIVES);
                $statement->execute();
                $agencyCEOs = $statement->fetchAll();

                foreach ($agencyCEOs as $agencyCEO) {
                    if(array_key_exists($agencyCEO['organization_id'], $tempCommitteeAgencies)){
                        $tempCommitteeAgencies[$agencyCEO['organization_id']]->updateDetails($agencyCEO['employee_name'], $agencyCEO['state_name']);
                    }
                }

                //now sort agencies into the committees
                foreach ($tempCommitteeAgencies as $tempCommitteeAgency){
                    if(array_key_exists($tempCommitteeAgency->getCommitteeId(), $tempCommittees)){
                        $tempCommittees[$tempCommitteeAgency->getCommitteeId()]->addAgency($tempCommitteeAgency);
                    }
                }

                //fix the serial no in each committees agency array
                /**
                 * @var ListOfFedCEOOfAgenciesCommittee $tempCommittee
                 * @var ListOfFedCEOOfAgenciesCEO $agencyEntry
                 */
                foreach ($tempCommittees as $tempCommittee){
                    $displaySerialNo = 0;
                    foreach ($tempCommittee->getAgencyEntries() as $agencyEntry){
                        $displaySerialNo++;
                        $agencyEntry->setDisplaySerialNo($displaySerialNo);
                    }
                }

                $report = new ListOfFedCEOOfAgencies();
                $report->setCommitteeEntries(array_values($tempCommittees));

            }


        } catch (\Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $report;
    }

}