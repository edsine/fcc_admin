<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/30/2017
 * Time: 3:44 AM
 */

namespace AppBundle\Model\Recruitment;


class FetchParam
{
    private $fetchAll = false;
    private $fetchCocComments = false;

    /**
     * FetchParam constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return bool
     */
    public function isFetchAll(): bool
    {
        return $this->fetchAll;
    }

    /**
     * @param bool $fetchAll
     */
    public function setFetchAll(bool $fetchAll)
    {
        $this->fetchAll = $fetchAll;
    }

    /**
     * @return bool
     */
    public function isFetchCocComments(): bool
    {
        return $this->fetchCocComments;
    }

    /**
     * @param bool $fetchCocComments
     */
    public function setFetchCocComments(bool $fetchCocComments)
    {
        $this->fetchCocComments = $fetchCocComments;
    }



}