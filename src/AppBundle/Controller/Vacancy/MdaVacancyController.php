<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 2/23/2017
 * Time: 3:02 AM
 */

namespace AppBundle\Controller\Vacancy;


use AppBundle\AppException\AppException;
use AppBundle\AppException\AppExceptionMessages;
use AppBundle\Model\Notification\AlertNotification;
use AppBundle\Model\SearchCriteria\RecruitmentAdvertSearchCriteria;
use AppBundle\Model\Security\SecurityUser;
use AppBundle\Model\Users\UserProfile;
use AppBundle\Model\Vacancy\RecruitmentAdvert;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\FileUploadHelper;
use AppBundle\Utils\GUIDHelper;
use AppBundle\Utils\Paginator;
use AppBundle\Utils\RecordSelectorHelper;
use AppBundle\Validation\Field;
use AppBundle\Validation\Validator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class MdaVacancyController extends Controller
{

    /**
     * @Route("/secure_area/vacancy/mda_admin/list", name="mda_admin_vacancy_list")
     */
    public function listAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var SecurityUser $loggedInUser
         */

        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        $searchFilter = new RecruitmentAdvertSearchCriteria();
        $searchFilter->setTitle($request->request->get('search_title'));
        $searchFilter->setOrganizationId($request->request->get('search_vacancy'));
        $searchFilter->setStartDate($request->request->get('search_start_date'));
        $searchFilter->setEndDate($request->request->get('search_end_date'));

        $paginator = new Paginator();
        $paginator->setStartRow($request->request->get('start_row', 0));

        $pageDirection = '';
        if ($request->request->has('btn_search')) {
            $pageDirection = "FIRST";
        } else if ($request->request->has('btn_page_first')) {
            $pageDirection = "FIRST";
        } else if ($request->request->has('btn_page_prev')) {
            $pageDirection = "PREVIOUS";
        } else if ($request->request->has('btn_page_next')) {
            $pageDirection = "NEXT";
        } else if ($request->request->has('btn_page_last')) {
            $pageDirection = "LAST";
        }

        $records = array();
        $mdaVacancyService = $this->get('app.mda_vacancy_service');
        try {
            $records = $mdaVacancyService->searchRecords($searchFilter, $paginator, $pageDirection);
        } catch (AppException $e) {
            $this->get('logger')->info($e->getMessage());
        }

        return $this->render('secure_area/vacancy/mda_admin/mda_admin_vacancy_list.html.twig',
            array('records' => $records,
                'searchFilter' => $searchFilter,
                'paginator' => $paginator
            )
        );
    }

    /**
     * @Route("/secure_area/vacancy/mda_admin/{selector}/show", name="mda_admin_vacancy_show")
     */
    public function showAction($selector, Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var UserProfile $loggedInUser
         */
        $loggedInUser = $this->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        $vacancySummary = null;
        $alertNotification = new AlertNotification();

        $vacancyService = $this->get('app.mda_vacancy_service');

        try {
            $vacancySummary = $vacancyService->getVacancy($selector);
        } catch (AppException $app_ex) {
        }

        if (!$vacancySummary) {
            $vacancySummary = new RecruitmentAdvert();
            $alertNotification->addError('Vacancy could not be loaded, go back and try again.');
        }

        return $this->render('secure_area/vacancy/mda_admin/mda_admin_vacancy_show.html.twig',
            array(
                'vacancySummary' => $vacancySummary,
                'alertNotification' => $alertNotification
            )
        );
    }

    /**
     * @Route("/secure_area/vacancy/mda_admin/new", name="mda_admin_vacancy_new")
     */
    public function newAction(Request $request)
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

        $vacancy = new RecruitmentAdvert();

        $validator = new Validator();
        $this->initializeValidationFields($validator, AppConstants::NEW);

        $alertNotification = new AlertNotification();
        $outcome = false;

        $logger = $this->get('logger');

        /**
         * @var FileUploadHelper $fileUploadHelper
         */
        $fileUploadHelper = null;
        $uploadDirectory = '';
        $updatedFileName = '';

        if ($request->request->has("btn_submit")) {

            $vacancy = $this->fillModelFromRequest($request);
            $this->validateForm($validator, $vacancy, AppConstants::NEW);

            if (!$validator->getFields()->hasErrors()) {

                $uploadedFile = $vacancy->getUploadedFile();

                $originalName = $uploadedFile->getClientOriginalName();
                $originalExtension = $uploadedFile->getClientOriginalExtension();

                $updatedFileName = $originalName . '-' . date("Y-m-d_His") . '.' . $originalExtension;

                $fileUploadHelper = new FileUploadHelper();
                $uploadDirectory = $fileUploadHelper->getVacancyUploadDirectory($loggedInUser->getOrganizationEstablishmentCode());

                if (!file_exists($uploadDirectory)) {
                    try {
                        $fs = new Filesystem();
                        $fs->mkdir($uploadDirectory);
                    } catch (Exception $e) {
                        $errorMessage = $e->getMessage();
                        $logger->error("CANNOT CREATE VACANCY UPLOAD DIR: " . $errorMessage);
                    }
                }

                if (file_exists($uploadDirectory)) {

                    $fileObj = $fileUploadHelper->uploadFile($uploadedFile, $uploadDirectory, $updatedFileName);

                    if ($fileObj) {
                        $vacancy->setUploadedFileName($updatedFileName);

                        $vacancy->setRecordStatus(AppConstants::ACTIVE);

                        $today = date("Y-m-d H:i:s");
                        $vacancy->setLastModified($today);
                        $vacancy->setLastModifiedByUserId($loggedInUser->getId());

                        $selectorHelper = new RecordSelectorHelper();
                        $vacancy->setSelector($selectorHelper->generateSelector());

                        $vacancy->setOrganizationId($loggedInUser->getOrganizationId());

                        $vacancyService = $this->get('app.mda_vacancy_service');
                        try {
                            $outcome = $vacancyService->addVacancy($vacancy);
                            if ($outcome) {
                                $alertNotification->addSuccess('Vacancy Posted successfully');
                                $vacancy = new RecruitmentAdvert();
                            }
                        } catch (AppException $app_exc) {
                            $logger->error('VACANCY UPLOAD: ' . $app_exc->getMessage());
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
                    $logger->alert('VACANCY UPLOAD: Invalid Target Directory');
                }

            }
        }

        return $this->render('secure_area/vacancy/mda_admin/mda_admin_vacancy_new.html.twig'
            , [
                'vacancy' => $vacancy,
                'validator' => $validator,
                'outcome' => $outcome,
                'alertNotification' => $alertNotification
            ]
        );

    }

    /**
     * @Route("/secure_area/vacancy/mda_admin/{selector}/edit", name="mda_admin_vacancy_edit")
     */
    public function updateAction(Request $request, $selector)
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

        $vacancy = new RecruitmentAdvert();

        $validator = new Validator();
        $this->initializeValidationFields($validator, AppConstants::EDIT);

        $alertNotification = new AlertNotification();
        $outcome = false;

        $logger = $this->get('logger');

        $vacancyService = $this->get('app.mda_vacancy_service');

        /**
         * @var FileUploadHelper $fileUploadHelper
         */
        $fileUploadHelper = null;
        $uploadDirectory = '';
        $updatedFileName = '';

        if (!$request->request->has('btn_submit')) {
            try {
                $vacancy = $vacancyService->getVacancy($selector);
            } catch (AppException $app_ex) {
                $this->get('logger')->info($app_ex->getMessage());
            }

            if (!$vacancy) {
                $vacancy = new RecruitmentAdvert();
                $alertNotification->addError('Vacancy could not be loaded, go back and try again.');
            }
        } elseif ($request->request->has("btn_submit")) {

            $vacancy = $this->fillModelFromRequest($request);
            $this->validateForm($validator, $vacancy, AppConstants::EDIT);

            if (!$validator->getFields()->hasErrors()) {

                $uploadedFile = $vacancy->getUploadedFile();

                if($uploadedFile){
                    $originalName = $uploadedFile->getClientOriginalName();
                    $originalExtension = $uploadedFile->getClientOriginalExtension();

                    $updatedFileName = $originalName . '-' . date("Y-m-d_His") . '.' . $originalExtension;

                    $fileUploadHelper = new FileUploadHelper();
                    $uploadDirectory = $fileUploadHelper->getVacancyUploadDirectory($loggedInUser->getOrganizationEstablishmentCode());

                    if (!file_exists($uploadDirectory)) {
                        try {
                            $fs = new Filesystem();
                            $fs->mkdir($uploadDirectory);
                        } catch (Exception $e) {
                            $errorMessage = $e->getMessage();
                            $logger->error("CANNOT CREATE VACANCY UPLOAD DIR: " . $errorMessage);
                        }
                    }

                    if (file_exists($uploadDirectory)) {
                        $fileObj = $fileUploadHelper->uploadFile($uploadedFile, $uploadDirectory, $updatedFileName);
                        if ($fileObj) {
                            $vacancy->setUploadedFileName($updatedFileName);
                        }
                    }
                }

                $today = date("Y-m-d H:i:s");
                $vacancy->setLastModified($today);
                $vacancy->setLastModifiedByUserId($loggedInUser->getId());

                $vacancyService = $this->get('app.mda_vacancy_service');
                try {
                    $outcome = $vacancyService->editVacancy($vacancy);
                    if ($outcome) {
                        $alertNotification->addSuccess('Vacancy updated successfully');

                        //if there was a file replacement, then delete the old one
                        if($uploadedFile && $vacancy->getUploadedFileName()){
                            $oldDocumentFileName = $request->request->get("old_document_file_name");
                            if($oldDocumentFileName){
                                if($fileUploadHelper){
                                    $mdaEstablishmentCode = $loggedInUser->getOrganizationEstablishmentCode();

                                    $trashDirectory = $fileUploadHelper->getPublicTrashDirectory($mdaEstablishmentCode);
                                    $fileUploadHelper->removeUploadedFile($uploadDirectory, $oldDocumentFileName, $trashDirectory);
                                }
                            }
                        }
                    }
                } catch (AppException $app_exc) {
                    $logger->error('VACANCY UPLOAD: ' . $app_exc->getMessage());
                    $alertNotification->addError(AppExceptionMessages::GENERAL_ERROR_MESSAGE);
                }

            }
        }

        return $this->render('secure_area/vacancy/mda_admin/mda_admin_vacancy_edit.html.twig'
            , [
                'vacancy' => $vacancy,
                'validator' => $validator,
                'outcome' => $outcome,
                'alertNotification' => $alertNotification
            ]
        );

    }

    /**
     * @Route("/secure_area/vacancy/mda_admin/{selector}/delete", name="mda_admin_vacancy_delete")
     */
    public function deleteAction($selector, Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var UserProfile $loggedInUser
         */
        $loggedInUser = $this->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        $vacancySummary = null;
        $vacancy = null;

        $alertNotification = new AlertNotification();
        $outcome = false;

        $vacancyService = $this->get('app.mda_vacancy_service');

        $logger = $this->get('logger');


        try {
            $vacancySummary = $vacancyService->getVacancy($selector);
        } catch (AppException $app_ex) {
        }

        if (!$vacancySummary) {
            $vacancySummary = new RecruitmentAdvert();
            $alertNotification->addError('Vacancy could not be loaded, go back and try again.');
        }

        if ($request->request->has("btn_submit")) {

            $vacancy = $this->fillModelFromRequest($request);
            if ($vacancy->getSelector()) {

                $vacancy->setRecordStatus(AppConstants::INACTIVE);

                $today = date("Y-m-d H:i:s");
                $vacancy->setLastModified($today);
                $vacancy->setLastModifiedByUserId($loggedInUser->getId());

                try {
                    $outcome = $vacancyService->deleteVacancy($vacancy);
                    if ($outcome) {

                        $fileUploadHelper = new FileUploadHelper();

                        $mdaEstablishmentCode = $loggedInUser->getOrganizationEstablishmentCode();

                        $uploadDirectory = $fileUploadHelper->getVacancyUploadDirectory($mdaEstablishmentCode);
                        $trashDirectory = $fileUploadHelper->getPublicTrashDirectory($mdaEstablishmentCode);
                        $fileUploadHelper->removeUploadedFile($uploadDirectory, $vacancySummary->getUploadedFileName(), $trashDirectory);

                        return $this->redirectToRoute('mda_admin_vacancy_list');
                    }
                } catch (AppException $app_exc) {
                    $alertNotification->addError(AppExceptionMessages::GENERAL_ERROR_MESSAGE);

                    $this->get('logger')->error($app_exc->getMessage());
                } catch (Exception $e) {

                }
            }else{
                $alertNotification->addError("Invalid record identifier");
            }

        }

        return $this->render('secure_area/vacancy/mda_admin/mda_admin_vacancy_delete.html.twig',
            array(
                'vacancySummary' => $vacancySummary,
                'outcome' => $outcome,
                'alertNotification' => $alertNotification
            )
        );
    }

    //helper methods
    private function initializeValidationFields(Validator $validator, string $which)
    {
        $validator->getFields()->addField('title', "Title is required");
        $validator->getFields()->addField('start_date', "Invalid start date");
        $validator->getFields()->addField('end_date', "Invalid end date");
        $validator->getFields()->addField('post', "Vacancy post is required.");
        $validator->getFields()->addField('vacancy_file', "Evidence of advert is required");

        switch ($which) {
            case AppConstants::EDIT:
                $validator->getFields()->addField('selector', "Invalid record identifier");
                break;

            case AppConstants::DELETE:
                $validator->getFields()->addField('selector', "Invalid record identifier");
                break;
        }
    }

    private function validateForm(Validator $validator, RecruitmentAdvert $vacancy, string $which)
    {
        $validator->textRequiredMax('title', $vacancy->getTitle());
        $validator->required('post', $vacancy->getVacancyPost());
        $validator->datePattern('start_date', $vacancy->getStartDate(), 'd/m/Y');
        $validator->datePattern('end_date', $vacancy->getEndDate(), 'd/m/Y');

        switch ($which) {
            case AppConstants::EDIT:
                $validator->required('selector', $vacancy->getSelector());
                break;

            case AppConstants::DELETE:
                $validator->required('selector', $vacancy->getSelector());
                break;
        }

        $uploadedFile = $vacancy->getUploadedFile();
        if ($which == AppConstants::NEW || $uploadedFile) {
            //validate file
            $fileValidationField = new Field("vacancy_file");
            if (!$vacancy->getUploadedFile()) {
                $fileValidationField->setErrorMessage('Evidence of advertisement is required');
            } else if (!($uploadedFile instanceof UploadedFile) && !($uploadedFile->getError() == UPLOAD_ERR_OK)) {
                $fileValidationField->setErrorMessage('Error uploading file');
            }

            $validator->getFields()->addFieldObject($fileValidationField);
        }
    }

    private function fillModelFromRequest(Request $request)
    {
        $vacancy = new RecruitmentAdvert();
        $vacancy->setTitle($request->request->get("title"));
        $vacancy->setVacancyPost($request->request->get("post"));
        $vacancy->setStartDate($request->request->get("start_date"));
        $vacancy->setEndDate($request->request->get("end_date"));
        $vacancy->setEndDate($request->request->get("end_date"));
        $vacancy->setVacancyPost($request->request->get("post"));
        $vacancy->setUploadedFile($request->files->get("vacancy_file"));
        $vacancy->setSelector($request->request->get("selector"));

        return $vacancy;
    }

}