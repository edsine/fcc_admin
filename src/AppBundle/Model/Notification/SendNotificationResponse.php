<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 9/11/2017
 * Time: 11:00 AM
 */

namespace AppBundle\Model\Notification;


class SendNotificationResponse
{

    private $smsHttpResponseCode = 0;
    private $smsHttpResponseBody;
    private $emailHttpResponseCode = 0;
    private $emailHttpResponseBody;

    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getSmsHttpResponseCode()
    {
        return $this->smsHttpResponseCode;
    }

    /**
     * @param mixed $smsHttpResponseCode
     */
    public function setSmsHttpResponseCode($smsHttpResponseCode)
    {
        $this->smsHttpResponseCode = $smsHttpResponseCode;
    }

    /**
     * @return mixed
     */
    public function getSmsHttpResponseBody()
    {
        return $this->smsHttpResponseBody;
    }

    /**
     * @param mixed $smsHttpResponseBody
     */
    public function setSmsHttpResponseBody($smsHttpResponseBody)
    {
        $this->smsHttpResponseBody = $smsHttpResponseBody;
    }

    /**
     * @return mixed
     */
    public function getEmailHttpResponseCode()
    {
        return $this->emailHttpResponseCode;
    }

    /**
     * @param mixed $emailHttpResponseCode
     */
    public function setEmailHttpResponseCode($emailHttpResponseCode)
    {
        $this->emailHttpResponseCode = $emailHttpResponseCode;
    }

    /**
     * @return mixed
     */
    public function getEmailHttpResponseBody()
    {
        return $this->emailHttpResponseBody;
    }

    /**
     * @param mixed $emailHttpResponseBody
     */
    public function setEmailHttpResponseBody($emailHttpResponseBody)
    {
        $this->emailHttpResponseBody = $emailHttpResponseBody;
    }

}