<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 2/21/2017
 * Time: 2:39 PM
 */

namespace AppBundle\Model\StaticCMS;


class StaticCMS
{

    private $contentCode;
    private $description;
    private $content;
    private $richText;
    private $lastModified;
    private $lastModifiedByUserId;

    private $displaySerialNo;

    private $guid;

    /**
     * StaticCMS constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getContentCode()
    {
        return $this->contentCode;
    }

    /**
     * @param mixed $contentCode
     */
    public function setContentCode($contentCode)
    {
        $this->contentCode = $contentCode;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getRichText()
    {
        return $this->richText;
    }

    /**
     * @param mixed $richText
     */
    public function setRichText($richText)
    {
        $this->richText = $richText;
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

}