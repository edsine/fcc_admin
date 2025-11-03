<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 2/21/2017
 * Time: 11:23 AM
 */

namespace AppBundle\Model\Security;


class SystemPrivilege
{

    private $id;
    private $description;
    private $reserved;

    /**
     * SystemPrivilege constructor.
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
    public function getReserved()
    {
        return $this->reserved;
    }

    /**
     * @param mixed $reserved
     */
    public function setReserved($reserved)
    {
        $this->reserved = $reserved;
    }
}