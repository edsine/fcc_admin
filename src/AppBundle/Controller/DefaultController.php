<?php

namespace AppBundle\Controller;

use AppBundle\Model\Security\SecurityUser;
use AppBundle\Model\Users\UserProfile;
use AppBundle\Utils\AppConstants;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="login")
     */
    public function loginAction(Request $request)
    {

        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $loginAppName = $this->get('app.shared_data_manager')->getStaticContent(AppConstants::STATIC_CONTENT_LOGIN_PAGE_APPLICATION_NAME);

        return $this->render('security/login.v3.html.twig'
            , array(
                'last_username' => $lastUsername,
                'error' => $error,
                'loginAppName' => $loginAppName
            ));

    }
    
    /**
     * @Route("/admin-login", name="admin-login")
     */
    public function adminLoginAction(Request $request)
    {
        return $this->render('security/login.admin.html.twig'
            , array());

    }

    /**
     * @Route("/handle-login-success",name="handle_login_success")
     */
    public function loginSuccessAction()
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var SecurityUser $loggedInUser
         */
        $loggedInUser = $this->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        if ($loggedInUser->getFirstName()) {

            /*if ($loggedInUser->getOrganizationEstablishmentCode() == '33') {
                return $this->redirectToRoute('dashboard');
            }*/

            /*if ($loggedInUser->getPrivilegeChecker()->isCanUploadFedMdaNominalRoll()
                || $loggedInUser->getPrivilegeChecker()->isCanUploadStateMdaNominalRoll()) {
                $displayName = $loggedInUser->getDisplayName();
                $message = "Hello, $displayName, ";
                $message .= "<br/>The portal will be open for submission on Wednesday 17th January, 2018.";
                $this->addFlash('success', $message);

                return $this->redirectToRoute('login');
            }*/

            return $this->redirectToRoute('dashboard');

        } else {

            /*if ($loggedInUser->getPrivilegeChecker()->isCanUploadFedMdaNominalRoll()
                || $loggedInUser->getPrivilegeChecker()->isCanUploadStateMdaNominalRoll()) {
                $displayName = $loggedInUser->getDisplayName();
                $message = "Hello, $displayName, ";
                $message .= "<br/>The portal will be open for submission on Wednesday 17th January, 2018.";
                $this->addFlash('success', $message);

                return $this->redirectToRoute('login');
            }*/

            return $this->redirectToRoute('empty_profile_update');
        }

    }

    /**
     * @Route("/secure_area/training/advert",name="training-advert")
     */
    public function trainingAdvertAction()
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var SecurityUser $loggedInUser
         */
        $loggedInUser = $this->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('secure_area/shared/training-advert.html.twig'
            , array());
    }

    /**
     * This is the route the user can use to logout.
     *
     * But, this will never be executed. Symfony will intercept this first
     * and handle the logout automatically. See logout in app/config/security.yml
     *
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {
        throw new \Exception('This should never be reached!');
    }

    /**
     * @Route("/static_cms/login_page_application_description", name="login_page_application_description")
     */
    public function loginPageApplicationNameAction()
    {
        $loginPageAppName = $this->get('app.shared_data_manager')->getStaticContent(AppConstants::STATIC_CONTENT_LOGIN_PAGE_APPLICATION_NAME);
        return new Response($loginPageAppName);
    }

    /**
     * @Route("/long_running/ping", name="ping")
     */
    public function pingAction()
    {
        $serverIp = $_SERVER['SERVER_ADDR'];
        $serverName = $_SERVER['SERVER_NAME'];
        return new Response("Hello from: " . $serverIp . ',' . $serverName);
    }

    /**
     * @Route("/long_running/keep_alive", name="keep_alive")
     */
    public function keepAliveAction()
    {
        $serverIp = $_SERVER['SERVER_ADDR'];
        $serverName = $_SERVER['SERVER_NAME'];
        return new Response("Hello from: " . $serverIp . ',' . $serverName);
    }
}
