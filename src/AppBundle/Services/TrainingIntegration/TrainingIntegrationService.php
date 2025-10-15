<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 2:11 PM
 */

namespace AppBundle\Services\TrainingIntegration;


use AppBundle\AppException\AppException;
use AppBundle\AppException\AppExceptionMessages;
use AppBundle\Model\Organizations\Organization;
use AppBundle\Model\Users\UserProfile;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\ServiceHelper;
use Doctrine\DBAL\Connection;
use \Throwable;

class TrainingIntegrationService extends ServiceHelper
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param Organization $organization
     * @param UserProfile $userProfile
     * @return bool
     * @throws AppException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function integrateOrganization(Organization $organization, UserProfile $userProfile): bool
    {
        $outcome = false;
        $statement = null;

        try {

            //begin a transaction
            $this->connection->beginTransaction();

            $shouldCreateOrganization = false;
            $shouldCreateUser = false;
            $shouldUpdateStatus = false;

            //check for duplicate code
            $query = "SELECT establishment_code,trim(organization_name) AS _organization_name
                      FROM organization 
                      WHERE establishment_code=:establishment_code LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':establishment_code', $organization->getEstablishmentCode());
            $statement->execute();
            $existingOrganizationDetails = $statement->fetch();

            if ($existingOrganizationDetails) {

                if (strtolower($existingOrganizationDetails['_organization_name']) == strtolower(trim($organization->getOrganizationName()))) {
                    //check if the mda admin exists
                    $query = "SELECT user_login, record_status FROM user_profile WHERE user_login=:user_login";
                    $statement = $this->connection->prepare($query);
                    $statement->bindValue(':user_login', $organization->getEstablishmentCode());
                    $statement->execute();
                    $mdaAdminUser = $statement->fetch();
                    if ($mdaAdminUser) {
                        if ($mdaAdminUser['record_status'] != AppConstants::ACTIVE) {
                            $shouldUpdateStatus = true;
                        }else{
                            throw new AppException(AppExceptionMessages::USER_ALREADY_ACTIVATED);
                        }
                    } else {
                        $shouldCreateUser = true;
                    }
                }else{
                    throw new AppException("ORG NAME MISMATCH : old:" . strtolower($existingOrganizationDetails['_organization_name']) . " , new:" . strtolower($organization->getOrganizationName()));
                }

            } else {
                $shouldCreateOrganization = true;
                $shouldCreateUser = true;
            }

            if ($shouldCreateOrganization) {
                $query = "INSERT INTO organization 
                (
                establishment_code,establishment_mnemonic,organization_name,level_of_government,establishment_type_id 
                ,state_owned_establishment_state_id,state_of_location_id,contact_address,website_address,email_address
                ,primary_phone,parent_organization_id,fcc_committee_id,fcc_desk_officer_id,record_status
                ,created,created_by,last_mod,last_mod_by,guid
                )
                VALUES 
                (
                :establishment_code,:establishment_mnemonic,:organization_name,:level_of_government,:establishment_type_id 
                ,:state_owned_establishment_state_id,:state_of_location_id,:contact_address,:website_address,:email_address
                ,:primary_phone,:parent_organization_id,:fcc_committee_id,:fcc_desk_officer_id
                ,:record_status,:created,:created_by,:last_mod,:last_mod_by,:guid
                )";

                $statement = $this->connection->prepare($query);
                $statement->bindValue(':establishment_code', $this->getValueOrNull($organization->getEstablishmentCode()));
                $statement->bindValue(':establishment_mnemonic', $this->getValueOrNull($organization->getEstablishmentMnemonic()));
                $statement->bindValue(':organization_name', $this->getValueOrNull($organization->getOrganizationName()));
                $statement->bindValue(':level_of_government', $this->getValueOrNull($organization->getLevelOfGovernment()));
                $statement->bindValue(':establishment_type_id', $this->getValueOrNull($organization->getEstablishmentTypeId()));
                $statement->bindValue(':state_owned_establishment_state_id', $this->getValueOrNull($organization->getStateOwnedEstablishmentStateId()));
                $statement->bindValue(':state_of_location_id', $this->getValueOrNull($organization->getStateOfLocationId()));
                $statement->bindValue(':contact_address', $this->getValueOrNull($organization->getContactAddress()));
                $statement->bindValue(':website_address', $this->getValueOrNull($organization->getWebsiteAddress()));
                $statement->bindValue(':email_address', $this->getValueOrNull($organization->getEmailAddress()));
                $statement->bindValue(':primary_phone', $this->getValueOrNull($organization->getPrimaryPhone()));
                $statement->bindValue(':parent_organization_id', $this->getValueOrNull($organization->getParentOrganizationId()));
                $statement->bindValue(':fcc_committee_id', $this->getValueOrNull($organization->getFccCommitteeId()));
                $statement->bindValue(':fcc_desk_officer_id', $this->getValueOrNull($organization->getFccDeskOfficerId()));
                $statement->bindValue(':record_status', $this->getValueOrNull($organization->getStatus()));
                $statement->bindValue(':created', $this->getValueOrNull($organization->getLastModified()));
                $statement->bindValue(':created_by', $this->getValueOrNull($organization->getLastModifiedByUserId()));
                $statement->bindValue(':last_mod', $this->getValueOrNull($organization->getLastModified()));
                $statement->bindValue(':last_mod_by', $this->getValueOrNull($organization->getLastModifiedByUserId()));
                $statement->bindValue(':guid', $this->getValueOrNull($organization->getGuid()));
                $statement->execute();
            }

            if ($shouldCreateUser) {

                //get the id of the organization
                $query = "SELECT id FROM organization WHERE establishment_code=:establishment_code LIMIT 1";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':establishment_code', $organization->getEstablishmentCode());
                $statement->execute();
                $organizationId = $statement->fetchColumn(0);

                $query = "INSERT INTO user_profile 
                (
                profile_type,user_login,user_pass,organization_id,primary_role
                ,record_status,first_login,created,created_by,last_mod,last_mod_by,guid
                )
                VALUES 
                (
                :profile_type,:user_login,:user_pass,:organization_id,:primary_role
                ,:record_status,:first_login,:created,:created_by,:last_mod,:last_mod_by,:guid
                )";

                $statement = $this->connection->prepare($query);
                $statement->bindValue(':profile_type', $this->getValueOrNull($userProfile->getProfileType()));
                $statement->bindValue(':user_login', $this->getValueOrNull($userProfile->getUsername()));
                $statement->bindValue(':user_pass', $this->getValueOrNull($userProfile->getPassword()));
                $statement->bindValue(':organization_id', $this->getValueOrNull($organizationId));
                $statement->bindValue(':primary_role', $this->getValueOrNull($userProfile->getPrimaryRole()));
                $statement->bindValue(':record_status', $this->getValueOrNull($userProfile->getStatus()));
                $statement->bindValue(':first_login', $this->getValueOrNull($userProfile->getFirstLogin()));
                $statement->bindValue(':created', $this->getValueOrNull($userProfile->getLastModified()));
                $statement->bindValue(':created_by', $this->getValueOrNull($userProfile->getLastModifiedByUserId()));
                $statement->bindValue(':last_mod', $this->getValueOrNull($userProfile->getLastModified()));
                $statement->bindValue(':last_mod_by', $this->getValueOrNull($userProfile->getLastModifiedByUserId()));
                $statement->bindValue(':guid', $this->getValueOrNull($userProfile->getGuid()));
                $statement->execute();

            }


            if ($shouldUpdateStatus) {

                $query = "UPDATE user_profile
                            SET 
                            record_status=:record_status
                            ,last_mod=:last_mod
                            ,last_mod_by=:last_mod_by
                            WHERE user_login=:user_login";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':record_status', $userProfile->getStatus());
                $statement->bindValue(':last_mod', $userProfile->getLastModified());
                $statement->bindValue(':last_mod_by', $userProfile->getLastModifiedByUserId());
                $statement->bindValue(':user_login', $organization->getEstablishmentCode());
                $statement->execute();

            }

            //commit
            $this->connection->commit();

            $outcome = true;

        } catch (Throwable $e) {
            //roll back
            if ($this->connection->isTransactionActive()) {
                $this->connection->rollBack();
            }

            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $outcome;

    }

}