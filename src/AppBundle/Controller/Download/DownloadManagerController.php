<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/3/2018
 * Time: 1:20 PM
 */

namespace AppBundle\Controller\Download;


use AppBundle\AppException\AppException;
use AppBundle\AppException\AppExceptionMessages;
use AppBundle\Model\Document\AttachedDocument;
use AppBundle\Model\Download\DownloadResource;
use AppBundle\Model\Notification\AlertNotification;
use AppBundle\Model\Users\UserProfile;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\FileUploadHelper;
use AppBundle\Utils\RecordSelectorHelper;
use AppBundle\Utils\StringHelper;
use AppBundle\Validation\Field;
use AppBundle\Validation\Validator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class DownloadManagerController extends Controller
{

    /**
     * @Route("/secure_area/download/manager/show", name="download_manager")
     */
    public function showAction()
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var UserProfile $loggedInUser
         */
        $loggedInUser = $this->getUser();

        $downloadResourcesCategories = null;
        $alertNotification = new AlertNotification();

        try {
            $downloadResourcesCategories = $this->get('app.download_manager')->getDownloadResources($loggedInUser->getOrganizationLevelOfGovernment());
        } catch (AppException $e) {
            $alertNotification->addError(AppExceptionMessages::GENERAL_ERROR_MESSAGE);
            $this->get('logger')->alert($e->getMessage());
        }

        if (!$downloadResourcesCategories) {
            $downloadResourcesCategories = array();
        }

        return $this->render('secure_area/downloads/download_manager.html.twig'
            , [
                'downloadResourcesCategories' => $downloadResourcesCategories,
                'alertNotification' => $alertNotification
            ]
        );
    }

    /**
     * @Route("/secure_area/download/manager/resource/{selector}/download/document", name="download_resource_document")
     */
    public function downloadResourceAction(Request $request, $selector)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $downloadResourceService = $this->get('app.download_manager');

        $downloadResource = $downloadResourceService->getDownloadResource($selector);

        if($downloadResource){

            $fileUploadHelper = new FileUploadHelper();

            $attachmentFileName = $downloadResource->getAttachment()->getFileName();

            $file = $fileUploadHelper->getResourceDownloadsDirectory() . $attachmentFileName;
            $response = new BinaryFileResponse($file);

            $response->headers->set('Cache-Control', 'no-cache, must-revalidate');
            $response->headers->set('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT');
            $response->headers->set('Content-Type', 'application/force-download');
            //$response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $response->headers->set('Content-Description', 'File Transfer');

            $stringHelper = new StringHelper();
            $simpleFileName = $stringHelper->substringBeforeLast($attachmentFileName, "_")
                . $stringHelper->substringFromLast($attachmentFileName, ".");

            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $simpleFileName
            );

            return $response;

        }

        return new Response('File Could Not Be Downloaded');
    }

    /**
     * @Route("/secure_area/download/manager/resource/new", name="download_resource_new")
     */
    public function newAction(Request $request)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->getUser();

        $downloadResource = null;
        $validator = new Validator();
        $this->initializeValidationFields($validator, AppConstants::NEW);

        $alertNotification = new AlertNotification();
        $outcome = false;

        $logger = $this->get('logger');

        $fileUploadHelper = null;
        $uploadDirectory = '';

        if ($request->request->has("btn_submit")) {

            $downloadResource = $this->fillModelFromRequest($request);
            $this->validateForm($validator, $downloadResource, AppConstants::NEW);

            if (!$validator->getFields()->hasErrors()) {

                $uploadedFile = $downloadResource->getAttachment()->getUploadedFile();

                $originalName = $uploadedFile->getClientOriginalName();
                $originalExtension = strtolower($uploadedFile->getClientOriginalExtension());

                $fileUploadHelper = new FileUploadHelper();

                $updatedFileName = basename($originalName, ".$originalExtension") . $fileUploadHelper->getRandomFileNameSuffix() . '.' . $originalExtension;

                $this->get('logger')->alert("$originalName , $updatedFileName");

                $uploadDirectory = $fileUploadHelper->getResourceDownloadsDirectory();

                if (!file_exists($uploadDirectory)) {
                    try {
                        $fs = new Filesystem();
                        $fs->mkdir($uploadDirectory);
                    } catch (\Exception $e) {
                        $errorMessage = $e->getMessage();
                        $logger->error("CANNOT CREATE UPLOAD DIR: " . $errorMessage);
                    }
                }

                if (file_exists($uploadDirectory)) {

                    $fileObj = $fileUploadHelper->uploadFile($uploadedFile, $uploadDirectory, $updatedFileName);

                    if ($fileObj) {

                        $downloadResource->getAttachment()->setFileName($updatedFileName);
                        $downloadResource->setRecordStatus(AppConstants::ACTIVE);

                        $today = date("Y-m-d H:i:s");
                        $downloadResource->setLastModified($today);
                        $downloadResource->setLastModifiedByUserId($loggedInUser->getId());

                        $recordSelectorHelper = new RecordSelectorHelper();
                        $downloadResource->setSelector($recordSelectorHelper->generateSelector());

                        $downloadResourceService = $this->get('app.download_manager');
                        try {
                            $outcome = $downloadResourceService->addDownloadResource($downloadResource);
                            if ($outcome) {
                                $downloadResource = new DownloadResource();
                                $alertNotification->addSuccess("Resource uploaded successfully");
                            }
                        } catch (AppException $e) {
                            $errorMessage = $e->getMessage();
                            switch ($errorMessage) {
                                case AppExceptionMessages::DUPLICATE_TITLE:
                                case AppExceptionMessages::DUPLICATE_DESCRIPTION:
                                case AppExceptionMessages::DUPLICATE_FILE_NAME:
                                    break;
                                default:
                                    $errorMessage = AppExceptionMessages::GENERAL_ERROR_MESSAGE;
                                    break;
                            }

                            if ($fileUploadHelper) {
                                $trashDirectory = $fileUploadHelper->getPublicTrashDirectory();
                                $fileUploadHelper->removeUploadedFile($uploadDirectory, $updatedFileName, $trashDirectory);
                            }

                            $alertNotification->addError($errorMessage);
                            $this->get('logger')->info($e);
                        }

                    }
                } else {
                    $alertNotification->addError("UPLOAD DIRECTORY NOT FOUND");
                }
            }

        }

        if (!$downloadResource) {
            $downloadResource = new DownloadResource();
        }

        $downloadCategories = $this->get('app.shared_data_manager')->getDownloadCategories();

        return $this->render('secure_area/downloads/download_resource_new.html.twig',
            [
                'downloadResource' => $downloadResource,
                'validator' => $validator,
                'downloadCategories' => $downloadCategories,
                'outcome' => $outcome,
                'alertNotification' => $alertNotification
            ]
        );

    }

    /**
     * @Route("/secure_area/download/manager/resource/{selector}/edit", name="download_resource_edit")
     */
    public function editAction(Request $request, $selector)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->getUser();

        $downloadResource = null;
        $validator = new Validator();
        $this->initializeValidationFields($validator, AppConstants::EDIT);

        $alertNotification = new AlertNotification();
        $outcome = false;

        $logger = $this->get('logger');

        $fileUploadHelper = null;
        $uploadDirectory = '';

        $downloadResourceService = $this->get('app.download_manager');

        if (!$request->request->has("btn_submit")) {
            try {
                $downloadResource = $downloadResourceService->getDownloadResource($selector);
            } catch (AppException $e) {
            }

            if (!$downloadResource) {
                $alertNotification->addError('Record could not be loaded, Try again.');
            }
        } elseif ($request->request->has("btn_submit")) {

            $downloadResource = $this->fillModelFromRequest($request);
            $this->validateForm($validator, $downloadResource, AppConstants::EDIT);

            if (!$validator->getFields()->hasErrors()) {

                $uploadedFile = $downloadResource->getAttachment()->getUploadedFile();

                if ($uploadedFile) {
                    $originalName = $uploadedFile->getClientOriginalName();
                    $originalExtension = strtolower($uploadedFile->getClientOriginalExtension());

                    $fileUploadHelper = new FileUploadHelper();

                    $updatedFileName = basename($originalName, ".$originalExtension") . $fileUploadHelper->getRandomFileNameSuffix() . '.' . $originalExtension;

                    $uploadDirectory = $fileUploadHelper->getResourceDownloadsDirectory();

                    if (!file_exists($uploadDirectory)) {
                        try {
                            $fs = new Filesystem();
                            $fs->mkdir($uploadDirectory);
                        } catch (\Exception $e) {
                            $errorMessage = $e->getMessage();
                            $logger->error("CANNOT CREATE UPLOAD DIR: " . $errorMessage);
                        }
                    }

                    if (file_exists($uploadDirectory)) {
                        $fileObj = $fileUploadHelper->uploadFile($uploadedFile, $uploadDirectory, $updatedFileName);
                        if ($fileObj) {
                            $downloadResource->getAttachment()->setFileName($updatedFileName);
                        }
                    } else {
                        $alertNotification->addError(AppExceptionMessages::DIRECTORY_NOT_FOUND);
                    }
                }

                $today = date("Y-m-d H:i:s");
                $downloadResource->setLastModified($today);
                $downloadResource->setLastModifiedByUserId($loggedInUser->getId());

                try {
                    $outcome = $downloadResourceService->editDownloadResource($downloadResource);
                    if ($outcome) {
                        $downloadResource = $downloadResourceService->getDownloadResource($selector);
                        $alertNotification->addSuccess("Record Saved Successfully");

                        //if there was a file replacement, then delete the old one
                        if ($uploadedFile && $downloadResource->getAttachment()->getFileName()) {
                            $oldDocumentFileName = $request->request->get("old_attachment_file_name");
                            if ($oldDocumentFileName) {
                                if ($fileUploadHelper) {
                                    $trashDirectory = $fileUploadHelper->getTrashDirectory();
                                    $fileUploadHelper->removeUploadedFile($uploadDirectory, $oldDocumentFileName, $trashDirectory);
                                }
                            }
                        }
                        //return $this->redirectToRoute('project_document_list', ['projectSelector' => $projectSummary->getSelector()]);
                    }
                } catch (AppException $e) {
                    $errorMessage = $e->getMessage();
                    switch ($errorMessage) {
                        case AppExceptionMessages::DUPLICATE_TITLE:
                        case AppExceptionMessages::DUPLICATE_DESCRIPTION:
                        case AppExceptionMessages::DUPLICATE_FILE_NAME:
                            break;
                        default:
                            $errorMessage = AppExceptionMessages::GENERAL_ERROR_MESSAGE;
                            break;
                    }

                    $alertNotification->addError($errorMessage);
                    $this->get('logger')->info($e->getMessage());
                }
            }

        }

        if (!$downloadResource) {
            $downloadResource = new DownloadResource();
        }

        $downloadCategories = $this->get('app.shared_data_manager')->getDownloadCategories();

        return $this->render('secure_area/downloads/download_resource_edit.html.twig',
            [
                'downloadResource' => $downloadResource,
                'validator' => $validator,
                'downloadCategories' => $downloadCategories,
                'outcome' => $outcome,
                'alertNotification' => $alertNotification
            ]
        );

    }

    /**
     * @Route("/secure_area/download/manager/resource/{selector}/delete", name="download_resource_delete")
     */
    public function deleteAction(Request $request, $selector)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->getUser();

        $downloadResource = null;

        $alertNotification = new AlertNotification();
        $outcome = false;

        $logger = $this->get('logger');

        $fileUploadHelper = null;

        $downloadResourceService = $this->get('app.download_manager');

       if ($request->request->has("btn_submit")) {

            try {
                $downloadResource = $downloadResourceService->getDownloadResource($selector);
                $outcome = $downloadResourceService->deleteDownloadResource($downloadResource);
                if ($outcome) {
                    $fileUploadHelper = new FileUploadHelper();
                    $uploadDirectory = $fileUploadHelper->getResourceDownloadsDirectory();
                    $trashDirectory = $fileUploadHelper->getTrashDirectory();
                    $fileUploadHelper->removeUploadedFile($uploadDirectory, $downloadResource->getAttachment()->getFileName(), $trashDirectory);

                    return $this->redirectToRoute('download_manager');
                }
            } catch (AppException $e) {
                $errorMessage = $e->getMessage();
                switch ($errorMessage) {
                    default:
                        $errorMessage = AppExceptionMessages::GENERAL_ERROR_MESSAGE;
                        break;
                }

                $alertNotification->addError($errorMessage);
                $this->get('logger')->info($e->getMessage());
            }

        }

        return $this->redirectToRoute('download_manager');

    }

    private function initializeValidationFields(Validator $validator, string $which)
    {
        $validator->getFields()->addField('title', "Title is required");
        $validator->getFields()->addField('description', "Description is required");
        $validator->getFields()->addField('category', "Category is required");
        $validator->getFields()->addField('uploaded_file', "Attachment is required");

        switch ($which) {
            case AppConstants::EDIT:
                $validator->getFields()->addField('selector', "Invalid record identifier");
                break;

            case AppConstants::DELETE:
                $validator->getFields()->addField('selector', "Invalid record identifier");
                break;
        }
    }

    private function validateForm(Validator $validator, DownloadResource $downloadResource, string $which)
    {
        $validator->textRequiredMax('title', $downloadResource->getTitle());
        $validator->textRequiredMax('description', $downloadResource->getDescription());
        $validator->required('category', $downloadResource->getCategoryId());

        switch ($which) {
            case AppConstants::EDIT:
                $validator->required('selector', $downloadResource->getSelector());
                break;

            case AppConstants::DELETE:
                $validator->required('selector', $downloadResource->getSelector());
                break;
        }

        $uploadedFile = $downloadResource->getAttachment()->getUploadedFile();
        if ($which == AppConstants::NEW || $uploadedFile) {
            //validate file
            $fileValidationField = new Field("uploaded_file");
            if (!$uploadedFile) {
                $fileValidationField->setErrorMessage('Attachment is required');
            } else if (!($uploadedFile instanceof UploadedFile) && !($uploadedFile->getError() == UPLOAD_ERR_OK)) {
                $fileValidationField->setErrorMessage('Error uploading file');
            }

            $validator->getFields()->addFieldObject($fileValidationField);
        }
    }

    private function fillModelFromRequest(Request $request)
    {
        $downloadResource = new DownloadResource();
        $downloadResource->setTitle($request->request->get("title"));
        $downloadResource->setDescription($request->request->get("description"));
        $downloadResource->setCategoryId($request->request->get("category"));

        $attachment = new AttachedDocument(null, null);
        $attachment->setUploadedFile($request->files->get("uploaded_file"));
        $downloadResource->setAttachment($attachment);

        $downloadResource->setSelector($request->request->get("selector"));

        return $downloadResource;
    }

}