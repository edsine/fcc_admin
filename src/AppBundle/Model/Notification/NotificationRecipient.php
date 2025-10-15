<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 9/11/2017
 * Time: 1:21 PM
 */

namespace AppBundle\Model\Notification;


class NotificationRecipient
{

    private $emailAddresses;
    private $emailName;
    private $phoneNumbers;
    private $firstName;
    private $lastName;
    private $organizationName;

    /**
     * NotificationRecipient constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getEmailAddresses()
    {
        return $this->emailAddresses;
    }

    /**
     * @param mixed $emailAddresses
     */
    public function setEmailAddresses($emailAddresses)
    {
        $this->emailAddresses = $emailAddresses;
    }

    /**
     * @return mixed
     */
    public function getEmailName()
    {
        return $this->emailName;
    }

    /**
     * @param mixed $emailName
     */
    public function setEmailName($emailName)
    {
        $this->emailName = $emailName;
    }

    /**
     * @return mixed
     */
    public function getPhoneNumbers()
    {
        return $this->phoneNumbers;
    }

    /**
     * @param mixed $phoneNumbers
     */
    public function setPhoneNumbers($phoneNumbers)
    {
        $this->phoneNumbers = $phoneNumbers;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return mixed
     */
    public function getOrganizationName()
    {
        return $this->organizationName;
    }

    /**
     * @param mixed $organizationName
     */
    public function setOrganizationName($organizationName)
    {
        $this->organizationName = $organizationName;
    }
}