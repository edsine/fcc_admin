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
use AppBundle\Model\Users\UserProfile;
use AppBundle\Utils\AppConstants;
use AppBundle\Validation\Validator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class EmptyProfileUpdateController extends Controller
{
    /**
     * @Route("/secure_area/user_account/profile/empty_profile_update",name="empty_profile_update")
     */
    public function emptyProfileUpdateAction(Request $request)
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
        $userProfile = null;
        $outcome = false;

        if (!$request->request->has("btnSubmit")) {//first page load

            try {
                $userProfile = $userProfileService->getUserProfile($loggedInUser->getGuid());
            } catch (AppException $app_ex) {

            }

            if (!$userProfile) {
                $userProfile = new UserProfile();
                $alertNotification->addError('User profile could not be loaded, go back and try again.');
            }

        } else if ($request->request->has("btnSubmit")) {


            $userProfile = $this->fillModelFromRequest($request);
            $this->validateForm($validator, $userProfile);

            if (!$validator->getFields()->hasErrors()) {

                $today = date("Y-m-d H:i:s");
                $userProfile->setLastModified($today);
                $userProfile->setLastModifiedByUserId($loggedInUser->getId());

                try {
                    $outcome = $this->get('app.manage_profile_service')->editBioAndContactData($userProfile);
                    if ($outcome) {
                        $userProfile = $userProfileService->getUserProfile($loggedInUser->getGuid());

                        $this->get('security.token_storage')->getToken()->getUser()->setFirstName($userProfile->getFirstName());
                        $this->get('security.token_storage')->getToken()->getUser()->setLastName($userProfile->getLastName());
                        $this->get('security.token_storage')->getToken()->getUser()->setEmailAddress($userProfile->getEmailAddress());
                        $this->get('security.token_storage')->getToken()->getUser()->setPrimaryPhone($userProfile->getPrimaryPhone());

                        return $this->redirectToRoute('dashboard');
                    }
                } catch (AppException $app_exc) {
                    switch ($app_exc->getMessage()) {
                        case AppConstants::DUPLICATE_PRIMARY_PHONE:
                            $errorMessage = "Duplicate primary phone.";
                            break;

                        case AppConstants::DUPLICATE_EMAIL:
                            $errorMessage = "Duplicate email address.";
                            break;

                        default:
                            $errorMessage = "An error occured, Try again.";
                            break;
                    }
                    $alertNotification->addError($errorMessage);
                }
            }

        }

        return $this->render("secure_area/user_account_profile/empty_profile_update.html.twig",
            array(
                'userProfile' => $userProfile,
                'alertNotification' => $alertNotification,
                'outcome' => $outcome,
                'validator' => $validator
            ));
    }

    //helper methods
    private function initializeValidationFields(Validator $validator)
    {
        $validator->getFields()->addField('first_name', "First name is required");
        $validator->getFields()->addField('last_name', "Last name is required");
        $validator->getFields()->addField('email', "Invalid email address");
        $validator->getFields()->addField('primary_phone', "Phone number is required");
        $validator->getFields()->addField('guid', "Invalid record identifier");
    }

    private function validateForm(Validator $validator, UserProfile $userProfile)
    {

        $validator->textRequiredMax('first_name', $userProfile->getFirstName(), true, 1, 60);
        $validator->textRequiredMax('last_name', $userProfile->getLastName(), true, 1, 60);
        $validator->email('email', $userProfile->getEmailAddress());
        $validator->phone('primary_phone', $userProfile->getPrimaryPhone());
        $validator->required('guid', $userProfile->getGuid());

    }

    private function fillModelFromRequest(Request $request)
    {
        $userProfile = new UserProfile();
        $userProfile->setFirstName($request->request->get("first_name", ""));
        $userProfile->setLastName($request->request->get("last_name"));
        $userProfile->setEmailAddress($request->request->get("email", ""));
        $userProfile->setPrimaryPhone($request->request->get("primary_phone", ""));
        $userProfile->setGuid($request->request->get("guid", ""));

        return $userProfile;
    }

}