<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/22/2017
 * Time: 7:44 AM
 */

namespace AppBundle\Controller;


use AppBundle\AppException\AppException;
use AppBundle\Model\Users\UserProfile;
use AppBundle\Utils\HttpHelper;
use GuzzleHttp\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bridge\Doctrine\Tests\Fixtures\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MiscController extends Controller
{

    /**
     * @Route("/app", name="app")
     */
    public function mobileApp(Request $request)
    {
        return $this->render('app.html.twig');
    }

    /**
     * @Route("/misc/fix_passwords", name="fix_passwords")
     */
    public function fixPasswordsAction(Request $request)
    {
        $users = $this->get('app.user_profile_service')->fixUserProfile($this->get('security.password_encoder'), $this->get('logger'));

        $userProfile = new UserProfile();
        $encoder = $this->get('security.password_encoder');
        //$encryptedPassword = $encoder->encodePassword($userProfile, 'fcc@#@sys#$#admin*');
        //$encryptedPassword = $encoder->encodePassword($userProfile, 'demopassword');
        //return new Response("Hi there:" . $encryptedPassword);

        return new Response("Hi there!");
    }

    /**
     * @Route("/misc/test_sms", name="test_sms")
     */
    public function testStuffAction(Request $request)
    {

        $guzzleClient = new Client();

        /*nameValuePairs.add(new BasicNameValuePair("cmd", "sendquickmsg"));
        nameValuePairs.add(new BasicNameValuePair("owneremail", "foreignreg@nysc.gov.ng"));
        nameValuePairs.add(new BasicNameValuePair("subacct", "NYSC STORES"));
        nameValuePairs.add(new BasicNameValuePair("subacctpwd", "n78astoe879a"));

        nameValuePairs.add(new BasicNameValuePair("message", message));
        nameValuePairs.add(new BasicNameValuePair("sender", sender));
        nameValuePairs.add(new BasicNameValuePair("sendto", destinations));
        nameValuePairs.add(new BasicNameValuePair("msgtype", "0"));*/

        $sender = 'FCC';
        $message = 'Hi there';
        $destinations = '07039689961,08154993348';

        $postData = array(
            'cmd' => 'sendquickmsg',
            'owneremail' => 'foreignreg@nysc.gov.ng',
            'subacct' => 'NYSC STORES',
            'subacctpwd' => 'n78astoe879a',
            'message' => $message,
            'sender' => $sender,
            'sendto' => $destinations,
            'msgtype' => '0'
        );

        try {
            $response = $guzzleClient->request(
                'POST'
                , 'http://www.smslive247.com/http/index.aspx'
                , array('query' => $postData));

            //http://www.smslive247.com/http/index.aspx
            //http://localhost/federal-character/web/app_dev.php/misc/test_sms

            $code = $response->getStatusCode();
            $body = $response->getBody();

            $stringBody = (string)$body;

            if ($code != 200) {
                $stringBody = "something went wrong";
            }
        } catch (\Throwable $t) {
            return new Response($t->getMessage());
        }


        return new Response($stringBody);
    }

    /**
     * @Route("/misc/ping_server2", name="ping_Server2")
     */
    public function checkContact(Request $request)
    {
        $internalServerError = new Response();
        $internalServerError->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $errorMessage = '';

        $submissionId = $request->request->get('submissionId');

        try {
            $guzzleClient = new Client();
            $response = $guzzleClient->request('GET', 'http://128.127.67.154:8282/fcc-job-scheduling/', [
            ]);

            $code = $response->getStatusCode();
            $body = $response->getBody();

            $stringBody = (string)$body;

            if ($code == 200) {
                $successResponse = new JsonResponse();
                $successResponse->setData(array(
                    'validationRequestStatus' => 'OK2',
                    'validationRequestMessage' => $stringBody
                ));
                return $successResponse;
            } else {
                $errorMessage = 'No Connect: Validation Job';
            }
        } catch (\Throwable $t) {
            $errorMessage = "An Error Ocurred: " . $t->getMessage();
        }

        $internalServerError->setContent($errorMessage);
        return $internalServerError;
    }

    /**
     * @Route("/misc/test_sms2", name="test_sms2")
     */
    public function testStuff2Action(Request $request)
    {
        return new Response($request->request->get('cmd', "no CMD received"));
    }



}