<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/16/2017
 * Time: 1:56 PM
 */

namespace AppBundle\Controller\SuperAdmin\Organization;


use AppBundle\AppException\AppException;
use AppBundle\Model\Organizations\FederalOrganizationProfileSummary;
use AppBundle\Model\Organizations\Organization;
use AppBundle\Model\Users\UserProfile;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\GUIDHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class FederalOrganizationProfileController extends Controller
{

    /**
     * @Route("/super_admin/federal_organization_profile/{guid}/summary",name="federal_organization_profile_summary")
     */
    public function showSummaryAction(Request $request, $guid)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        $organizationService = $this->get('app.organization_service');

        $organization = null;
        $profileSummary = new FederalOrganizationProfileSummary();

        try {
            $organization = $organizationService->getOrganization($guid);
        } catch (AppException $app_ex) {

        }

        if (!$organization) {
            $organization = new Organization();
        }

        return $this->render("super_admin/organization/federal_organization_profile_summary.html.twig",
            array(
                'organization' => $organization,
                'profileSummary' => $profileSummary
            ));
    }

    /**
     * @Route("/super_admin/federal_organization_profile/{guid}/desk_officers_federal",name="federal_organization_profile_desk_officers_federal")
     */
    public function showFederalDeskOfficers(Request $request, $guid)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        $federalOrganizationProfileService = $this->get('app.federal_organization_profile_service');
        $organizationService = $this->get('app.organization_service');

        $organization = null;
        $deskOfficersFederal = array();

        try {
            $organization = $organizationService->getOrganization($guid);
            $deskOfficersFederal = $federalOrganizationProfileService->getOrganizationProfileUsersByPrivilege($organization->getId(),
                AppConstants::PRIV_CONFIRM_FED_MDA_NOMINAL_ROLL_UPLOAD);
        } catch (AppException $app_ex) {

        }

        if (!$organization) {
            $organization = new Organization();
        }

        return $this->render("super_admin/organization/federal_organization_profile_desk_officers_federal.html.twig",
            array(
                'organization' => $organization,
                'deskOfficersFederal' => $deskOfficersFederal
            ));
    }

    /**
     * @Route("/super_admin/federal_organization_profile/{guid}/desk_officers_state",name="federal_organization_profile_desk_officers_state")
     */
    public function showStateDeskOfficers(Request $request, $guid)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        $federalOrganizationProfileService = $this->get('app.federal_organization_profile_service');
        $organizationService = $this->get('app.organization_service');


        $organization = null;
        $deskOfficersState = array();
        $availableStates = array();


        try {
            $organization = $organizationService->getOrganization($guid);
            $deskOfficersState = $federalOrganizationProfileService->getOrganizationProfileUsersByPrivilege($organization->getId(),
                'ABC');
            $availableStates = $federalOrganizationProfileService->getStatesWithoutFederalOrganizationUser($organization->getId());



        } catch (AppException $app_ex) {

        }

        if (!$organization) {
            $organization = new Organization();
        }

        //comming from addStateDeskOfficers
        $selectedStateIds = $this->get('session')->get('selected_states_for_new_fed_mda_state_desk_officers', array());
        $this->get('session')->remove('selected_states_for_new_fed_mda_state_desk_officers');

        return $this->render("super_admin/organization/federal_organization_profile_desk_officers_state.html.twig",
            array(
                'organization' => $organization,
                'deskOfficersState' => $deskOfficersState,
                'availableStates' => $availableStates,
                'selectedStateIds' => $selectedStateIds
            ));
    }

    /**
     * @Route("/super_admin/federal_organization_profile_desk_officers_state/{guid}/new",name="federal_organization_profile_desk_officers_state_new")
     */
    public function addStateDeskOfficers(Request $request, $guid)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        $selectedStateIds = $request->request->get('stateIds', array());
        if ($selectedStateIds) {
            try {

                $organization = $this->get('app.organization_service')->getOrganization($guid);

                $stateUsers = array();
                $guidHelper = new GUIDHelper();
                $stateInfo = array();

                foreach ($selectedStateIds as $stateDetails) {

                    $stateInfo = explode('#', $stateDetails);

                    $stateUser = new UserProfile();
                    $stateUser->setProfileType(AppConstants::MDA_USER_PROFILE);
                    $stateUser->setUsername($stateInfo[1] . $organization->getEstablishmentCode());

                    $stateUser->setPlainPassword($organization->getEstablishmentCode());
                    $encoder = $this->get('security.password_encoder');
                    $encryptedPassword = $encoder->encodePassword($stateUser, $stateUser->getPlainPassword());
                    $stateUser->setPassword($encryptedPassword);

                    $stateUser->setOrganizationId($organization->getId());
                    $stateUser->setStateOfPostingId($stateInfo[0]);

                    //$stateUser->setPrimaryRole(AppConstants::ROLE_FEDERAL_MDA_DESK_OFFICER_STATE);
                    $stateUser->setStatus(AppConstants::ACTIVE);
                    $stateUser->setFirstLogin('N');

                    $today = date("Y-m-d H:i:s");
                    $stateUser->setLastModified($today);
                    $stateUser->setLastModifiedByUserId($loggedInUser->getId());
                    $stateUser->setGuid($guidHelper->getGUIDUsingMD5($loggedInUser->getUsername()));

                    $stateUsers[] = $stateUser;
                }


                $outcome = $this->get('app.federal_organization_profile_service')->addFederalOrganizationStateLevelDeskOfficers($stateUsers, $this->get('logger'));
                if ($outcome) {
                    $this->addFlash('success', 'Users for Selected States have been created successfully');
                } else {
                    $this->addFlash('danger', 'Users could not be created at the moment, Please try again later.');
                }

            } catch (AppException $appExc) {
                $this->addFlash('danger', 'Users could not be created at the moment, Please try again later.');

                $this->get('session')->set('selected_states_for_new_fed_mda_state_desk_officers', $selectedStateIds);
                $this->get('logger')->info($appExc->getMessage());
            }

        }

        return $this->redirectToRoute('federal_organization_profile_desk_officers_state', array('guid' => $guid));
    }

}