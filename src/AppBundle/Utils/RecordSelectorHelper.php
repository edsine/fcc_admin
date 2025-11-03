<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 5/14/2017
 * Time: 11:18 PM
 */

namespace AppBundle\Utils;
use ParagonIE\ConstantTime\Base64UrlSafe;


class RecordSelectorHelper
{

    function generateSelector(): string
    {
        return Base64UrlSafe::encode(random_bytes(9));
    }

}