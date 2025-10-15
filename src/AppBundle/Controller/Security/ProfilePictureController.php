<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/22/2017
 * Time: 8:49 AM
 */

namespace AppBundle\Controller\Security;


use AppBundle\AppException\AppException;
use AppBundle\AppException\AppExceptionMessages;
use AppBundle\Model\Notification\AlertNotification;
use AppBundle\Model\Security\ChangePassword;
use AppBundle\Model\Users\UserProfile;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\FileUploadHelper;
use AppBundle\Validation\Field;
use AppBundle\Validation\Validator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class ProfilePictureController extends Controller
{
    /**
     * @Route("/secure_area/user_account/profile/picture/edit",name="user_profile_change_profile_picture")
     */
    public function changeProfilePhotoAction(Request $request)
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

        $alertNotification = new AlertNotification();

        $userProfile = null;
        $profileSummary = null;
        $outcome = false;

        $logger = $this->get('logger');

        try {
            $profileSummary = $userProfileService->getUserProfile($loggedInUser->getGuid());
            if (!$profileSummary) {
                $profileSummary = new UserProfile();
            }
        } catch (AppException $app_ex) {
            $logger->alert($app_ex);
        }

        $mdaEstablishmentCode = $loggedInUser->getOrganizationEstablishmentCode();

        if ($request->request->has("btn_submit")) {

            $userProfile = $this->fillModelFromRequest($request);
            $this->validateForm($validator, $userProfile);

            if (!$validator->getFields()->hasErrors()) {

                $uploadedFile = $userProfile->getUploadedPhotoFile();

                $originalName = $uploadedFile->getClientOriginalName();
                $originalExtension = $uploadedFile->getClientOriginalExtension();

                $updatedFileName = $loggedInUser->getUsername() . '-' . date("YmdHis") . '.' . $originalExtension;

                $fileUploadHelper = new FileUploadHelper();
                $uploadDirectory = $fileUploadHelper->getProfileUploadDirectory($mdaEstablishmentCode);

                if (!file_exists($uploadDirectory)) {
                    try {
                        $fs = new Filesystem();
                        $fs->mkdir($uploadDirectory);
                    } catch (\Exception $e) {
                        $errorMessage = $e->getMessage();
                        $logger->error("CANNOT CREATE PROFILE UPLOAD DIR: " . $errorMessage);
                    }
                }

                if (file_exists($uploadDirectory)) {

                    $fileObj = $fileUploadHelper->uploadFile($uploadedFile, $uploadDirectory, $updatedFileName);

                    if ($fileObj) {
                        $userProfile->setUploadedPhotoFileName($updatedFileName);

                        $today = date("Y-m-d H:i:s");
                        $userProfile->setLastModified($today);
                        $userProfile->setLastModifiedByUserId($loggedInUser->getId());
                        try {
                            $outcome = $userProfileService->updateProfilePhoto($userProfile);
                            if ($outcome) {
                                //$alertNotification->addSuccess('Vacancy Posted successfully');
                                //$userProfile = new UserProfile();

                                $picturePreviewUrl = $fileUploadHelper->getProfileUrl() . $mdaEstablishmentCode . "/" . $userProfile->getUploadedPhotoFileName();
                                $loggedInUser->setProfilePictureDisplayUrl($picturePreviewUrl);

                                //$userProfile->setProfilePictureDisplayUrl($picturePreviewUrl);

                                return $this->redirectToRoute('user_profile_change_profile_picture');
                            }
                        } catch (AppException $app_exc) {
                            $logger->error('PROFILE PHOTO UPLOAD: ' . $app_exc->getMessage());
                            $alertNotification->addError(AppExceptionMessages::GENERAL_ERROR_MESSAGE);

                            if ($fileUploadHelper) {
                                $fileUploadHelper = new FileUploadHelper();

                                $mdaEstablishmentCode = $loggedInUser->getOrganizationEstablishmentCode();
                                $trashDirectory = $fileUploadHelper->getPublicTrashDirectory($mdaEstablishmentCode);
                                $fileUploadHelper->removeUploadedFile($uploadDirectory, $updatedFileName, $trashDirectory);
                            }
                        }
                    }
                } else {
                    $alertNotification->addError("Invalid Target Directory");
                    $logger->alert('PHOTO UPLOAD: Invalid Target Directory');
                }

            }

        }

        if (!$userProfile) {
            $userProfile = new UserProfile();
        }

        return $this->render("secure_area/user_account_profile/user_profile_change_profile_picture.html.twig",
            array(
                'userProfile' => $userProfile,
                'profileSummary' => $profileSummary,
                'alertNotification' => $alertNotification,
                'outcome' => $outcome,
                'validator' => $validator
            ));
    }

    //helper methods
    private function initializeValidationFields(Validator $validator)
    {
        $validator->getFields()->addField('profile_photo_file', "Photo attachment is required");
        $validator->getFields()->addField('guid', "Invalid record identifier");
    }

    private function validateForm(Validator $validator, UserProfile $userProfile)
    {
        $validator->required('guid', $userProfile->getGuid());

        $uploadedPhotoFile = $userProfile->getUploadedPhotoFile();
        $fileValidationField = new Field("profile_photo_file");
        if (!$userProfile->getUploadedPhotoFile()) {
            $fileValidationField->setErrorMessage('Profile photo is required');
        } else if (!($uploadedPhotoFile instanceof UploadedFile) && !($uploadedPhotoFile->getError() == UPLOAD_ERR_OK)) {
            $fileValidationField->setErrorMessage('Error uploading file');
        }

        $validator->getFields()->addFieldObject($fileValidationField);
    }

    private function fillModelFromRequest(Request $request)
    {
        $userProfile = new UserProfile();
        $userProfile->setUploadedPhotoFile($request->files->get("profile_photo_file"));
        $userProfile->setGuid($request->request->get("guid"));

        return $userProfile;
    }

}