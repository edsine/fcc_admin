<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 2:11 PM
 */

namespace AppBundle\Services\Notification;


use AppBundle\AppException\AppException;
use AppBundle\Model\Notification\Notification;
use AppBundle\Model\Notification\NotificationRecipient;
use AppBundle\Model\Notification\RecipientsContactInfo;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\Paginator;
use Doctrine\DBAL\Connection;
use \PDO;
use \Throwable;

class NotificationService
{
    private $connection;
    private $rows_per_page;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->rows_per_page = AppConstants::ROWS_PER_PAGE;
    }

    public function searchRecords($recipient, $groupRecipient, Paginator $paginator, $pageDirection): array
    {
        $notifications = array();
        $statement = null;

        try {

            $searchRecipient = $recipient;
            $where = array();

            if (!$searchRecipient) {
                $searchRecipient = 'none';
            }

            if (!$groupRecipient) {
                $groupRecipient = 'none';
            }

            if ($searchRecipient) {
                $where[] = "d.recipient_id_or_group = :recipient OR d.recipient_id_or_group = :group_recipient";
            }

            if ($where) {
                $where = implode(" AND ", $where);
            } else {
                $where = '';
            }

            //fetch total matching rows
            $countQuery = "SELECT COUNT(d.id) AS totalRows FROM notification d WHERE $where";
            $statement = $this->connection->prepare($countQuery);
            if ($searchRecipient) {
                $statement->bindValue(':recipient', $searchRecipient);
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
            $query = "SELECT d.id,d.sender_id,d.recipient_id_or_group,d.notification_subject,d.notification_message 
                ,date_format(d.created,'%e-%b-%Y %h:%i %p') as date_sent 
                ,d.guid 
                ,concat_ws(' ', u.first_name, u.last_name ) as _sender_name 
                ,o.organization_name as _sender_organization 
                FROM notification d 
                LEFT JOIN user_profile u on d.sender_id = u.id
                LEFT JOIN organization o on u.organization_id = o.id
                WHERE $where order by d.created desc LIMIT $limitStartRow ,$this->rows_per_page ";
            $statement = $this->connection->prepare($query);
            if ($searchRecipient) {
                $statement->bindValue(':recipient', $searchRecipient);
            }

            $statement->execute();

            $records = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($records) {

                $displaySerialNo = $paginator->getStartRow() + 1;

                for ($i = 0; $i < count($records); $i++) {

                    $record = $records[$i];

                    $notification = new Notification();
                    $notification->setId($record['id']);
                    $notification->setSenderId($record['sender_id']);
                    $notification->setSenderName($record['_sender_name']);
                    $notification->setSenderOrganization($record['_sender_organization']);
                    $notification->setSubject($record['notification_subject']);
                    $notification->setMessage($record['notification_message']);
                    $notification->setCreated($record['date_sent']);
                    $notification->setGuid($record['guid']);

                    $notification->setDisplaySerialNo($displaySerialNo);
                    $displaySerialNo++;

                    $notifications[] = $notification;
                }
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $notifications;
    }

    public function getUserMessages($userProfileId):?array
    {
        $notifications = array();
        $statement = null;

        try {

            $query = "select user.profile_type,user.primary_role,user.primary_phone,user.email_address 
                      ,organization.level_of_government
                      from user_profile user
                      join organization as organization on user.organization_id = organization.id
                      where user.id = :recipientId";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':recipientId', $userProfileId);
            $statement->execute();

            $recipientDetails = $statement->fetch();

            $profileType = $recipientDetails['profile_type'];
            $organizationLevelOfGovernment = $recipientDetails['level_of_government'];

            $possibleRecipientOrGroupValues = array();
            $possibleRecipientOrGroupValues[] = $userProfileId;
            if($profileType == AppConstants::MDA_USER_PROFILE){
                if($organizationLevelOfGovernment == AppConstants::FEDERAL){
                    $possibleRecipientOrGroupValues[] = AppConstants::FEDERAL_MDA;
                }
            }

            $possibleRecipientValueList = "'" . implode("','", $possibleRecipientOrGroupValues) . "'";

            //TODO: Handle if recipient is part of open notification type of message

            //now exec SELECT with
            $query = "SELECT d.id,d.sender_id,d.recipient_id_or_group,d.notification_subject,d.notification_message 
                ,date_format(d.created,'%d/%m/%y') AS date_sent 
                ,d.guid 
                ,concat_ws(' ', u.first_name, u.last_name ) AS _sender_name 
                ,o.organization_name AS _sender_organization 
                FROM notification d 
                LEFT JOIN user_profile u ON d.sender_id = u.id 
                LEFT JOIN organization o ON u.organization_id = o.id 
                WHERE d.recipient_id_or_group in ($possibleRecipientValueList) ORDER BY d.created DESC ";
            $statement = $this->connection->prepare($query);
            $statement->execute();

            $records = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($records) {

                $displaySerialNo = 1;

                foreach ($records as $record) {

                    $notification = new Notification();
                    $notification->setId($record['id']);
                    $notification->setSenderId($record['sender_id']);
                    $notification->setSenderName($record['_sender_name']);
                    $notification->setSenderOrganization($record['_sender_organization']);
                    $notification->setSubject($record['notification_subject']);
                    //$notification->setMessage($record['notification_message']);
                    $notification->setCreated($record['date_sent']);
                    $notification->setGuid($record['guid']);

                    $notification->setDisplaySerialNo($displaySerialNo);
                    $displaySerialNo++;

                    $notifications[] = $notification;
                }
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $notifications;
    }

    public function getTotalUserUnreadMessage($userProfileId):?int
    {
        $totalUnreadMessages = 0;
        $statement = null;

        try {

            //TODO: make sure this query used is same as one used to fetch user messages. consider using a different function

            //GET ID OF ALL MESSAGES RECEIVED BY USER
            $query = "select user.profile_type,user.primary_role,user.primary_phone,user.email_address 
                      ,organization.level_of_government
                      from user_profile user
                      join organization as organization on user.organization_id = organization.id
                      where user.id = :recipientId";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':recipientId', $userProfileId);
            $statement->execute();

            $recipientDetails = $statement->fetch();

            $profileType = $recipientDetails['profile_type'];
            $organizationLevelOfGovernment = $recipientDetails['level_of_government'];

            $possibleRecipientOrGroupValues = array();
            $possibleRecipientOrGroupValues[] = $userProfileId;
            if($profileType == AppConstants::MDA_USER_PROFILE){
                if($organizationLevelOfGovernment == AppConstants::FEDERAL){
                    $possibleRecipientOrGroupValues[] = AppConstants::FEDERAL_MDA;
                }
            }

            $possibleRecipientValueList = "'" . implode("','", $possibleRecipientOrGroupValues) . "'";

            $query = "SELECT d.id
                FROM notification d 
                WHERE d.recipient_id_or_group in ($possibleRecipientValueList)";
            $statement = $this->connection->prepare($query);
            $statement->execute();

            $idsOfReceivedMessages = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

            if($idsOfReceivedMessages){

                $query = "select notification_id from notifications_read_by_user where user_profile_id=:user_profile_id";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(":user_profile_id", $userProfileId);
                $statement->execute();

                $idsOfReadMessages = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

                if(!$idsOfReadMessages){
                    $totalUnreadMessages = count($idsOfReceivedMessages);
                }else{
                    $idsOfUnreadMessages = array_diff($idsOfReceivedMessages, $idsOfReadMessages);
                    if($idsOfUnreadMessages){
                        $totalUnreadMessages = count($idsOfUnreadMessages);
                    }
                }
            }
        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $totalUnreadMessages;
    }

    public function addNotification(Notification $notification): bool
    {
        $outcome = false;
        $statement = null;

        try {

            //now insert
            $query = "INSERT INTO notification 
                (
                sender_id,recipient_id_or_group,recipient_email_addresses,recipient_phone_numbers
                ,notification_subject,notification_message,sms_notification_message,created,created_by,guid
                ) 
                VALUES 
                (
                :sender_id,:recipient_id_or_group,:recipient_email_addresses,:recipient_phone_numbers
                ,:notification_subject,:notification_message,:sms_notification_message,:created,:created_by,:guid
                )";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':sender_id', $notification->getSenderId());
            $statement->bindValue(':recipient_id_or_group', $notification->getRecipientIdOrGroup());
            $statement->bindValue(':recipient_email_addresses', $notification->getRecipientEmailAddresses());
            $statement->bindValue(':recipient_phone_numbers', $notification->getRecipientPhoneNumbers());
            $statement->bindValue(':notification_subject', $notification->getSubject());
            $statement->bindValue(':notification_message', $notification->getMessage());
            $statement->bindValue(':sms_notification_message', $notification->getSmsNotificationMessage());
            $statement->bindValue(':created', $notification->getCreated());
            $statement->bindValue(':created_by', $notification->getCreatedByUserId());
            $statement->bindValue(':guid', $notification->getGuid());

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

    public function updateNotificationSendStatus(Notification $notification): bool
    {
        $outcome = false;
        $statement = null;

        try {

            //now insert
            $query = "UPDATE notification 
                SET
                email_send_status=:email_send_status
                ,sms_send_status=:sms_send_status
                WHERE guid=:guid";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':email_send_status', $notification->getEmailSendStatus());
            $statement->bindValue(':sms_send_status', $notification->getSmsSendStatus());
            $statement->bindValue(':guid', $notification->getGuid());

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

    public function updateDeliveryStatus(Notification $notification): bool
    {
        $outcome = false;
        $statement = null;

        try {

            //now insert
            $query = "UPDATE notification 
                SET
                email_delivery_status=:email_delivery_status
                ,sms_delivery_status=:sms_delivery_status
                WHERE guid=:guid";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':email_delivery_status', $notification->getEmailDeliveryStatus());
            $statement->bindValue(':sms_delivery_status', $notification->getSmsDeliveryStatus());
            $statement->bindValue(':guid', $notification->getGuid());

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

    public function getNotification($guid): ?Notification
    {
        $notification = null;
        $statement = null;

        try {
            //now exec SELECT with limit clause
            $query = "SELECT d.id,d.sender_id,d.recipient_id_or_group,d.notification_subject,d.notification_message 
                ,date_format(d.created,'%D %b, %Y %h:%i %p') AS date_sent 
                ,d.guid 
                ,concat_ws(' ', u.first_name, u.last_name ) AS _sender_name 
                ,o.organization_name AS _sender_organization 
                FROM notification d 
                LEFT JOIN user_profile u ON d.sender_id = u.id 
                LEFT JOIN organization o ON u.organization_id = o.id 
                WHERE d.guid=:guid ";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':guid', $guid);
            $statement->execute();

            $record = $statement->fetch(PDO::FETCH_ASSOC);
            if ($record) {
                $notification = new Notification();
                $notification->setId($record['id']);
                $notification->setSenderId($record['sender_id']);
                $notification->setSenderName($record['_sender_name']);
                $notification->setSenderOrganization($record['_sender_organization']);
                $notification->setSubject($record['notification_subject']);
                $notification->setMessage($record['notification_message']);
                $notification->setCreated($record['date_sent']);
                $notification->setGuid($record['guid']);
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $notification;
    }


    public function saveUserReadNotification($userProfileId, $notificationId, $dateRead): bool
    {
        $outcome = false;
        $statement = null;

        try {

            //now insert
            $query = "insert ignore into notifications_read_by_user values (:user_profile_id,:notification_id,:created) ";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':user_profile_id', $userProfileId);
            $statement->bindValue(':notification_id', $notificationId);
            $statement->bindValue(':created', $dateRead);
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


    public function getRecipientContactInfoByRole(array $recipientRoles)
    {
        $statement = null;

        try {

            //build the in string
            $inString = '';
            for ($i = 0; $i < count($recipientRoles); $i++) {
                if ($i === 0) {
                    $inString = "'$recipientRoles[$i]'";
                } else {
                    $inString .= ",'$recipientRoles[$i]'";
                }
            }

            $query = "select u.primary_phone, u.email_address 
                    FROM user_profile u 
                    where 
                    u.primary_role in 
                    ( 
                    select p.role_id from system_role_privileges p where p.privilege_id in ($inString) 
                    ) 
                    and (u.primary_phone IS NOT NULL) and u.record_status='ACTIVE'";
            $statement = $this->connection->prepare($query);
            $statement->execute();

            $contactInfo = $statement->fetchAll(PDO::FETCH_ASSOC);

        } catch (Throwable $t) {
            throw new AppException($t->getMessage());
        }

        return $contactInfo;
    }

    public function getMDAContactInformationByLevelOfGovernment($levelOfGovernment):?RecipientsContactInfo
    {
        $recipientsContactInfo = new RecipientsContactInfo();
        $statement = null;

        try {
            $query = "SELECT recipient.first_name,recipient.last_name,recipient.primary_phone as contact_phone
                      , recipient.email_address as contact_email
                      , concat(recipient.first_name,' ',recipient.last_name, ' (' , organization.organization_name, ')') as contact_name 
                     FROM user_profile as recipient
                     LEFT JOIN organization AS organization ON recipient.organization_id=organization.id
                     where recipient.profile_type = :profile_type and organization.level_of_government=:level_of_government";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':profile_type', AppConstants::MDA_USER_PROFILE);
            $statement->bindValue(':level_of_government', $levelOfGovernment);
            $statement->execute();

            $recipientRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if($recipientRecords){
                $recipientsEmailAddresses = array();
                $recipientPhones = array();

                foreach ($recipientRecords as $recipientRecord){
                    if($recipientRecord['contact_email']){
                        $recipientEmail = array();
                        $recipientEmail['contact_email'] = $recipientRecord['contact_email'];
                        $recipientEmail['contact_name'] = $recipientRecord['contact_name'];

                        $recipientsEmailAddresses[] = $recipientEmail;
                    }

                    if($recipientRecord['contact_phone']){
                        $recipientPhones[] = $recipientRecord['contact_phone'];
                    }
                }

                if($recipientsEmailAddresses){
                    $recipientsContactInfo->setRecipientsEmailAddresses($recipientsEmailAddresses);
                }

                if($recipientPhones){
                    $recipientsContactInfo->setRecipientsPhoneNumbers(implode(',', $recipientPhones));
                }
            }

        } catch (Throwable $t) {
            throw new AppException($t->getMessage());
        }

        return $recipientsContactInfo;
    }

    public function getTestingRecipientsContactInfo():?RecipientsContactInfo
    {
        $recipientsContactInfo = new RecipientsContactInfo();
        $statement = null;

        try {
            $query = "SELECT recipient.first_name,recipient.last_name,recipient.primary_phone as contact_phone
                      , recipient.email_address as contact_email
                      , concat(recipient.first_name,' ',recipient.last_name, ' (' , organization.organization_name, ')') as contact_name 
                     FROM `_test_notification_recipients_user_profile` as recipient
                     LEFT JOIN organization AS organization ON recipient.organization_id=organization.id
                     where recipient.id <> 333";
            $statement = $this->connection->prepare($query);
            $statement->execute();

            $recipientRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if($recipientRecords){
                $recipientsEmailAddresses = array();
                $recipientPhones = array();

                foreach ($recipientRecords as $recipientRecord){
                    $recipientEmail = array();
                    $recipientEmail['contact_email'] = $recipientRecord['contact_email'];
                    $recipientEmail['contact_name'] = $recipientRecord['contact_name'];

                    $recipientsEmailAddresses[] = $recipientEmail;

                    $recipientPhones[] = $recipientRecord['contact_phone'];
                }

                $recipientsContactInfo->setRecipientsEmailAddresses($recipientsEmailAddresses);
                $recipientsContactInfo->setRecipientsPhoneNumbers(implode(',', $recipientPhones));
            }

        } catch (Throwable $t) {
            throw new AppException($t->getMessage());
        }

        return $recipientsContactInfo;
    }

}