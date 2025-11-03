<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/23/2017
 * Time: 4:57 AM
 */

namespace AppBundle\Controller\Notification;


use AppBundle\Model\Notification\AlertNotification;
use AppBundle\Model\Notification\Notification;
use AppBundle\Model\Notification\RecipientsContactInfo;
use AppBundle\Model\Users\UserProfile;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\GUIDHelper;
use AppBundle\Validation\Validator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SendSingleNotificationController extends Controller
{

    /**
     * @Route(
     *     "/secure_area/notification/send_single_notification/{recipientUserId}/{phone}/{email}/{name}"
     * ,name="send_single_recipient_notification"
     * ,defaults={"phone":"000-000-0000","email":"abc@local.domain","name":"No Name"}
     *     )
     */
    public function sendSingleNotificationAction(Request $request, $recipientUserId, $phone, $email, $name)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var UserProfile $loggedInUser
         */
        $loggedInUser = $this->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        $notification = null;

        $validator = new Validator();
        $this->initializeValidationFields($validator);

        $alertNotification = new AlertNotification();
        $outcome = false;

        if (!$request->request->has("btnSend")) {

            $notification = new Notification();
            $notification->setRecipientIdOrGroup($recipientUserId);
            $notification->setRecipientName($name);
            $notification->setRecipientPhoneNumbers($phone);
            $notification->setRecipientEmailAddresses($email);

        } elseif ($request->request->has("btnSend")) {

            $notification = $this->fillModelFromRequest($request);
            $this->validateForm($validator, $notification);

            try {
                if (!$validator->getFields()->hasErrors()) {

                    $notificationService = $this->get('app.notification_service');
                    $notificationSenderService = $this->get('app.notification_sender_service');

                    $today = date("Y-m-d H:i:s");

                    $notification->setSenderId($loggedInUser->getId());
                    $notification->setSenderOrganization($loggedInUser->getOrganizationName());
                    $notification->setSenderName($loggedInUser->getDisplayName());
                    $notification->setSenderEmail($loggedInUser->getEmailAddress());

                    $notification->setCreated($today);
                    $notification->setCreatedByUserId($loggedInUser->getId());

                    $guidHelper = new GUIDHelper();
                    $notification->setGuid($guidHelper->getGUIDUsingMD5($loggedInUser->getUsername()));

                    if (!$notification->getSmsNotificationMessage()) {
                        $notification->setSmsNotificationMessage($loggedInUser->getOrganizationName() . ' ' . AppConstants::DEFAULT_SMS_NOTIFICATION_SUFFIX);
                    }

                    $addNotificationOutcome = $notificationService->addNotification($notification);

                    if ($addNotificationOutcome) {
                        try {

                            $recipientsContactInfo = new RecipientsContactInfo();
                            $recipientsContactInfo->setRecipientsPhoneNumbers($notification->getRecipientPhoneNumbers());

                            $recipientsEmailAddresses = array();

                            $emailAddresses = explode(",", $notification->getRecipientEmailAddresses());
                            if ($emailAddresses) {
                                foreach ($emailAddresses as $emailAddress) {
                                    $recipientEmail = array();
                                    $recipientEmail['contact_email'] = $emailAddress;
                                    $recipientEmail['contact_name'] = $notification->getRecipientName();

                                    $recipientsEmailAddresses[] = $recipientEmail;
                                }

                                if ($recipientsEmailAddresses) {
                                    $recipientsContactInfo->setRecipientsEmailAddresses($recipientsEmailAddresses);
                                }
                            }

                            //inject the senders contact information also to recipients information
                            /*$recipientsContactInfo->appendSendersContactInformation(
                                $loggedInUser->getDisplayName()
                                , $loggedInUser->getEmailAddress()
                                , $loggedInUser->getPrimaryPhone()
                            );*/

                            $notification->setSmsNotificationSender(AppConstants::FCC_SMS_SENDER);

                            $sendResponse = $notificationSenderService->sendNotification($notification, $recipientsContactInfo);
                            if ($sendResponse) {
                                $smsSentForDelivery = ($sendResponse->getSmsHttpResponseCode() === 200)
                                    && (strpos($sendResponse->getSmsHttpResponseBody(), 'OK:') === 0);

                                $emailSentForDelivery = $sendResponse->getEmailHttpResponseCode() === 202;

                                if ($smsSentForDelivery || $emailSentForDelivery) {
                                    //move this later and see how it goes
                                    return $this->redirectToRoute('notification_success');
                                } else {
                                    $alertNotification->addError("Your Message Could Not Be Sent At The Moment, Please Try Again Later.");
                                }


                            } else {
                                $alertNotification->addError("Your Message Could Not Be Sent At The Moment, Please Try Again Later.");
                            }

                        } catch (\Throwable $st) {
    $this->get('logger')->error('Error while sending email: ' . $st->getMessage(), [
        'trace' => $st->getTraceAsString()
    ]);
    $alertNotification->addError("Error sending email: " . $st->getMessage());
}

                        //this should go later
                        return $this->redirectToRoute('notification_success');


                    } else {
                        $alertNotification->addError("Notification could not be sent at the moment, Please try again.");
                    }

                }
            } catch (\Throwable $t) {
                $this->get('logger')->info($t->getMessage());
                $alertNotification->addError("Notification could not be sent at the moment, Please try again.");
            }

        }

        if (!$notification) {
            $notification = new Notification();
        }

        return $this->render('secure_area/notification/send_single_recipient_notification.html.twig',
            array(
                'recipientUserId' => $recipientUserId,
                'notification' => $notification,
                'validator' => $validator,
                'outcome' => $outcome,
                'alertNotification' => $alertNotification
            ));
    }

    private function initializeValidationFields(Validator $validator)
    {
        $validator->getFields()->addField('recipient_id_or_group', "Invalid Recipient Id");
        $validator->getFields()->addField('recipient_phone', "Phone is required");
        $validator->getFields()->addField('recipient_email', "Email is required");
        $validator->getFields()->addField('recipient_name', "Name is required");
        $validator->getFields()->addField('subject', "Subject is required");
        $validator->getFields()->addField('message', "Message is required");
    }

    private function validateForm(Validator $validator, Notification $notification)
    {
        $validator->required('recipient_id_or_group', $notification->getRecipientIdOrGroup());
        $validator->required('recipient_phone', $notification->getRecipientPhoneNumbers());
        $validator->required('recipient_email', $notification->getRecipientEmailAddresses());
        //$validator->required('recipient_name', $notification->getRecipientName());
        $validator->required('subject', $notification->getSubject());
        $validator->required('message', $notification->getMessage());
    }

    private function fillModelFromRequest(Request $request)
    {
        $notification = new Notification();
        $notification->setRecipientIdOrGroup($request->request->get("recipient_id_or_group"));
        $notification->setRecipientPhoneNumbers($request->request->get("recipient_phone"));
        $notification->setRecipientEmailAddresses($request->request->get("recipient_email"));
        $notification->setRecipientName($request->request->get("recipient_name"));
        $notification->setSubject($request->request->get("subject"));
        $notification->setMessage($request->request->get("message"));
        $notification->setSmsNotificationMessage($request->request->get("sms_notification_message"));

        return $notification;
    }

}