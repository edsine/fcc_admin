<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/20/2017
 * Time: 11:50 AM
 */

namespace AppBundle\Model\Notification;


class GroupNotification
{
    private $groupRecipients;
    private $subject;
    private $message;

    private $created, $createdByUserId;

    /**
     * SendSubmissionNoticeController constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getGroupRecipients()
    {
        return $this->groupRecipients;
    }

    /**
     * @param mixed $groupRecipients
     */
    public function setGroupRecipients($groupRecipients)
    {
        $this->groupRecipients = $groupRecipients;
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
}