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

class AllFederalMDANotificationController extends Controller
{

    /**
     * @Route("/notification/all_federal_mda",name="notification_for_all_federal_mda")
     */
    public function notifyAllFederalMdaAction(Request $request)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var UserProfile $loggedInUser
         */
        $loggedInUser = $this->getUser();

        $notification = new Notification();

        $validator = new Validator();
        $this->initializeValidationFields($validator);

        $alertNotification = new AlertNotification();
        $outcome = false;

        $recipientsContactInfo = new RecipientsContactInfo();

        //check if the form was submitted and process else render empty form
        if ($request->request->has("btnSend")) {

            $notification = $this->fillModelFromRequest($request);
            $this->validateForm($validator, $notification);

            try {
                if (!$validator->getFields()->hasErrors()) {

                    $notificationService = $this->get('app.notification_service');
                    $notificationSenderService = $this->get('app.notification_sender_service');

                    $notification->setSenderId($loggedInUser->getId());
                    $notification->setSenderOrganization($loggedInUser->getOrganizationName());
                    $notification->setSenderName($loggedInUser->getDisplayName());
                    $notification->setSenderEmail($loggedInUser->getEmailAddress());

                    $notification->setRecipientIdOrGroup(AppConstants::FEDERAL_MDA);

                    $today = date("Y-m-d H:i:s");
                    $notification->setCreated($today);
                    $notification->setCreatedByUserId($loggedInUser->getId());

                    $guidHelper = new GUIDHelper();
                    $notification->setGuid($guidHelper->getGUIDUsingMD5($loggedInUser->getUsername()));

                    if (!$notification->getSmsNotificationMessage()) {
                        $notification->setSmsNotificationMessage(AppConstants::DEFAULT_SMS_NOTIFICATION_MESSAGE);
                    }

                    $addNotificationOutcome = $notificationService->addNotification($notification);

                    if ($addNotificationOutcome) {
                        try {

                            $recipientsContactInfo = $notificationService->getMDAContactInformationByLevelOfGovernment(AppConstants::FEDERAL);

                            //$recipientsContactInfo = $notificationService->getTestingRecipientsContactInfo();

                            //inject the senders contact information also to recipients information
                            $recipientsContactInfo->appendSendersContactInformation(
                                $loggedInUser->getDisplayName()
                                , $loggedInUser->getEmailAddress()
                                , $loggedInUser->getPrimaryPhone()
                            );

                            $notification->setSmsNotificationSender(AppConstants::FCC_SMS_SENDER);

                            $sendResponse = $notificationSenderService->sendNotification($notification, $recipientsContactInfo);
                            if ($sendResponse) {
                                $smsSentForDelivery = ($sendResponse->getSmsHttpResponseCode() === 200)
                                    && (strpos($sendResponse->getSmsHttpResponseBody(), 'OK:') === 0);

                                $emailSentForDelivery = $sendResponse->getEmailHttpResponseCode() === 202;

                                if($smsSentForDelivery || $emailSentForDelivery){
                                    //move this later and see how it goes
                                    return $this->redirectToRoute('notification_success');
                                }else{
                                    $alertNotification->addError("Your Message Could Not Be Sent At The Moment, Please Try Again Later.");
                                }


                            } else {
                                $alertNotification->addError("Your Message Could Not Be Sent At The Moment, Please Try Again Later.");
                            }

                        } catch (\Throwable $st) {
                        }
                        //this should go later
                        //return $this->redirectToRoute('notification_success');


                    } else {
                        $alertNotification->addError("Notification could not be sent at the moment, Please try again.");
                    }

                }
            } catch (\Throwable $t) {
                $this->get('logger')->info($t->getMessage());
                $alertNotification->addError("Notification could not be sent at the moment, Please try again.");
            }

        }

        return $this->render('notification/notification_for_all_federal_mda.html.twig',
            array(
                'notification' => $notification,
                'validator' => $validator,
                'outcome' => $outcome,
                'alertNotification' => $alertNotification
            ));
    }

    //helper methods
    private function initializeValidationFields(Validator $validator)
    {
        $validator->getFields()->addField('subject', "Subject is required");
        $validator->getFields()->addField('message', "Message is required");
        $validator->getFields()->addField('sms_notification_message', "SMS Notification message is required");
    }

    private function validateForm(Validator $validator, Notification $notification)
    {
        $validator->required('subject', $notification->getSubject());
        $validator->required('message', $notification->getMessage());
        //$validator->required('sms_notification_message', $notification->getSmsNotificationMessage());
    }

    private function fillModelFromRequest(Request $request)
    {
        $notification = new Notification();
        $notification->setSubject($request->request->get("subject"));
        $notification->setMessage($request->request->get("message"));
        $notification->setSmsNotificationMessage($request->request->get("sms_notification_message"));

        return $notification;
    }

}