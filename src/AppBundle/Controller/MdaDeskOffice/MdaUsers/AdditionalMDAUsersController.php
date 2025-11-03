<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 10/19/2017
 * Time: 8:59 AM
 */

namespace AppBundle\Controller\MdaDeskOffice\MdaUsers;


use AppBundle\AppException\AppException;
use AppBundle\Model\Notification\AlertNotification;
use AppBundle\Model\Users\UserProfile;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\GUIDHelper;
use AppBundle\Validation\Validator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AdditionalMDAUsersController extends Controller
{

    /**
     * @Route("/secure_area/mda_admin/additional/users",name="mda_admin_additional_users")
     */
    public function additionalMdaUsersAction(Request $request)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var UserProfile $loggedInUser
         */
        $loggedInUser = $this->getUser();

        $profileSummary = null;

        $userProfile = new UserProfile();

        $validator = new Validator();
        $this->initializeValidationFields($validator, AppConstants::NEW);

        $alertNotification = new AlertNotification();
        $outcome = false;

        $userProfileService = $this->get('app.user_profile_service');

        try {
            $profileSummary = $userProfileService->getUserProfile($loggedInUser->getGuid());
        } catch (AppException $app_ex) {

        }

        //check if the form was submitted and process else render empty form
        if ($request->request->has("btnSubmit")) {

            $userProfile = $this->fillModelFromRequest($request);
            $this->validateForm($validator, $userProfile, AppConstants::NEW);

            if (!$validator->getFields()->hasErrors()) {

                $establishmentCode = $loggedInUser->getOrganizationEstablishmentCode();
                $userNameSuffix = $userProfile->getUsername();

                $newUserName = $establishmentCode . '-' . $userNameSuffix;

                $userProfile->setProfileType($loggedInUser->getProfileType());
                $userProfile->setUsername($newUserName);
                $userProfile->setPlainPassword($establishmentCode);

                $userProfile->setPrimaryRole($loggedInUser->getPrimaryRole());

                $userProfile->setOrganizationId($loggedInUser->getOrganizationId());
                $userProfile->setStatus(AppConstants::ACTIVE);

                $today = date("Y-m-d H:i:s");
                $userProfile->setLastModified($today);
                $userProfile->setLastModifiedByUserId($loggedInUser->getId());

                $guidHelper = new GUIDHelper();
                $userProfile->setGuid($guidHelper->getGUIDUsingMD5($loggedInUser->getUsername()));
                $userProfile->setFirstLogin('N');

                $encoder = $this->get('security.password_encoder');
                //$encoded = $encoder->encodePassword($user, $plainPassword);
                $encryptedPassword = $encoder->encodePassword($userProfile, $userProfile->getPlainPassword());

                $userProfile->setPassword($encryptedPassword);

                try {
                    $outcome = $userProfileService->addUserProfile($userProfile);
                    if ($outcome) {
                        $alertNotification->addSuccess('User profile created successfully');
                        $userProfile = new UserProfile();
                    }
                } catch (AppException $app_exc) {

                    if($userProfile){
                        $userProfile->setUsername($userNameSuffix);
                    }

                    $this->get('logger')->info($app_exc->getMessage());

                    switch ($app_exc->getMessage()) {
                        case AppConstants::DUPLICATE_USERNAME:
                            $errorMessage = "Username is already in use.";
                            break;

                        case AppConstants::DUPLICATE_EMAIL:
                            $errorMessage = "Email is already in use.";
                            break;

                        case AppConstants::DUPLICATE_PRIMARY_PHONE:
                            $errorMessage = "Primary phone number is already in use.";
                            break;

                        case AppConstants::DUPLICATE_SECONDARY_PHONE:
                            $errorMessage = "Secondary phone number is already in use.";
                            break;

                        case AppConstants::DUPLICATE_GUID:
                            $errorMessage = "Duplicate identifier, Please try again.";
                            break;

                        default:
                            $errorMessage = "An error occured, Try again.";
                            break;
                    }
                    $alertNotification->addError($errorMessage);
                }
            }

        }

        if (!$profileSummary) {
            $profileSummary = new UserProfile();
        }

        //fetch the existing user records
        $mdaExtraUsers = $userProfileService->getMDAExtraUserProfiles($loggedInUser->getOrganizationId());

        return $this->render('secure_area/user_account_profile/mda/mda_additional_users.html.twig',
            array(
                'userProfile' => $userProfile,
                'validator' => $validator,
                'outcome' => $outcome,
                'alertNotification' => $alertNotification,
                'mdaExtraUsers' => $mdaExtraUsers,
                'profileSummary' => $profileSummary
            )
        );

    }


    //helper methods
    private function initializeValidationFields(Validator $validator, string $which)
    {
        $validator->getFields()->addField('username', "Username is required");
    }

    private function validateForm(Validator $validator, UserProfile $userProfile, string $which)
    {
        $validator->textRequiredMax('username', $userProfile->getUsername(),true, 3, 60);
    }

    private function fillModelFromRequest(Request $request)
    {
        $userProfile = new UserProfile();
        $userProfile->setUsername($request->request->get("username"));

        return $userProfile;
    }

}