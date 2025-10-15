<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/3/2018
 * Time: 12:48 PM
 */

namespace AppBundle\Model\Download;


class DownloadResourceCategory
{

    private $id;
    private $title;
    /**
     * @var DownloadResource[]
     */
    private $downloads;

    /**
     * DownloadResourceCategory constructor.
     */
    public function __construct()
    {
        $this->downloads = array();
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
     * @return DownloadResource[]
     */
    public function getDownloads(): ?array
    {
        return $this->downloads;
    }

    /**
     * @param DownloadResource[] $downloads
     */
    public function setDownloads(array $downloads)
    {
        $this->downloads = $downloads;
    }

    public function addResource(DownloadResource $downloadResource)
    {
        $this->downloads[] = $downloadResource;
    }

}