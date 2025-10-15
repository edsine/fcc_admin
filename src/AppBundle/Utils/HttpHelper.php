<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 2/13/2017
 * Time: 9:16 PM
 */

namespace AppBundle\Utils;


use GuzzleHttp\Client;

class HttpHelper
{
    const PROXY_PROD_BASE_URL = 'http://federalcharacter.net';
    const PROXY_DEV_BASE_URL = 'http://localhost/federal-character-proxy/web/app_dev.php';


    //*******************************************PROXY STUFFS*******************************

    public static function pingMiddleWare() : int
    {
        $pingResponseCode = 0;
        $pingUrl = null;

        $serverName = $_SERVER['SERVER_NAME'];
        if($serverName !== 'localhost'){
            $pingUrl = self::PROXY_PROD_BASE_URL . '/proxy/ping/middleware';
        }else{
            $pingUrl = self::PROXY_DEV_BASE_URL  . '/proxy/ping/middleware';
        }

        try {
            $guzzleClient = new Client();
            $response = $guzzleClient->request('GET', $pingUrl, []);
            $pingResponseCode = $response->getStatusCode();

        } catch (\Throwable $t) {
        }

        return $pingResponseCode;
    }

    public static function getProxyFederalNominalRollStageAndValidateSchedulerUrl(){
        $serverName = $_SERVER['SERVER_NAME'];
        if($serverName !== 'localhost'){
            return self::PROXY_PROD_BASE_URL . '/proxy/federal/nominal_roll/submission/stage_and_validate/scheduler';
        }else{
            return self::PROXY_DEV_BASE_URL  . '/proxy/federal/nominal_roll/submission/stage_and_validate/scheduler';
        }
    }

    public static function getProxyFederalNominalRollBaseReportAnalysisSchedulerUrl(){

        $serverName = $_SERVER['SERVER_NAME'];
        if($serverName !== 'localhost'){
            return self::PROXY_PROD_BASE_URL . '/proxy/federal/nominal_roll/base_report_analysis/scheduler';
        }else{
            return self::PROXY_DEV_BASE_URL  . '/proxy/federal/nominal_roll/base_report_analysis/scheduler';
        }

    }

    public static function getProxyFederalNominalRollBatchReValidationSchedulerUrl(){

        $serverName = $_SERVER['SERVER_NAME'];
        if($serverName !== 'localhost'){
            return self::PROXY_PROD_BASE_URL . '/proxy/federal/nominal_roll/batch_re_validation/scheduler';
        }else{
            return self::PROXY_DEV_BASE_URL  . '/proxy/federal/nominal_roll/batch_re_validation/scheduler';
        }

    }

}