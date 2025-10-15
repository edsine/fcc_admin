<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/22/2017
 * Time: 8:49 AM
 */

namespace AppBundle\Controller\Security;


use AppBundle\AppException\AppException;
use AppBundle\Model\Notification\AlertNotification;
use AppBundle\Model\Security\ChangePassword;
use AppBundle\Model\Users\UserProfile;
use AppBundle\Validation\Validator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ChangePasswordController extends Controller
{
    /**
     * @Route("/secure_area/user_account/profile/change_password",name="user_profile_change_password")
     */
    public function changePasswordAction(Request $request)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var UserProfile $loggedInUser
         */
        $loggedInUser = $this->getUser();

        $userProfileService = $this->get('app.user_profile_service');

        $validator = new Validator();
        $this->initializeValidationFields($validator);

        $alertNotification =  new AlertNotification();

        $changePassword = null;
        $profileSummary = null;
        $outcome = false;

        try {
            $profileSummary = $userProfileService->getUserProfile($loggedInUser->getGuid());
            if(!$profileSummary){
                $profileSummary = new UserProfile();
            }
        } catch (AppException $app_ex) {
            $this->get('logger')->info($app_ex);
        }

        if ($request->request->has("btnSubmit")) {

            $changePassword = $this->fillModelFromRequest($request);
            $this->validateForm($validator, $changePassword);

            if (!$validator->getFields()->hasErrors()) {

                //check if the old passwordis correct
                $encoder = $this->get('security.password_encoder');
                $isOldPasswordCorrect = $encoder->isPasswordValid($loggedInUser, $changePassword->getOldPassword());

                if($isOldPasswordCorrect){
                    $userProfile = new UserProfile();
                    $userProfile->setPlainPassword($changePassword->getNewPassword());
                    $userProfile->setGuid($changePassword->getUserProfileGuid());

                    $encryptedPassword = $encoder->encodePassword($userProfile, $userProfile->getPlainPassword());

                    $userProfile->setPassword($encryptedPassword);

                    $today = date("Y-m-d H:i:s");
                    $userProfile->setLastModified($today);
                    $userProfile->setLastModifiedByUserId($loggedInUser->getId());

                    try {
                        $outcome = $userProfileService->resetPassword($userProfile);
                        if ($outcome) {
                            $changePassword = new ChangePassword();
                            $alertNotification->addSuccess('Password Changed Successfully');
                            return $this->redirectToRoute('logout');
                        }
                    } catch (AppException $app_exc) {
                        $errorMessage = "An error occurred, Try again.";
                        $alertNotification->addError($errorMessage);
                    }
                }else{
                    $alertNotification->addError('Invalid Old Password');
                }
            }

        }

        if (!$changePassword) {
            $changePassword = new ChangePassword();
        }

        return $this->render("secure_area/user_account_profile/user_profile_change_password.html.twig",
            array(
                'changePassword' => $changePassword,
                'profileSummary' => $profileSummary,
                'alertNotification' => $alertNotification,
                'outcome' => $outcome,
                'validator' => $validator
            ));
    }

    private function initializeValidationFields(Validator $validator)
    {
        $validator->getFields()->addField('old_pass', "Old password is required");
        $validator->getFields()->addField('new_pass', "New password is required");
        $validator->getFields()->addField('confirm_new_pass', "Confirm new password is required");
        $validator->getFields()->addField('password_match', "Passwords do not match");
        $validator->getFields()->addField('guid', "Invalid record identifier");
    }

    private function validateForm(Validator $validator, ChangePassword $changePassword)
    {
        $validator->required('old_pass', $changePassword->getOldPassword());
        $validator->required('new_pass', $changePassword->getNewPassword());
        $validator->required('confirm_new_pass', $changePassword->getConfirmNewPassword());
        $validator->matches('password_match', $changePassword->getNewPassword(), $changePassword->getConfirmNewPassword());
        $validator->required('guid', $changePassword->getUserProfileGuid());
    }

    private function fillModelFromRequest(Request $request)
    {
        $userProfile = new ChangePassword();
        $userProfile->setOldPassword($request->request->get("old_pass", ""));
        $userProfile->setNewPassword($request->request->get("new_pass", ""));
        $userProfile->setConfirmNewPassword($request->request->get("confirm_new_pass"));
        $userProfile->setUserProfileGuid($request->request->get("guid", ""));

        return $userProfile;
    }

}