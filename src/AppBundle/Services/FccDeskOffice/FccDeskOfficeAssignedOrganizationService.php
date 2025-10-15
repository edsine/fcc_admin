<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 2:11 PM
 */

namespace AppBundle\Services\FccDeskOffice;


use AppBundle\AppException\AppException;
use AppBundle\Model\Organizations\Organization;
use AppBundle\Model\SearchCriteria\OrganizationSearchCriteria;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\Paginator;
use AppBundle\Utils\ServiceHelper;
use Doctrine\DBAL\Connection;
use \PDO;
use \Throwable;

class FccDeskOfficeAssignedOrganizationService extends ServiceHelper
{
    private $connection;
    private $rows_per_page;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        //$this->rows_per_page = AppConstants::ROWS_PER_PAGE;
        $this->rows_per_page = 200;
    }


    public function searchRecords(OrganizationSearchCriteria $searchCriteria, Paginator $paginator, $pageDirection) : array
    {
        $organizations = array();
        $statement = null;

        try {

            $searchFccDeskOfficerComomittee = $searchCriteria->getFccCommittee();
            if(!$searchFccDeskOfficerComomittee){
                $searchFccDeskOfficerComomittee = '0';
            }

            $searchStatus = 'ACTIVE';

            $where = array("d.id > 0");

            if ($searchFccDeskOfficerComomittee) {
                $where[] = "d.fcc_committee_id = :fcc_committee_id";
            }

            if ($searchStatus) {
                $where[] = "d.record_status = :record_status";
            }

            if ($where) {
                $where = implode(" AND ", $where);
            } else {
                $where = '';
            }

            //fetch total matching rows
            $countQuery = "SELECT COUNT(d.id) AS totalRows FROM organization d WHERE $where";
            $statement = $this->connection->prepare($countQuery);

            if ($searchFccDeskOfficerComomittee) {
                $statement->bindValue(':fcc_committee_id', $searchFccDeskOfficerComomittee);
            }

            if ($searchStatus) {
                $statement->bindValue(':record_status', $searchStatus);
            }

            $statement->execute();
            $totalRows = $statement->fetchColumn();

            $paginator->setTotalRows($totalRows);
            $paginator->setRowsPerPage($this->rows_per_page);

            switch ($pageDirection) {
                case 'FIRST':
                    $paginator->pageFirst();
                    break;
                case 'PREVIOUS':
                    $paginator->pagePrevious();
                    break;
                case 'NEXT':
                    $paginator->pageNext();
                    break;
                case 'LAST':
                    $paginator->pageLast();
                    break;
                default:
                    $paginator->pageFirst();
                    break;
            }

            $limitStartRow = $paginator->getStartRow();

            //now exec SELECT with limit clause
            $query = "SELECT d.id,d.establishment_code,d.establishment_mnemonic,d.organization_name 
                ,d.establishment_type_id,d.state_owned_establishment_state_id,d.state_of_location_id 
                ,d.contact_address,d.website_address,d.email_address,d.primary_phone,d.record_status,d.guid 
                ,d.parent_organization_id, d.fcc_committee_id, d.fcc_desk_officer_id
                ,e.description as _establishment_type_name
                ,s.state_code as _state_owned_establishment_state_code,s.state_name as _state_owned_establishment_state_name 
                ,sl.state_name as _state_of_location_name
                ,o.organization_name as _parent_organization_name, c.committee_name as _fcc_committee_name
                ,concat_ws(' ', u.first_name,u.last_name) as _fcc_desk_officer_name 
                ,u.email_address as _fcc_desk_officer_email,u.primary_phone as _fcc_desk_officer_phone 
                ,m.id as _mda_desk_officer_id,concat_ws(' ', m.first_name,m.last_name) as _mda_desk_officer_name 
                ,m.email_address as _mda_desk_officer_email,m.primary_phone as _mda_desk_officer_phone 
                FROM organization d 
                LEFT JOIN establishment_types e on d.establishment_type_id=e.id 
                LEFT JOIN states s on d.state_owned_establishment_state_id=s.id 
                LEFT JOIN states sl on d.state_of_location_id=sl.id  
                LEFT JOIN organization o on d.parent_organization_id=o.id 
                LEFT JOIN committees c on d.fcc_committee_id=c.id 
                LEFT JOIN user_profile u on (d.fcc_committee_id = u.fcc_committee_id 
                                                  and u.primary_role=:fcc_desk_officer_role)
                LEFT JOIN user_profile m on d.establishment_code=m.user_login 
                WHERE $where order by d.organization_name LIMIT $limitStartRow ,$this->rows_per_page ";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':fcc_desk_officer_role', AppConstants::ROLE_FCC_DESK_OFFICER_FEDERAL);

            if ($searchFccDeskOfficerComomittee) {
                $statement->bindValue(':fcc_committee_id', $searchFccDeskOfficerComomittee);
            }

            if ($searchStatus) {
                $statement->bindValue(':record_status', $searchStatus);
            }

            $statement->execute();

            $records = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($records) {

                $displaySerialNo = $paginator->getStartRow() + 1;

                for ($i = 0; $i < count($records); $i++) {

                    $record = $records[$i];

                    $organization = new Organization();
                    $organization->setId($record['id']);
                    $organization->setEstablishmentCode($record['establishment_code']);
                    $organization->setEstablishmentMnemonic($record['establishment_mnemonic']);
                    $organization->setOrganizationName($record['organization_name']);

                    $organization->setEstablishmentTypeId($record['establishment_type_id']);
                    $organization->setEstablishmentTypeName($record['_establishment_type_name']);

                    $organization->setStateOwnedEstablishmentStateId($record['state_owned_establishment_state_id']);
                    $organization->setStateOwnedEstablishmentStateCode($record['_state_owned_establishment_state_code']);
                    $organization->setStateOwnedEstablishmentStateName($record['_state_owned_establishment_state_name']);

                    $organization->setStateOfLocationId($record['state_of_location_id']);
                    $organization->setStateOfLocationName($record['_state_of_location_name']);

                    $organization->setContactAddress($record['contact_address']);
                    $organization->setWebsiteAddress($record['website_address']);
                    $organization->setEmailAddress($record['email_address']);
                    $organization->setPrimaryPhone($record['primary_phone']);

                    $organization->setParentOrganizationId($record['parent_organization_id']);
                    $organization->setParentOrganizationName($record['_parent_organization_name']);

                    $organization->setFccCommitteeId($record['fcc_committee_id']);
                    $organization->setFccCommitteeName($record['_fcc_committee_name']);

                    $organization->setFccDeskOfficerId($record['fcc_desk_officer_id']);
                    $organization->setFccDeskOfficerName($record['_fcc_desk_officer_name']);
                    $organization->setFccDeskOfficerEmail($record['_fcc_desk_officer_email']);
                    $organization->setFccDeskOfficerPhone($record['_fcc_desk_officer_phone']);

                    $organization->setMdaDeskOfficerId($record['_mda_desk_officer_id']);
                    $organization->setMdaDeskOfficerName($record['_mda_desk_officer_name']);
                    $organization->setMdaDeskOfficerEmail($record['_mda_desk_officer_email']);
                    $organization->setMdaDeskOfficerPhone($record['_mda_desk_officer_phone']);

                    $organization->setStatus($record['record_status']);
                    $organization->setGuid($record['guid']);

                    $organization->setDisplaySerialNo($displaySerialNo);
                    $displaySerialNo++;

                    $organizations[] = $organization;
                }
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $organizations;
    }

}