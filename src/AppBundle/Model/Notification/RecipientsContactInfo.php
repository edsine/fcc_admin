<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 9/11/2017
 * Time: 2:25 PM
 */

namespace AppBundle\Model\Notification;


class RecipientsContactInfo
{

    private $recipientsEmailAddresses;
    private $recipientsPhoneNumbers;

    /**
     * RecipientsContactInfo constructor.
     */
    public function __construct()
    {
        $this->recipientsEmailAddresses = array();
        $this->recipientsPhoneNumbers = array();
    }

    /**
     * @return mixed
     */
    public function getRecipientsEmailAddresses()
    {
        return $this->recipientsEmailAddresses;
    }

    /**
     * @param mixed $recipientsEmailAddresses
     */
    public function setRecipientsEmailAddresses($recipientsEmailAddresses)
    {
        $this->recipientsEmailAddresses = $recipientsEmailAddresses;
    }

    /**
     * @return mixed
     */
    public function getRecipientsPhoneNumbers()
    {
        return $this->recipientsPhoneNumbers;
    }

    /**
     * @param mixed $recipientsPhoneNumbers
     */
    public function setRecipientsPhoneNumbers($recipientsPhoneNumbers)
    {
        $this->recipientsPhoneNumbers = $recipientsPhoneNumbers;
    }

    public function appendSendersContactInformation($sendersName, $sendersEmail, $sendersPhoneNumber){
        if($sendersPhoneNumber){
            $this->recipientsPhoneNumbers = $this->recipientsPhoneNumbers . ',' . $sendersPhoneNumber;
        }

        if($sendersName && $sendersEmail){
            $recipientEmail = array();
            $recipientEmail['contact_email'] = $sendersEmail;
            $recipientEmail['contact_name'] = $sendersName;

            $this->recipientsEmailAddresses[] = $recipientEmail;
        }

    }


}