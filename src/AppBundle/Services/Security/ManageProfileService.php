<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 2:11 PM
 */

namespace AppBundle\Services\Security;


use AppBundle\AppException\AppException;
use AppBundle\Model\Organizations\Organization;
use AppBundle\Model\Users\UserProfile;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\ServiceHelper;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\User\User;
use \Throwable;

class ManageProfileService extends ServiceHelper
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function editBioAndContactData(UserProfile $userProfile): bool
    {
        $outcome = false;
        $statement = null;

        try {

            //check duplicate email
            $query = "SELECT id FROM user_profile WHERE email_address=:email_address AND guid<>:guid LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':email_address', $userProfile->getEmailAddress());
            $statement->bindValue(':guid', $userProfile->getGuid());
            $statement->execute();
            $existingUser = $statement->fetch();

            if ($existingUser) {
                throw new AppException(AppConstants::DUPLICATE_EMAIL);
            }

            //check duplicate prim phone
            $query = "SELECT id FROM user_profile WHERE primary_phone=:primary_phone AND guid<>:guid LIMIT 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':primary_phone', $userProfile->getPrimaryPhone());
            $statement->bindValue(':guid', $userProfile->getGuid());
            $statement->execute();
            $existingUser = $statement->fetch();

            if ($existingUser) {
                throw new AppException(AppConstants::DUPLICATE_PRIMARY_PHONE);
            }

            //check duplicate sec phone
            if ($userProfile->getSecondaryPhone()) {
                $query = "SELECT id FROM user_profile WHERE secondary_phone=:secondary_phone AND guid<>:guid LIMIT 1";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':secondary_phone', $userProfile->getSecondaryPhone());
                $statement->bindValue(':guid', $userProfile->getGuid());
                $statement->execute();
                $existingUser = $statement->fetch();

                if ($existingUser) {
                    throw new AppException(AppConstants::DUPLICATE_SECONDARY_PHONE);
                }
            }

            //now update
            $query = "UPDATE user_profile "
                . "SET first_name=:first_name, last_name=:last_name, middle_name=:middle_name, email_address=:email_address "
                . ",primary_phone=:primary_phone, secondary_phone=:secondary_phone "
                . ",last_mod=:last_mod, last_mod_by=:last_mod_by  "
                . "WHERE guid=:guid";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':first_name', $this->getValueOrNull($userProfile->getFirstName()));
            $statement->bindValue(':last_name', $this->getValueOrNull($userProfile->getLastName()));
            $statement->bindValue(':middle_name', $this->getValueOrNull($userProfile->getMiddleName()));
            $statement->bindValue(':email_address', $this->getValueOrNull($userProfile->getEmailAddress()));
            $statement->bindValue(':primary_phone', $this->getValueOrNull($userProfile->getPrimaryPhone()));
            $statement->bindValue(':secondary_phone', $this->getValueOrNull($userProfile->getSecondaryPhone()));
            $statement->bindValue(':last_mod', $this->getValueOrNull($userProfile->getLastModified()));
            $statement->bindValue(':last_mod_by', $this->getValueOrNull($userProfile->getLastModifiedByUserId()));
            $statement->bindValue(':guid', $userProfile->getGuid());

            $outcome = $statement->execute();

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $outcome;

    }

    public function changePassword(UserProfile $userProfile, $oldPasswordEncrypted): bool
    {
        $outcome = false;
        $statement = null;

        try {

            //now delete
            $query = "UPDATE user_profile "
                . "SET user_pass=:user_pass "
                . ",last_mod=:last_mod,last_mod_by=:last_mod_by  "
                . "WHERE guid=:guid";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':user_pass', $userProfile->getPassword());
            $statement->bindValue(':last_mod', $userProfile->getLastModified());
            $statement->bindValue(':last_mod_by', $userProfile->getLastModifiedByUserId());
            $statement->bindValue(':guid', $userProfile->getGuid());

            $statement->execute();

            $outcome = true;

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $outcome;

    }

    public function checkUserEmail($userEmail): bool
    {
        $outcome = false;
        $statement = null;

        try {

            //now delete
            $query = "SELECT id FROM user_profile WHERE email_address=:email_address ";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':email_address', $userEmail);

            $statement->execute();

            $exists = $statement->fetch();

            if ($exists) {
                $outcome = true;
            }


        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $outcome;

    }

    public function getOrganizationFccDeskOfficer($organizationId) :?UserProfile
    {
        $fccDeskOfficer = null;
        $statement = null;

        try {
            $query = "SELECT u.id AS fcc_desk_officer_id,concat_ws(' ', u.first_name,u.last_name) AS fcc_desk_officer_name 
                ,u.primary_phone AS fcc_desk_officer_phone,u.email_address AS fcc_desk_officer_email 
                ,o.organization_name AS organization_name                
                FROM user_profile u 
                LEFT JOIN organization o ON u.fcc_committee_id=o.fcc_committee_id 
                WHERE o.id=:id AND u.primary_role=:fcc_desk_officer_role";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':id', $organizationId);
            $statement->bindValue(':fcc_desk_officer_role', AppConstants::ROLE_FCC_DESK_OFFICER_FEDERAL);
            $statement->execute();

            $record = $statement->fetch(\PDO::FETCH_ASSOC);
            if ($record) {
                $fccDeskOfficer = new UserProfile();
                $fccDeskOfficer->setId($record['fcc_desk_officer_id']);
                $fccDeskOfficer->setDisplayName($record['fcc_desk_officer_name']);
                $fccDeskOfficer->setOrganizationName($record['organization_name']);

                $fccDeskOfficer->setEmailAddress($record['fcc_desk_officer_email']);
                $fccDeskOfficer->setPrimaryPhone($record['fcc_desk_officer_phone']);
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $fccDeskOfficer;
    }

}