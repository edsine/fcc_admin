<?php
namespace AppBundle\Services\Notification;

use AppBundle\Model\Notification\Notification;
use AppBundle\Model\Notification\RecipientsContactInfo;
use AppBundle\Model\Notification\SendNotificationResponse;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Client;
use Throwable;

class NotificationSenderService
{
    private $smtpHost;
    private $smtpUser;
    private $smtpPassword;
    private $smtpPort;
    private $smtpEncryption;
    private $smsApiUrl;
    private $smsApiKey;
    private $smsSenderId;
    private $logger;

    public function __construct(
        string $smtpHost,
        string $smtpUser,
        string $smtpPassword,
        int $smtpPort,
        string $smtpEncryption,
        string $smsApiUrl,
        string $smsApiKey,
        string $smsSenderId,
        LoggerInterface $logger
    ) {
        $this->smtpHost = $smtpHost;
        $this->smtpUser = $smtpUser;
        $this->smtpPassword = $smtpPassword;
        $this->smtpPort = $smtpPort;
        $this->smtpEncryption = $smtpEncryption;
        $this->smsApiUrl = $smsApiUrl;
        $this->smsApiKey = $smsApiKey;
        $this->smsSenderId = $smsSenderId;
        $this->logger = $logger;
    }

    public function sendNotification(Notification $notification, RecipientsContactInfo $recipientsContactInfo): SendNotificationResponse
    {
        $notificationResponse = new SendNotificationResponse();

        // ✅ SEND SMS (new API)
      $rawPhoneNumbers = $recipientsContactInfo->getRecipientsPhoneNumbers();

if (is_string($rawPhoneNumbers)) {
    $phoneNumbers = array_filter(array_map('trim', explode(",", $rawPhoneNumbers)));
} elseif (is_array($rawPhoneNumbers)) {
    $phoneNumbers = array_map('trim', $rawPhoneNumbers);
} else {
    $phoneNumbers = [];
}

if (!empty($phoneNumbers)) {
    try {
        $client = new Client();

        foreach ($phoneNumbers as $phoneNumber) {
            $payload = [
                'senderID'     => $this->smsSenderId,
                'mobileNumber' => $phoneNumber,
                'messageText'  => $notification->getSmsNotificationMessage(),
            ];

            $response = $client->request('POST', $this->smsApiUrl, [
                'headers' => [
                    'Authorization' => $this->smsApiKey,
                    'accept'        => 'application/json',
                    'content-type'  => 'application/*+json',
                ],
                'body' => json_encode($payload),
            ]);

            $notificationResponse->setSmsHttpResponseCode($response->getStatusCode());
            $notificationResponse->setSmsHttpResponseBody($response->getBody()->getContents());
        }
    } catch (Throwable $t) {
        $this->logger->error('SMS sending failed: ' . $t->getMessage());
    }
}


        // ✅ SEND EMAIL
        if ($recipientsContactInfo && $recipientsContactInfo->getRecipientsEmailAddresses()) {
            foreach ($recipientsContactInfo->getRecipientsEmailAddresses() as $recipient) {
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = $this->smtpHost;
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $this->smtpUser;
                    $mail->Password   = $this->smtpPassword;
                    $mail->SMTPSecure = $this->smtpEncryption;
                    $mail->Port       = $this->smtpPort;

                    $mail->setFrom("superadmin@niwaoptima.com.ng", "FCC Portal");
                    $mail->addReplyTo('no-reply@federalcharacter.gov.ng');

                    if (is_array($recipient) && isset($recipient['contact_email'])) {
                        $mail->addAddress(trim($recipient['contact_email']));
                    } elseif (is_string($recipient)) {
                        $mail->addAddress(trim($recipient));
                    }

                    $mail->isHTML(true);
                    $mail->Subject = $notification->getSubject();
                    $mail->Body    = $notification->getMessage();
                    $mail->AltBody = strip_tags($notification->getMessage());

                    $mail->send();
                } catch (\Throwable $e) {
                    $this->logger->error('PHPMailer error: ' . $e->getMessage());
                }
            }
        }

        return $notificationResponse;
    }
}
