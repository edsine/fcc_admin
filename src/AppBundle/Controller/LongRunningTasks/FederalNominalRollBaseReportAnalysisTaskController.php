<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/30/2016
 * Time: 1:50 PM
 */

namespace AppBundle\Controller\LongRunningTasks;

use AppBundle\Utils\AppConstants;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use \Throwable;

class FederalNominalRollBaseReportAnalysisTaskController extends Controller
{
    /**
     * @Route("/long_running/federal/nominal_roll/base_report_analysis", name="long_running_federal_nominal_roll_base_report_analysis")
     */
    public function longRunningFederalNominalRollBaseReportAnalysisAction(Request $request)
    {
        //set_time_limit(1800);

        $logger = $this->get('logger');
        $submissionId = $request->request->get('submissionId');
        
        $logger->alert('RECEIVED ID: ' . $submissionId . ' FOR PROCESSING ON ' . date('H:i:s'));

        $internalServerError = new Response();
        $internalServerError->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $errorMessage = '';

        if ($submissionId) {

            $submissionService = $this->get('app.federal_nominal_roll_service');

            $nominalRoleSubmission = $submissionService->getSubmission($submissionId);

            if ($nominalRoleSubmission) {

                $logger = $this->get('logger');


                try {

                    //$logger->info('REPORT GEN: START TIME ' . date('H:i:s'));

                    $outcome = false;

                    if($nominalRoleSubmission->isIsFederalLevelSubmission()){
                        //$logger->info('REPORT GEN: PROCESSINF FEDERAL LEVEL ' . date('H:i:s'));
                        $outcome = $this->get('app.nominal_role_bg_task_service')->processSubmissionAnalysis($nominalRoleSubmission);

                        if($outcome){
                            $this->get('app.shared_data_manager')->removeCacheItem(AppConstants::KEY_CACHED_ORGANIZATIONS);
                        }

                    }else if($nominalRoleSubmission->isIsStateLevelSubmission()){
                        //$logger->info('REPORT GEN: PROCESSINF STATE LEVEL ' . date('H:i:s'));
                        //$outcome = $this->get('app.state_establishment_nominal_role_bg_task_service')->processSubmissionAnalysis($nominalRoleSubmission, $logger);
                    }

                    //$logger->info('REPORT GEN: OUTCOME ' . $outcome);

                    //$logger->info('REPORT GEN: ' . date('H:i:s') . ' CURRENT MEMORY USAGE: ' . (memory_get_usage(true) / 1024 / 1024) . " MB");
                    //$logger->info('REPORT GEN: ' . date('H:i:s') . " PEAK MEMORY USAGE: " . (memory_get_peak_usage(true) / 1024 / 1024) . " MB");

                    $successResponse = new JsonResponse();
                    $successResponse->setData(array(
                        'opStatus' => 'OK'
                    ));
                    return $successResponse;

                } catch (Throwable $t) {
                    $errorMessage = "An exception occurred";
                    $logger->alert('REPORT GEN: THROWABLE: ' . $t->getMessage());
                } finally {
                }
            } else {
                $errorMessage = "An error occurred while getting submission details";
                //$logger->error('REPORT GEN: COULD NOT LOAD SUBMISSION DETAIL FROM DATABASE');
            }
        } else {
            $errorMessage = "Invalid Submission Id";
            //$logger->error('REPORT GEN: INVALID SUBMISSION ID');
        }


        $internalServerError->setContent($errorMessage);
        return $internalServerError;
    }

    /**
     * @Route("/long_running/nominal_role_report_analysis2", name="nominal_role_report_analysis2")
     */
    public function testStuff()
    {
        return new Response("Hi there!: <br>");
    }

}