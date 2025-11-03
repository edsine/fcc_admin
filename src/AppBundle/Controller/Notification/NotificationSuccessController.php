<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/18/2017
 * Time: 2:25 PM
 */

namespace AppBundle\Controller\Notification;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class NotificationSuccessController extends Controller
{

    /**
     * @Route("/secure_area/notification/notification_success", name="notification_success")
     */
    public function showNotificationSuccessAction()
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('secure_area/notification/notification_success.html.twig',
            array());

    }


}