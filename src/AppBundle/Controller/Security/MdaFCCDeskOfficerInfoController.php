<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/22/2017
 * Time: 8:49 AM
 */

namespace AppBundle\Controller\Security;


use AppBundle\AppException\AppException;
use AppBundle\Model\Organizations\Organization;
use AppBundle\Model\Users\UserProfile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class MdaFCCDeskOfficerInfoController extends Controller
{

    /**
     * @Route("/user_profile/mda_fcc_desk_officer_summary",name="mda_fcc_desk_officer_summary")
     */
    public function showMdaFccDeskOfficerAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        $userProfileService = $this->get('app.user_profile_service');

        $profileSummary = null;
        $userGuid = $loggedInUser->getGuid();

        $mdaFccDeskOfficerInfo = new UserProfile();

        try {
            $profileSummary = $userProfileService->getUserProfile($userGuid);
            if($profileSummary){
                $mdaFccDeskOfficerInfo = $this->get('app.manage_profile_service')->getOrganizationFccDeskOfficer($profileSummary->getOrganizationId());
            }
        } catch (AppException $app_ex) {
            $this->get('logger')->info($app_ex->getMessage());
        }

        if (!$profileSummary) {
            $profileSummary = new UserProfile();
        }

        if(!$mdaFccDeskOfficerInfo){
            $mdaFccDeskOfficerInfo = new UserProfile();
        }

        return $this->render("secure_area/user_account_profile/mda/mda_fcc_desk_officer_summary.html.twig",
            array(
                'profileSummary' => $profileSummary,
                'mdaFccDeskOfficerInfo' => $mdaFccDeskOfficerInfo
            ));
    }

}