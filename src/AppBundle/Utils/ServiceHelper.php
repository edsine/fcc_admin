<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/24/2016
 * Time: 2:57 PM
 */

namespace AppBundle\Utils;


class ServiceHelper
{
    public function getValueOrNull($value)
    {
        if (!$value) {
            return null;
        } else {
            return $value;
        }
    }

    public function getBoolFromChar($char = 0)
    {
        if($char == '0' || $char == 'N' || !$char){
            return false;
        }else if($char == '1' || $char == 'Y'){
            return true;
        }else{
            return null;
        }
    }

    public function getValueOrZero($value)
    {
        if (!$value) {
            return 0;
        } else {
            return $value;
        }
    }

    public function getDateBetweenFormats($dateTimeString, $fromFormat, $toFormat)
    {
        if ($dateTimeString) {
            return \DateTime::createFromFormat($fromFormat, $dateTimeString)->format($toFormat);
        } else {
            return null;
        }
    }

    public function getDateAsFormatOrNull(\DateTime $dateTime, $format)
    {
        if ($dateTime) {
            return $dateTime->format($format);
        } else {
            return null;
        }
    }

    public function getMoneyValueOrNull($formattedValue)
    {
        if ($formattedValue) {
            return str_replace(',', '', $formattedValue);
        } else {
            return null;
        }
    }

    public function fix100PercentIssue($value){

        if($value == 0 || $value == 0.0 || $value == 0.00){
            return 0;
        }

        if($value > 100){
            return 100;
        }

        return $value;
    }

    function addOrdinalNumberSuffix($num) {
        if (!in_array(($num % 100),array(11,12,13))){
            switch ($num % 10) {
                // Handle 1st, 2nd, 3rd
                case 1:  return $num.'st';
                case 2:  return $num.'nd';
                case 3:  return $num.'rd';
            }
        }
        return $num.'th';
    }
}