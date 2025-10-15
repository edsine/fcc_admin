<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/27/2016
 * Time: 8:41 AM
 */

namespace AppBundle\Controller\NominalRoll;


use AppBundle\AppException\AppException;
use AppBundle\AppException\AppExceptionMessages;
use AppBundle\Model\Notification\AlertNotification;
use AppBundle\Model\Submission\NominalRollSubmission;
use AppBundle\Model\Users\UserProfile;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\FileUploadHelper;
use AppBundle\Utils\GUIDHelper;
use AppBundle\Utils\HttpHelper;
use AppBundle\Utils\RecordSelectorHelper;
use GuzzleHttp\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use \Exception;

class FederalNominalRollSubmissionController extends Controller
{
    /**
     * @Route("/secure_area/federal/mda_admin/nominal_roll/main/submission/new/{submissionYearInBase64}"
     * , name="federal_mda_admin_nominal_roll_main_submission_new", defaults={"submissionYearInBase64":"0"})
     */
    public function showNewMainFederalNominalRollSubmissionAction($submissionYearInBase64)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var UserProfile $loggedInUser
         */
        $loggedInUser = $this->getUser();

        if(!$submissionYearInBase64){
            return $this->redirectToRoute('check_new_federal_mda_admin_nominal_roll_main_submission');
        }

        $submissionYear = base64_decode($submissionYearInBase64);

        //check if mda has set baseline year
        try {
            $mdaBaselineHasBeenSet = $this->get('app.mda_baseline_service')->checkOrganizationBaseline($loggedInUser->getOrganizationId());
            if (!$mdaBaselineHasBeenSet) {
                return $this->redirectToRoute('mda_no_baseline_notice');
            }
        } catch (AppException $e) {
            $this->get('logger')->info($e->getMessage());
            throw $this->createAccessDeniedException();
        }

        $submissionInstructions = $this->get('app.shared_data_manager')->getStaticContent(AppConstants::STATIC_CONTENT_FEDERAL_LEVEL_NOMINAL_ROLE_SUBMISSION_INSTRUCTIONS);

        $connectedToMiddleware = false;
        $middlewareResponseCode = HttpHelper::pingMiddleWare();

        if($middlewareResponseCode == 200){
            $connectedToMiddleware = true;
        }
        $submissionType = AppConstants::MAIN_SUBMISSION;

        return $this->render('secure_area/federal/nominal_roll/mda_admin/federal_mda_admin_nominal_roll_main_submission_new.html.twig',
            array(
                'submissionYear' => $submissionYear,
                'submissionInstructions' => $submissionInstructions,
                'submissionType' => $submissionType,
                'connectedToMiddleware' => $connectedToMiddleware
            ));
    }

    /**
     * @Route("/secure_area/federal/mda_admin/nominal_roll/quarterly/return/new/{submissionYearInBase64}"
     * , name="federal_mda_admin_nominal_roll_quarterly_return_new", defaults={"submissionYearInBase64":"0"})
     */
    public function showNewFederalNominalRollQuarterlyReturnsAction($submissionYearInBase64)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var UserProfile $loggedInUser
         */
        $loggedInUser = $this->getUser();

        if(!$submissionYearInBase64){
            return $this->redirectToRoute('check_new_federal_mda_admin_nominal_roll_quarterly_return');
        }

        $submissionYear = base64_decode($submissionYearInBase64);

        //check if mda has set baseline year
        try {
            $mdaBaselineHasBeenSet = $this->get('app.mda_baseline_service')->checkOrganizationBaseline($loggedInUser->getOrganizationId());
            if (!$mdaBaselineHasBeenSet) {
                return $this->redirectToRoute('mda_no_baseline_notice');
            }
        } catch (AppException $e) {
            $this->get('logger')->info($e->getMessage());
            throw $this->createAccessDeniedException();
        }

        $submissionInstructions = $this->get('app.shared_data_manager')->getStaticContent(AppConstants::STATIC_CONTENT_FEDERAL_LEVEL_NOMINAL_ROLE_SUBMISSION_INSTRUCTIONS);

        $connectedToMiddleware = false;
        $middlewareResponseCode = HttpHelper::pingMiddleWare();

        if($middlewareResponseCode == 200){
            $connectedToMiddleware = true;
        }
        $submissionType = AppConstants::QUARTERLY_RETURN;

        return $this->render('secure_area/federal/nominal_roll/mda_admin/federal_mda_admin_nominal_roll_quarterly_return_new.html.twig',
            array(
                'submissionYear' => $submissionYear,
                'submissionInstructions' => $submissionInstructions,
                'submissionType' => $submissionType,
                'connectedToMiddleware' => $connectedToMiddleware
            ));
    }

    /**
     * @Route("/secure_area/federal/mda_admin/nominal_roll/submission/upload", name="federal_mda_admin_nominal_roll_submission_upload")
     */
    public function uploadFederalNominalRollAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var UserProfile $loggedInUser
         */
        $loggedInUser = $this->getUser();

        $mdaEstablishmentCode = $loggedInUser->getOrganizationEstablishmentCode();

        $internalServerError = new Response();
        $internalServerError->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $errorMessage = '';

        $logger = $this->get('logger');

        /**
         * @var FileUploadHelper $fileUploadHelper
         */
        $fileUploadHelper = null;
        $uploadDirectory = '';
        $updatedFileName = '';

        $nominalRoleSubmission = null;
        try {
            if ($request->files->has("nominalRollFile")) {

                //check if upload can be done
                $fileUploadHelper = new FileUploadHelper();

                $uploadedFile = $request->files->get("nominalRollFile");
                $submissionYear = $request->request->get('submissionYear');
                //$confirmSubmissionYear = $request->request->get('confirmSubmissionYear');
                $permissionCode = $request->request->get('permission_code');
                $submissionType = $request->request->get('submission_type');

                $nominalRoleSubmission = new NominalRollSubmission();
                $nominalRoleSubmission->setSubmissionYear($submissionYear);
                $nominalRoleSubmission->setOrganizationId($loggedInUser->getOrganizationId());
                $nominalRoleSubmission->setPermissionCode($permissionCode);
                $nominalRoleSubmission->setSubmissionType($submissionType);

                $mdaDeskOfficeSubmissionService = $this->get('app.federal_nominal_roll_service');
                //$mdaDeskOfficeSubmissionService->checkIfSubmissionCanBeMade($nominalRoleSubmission);

                if (($uploadedFile instanceof UploadedFile) && ($uploadedFile->getError() == UPLOAD_ERR_OK)) {

                    $recordSelectorHelper = new RecordSelectorHelper();
                    $submissionId = $recordSelectorHelper->generateSelector();

                    //$originalName = $uploadedFile->getClientOriginalName();
                    $originalExtension = $uploadedFile->getClientOriginalExtension();

                    $submissionTime = date("YmdHis");

                    $updatedFileName = $loggedInUser->getOrganizationEstablishmentCode() . '_' . $submissionYear . '_' . $submissionTime . '.' . $originalExtension;
                    $uploadDirectory = $fileUploadHelper->getNominalRollUploadDirectory($mdaEstablishmentCode);

                    if (!file_exists($uploadDirectory)) {
                        try {
                            $fs = new Filesystem();
                            $fs->mkdir($uploadDirectory);
                        } catch (Exception $e) {
                            $errorMessage = $e->getMessage();
                            $logger->error("UPLOAD FED NOMINAL ROLL: " . $errorMessage);
                        }
                    }

                    if (file_exists($uploadDirectory)) {

                        $error = $uploadedFile->getError();
                        if ($error == UPLOAD_ERR_OK) {
                            $fileObj = $uploadedFile->move($uploadDirectory, $updatedFileName);

                            if ($fileObj) {
                                $nominalRoleSubmission->setSubmissionId($submissionId);
                                $nominalRoleSubmission->setUploadedFileName($updatedFileName);
                                $nominalRoleSubmission->setTotalRowsImported(0);

                                $nominalRoleSubmission->setValidationStatus(AppConstants::PENDING);
                                $nominalRoleSubmission->setFccDeskOfficerConfirmationStatus(AppConstants::PENDING);
                                $nominalRoleSubmission->setFccMisHeadApprovalStatus(AppConstants::PENDING);
                                $nominalRoleSubmission->setProcessingStatus(AppConstants::PENDING);
                                $nominalRoleSubmission->setActiveStatus(AppConstants::Y);

                                $today = date("Y-m-d H:i:s");
                                $nominalRoleSubmission->setLastModified($today);
                                $nominalRoleSubmission->setLastModifiedByUserId($loggedInUser->getId());

                                $outcome = $mdaDeskOfficeSubmissionService->addMainSubmission($nominalRoleSubmission);

                                if ($outcome) {
                                    $successResponse = new JsonResponse();
                                    $successResponse->setData(array(
                                        'uploadStatus' => 'OK',
                                        'uploadMessage' => 'File Upload Successful',
                                        'submissionId' => $submissionId
                                    ));
                                    return $successResponse;
                                } else {
                                    $errorMessage = "An exception occurred - INS";
                                }
                            } else {
                                $errorMessage = "An exception occurred - FNF";
                            }
                        }
                    } else {
                        $errorMessage = "Invalid Target Directory";
                    }

                } else {
                    $errorMessage = "Error uploading file";
                }
            } else {
                $errorMessage = "File not received";
            }

        } catch (AppException $app_exc) {
            $errorMessage = $app_exc->getMessage();
            switch($errorMessage){
                case AppExceptionMessages::BELOW_BASELINE_YEAR:
                case AppExceptionMessages::DUPLICATE_MAIN_SUBMISSION:
                case AppExceptionMessages::DUPLICATE_SUBMISSION_ID:
                case AppExceptionMessages::INVALID_QUARTERLY_RETURN_YEAR:
                    break;

                case AppExceptionMessages::PREV_FAILED_SUBMISSION:
                    if($nominalRoleSubmission){
                        $submissionYear = $nominalRoleSubmission->getSubmissionYear();
                        $errorMessage = sprintf($errorMessage, $submissionYear);
                    }
                    break;

                case AppExceptionMessages::SKIPPED_PREVIOUS_YEAR:
                    if($nominalRoleSubmission){
                        $submissionYear = $nominalRoleSubmission->getSubmissionYear();
                        $previousYear = $submissionYear - 1;
                        $errorMessage = sprintf($errorMessage, $submissionYear,$previousYear,$submissionYear);
                    }
                    break;

                case AppExceptionMessages::NOT_PERMITTED_YEAR:
                    if($nominalRoleSubmission){
                        $submissionYear = $nominalRoleSubmission->getSubmissionYear();
                        $errorMessage = sprintf($errorMessage, $submissionYear,$submissionYear,$submissionYear);
                    }
                    break;

                default:
                    $errorMessage = AppExceptionMessages::GENERAL_ERROR_MESSAGE;
                    break;
            }
            $logger->error("UPLOAD FED NOMINAL ROLL: " . $app_exc->getMessage());

            if($fileUploadHelper){
                $fileUploadHelper->removeUploadedFile($uploadDirectory, $updatedFileName, $fileUploadHelper->getTrashDirectory($mdaEstablishmentCode));
            }

        }catch (FileException $fe) {
            $errorMessage = "An exception occurred while uploading the file";
            $logger->error("UPLOAD FED NOMINAL ROLL: " . $errorMessage);

            if($fileUploadHelper){
                $fileUploadHelper->removeUploadedFile($uploadDirectory, $updatedFileName, $fileUploadHelper->getTrashDirectory($mdaEstablishmentCode));
            }
        }

        $internalServerError->setContent($errorMessage);
        return $internalServerError;
    }

    /**
     * @Route("/secure_area/federal/mda_admin/nominal_roll/submission/validate", name="federal_mda_admin_nominal_roll_submission_validate")
     */
    public function validateFederalNominalRollSubmissionAction(Request $request)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $internalServerError = new Response();
        $internalServerError->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);

        $submissionId = $request->request->get('submissionId');

        if ($submissionId) {
            try {
                $guzzleClient = new Client();
                /*$response = $guzzleClient->request('GET', HttpHelper::getSchedulerFedLevelValidationUrl(), [
                    'query' => ['submissionId' => $submissionId]
                ]);*/

                $response = $guzzleClient->request('GET', HttpHelper::getProxyFederalNominalRollStageAndValidateSchedulerUrl(), [
                    'query' => ['submissionId' => $submissionId]
                ]);

                $code = $response->getStatusCode();
                $body = $response->getBody();

                $stringBody = (string)$body;

                if ($code == 200) {
                    $successResponse = new JsonResponse();
                    $successResponse->setData(array(
                        'validationRequestStatus' => 'OK',
                        'validationRequestMessage' => $stringBody
                    ));
                    return $successResponse;
                } else {
                    $errorMessage = 'No Connect: Validation Job';
                }
            } catch (\Throwable $t) {
                $errorMessage = "An Error Ocurred: " . $t->getMessage();
            }
        } else {
            $errorMessage = "Invalid Submission Id";
        }

        $internalServerError->setContent($errorMessage);
        return $internalServerError;
    }


    /**
     * @Route("/secure_area/federal/mda_admin/nominal_roll/submission/validation/check/status", name="federal_mda_admin_nominal_roll_submission_validation_check_status")
     */
    public function checkFederalNominalRollValidationStatusAction(Request $request)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $internalServerError = new Response();
        $internalServerError->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $errorMessage = '';

        $submissionId = $request->query->get('submissionId'); //this is a get request

        if ($submissionId) {
            try {

                $validationStatus = $this->get('app.federal_nominal_roll_service')->getSubmissionValidationStatus($submissionId);

                if ($validationStatus) {
                    $successResponse = new JsonResponse();
                    $successResponse->setData(array(
                        'checkStatus' => 'OK',
                        'validationStatus' => $validationStatus
                    ));
                    return $successResponse;
                }else{

                }
            } catch (\Throwable $t) {
                $errorMessage = "An Error Ocurred: " . $t->getMessage();
            }
        } else {
            $errorMessage = "Invalid Submission Id";
        }

        $internalServerError->setContent($errorMessage);
        return $internalServerError;
    }

    /**
     * @Route("/secure_area/federal/mda_admin/nominal_roll/submission/{submissionId}/delete", name="federal_mda_admin_nominal_roll_submission_delete")
     */
    public function deleteFederalNominalRollSubmissionAction(Request $request, $submissionId)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $alertNotification = new AlertNotification();
        $errorMessage = '';

        if ($request->request->has("btnSubmit")) {
            try {
                $submissionService = $this->get('app.federal_nominal_roll_service');
                $nominalRoleSubmission = $submissionService->getSubmission($submissionId);

                if(!$nominalRoleSubmission->isFccDeskOfficerConfirmed()){
                    $outcome = $submissionService->deleteNominalRoll($nominalRoleSubmission->getSubmissionId());
                    if ($outcome) {

                        $mdaEstablishmentCode = $nominalRoleSubmission->getOrganizationEstablishmentCode();
                        $fileName = $nominalRoleSubmission->getUploadedFileName();

                        $fileUploadHelper = new FileUploadHelper();
                        $fileUploadHelper->removeUploadedFile($fileUploadHelper->getNominalRollUploadDirectory($mdaEstablishmentCode), $fileName, $fileUploadHelper->getTrashDirectory($mdaEstablishmentCode));

                        if($nominalRoleSubmission->isMainSubmission()){
                            return $this->redirectToRoute('federal_mda_admin_nominal_roll_main_submission_list');
                        }else if($nominalRoleSubmission->isQuarterlyReturn()){
                            return $this->redirectToRoute('federal_mda_admin_nominal_roll_quarterly_return_list');
                        }else{
                            return $this->redirectToRoute('dashboard');
                        }

                    }
                }

            } catch (AppException $app_exc) {
                $errorMessage = "An error occured, Try again.";
                $alertNotification->addError($errorMessage);
            } catch (Exception $e) {

            }

        }

        $this->addFlash('danger', $errorMessage);
        return $this->redirectToRoute('federal_nominal_roll_show',
            array('submissionId' => $submissionId));
    }

    /**
     * @Route("/secure_area/federal/mda_admin/nominal_roll/submission/{submissionId}/re_validation", name="federal_mda_admin_nominal_roll_submission_re_validate")
     */
    public function reValidateFederalNominalRollAction($submissionId)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $nominalRollSubmission = null;

        $federalNominalRollService = $this->get('app.federal_nominal_roll_service');
        try{
            $nominalRollSubmission = $federalNominalRollService->getSubmission($submissionId);
        }catch(AppException $e){
        }


        return $this->render('secure_area/federal/nominal_roll/mda_admin/federal_mda_admin_nominal_roll_submission_re_validate.html.twig',
            array(
                'nominalRollSubmission' => $nominalRollSubmission
            ));
    }

}