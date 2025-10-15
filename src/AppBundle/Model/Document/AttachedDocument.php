<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 4/25/2017
 * Time: 12:01 PM
 */

namespace AppBundle\Model\Document;


use Symfony\Component\HttpFoundation\File\UploadedFile;

class AttachedDocument
{
    private $title;
    private $fileName;
    private $previewUrl;
    private $recordStatus;
    private $created, $createdByUserId;
    private $lastModified, $lastModifiedByUserId, $lastModifiedByUserName;
    private $selector;
    private $pdfFile = false, $imageFile = false, $videoFile = false;

    /**
     * @var UploadedFile
     */
    private $uploadedFile;

    /**
     * AttachedDocument constructor.
     * @param $title
     * @param $fileName
     */
    public function __construct($title, $fileName)
    {
        $this->title = $title;
        $this->fileName = $fileName;

        $this->resolveFileType();
    }

    private function resolveFileType()
    {
        $extension = substr($this->fileName, strripos($this->fileName, '.')+1);
        switch (strtolower($extension)) {
            case 'pdf':
                $this->pdfFile = true;
                break;

            case 'jpg':
            case 'png':
                $this->imageFile = true;
                break;

            case 'mp4':
                $this->videoFile = true;
                break;
        }
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param mixed $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * @return UploadedFile
     */
    public function getUploadedFile():? UploadedFile
    {
        return $this->uploadedFile;
    }

    /**
     * @param UploadedFile $uploadedFile
     */
    public function setUploadedFile($uploadedFile)
    {
        $this->uploadedFile = $uploadedFile;
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
    public function getLastModifiedByUserName()
    {
        return $this->lastModifiedByUserName;
    }

    /**
     * @param mixed $lastModifiedByUserName
     */
    public function setLastModifiedByUserName($lastModifiedByUserName)
    {
        $this->lastModifiedByUserName = $lastModifiedByUserName;
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

    /**
     * @return mixed
     */
    public function getPreviewUrl()
    {
        return $this->previewUrl;
    }

    /**
     * @param mixed $previewUrl
     */
    public function setPreviewUrl($previewUrl)
    {
        $this->previewUrl = $previewUrl;
    }

    /**
     * @return bool
     */
    public function isPdfFile(): bool
    {
        return $this->pdfFile;
    }

    /**
     * @return bool
     */
    public function isImageFile(): bool
    {
        return $this->imageFile;
    }

    /**
     * @return bool
     */
    public function isVideoFile(): bool
    {
        return $this->videoFile;
    }

}