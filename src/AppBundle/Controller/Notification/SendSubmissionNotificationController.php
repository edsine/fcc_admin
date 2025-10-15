<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/18/2017
 * Time: 2:25 PM
 */

namespace AppBundle\Controller\Notification;


use AppBundle\Model\Notification\AlertNotification;
use AppBundle\Model\Notification\Notification;
use AppBundle\Model\Notification\SubmissionDemandNotice;
use AppBundle\Model\SubmissionSetup\SubmissionSchedule;
use AppBundle\Model\Users\UserProfile;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\GUIDHelper;
use AppBundle\Validation\Field;
use AppBundle\Validation\Validator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SendSubmissionNotificationController extends Controller
{

    /**
     * @Route("/notification/send_submission_demand_notice",name="send_submission_demand_notice")
     */
    public function sendSubmissionAlertAction(Request $request)
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

        $submissionNotice = new SubmissionDemandNotice();

        if(!$request->request->has("btnSend")){
            $submissionNotice->setMessage($this->get('app.shared_data_manager')->getStaticContent(AppConstants::STATIC_CONTENT_NOMINAL_ROLE_DEMAND_NOTICE_MESSAGE));
        }

        $validator = new Validator();
        $this->initializeValidationFields($validator, AppConstants::NEW);

        $alertNotification = new AlertNotification();
        $outcome = false;

        //check if the form was submitted and process else render empty form
        if ($request->request->has("btnSend")) {

            try {

                $submissionNotice = $this->fillModelFromRequest($request);
                $this->validateForm($validator, $submissionNotice);

                //try to parse the dates
                $startDate = date_create_from_format('d/m/Y', $submissionNotice->getStartDate());
                $endDate = date_create_from_format('d/m/Y', $submissionNotice->getEndDate());

                if (!$validator->getFields()->hasErrors() && $startDate && $endDate) {

                    $notificationService = $this->get('app.notification_service');
                    $notificationSenderService = $this->get('app.notification_sender_service');

                    $today = date("Y-m-d H:i:s");
                    $submissionNotice->setCreated($today);
                    $submissionNotice->setCreatedByUserId($loggedInUser->getId());

                    //insert the schedule
                    $submissionSchedule = new SubmissionSchedule();
                    $submissionSchedule->setSubmissionYear($submissionNotice->getSubmissionYear());
                    $submissionSchedule->setStartDate($startDate->format('Y-m-d'));
                    $submissionSchedule->setEndDate($endDate->format('Y-m-d'));
                    $submissionSchedule->setLastModified($today);
                    $submissionSchedule->setLastModifiedByUserId($loggedInUser->getId());

                    $addScheduleOutcome = $this->get('app.submission_schedule_service')->addSubmissionSchedule($submissionSchedule);

                    if ($addScheduleOutcome) {

                        $this->get('app.shared_data_manager')->removeCacheItem(AppConstants::KEY_CACHED_SUBMISSION_YEARS);

                        $notification = new Notification();
                        $notification->setSenderId($loggedInUser->getId());
                        $notification->setRecipientIdOrGroup('TO_MDA_DESK_OFFICERS');
                        $notification->setSubject($submissionNotice->getSubject());
                        $notification->setMessage($submissionNotice->getMessage());
                        $notification->setCreated($today);
                        $notification->setCreatedByUserId($loggedInUser->getId());

                        $guidHelper = new GUIDHelper();
                        $notification->setGuid($guidHelper->getGUIDUsingMD5($loggedInUser->getUsername()));

                        $addNotificationOutcome = $notificationService->addNotification($notification);

                        if ($addNotificationOutcome) {

                            //get the mda phone numbers
                            $contactInfo = $notificationService->getRecipientContactInfoByRole(
                                array(AppConstants::PRIV_FED_MDA_UPLOAD_NOMINAL_ROLL
                                , AppConstants::PRIV_STATE_MDA_UPLOAD_NOMINAL_ROLL
                                )
                            );

                            if ($contactInfo) {
                                $this->get('logger')->info('CONTACT INFO: ' . print_r($contactInfo, true));
                                $recipientNosArray = array();

                                foreach ($contactInfo as $recipient) {
                                    if ($recipient['primary_phone']) {
                                        $recipientNosArray[] = $recipient['primary_phone'];
                                    }
                                }

                                $this->get('logger')->info('RECIPIENT ARRAY: ' . print_r($recipientNosArray, true));

                                $recipientNos = implode(',', $recipientNosArray);
                                $this->get('logger')->info($recipientNos);

                                try{
                                    $sendResponse = $notificationSenderService->sendSms($submissionNotice->getMessage(), 'FED.CHAR.CO', $recipientNos);
                                    if ($sendResponse) {
                                        $code = $sendResponse['responseCode'];
                                        $body = $sendResponse['responseBody'];

                                        if ($code === 200) {
                                            if (strpos($body, 'OK:') === 0) {
                                                return $this->redirectToRoute('notification_success');
                                            }
                                        }
                                    } else {

                                    }
                                }catch(\Throwable $st){

                                }
                                //this should go later
                                return $this->redirectToRoute('notification_success');


                            }

                        }else{
                            $alertNotification->addError("Notification could not be sent at the moment, Please try again.");
                        }


                    }

                }
            } catch (\Throwable $t) {
                $this->get('logger')->info($t->getMessage());
                $alertNotification->addError("Notification could not be sent at the moment, Please try again.");
            }

        }

        return $this->render('notification/send_submission_demand_notice.html.twig',
            array(
                'submissionNotice' => $submissionNotice,
                'validator' => $validator,
                'outcome' => $outcome,
                'alertNotification' => $alertNotification
            ));
    }

    private function initializeValidationFields(Validator $validator, string $which)
    {
        $validator->getFields()->addField('submission_year', "Year is required");
        $validator->getFields()->addField('start_date', "Invalid start date");
        $validator->getFields()->addField('end_date', "Invalid end date");
        $validator->getFields()->addField('subject', "Subject is required");
        $validator->getFields()->addField('message', "Message is required");
    }

    private function validateForm(Validator $validator, SubmissionDemandNotice $submissionNotice)
    {
        $validator->required('submission_year', $submissionNotice->getSubmissionYear());
        $validator->required('start_date', $submissionNotice->getStartDate());
        $validator->required('end_date', $submissionNotice->getEndDate());
        $validator->required('subject', $submissionNotice->getSubject());
        $validator->required('message', $submissionNotice->getMessage());
    }

    private function fillModelFromRequest(Request $request)
    {
        $submissionNotice = new SubmissionDemandNotice();
        $submissionNotice->setSubmissionYear($request->request->get("submission_year", ""));
        $submissionNotice->setStartDate($request->request->get("start_date"));
        $submissionNotice->setEndDate($request->request->get("end_date"));
        $submissionNotice->setSubject($request->request->get("subject"));
        $submissionNotice->setMessage($request->request->get("message", ""));

        return $submissionNotice;
    }

}