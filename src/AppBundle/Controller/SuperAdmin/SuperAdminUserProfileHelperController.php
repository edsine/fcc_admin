<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/17/2016
 * Time: 11:56 PM
 */

namespace AppBundle\Controller\SuperAdmin;


use AppBundle\AppException\AppException;
use AppBundle\Model\Notification\AlertNotification;
use AppBundle\Model\Users\UserProfile;
use AppBundle\Validation\Validator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SuperAdminUserProfileHelperController extends Controller
{

    /**
     * @Route("/super_admin/user/{guid}/reset_password", name="super_admin_reset_user_password")
     */
    public function resetUserPasswordAction($guid , Request $request){

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        $userProfile = new UserProfile();

        $validator = new Validator();
        $validator->getFields()->addField('new_pass', "New password is required");

        $alertNotification = new AlertNotification();
        $outcome = false;

        $userProfileService = $this->get('app.user_profile_service');

        if (!$request->request->has("btnSubmit")) {//first page load

            try {
                $userProfile = $userProfileService->getUserProfile($guid);
            } catch (AppException $app_ex) {
            }

            if (!$userProfile) {
                $userProfile = new UserProfile();
                $alertNotification->addError('User profile could not be loaded, go back and try again.');
            }

        } else if ($request->request->has("btnSubmit")) {

            //populate object
            $userProfile->setPlainPassword($request->request->get("new_pass"));

            $userProfile->setUsername($request->request->get("username"));
            $userProfile->setFirstName($request->request->get("first_name"));
            $userProfile->setLastName($request->request->get("last_name"));
            $userProfile->setEmailAddress($request->request->get("email"));
            $userProfile->setPrimaryPhone($request->request->get("primary_phone"));
            $userProfile->setOrganizationName($request->request->get("organization_name"));
            $userProfile->setStateOfPostingName($request->request->get("state_of_posting"));
            $userProfile->setPrimaryRole($request->request->get("primary_role"));
            $userProfile->setGuid($request->request->get("guid"));

            //validate
            $validator->required('new_pass', $userProfile->getPlainPassword());

            if (!$validator->getFields()->hasErrors()) {

                $encoder = $this->get('security.password_encoder');
                //$encoded = $encoder->encodePassword($user, $plainPassword);
                $encryptedPassword = $encoder->encodePassword($userProfile, $userProfile->getPlainPassword());

                $userProfile->setPassword($encryptedPassword);

                $today = date("Y-m-d H:i:s");
                $userProfile->setLastModified($today);
                $userProfile->setLastModifiedByUserId($loggedInUser->getId());

                try {
                    $outcome = $userProfileService->resetPassword($userProfile);
                    if ($outcome) {
                        $alertNotification->addSuccess('Password Changed Successfully');
                    }
                } catch (AppException $app_exc) {
                    $errorMessage = "An error occurred, Try again.";
                    $alertNotification->addError($errorMessage);

                    //$this->get('logger')->error($app_exc->getMessage());
                }
            }
        }

        return $this->render('super_admin/reset_user_password.html.twig',
            array(
                'userProfile' => $userProfile,
                'validator' => $validator,
                'outcome' => $outcome,
                'alertNotification' => $alertNotification
            )
        );

    }

}