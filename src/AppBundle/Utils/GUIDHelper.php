<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/20/2016
 * Time: 6:24 PM
 */

namespace AppBundle\Utils;


class GUIDHelper
{

    /**
     * GUIDHelper constructor.
     */
    public function __construct()
    {
    }

    public function getGUIDUsingMD5($prefix = ''){
        return md5(time() . mt_rand(1,1000000));
    }
}