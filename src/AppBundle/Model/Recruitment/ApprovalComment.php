<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 2/21/2017
 * Time: 3:02 PM
 */

namespace AppBundle\Model\Recruitment;


use AppBundle\Model\Document\AttachedDocument;

class ApprovalComment
{

    private $id;
    private $contextId, $contextSelector;
    private $fromUserId, $fromUserName;
    private $comment;
    private $toUserId, $toUserName;
    private $recordStatus;
    private $created, $createdByUserId;
    private $lastModified, $lastModifiedByUserId;
    private $selector;

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
    public function getContextId()
    {
        return $this->contextId;
    }

    /**
     * @param mixed $contextId
     */
    public function setContextId($contextId)
    {
        $this->contextId = $contextId;
    }

    /**
     * @return mixed
     */
    public function getContextSelector()
    {
        return $this->contextSelector;
    }

    /**
     * @param mixed $contextSelector
     */
    public function setContextSelector($contextSelector)
    {
        $this->contextSelector = $contextSelector;
    }

    /**
     * @return mixed
     */
    public function getFromUserId()
    {
        return $this->fromUserId;
    }

    /**
     * @param mixed $fromUserId
     */
    public function setFromUserId($fromUserId)
    {
        $this->fromUserId = $fromUserId;
    }

    /**
     * @return mixed
     */
    public function getFromUserName()
    {
        return $this->fromUserName;
    }

    /**
     * @param mixed $fromUserName
     */
    public function setFromUserName($fromUserName)
    {
        $this->fromUserName = $fromUserName;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return mixed
     */
    public function getToUserId()
    {
        return $this->toUserId;
    }

    /**
     * @param mixed $toUserId
     */
    public function setToUserId($toUserId)
    {
        $this->toUserId = $toUserId;
    }

    /**
     * @return mixed
     */
    public function getToUserName()
    {
        return $this->toUserName;
    }

    /**
     * @param mixed $toUserName
     */
    public function setToUserName($toUserName)
    {
        $this->toUserName = $toUserName;
    }

    /**
     * @return mixed
     */
    public function getRecordStatus()
    {
        return $this->recordStatus;
    }

    /**
     * @param mixed $recordStatus
     */
    public function setRecordStatus($recordStatus)
    {
        $this->recordStatus = $recordStatus;
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
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * @param mixed $lastModified
     */
    public function setLastModified($lastModified)
    {
        $this->lastModified = $lastModified;
    }

    /**
     * @return mixed
     */
    public function getLastModifiedByUserId()
    {
        return $this->lastModifiedByUserId;
    }

    /**
     * @param mixed $lastModifiedByUserId
     */
    public function setLastModifiedByUserId($lastModifiedByUserId)
    {
        $this->lastModifiedByUserId = $lastModifiedByUserId;
    }

    /**
     * @return mixed
     */
    public function getSelector()
    {
        return $this->selector;
    }

    /**
     * @param mixed $selector
     */
    public function setSelector($selector)
    {
        $this->selector = $selector;
    }

}