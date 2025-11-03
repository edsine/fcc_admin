<?php
namespace AppBundle\Controller\Notification;

use AppBundle\Model\Notification\AlertNotification;
use AppBundle\Model\Notification\Notification;
use AppBundle\Model\Notification\RecipientsContactInfo;
use AppBundle\Model\Users\UserProfile;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\GUIDHelper;
use AppBundle\Validation\Validator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OpenNotificationController extends Controller
{
    /**
     * @Route("/notification/open_notification", name="open_notification")
     */
    public function openNotificationAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /** @var UserProfile $loggedInUser */
        $loggedInUser = $this->getUser();

        $notification = new Notification();
        $validator = new Validator();
        $this->initializeValidationFields($validator);

        $alertNotification = new AlertNotification();
        $outcome = false;

        if ($request->request->has("btnSend")) {
            $notification = $this->fillModelFromRequest($request);
            $this->validateForm($validator, $notification);

            try {
                if (!$validator->getFields()->hasErrors()) {
                    $notificationService = $this->get('app.notification_service');
                    $notificationSenderService = $this->get('app.notification_sender_service');

                    // Populate notification data
                    $notification->setSenderId($loggedInUser->getId());
                    $notification->setSenderOrganization($loggedInUser->getOrganizationName());
                    $notification->setSenderName($loggedInUser->getDisplayName());
                    $notification->setSenderEmail($loggedInUser->getEmailAddress());
                    $notification->setRecipientIdOrGroup(AppConstants::OPEN_NOTIFICATION);
                    $notification->setCreated(date("Y-m-d H:i:s"));
                    $notification->setCreatedByUserId($loggedInUser->getId());

                   


                    $guidHelper = new GUIDHelper();
                    $notification->setGuid($guidHelper->getGUIDUsingMD5($loggedInUser->getUsername()));

                    if (!$notification->getSmsNotificationMessage()) {
                        $notification->setSmsNotificationMessage(AppConstants::DEFAULT_SMS_NOTIFICATION_MESSAGE);
                    }

                    $addNotificationOutcome = $notificationService->addNotification($notification);

                    if ($addNotificationOutcome) {
                        try {
                            $recipientsContactInfo = new RecipientsContactInfo();

                            // 📧 Parse and clean email addresses (PHPMailer expects strings)
                            $emailAddresses = array_filter(array_map('trim', explode(",", $notification->getRecipientEmailAddresses())));

                            if (!empty($emailAddresses)) {
                                $recipientsContactInfo->setRecipientsEmailAddresses($emailAddresses);
                            }

                            // Optionally set phone numbers if needed for SMS
                             $phonesRaw = $notification->getRecipientPhoneNumbers();
                             $recipientsContactInfo->setRecipientsPhoneNumbers($phonesRaw);

                             

                            // Add sender info as fallback
                            $recipientsContactInfo->appendSendersContactInformation(
                                $loggedInUser->getDisplayName(),
                                $loggedInUser->getEmailAddress(),
                                $loggedInUser->getPrimaryPhone()
                            );

                            $notification->setSmsNotificationSender(AppConstants::FCC_SMS_SENDER);

                            $sendResponse = $notificationSenderService->sendNotification($notification, $recipientsContactInfo);

                            if ($sendResponse) {
                                return $this->redirectToRoute('notification_success');
                            } else {
                                $alertNotification->addError("Your message could not be sent. Please try again later.");
                            }

                        } catch (\Throwable $e) {
                            $this->get('logger')->error('SendNotification Error: ' . $e->getMessage());
                            $alertNotification->addError("An unexpected error occurred while sending the notification.");
                        }
                    } else {
                        $alertNotification->addError("Notification could not be saved. Please try again.");
                    }
                }
            } catch (\Throwable $e) {
                $this->get('logger')->error('Notification Error: ' . $e->getMessage());
                $alertNotification->addError("An error occurred. Please try again.");
            }
        }

        return $this->render('notification/open_notification.html.twig', [
            'notification' => $notification,
            'validator' => $validator,
            'outcome' => $outcome,
            'alertNotification' => $alertNotification
        ]);
    }

    private function initializeValidationFields(Validator $validator)
    {
        $validator->getFields()->addField('email_addresses', "Email addresses are required");
        $validator->getFields()->addField('phone_numbers', "Phone numbers are required");
        $validator->getFields()->addField('subject', "Subject is required");
        $validator->getFields()->addField('message', "Message is required");
        $validator->getFields()->addField('sms_notification_message', "SMS Notification message is required");
    }

    private function validateForm(Validator $validator, Notification $notification)
    {
        $validator->required('email_addresses', $notification->getRecipientEmailAddresses());
        $validator->required('subject', $notification->getSubject());
        $validator->required('message', $notification->getMessage());
    }

    private function fillModelFromRequest(Request $request): Notification
    {
        $notification = new Notification();
        $notification->setRecipientEmailAddresses($request->request->get("email_addresses"));
        $notification->setRecipientPhoneNumbers($request->request->get("phone_numbers"));
        $notification->setSubject($request->request->get("subject"));
        $notification->setMessage($request->request->get("message"));
        $notification->setSmsNotificationMessage($request->request->get("sms_notification_message"));

        return $notification;
    }
}
