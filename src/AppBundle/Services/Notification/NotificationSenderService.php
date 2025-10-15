<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 2:11 PM
 */

namespace AppBundle\Services\Notification;

use AppBundle\Model\Notification\Notification;
use AppBundle\Model\Notification\RecipientsContactInfo;
use AppBundle\Model\Notification\SendNotificationResponse;
use AppBundle\Utils\ApiKeys;
use GuzzleHttp\Client;
use SendGrid\Content;
use SendGrid\Email;
use SendGrid\Mail;
use SendGrid\Personalization;
use \Swift_Mailer;
use \Throwable;

class NotificationSenderService
{
    private $mailer;

    public function __construct(Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendSms($message, $sender, $destinations): array
    {

        $responseData = array();

        $postData = array(
            'cmd' => 'sendquickmsg',
            'owneremail' => 'saleahmadu@gmail.com',
            'subacct' => 'FEDERAL-CC',
            'subacctpwd' => 'Federal@1#',
            'message' => $message,
            'sender' => $sender,
            'sendto' => $destinations,
            'msgtype' => '0'
        );

        try {
            $guzzleClient = new Client();
            $response = $guzzleClient->request(
                'POST'
                , 'http://www.smslive247.com/http/index.aspx'
                , array('query' => $postData));

            $code = $response->getStatusCode();
            $body = $response->getBody();

            $stringBody = (string)$body;

            $responseData['responseCode'] = $code;
            $responseData['responseBody'] = $stringBody;
        } catch (Throwable $t) {
        }
        return $responseData;
    }

    public function sendNotification(Notification $notification,RecipientsContactInfo $recipientsContactInfo): SendNotificationResponse
    {

        $notificationResponse = new SendNotificationResponse();

        if($recipientsContactInfo){

            if($recipientsContactInfo->getRecipientsPhoneNumbers()){
                //SEND SMS MESSAGE
                $postData = array(
                    'cmd' => 'sendquickmsg',
                    'owneremail' => 'saleahmadu@gmail.com',
                    'subacct' => 'FEDERAL-CC',
                    'subacctpwd' => 'Federal@1#',
                    'message' => $notification->getSmsNotificationMessage(),
                    'sender' => $notification->getSmsNotificationSender(),
                    'sendto' => $recipientsContactInfo->getRecipientsPhoneNumbers(),
                    'msgtype' => '0'
                );

                try {
                    $guzzleClient = new Client();
                    $response = $guzzleClient->request(
                        'POST'
                        , 'http://www.smslive247.com/http/index.aspx'
                        , array('query' => $postData));

                    $sendSmsResponseCode = $response->getStatusCode();
                    $sendSmsResponseBody = (string)$response->getBody();

                    $notificationResponse->setSmsHttpResponseCode($sendSmsResponseCode);
                    $notificationResponse->setSmsHttpResponseBody($sendSmsResponseBody);
                } catch (Throwable $t) {
                }
            }

            //SEND THE EMAIL
            if($recipientsContactInfo->getRecipientsEmailAddresses()){
                try{
                    $senderName = $notification->getSenderName();
                    $senderOrganization = $notification->getSenderOrganization();

                    $fromSenderName = $senderOrganization . ((empty ($senderName) ? '' : (' (' . $senderName . ')')));
                    $fromSenderEmail = $notification->getSenderEmail();

                    $from = new Email($fromSenderName, $fromSenderEmail);
                    $subject = $notification->getSubject();
                    $to = new Email("FCC Portal Admin", "saleahmadu@gmail.com");
                    $content = new Content("text/html", $notification->getMessage());
                    $mail = new Mail($from, $subject, $to, $content);


                    foreach ($recipientsContactInfo->getRecipientsEmailAddresses() as $recipientEmailAddress)
                    {
                        $personalization = new Personalization();
                        $email = new Email($recipientEmailAddress['contact_name'], $recipientEmailAddress['contact_email']);
                        $personalization->addTo($email);
                        $mail->addPersonalization($personalization);
                    }



                    $apiKey = ApiKeys::SENDGRIG_API_KEY;
                    $sg = new \SendGrid($apiKey);

                    $response = $sg->client->mail()->send()->post($mail);
                    $notificationResponse->setEmailHttpResponseCode($response->statusCode());
                    $notificationResponse->setEmailHttpResponseBody($response->body());

                }catch(Throwable $t){

                }
            }


            /*if($recipientsContactInfo->getRecipientsEmailAddresses()){
                try {
                    // Create a message
                    $message = (new \Swift_Message($notification->getSubject()))
                        ->setFrom('no-reply@federalcharacter.gov.ng')
                        ->setBody(
                            $notification->getMessage(),
                            'text/html'
                        );

                    // Send the message
                    $failedRecipients = [];
                    $numSent = 0;
                    foreach ($recipientsContactInfo->getRecipientsEmailAddresses() as $recipientEmailAddress)
                    {
                        $message->setTo($recipientEmailAddress);
                        $numSent += $this->mailer->send($message, $failedRecipients);
                    }

                    if($numSent){

                    }
                } catch (Throwable $t) {
                }
            }*/
        }

        return $notificationResponse;

    }

}