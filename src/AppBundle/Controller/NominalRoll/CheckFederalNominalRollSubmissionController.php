<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/27/2016
 * Time: 8:41 AM
 */

namespace AppBundle\Controller\NominalRoll;


use AppBundle\AppException\AppException;
use AppBundle\Model\Notification\AlertNotification;
use AppBundle\Model\Users\UserProfile;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\HttpHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CheckFederalNominalRollSubmissionController extends Controller
{
    /**
     * @Route("/secure_area/federal/mda_admin/nominal_roll/main/submission/new/check", name="check_new_federal_mda_admin_nominal_roll_main_submission")
     */
    public function showCheckNewMainFederalNominalRollSubmissionAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var UserProfile $loggedInUser
         */
        $loggedInUser = $this->getUser();
        $alertNotification = new AlertNotification();

        $submissionYear = $request->request->get('submissionYear');

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

        $submissionYears = $this->get('app.mda_baseline_service')->getMdaSubmissionYears($loggedInUser->getOrganizationBaseLineYear());

        $submissionInstructions = $this->get('app.shared_data_manager')->getStaticContent(AppConstants::STATIC_CONTENT_FEDERAL_LEVEL_NOMINAL_ROLE_SUBMISSION_INSTRUCTIONS);

        $connectedToMiddleware = false;
        $middlewareResponseCode = HttpHelper::pingMiddleWare();

        if($middlewareResponseCode == 200){
            $connectedToMiddleware = true;
        }
        $submissionType = AppConstants::MAIN_SUBMISSION;

        $federalNominalRollService = $this->get('app.federal_nominal_roll_service');

        $missingSubmissionYears = null;

        if($request->request->has('btn_submit')){
            if($submissionYear){
                $missingSubmissionYears = $federalNominalRollService->getMissingSubmissionYears($loggedInUser->getOrganizationId(), $submissionYear);
                if(empty($missingSubmissionYears)){
                    $submissionYearInBase64 = base64_encode($submissionYear);
                    return $this->redirectToRoute('federal_mda_admin_nominal_roll_main_submission_new'
                        , [
                            'submissionYearInBase64' => $submissionYearInBase64
                        ]
                    );
                }else{

                    $missingYearString = implode(', ', $missingSubmissionYears);

                    $errorMessage = "You cannot make submission for $submissionYear at this moment.";
                    $errorMessage .= "<br><br>Your MDA has not made submission for the following years $missingYearString respectively.";
                    $errorMessage .= "<br>To continue please PREPARE THE NOMINAL ROLL FOR THESE YEARS respectively and submit.";
                    $alertNotification->addError(
                        $errorMessage
                    );
                }
            }else{
                $alertNotification->addError('Submission Year Is Required');
            }
        }

        return $this->render('secure_area/federal/nominal_roll/mda_admin/check_new_federal_mda_admin_nominal_roll_main_submission.html.twig',
            array(
                'submissionYears' => $submissionYears,
                'submissionYear' => $submissionYear,
                'submissionInstructions' => $submissionInstructions,
                'submissionType' => $submissionType,
                'connectedToMiddleware' => $connectedToMiddleware,
                'missingSubmissionYears' => $missingSubmissionYears,
                'alertNotification' => $alertNotification
            ));
    }

    /**
     * @Route("/secure_area/federal/mda_admin/nominal_roll/quarterly/return/new/check", name="check_new_federal_mda_admin_nominal_roll_quarterly_return")
     */
    public function showCheckNewFederalNominalRollQuarterlyReturnsAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var UserProfile $loggedInUser
         */
        $loggedInUser = $this->getUser();

        $submissionYear = $request->request->get('submissionYear');

        $alertNotification = new AlertNotification();

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

        $submissionYears = $this->get('app.mda_baseline_service')->getMdaSubmissionYears($loggedInUser->getOrganizationBaseLineYear());

        $submissionInstructions = $this->get('app.shared_data_manager')->getStaticContent(AppConstants::STATIC_CONTENT_FEDERAL_LEVEL_NOMINAL_ROLE_SUBMISSION_INSTRUCTIONS);

        $connectedToMiddleware = false;
        $middlewareResponseCode = HttpHelper::pingMiddleWare();

        if($middlewareResponseCode == 200){
            $connectedToMiddleware = true;
        }
        $submissionType = AppConstants::QUARTERLY_RETURN;

        $federalNominalRollService = $this->get('app.federal_nominal_roll_service');

        if($request->request->has('btn_submit')){
            if($submissionYear){
                $canUploadReturn = $federalNominalRollService->checkIfReturnCanBeUploaded($loggedInUser->getOrganizationId(), $submissionYear);
                if($canUploadReturn){
                    $submissionYearInBase64 = base64_encode($submissionYear);
                    return $this->redirectToRoute('federal_mda_admin_nominal_roll_quarterly_return_new'
                        , [
                            'submissionYearInBase64' => $submissionYearInBase64
                        ]
                    );
                }else{
                    $errorMessage = "A main submission must exist before a quarterly return can be made";
                    $errorMessage .= "<br/>We did not find any $submissionYear MAIN SUBMISSION.";
                    $alertNotification->addError($errorMessage);
                }
            }else{
                $alertNotification->addError('Submission Year Is Required');
            }
        }

        return $this->render('secure_area/federal/nominal_roll/mda_admin/check_new_federal_mda_admin_nominal_roll_quarterly_return.html.twig',
            array(
                'submissionYears' => $submissionYears,
                'submissionYear' => $submissionYear,
                'submissionInstructions' => $submissionInstructions,
                'submissionType' => $submissionType,
                'connectedToMiddleware' => $connectedToMiddleware,
                'alertNotification' => $alertNotification
            ));
    }
}