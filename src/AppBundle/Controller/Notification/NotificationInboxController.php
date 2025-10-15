<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/18/2017
 * Time: 2:25 PM
 */

namespace AppBundle\Controller\Notification;


use AppBundle\AppException\AppException;
use AppBundle\Model\Notification\Notification;
use AppBundle\Model\Users\UserProfile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class NotificationInboxController extends Controller
{

    /**
     * @Route("/secure_area/notification/notification_inbox",name="notification_inbox")
     */
    public function showInboxAction(Request $request){

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

        $messages = array();

        $logger = $this->get('logger');

        $notificationService = $this->get('app.notification_service');
        try {
            $messages = $notificationService->getUserMessages($loggedInUser->getId());
        } catch (AppException $e) {
            $logger->alert($e->getMessage());
        }

        return $this->render('secure_area/notification/notification_inbox.html.twig',
            array(
                'messages' => $messages
            ));
    }

    /**
     * @Route("/notification/notification/{guid}/show",name="notification_show")
     */
    public function showNotificationAction(Request $request, $guid){

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

        $notificationService = $this->get('app.notification_service');
        $notification = null;

        $logger = $this->get('logger');

        try {
            $notification = $notificationService->getNotification($guid);
        } catch (AppException $e) {
            $logger->alert($e->getMessage());
        }

        //TODO: do this a better way. this will always execute when a message is fetched, not good
        try {
            if($notification){
                $now = date("Y-m-d H:i:s");
                $notificationService->saveUserReadNotification($loggedInUser->getId(), $notification->getId(), $now);
            }
        } catch (AppException $e) {
            $logger->alert($e->getMessage());
        }

        if(!$notification){
            $notification = new Notification();
        }

        return $this->render('secure_area/notification/notification_show.html.twig',
            array(
                'notification' => $notification
            ));
    }

    /**
     * @Route("/secure_area/notification/empty/message",name="no_selected_message_show")
     */
    public function emptyMessageAction(){

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

        return $this->render('secure_area/notification/empty-message.html.twig'
            ,[

            ]
        );
    }

}