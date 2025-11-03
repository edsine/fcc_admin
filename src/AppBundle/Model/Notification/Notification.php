<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/21/2017
 * Time: 7:53 PM
 */

namespace AppBundle\Model\Notification;


class Notification
{
    private $id;
    private $senderId, $senderName, $senderEmail, $senderOrganization;
    private $recipientIdOrGroup;
    private $recipientEmailAddresses, $recipientPhoneNumbers, $recipientName;
    private $subject, $message;
    private $smsNotificationSender;
    private $smsNotificationMessage;
    private $emailSendStatus, $smsSendStatus;
    private $emailDeliveryStatus, $smsDeliveryStatus;
    private $created;
    private $createdByUserId;
    private $guid;
    private $displaySerialNo;

    /**
     * Notification constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getSenderId()
    {
        return $this->senderId;
    }

    /**
     * @param mixed $senderId
     */
    public function setSenderId($senderId)
    {
        $this->senderId = $senderId;
    }

    /**
     * @return mixed
     */
    public function getSenderName()
    {
        return $this->senderName;
    }

    /**
     * @param mixed $senderName
     */
    public function setSenderName($senderName)
    {
        $this->senderName = $senderName;
    }

    /**
     * @return mixed
     */
    public function getSenderEmail()
    {
        return $this->senderEmail;
    }

    /**
     * @param mixed $senderEmail
     */
    public function setSenderEmail($senderEmail)
    {
        $this->senderEmail = $senderEmail;
    }

    /**
     * @return mixed
     */
    public function getSenderOrganization()
    {
        return $this->senderOrganization;
    }

    /**
     * @param mixed $senderOrganization
     */
    public function setSenderOrganization($senderOrganization)
    {
        $this->senderOrganization = $senderOrganization;
    }

    /**
     * @return mixed
     */
    public function getRecipientIdOrGroup()
    {
        return $this->recipientIdOrGroup;
    }

    /**
     * @param mixed $recipientIdOrGroup
     */
    public function setRecipientIdOrGroup($recipientIdOrGroup)
    {
        $this->recipientIdOrGroup = $recipientIdOrGroup;
    }

    /**
     * @return mixed
     */
    public function getRecipientEmailAddresses()
    {
        return $this->recipientEmailAddresses;
    }

    /**
     * @param mixed $recipientEmailAddresses
     */
    public function setRecipientEmailAddresses($recipientEmailAddresses)
    {
        $this->recipientEmailAddresses = $recipientEmailAddresses;
    }

    /**
     * @return mixed
     */
    public function getRecipientPhoneNumbers()
    {
        return $this->recipientPhoneNumbers;
    }

    /**
     * @param mixed $recipientPhoneNumbers
     */
    public function setRecipientPhoneNumbers($recipientPhoneNumbers)
    {
        $this->recipientPhoneNumbers = $recipientPhoneNumbers;
    }

    /**
     * @return mixed
     */
    public function getRecipientName()
    {
        return $this->recipientName;
    }

    /**
     * @param mixed $recipientName
     */
    public function setRecipientName($recipientName)
    {
        $this->recipientName = $recipientName;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param mixed $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getSmsNotificationSender()
    {
        return $this->smsNotificationSender;
    }

    /**
     * @param mixed $smsNotificationSender
     */
    public function setSmsNotificationSender($smsNotificationSender)
    {
        $this->smsNotificationSender = $smsNotificationSender;
    }

    /**
     * @return mixed
     */
    public function getSmsNotificationMessage()
    {
        return $this->smsNotificationMessage;
    }

    /**
     * @param mixed $smsNotificationMessage
     */
    public function setSmsNotificationMessage($smsNotificationMessage)
    {
        $this->smsNotificationMessage = $smsNotificationMessage;
    }

    /**
     * @return mixed
     */
    public function getEmailSendStatus()
    {
        return $this->emailSendStatus;
    }

    /**
     * @param mixed $emailSendStatus
     */
    public function setEmailSendStatus($emailSendStatus)
    {
        $this->emailSendStatus = $emailSendStatus;
    }

    /**
     * @return mixed
     */
    public function getSmsSendStatus()
    {
        return $this->smsSendStatus;
    }

    /**
     * @param mixed $smsSendStatus
     */
    public function setSmsSendStatus($smsSendStatus)
    {
        $this->smsSendStatus = $smsSendStatus;
    }

    /**
     * @return mixed
     */
    public function getEmailDeliveryStatus()
    {
        return $this->emailDeliveryStatus;
    }

    /**
     * @param mixed $emailDeliveryStatus
     */
    public function setEmailDeliveryStatus($emailDeliveryStatus)
    {
        $this->emailDeliveryStatus = $emailDeliveryStatus;
    }

    /**
     * @return mixed
     */
    public function getSmsDeliveryStatus()
    {
        return $this->smsDeliveryStatus;
    }

    /**
     * @param mixed $smsDeliveryStatus
     */
    public function setSmsDeliveryStatus($smsDeliveryStatus)
    {
        $this->smsDeliveryStatus = $smsDeliveryStatus;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getCreatedByUserId()
    {
        return $this->createdByUserId;
    }

    /**
     * @param mixed $createdByUserId
     */
    public function setCreatedByUserId($createdByUserId)
    {
        $this->createdByUserId = $createdByUserId;
    }

    /**
     * @return mixed
     */
    public function getGuid()
    {
        return $this->guid;
    }

    /**
     * @param mixed $guid
     */
    public function setGuid($guid)
    {
        $this->guid = $guid;
    }

    /**
     * @return mixed
     */
    public function getDisplaySerialNo()
    {
        return $this->displaySerialNo;
    }

    /**
     * @param mixed $displaySerialNo
     */
    public function setDisplaySerialNo($displaySerialNo)
    {
        $this->displaySerialNo = $displaySerialNo;
    }

}