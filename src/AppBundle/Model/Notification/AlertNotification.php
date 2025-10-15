<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/25/2016
 * Time: 5:00 PM
 */

namespace AppBundle\Model\Notification;


use AppBundle\Utils\AppConstants;

class AlertNotification
{
    private $type = 'undefined';
    private $messages = array();

    /**
     * Notification constructor.
     */
    public function __construct()
    {
    }

    public function hasMessage()
    {
        return !empty($this->messages);
    }

    public function addSuccess($message)
    {
        if (strcmp($this->type, AppConstants::ALERT_SUCCESS) !== 0) {
            $this->type = AppConstants::ALERT_SUCCESS;
        }
        $this->messages[] = $message;
    }

    public function addInfo($message)
    {
        if (strcmp($this->type, AppConstants::ALERT_INFO) !== 0) {
            $this->type = AppConstants::ALERT_INFO;
        }
        $this->messages[] = $message;
    }

    public function addWarning($message)
    {
        if (strcmp($this->type, AppConstants::ALERT_WARNING) !== 0) {
            $this->type = AppConstants::ALERT_WARNING;
        }
        $this->messages[] = $message;
    }

    public function addError($message)
    {
        if (strcmp($this->type, AppConstants::ALERT_DANGER) !== 0) {
            $this->type = AppConstants::ALERT_DANGER;
        }
        $this->messages[] = $message;
    }

    public function clear()
    {
        $this->messages = array();
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param array $messages
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }


}