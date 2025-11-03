<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 2/6/2017
 * Time: 3:05 PM
 */

namespace AppBundle\Controller;


use AppBundle\AppException\AppException;
use AppBundle\Model\Notification\AlertNotification;
use AppBundle\Model\Security\SecurityUser;
use AppBundle\Utils\AppConstants;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends Controller
{
    /**
     * @Route("/secure_area/shared/dashboard",name="dashboard")
     */
    public function dashboardAction(Request $request)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var SecurityUser $loggedInUser
         */
        $loggedInUser = $this->getUser();

        $alertNotification = new AlertNotification();

        $messages = array();
        $submissionStats = '';

        try {

            $dashboardService = $this->get('app.dashboard_service');

            if($loggedInUser->getPrivilegeChecker()->isSuperAdmin()){

            }else if($loggedInUser->getPrivilegeChecker()->isMisHead()){
                $submissionStats = $dashboardService->getMisHeadDashboard();
            }else if($loggedInUser->getPrivilegeChecker()->isCanConfirmFedMdaNominalRollUpload()){
                $submissionStats = $dashboardService->getFccDeskOfficerDashboard($loggedInUser->getFccCommitteeId());
            }else if($loggedInUser->getPrivilegeChecker()->isCanConfirmStateMdaNominalRollUpload()){
                $submissionStats = $dashboardService->getFccDeskOfficerDashboard($loggedInUser->getFccCommitteeId());
            }else if($loggedInUser->getPrivilegeChecker()->isCanUploadFedMdaNominalRoll()){
                $submissionStats = $dashboardService->getFederalMdaAdminDashboard($loggedInUser->getOrganizationId());
            }

            //$notificationService = $this->get('app.notification_service');
            //$messages = $notificationService->getUserMessages($loggedInUser->getId());

            $federalNominalRollService = $this->get('app.federal_nominal_roll_service');
            $missingSubmissionYears = $federalNominalRollService->getMissingSubmissionYears($loggedInUser->getOrganizationId());
            if($missingSubmissionYears){
                $missingYearString = implode(', ', $missingSubmissionYears);

                $errorMessage = "<b>IMPORTANT NOTICE!</b>
                                <br/>
                                <ul>
                                <li>
                                Your MDA HAS NOT made submission for the following years $missingYearString respectively.
                                <br/>
                                It is vital that you PREPARE THE NOMINAL ROLL FOR THESE YEARS respectively and submit.                                
                                </li>
                                <li style='margin-top: 20px;'>
                                LGA Conversion formula and procedure is now available in an updated Formula document which is now available for download.
                                </li>
                                </ul>                               
                                ";
                $alertNotification->addInfo(
                    $errorMessage
                );
            }

        } catch (AppException $e) {
            $this->get('logger')->info($e->getMessage());
        }

        if(!$submissionStats){
            $submissionStats = array();
        }

        return $this->render('secure_area/shared/dashboard.html.twig'
            , array(
                'messages' => $messages,
                'submissionStats' => $submissionStats,
                'alertNotification' => $alertNotification
            ));

    }

}