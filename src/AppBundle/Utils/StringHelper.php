<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/6/2017
 * Time: 6:07 PM
 */

namespace AppBundle\Utils;


class StringHelper
{


    /**
     * StringHelper constructor.
     */
    public function __construct()
    {
    }

    public function substringBeforeLast($value, $character){
        return substr($value, 0, strripos($value, $character));
    }

    public function substringFromLast($value, $character){
        return substr($value, strripos($value, '.'));
    }

    public function substringAfterLast($value, $character){
        return substr($value, strripos($value, '.'));
    }



}