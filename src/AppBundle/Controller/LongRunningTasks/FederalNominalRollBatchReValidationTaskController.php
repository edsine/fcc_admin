<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/30/2016
 * Time: 1:50 PM
 */

namespace AppBundle\Controller\LongRunningTasks;


use AppBundle\Model\LongRunning\BatchRevalidation;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\HttpHelper;
use GuzzleHttp\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use \Throwable;

class FederalNominalRollBatchReValidationTaskController extends Controller
{

    /**
     * @Route("/long_running/federal/nominal_roll/batch_re_validation/show", name="federal_level_nominal_roll_batch_re_validation_show")
     */
    public function showFederalLevelBatchReValidation()
    {
        return $this->render('mis_head/batch_re_validation.html.twig');
    }

    /**
     * @Route("/long_running/federal/nominal_roll/batch_re_validation", name="long_running_federal_nominal_roll_batch_re_validation")
     */
    public function longRunningFederalNominalRollBatchReValidationAction()
    {
        //set_time_limit(1800);

        $logger = $this->get('logger');

        $internalServerError = new Response();
        $internalServerError->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $errorMessage = '';


        $batchRevalidationService = $this->get('app.batch_revalidation');

        try {

            $logger->info('BATCH REVAL: starting');

            $recordsToValidate = $batchRevalidationService->initializeBatchToReValidate();

            if ($recordsToValidate) {

                $submissionService = $this->get('app.federal_nominal_roll_service');
                $nominalRoleBgTaskService = $this->get('app.nominal_role_bg_task_service');
                //$stateEstablishmentNominalRoleBgTaskService = $this->get('app.state_establishment_nominal_role_bg_task_service');

                /**
                 * @var BatchRevalidation $failedSubmission
                 */
                foreach ($recordsToValidate as $failedSubmission) {

                    $nominalRoleSubmission = $submissionService->getSubmission($failedSubmission->getSubmissionId());

                    if ($failedSubmission->getEstablishmentType() == AppConstants::FEDERAL_MINISTRY_ESTABLISHMENT
                        || $failedSubmission->getEstablishmentType() == AppConstants::FEDERAL_PARASTATAL_ESTABLISHMENT
                    ) {
                        $validationResult = $nominalRoleBgTaskService->validateNominalRollSubmission($nominalRoleSubmission);
                    } else {
                        //$validationResult = $stateEstablishmentNominalRoleBgTaskService->validateNominalRoleSubmission($nominalRoleSubmission, $logger);
                    }

                    $batchRevalidationService->updateBatchRevalidationStatus($failedSubmission->getSubmissionId());
                }

                //$batchRevalidationService->cleanUpBatchRevalidationTracking();

                $successResponse = new JsonResponse();
                $successResponse->setData(array(
                    'opStatus' => 'OK',
                    'opMessage' => 'Re-Validation Completed'
                ));
                return $successResponse;

            } else {

                $successResponse = new JsonResponse();
                $successResponse->setData(array(
                    'opStatus' => 'OK',
                    'opMessage' => 'No Records To Re-Validate'
                ));
                return $successResponse;

            }

        } catch (Throwable $t) {
            $errorMessage = "An exception occurred";
            $logger->info('BATCH REVAL: ' . $t->getMessage());
        } finally {
        }


        $internalServerError->setContent($errorMessage);
        return $internalServerError;
    }

    /**
     * @Route("/long_running/federal/nominal_roll/batch_re_validation/scheduler", name="long_running_federal_nominal_roll_batch_re_validation_scheduler")
     */
    public function scheduleFederalNominalRollBatchReValidation()
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->getUser();

        $internalServerError = new Response();
        $internalServerError->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $errorMessage = '';

        try {

            $batchRevalidationService = $this->get('app.batch_revalidation');

            $revalidationAnalysis = $batchRevalidationService->getBatchRevalidationStatus();

            if($revalidationAnalysis){

                if($revalidationAnalysis['totalPendingReValidation'] == 0){
                    $guzzleClient = new Client();

                    $response = $guzzleClient->request('GET', HttpHelper::getProxyFederalNominalRollBatchReValidationSchedulerUrl());

                    $code = $response->getStatusCode();
                    $body = $response->getBody();

                    $stringBody = (string)$body;

                    if ($code == 200) {
                        $successResponse = new JsonResponse();
                        $successResponse->setData(array(
                            'reValidationRequestStatus' => 'OK',
                            'reValidationRequestMessage' => $stringBody
                        ));
                        return $successResponse;
                    } else {
                        $errorMessage = 'No Connect: Batch Re-Validation Job';
                    }
                }else{
                    $successResponse = new JsonResponse();
                    $successResponse->setData(array(
                        'reValidationRequestStatus' => 'ALREADY_PROCESSING',
                        'reValidationRequestMessage' => 'A batch revalidation process is running already.'
                    ));
                    return $successResponse;
                }

            }


        } catch (\Throwable $t) {
            $errorMessage = "An Error Ocurred: " . $t->getMessage();
        }

        $internalServerError->setContent($errorMessage);
        return $internalServerError;
    }

    //batch_revalidation_status_check

    /**
     * @Route("/long_running/federal/nominal_roll/batch_re_validation/status/check", name="long_running_federal_nominal_roll_batch_re_validation_status_check")
     */
    public function federalNominalRollBatchReValidationStatusCheckAction()
    {
        //set_time_limit(1800);
        $internalServerError = new Response();
        $internalServerError->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $errorMessage = '';


        $batchRevalidationService = $this->get('app.batch_revalidation');

        try {

            $revalidationAnalysis = $batchRevalidationService->getBatchRevalidationStatus();

            //$this->get('logger')->info(print_r($revalidationAnalysis, true));

            $successResponse = new JsonResponse();
            $successResponse->setData($revalidationAnalysis);
            return $successResponse;

        } catch (Throwable $t) {
            $errorMessage = "An exception occurred";
        } finally {
        }

        $internalServerError->setContent($errorMessage);
        return $internalServerError;
    }

}