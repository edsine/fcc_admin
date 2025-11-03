<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/2/2017
 * Time: 1:00 PM
 */

namespace AppBundle\Controller\MdaDeskOffice\Baseline;


use AppBundle\AppException\AppException;
use AppBundle\Model\Notification\AlertNotification;
use AppBundle\Model\Security\SecurityUser;
use AppBundle\Model\SubmissionSetup\MdaBaseline;
use AppBundle\Model\Users\UserProfile;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\GUIDHelper;
use AppBundle\Utils\RecordSelectorHelper;
use AppBundle\Validation\Validator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class MdaBaselineController extends Controller
{
    /**
     * @Route("secure_area/mda_admin/no_baseline_notice", name="mda_no_baseline_notice")
     */
    public function noBaselineNoticeAction(){

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->getUser();

        return $this->render('secure_area/user_account_profile/mda/no_baseline_notice.html.twig');

    }

    /**
     * @Route("secure_area/mda_admin/mda_baseline_edit",name="mda_baseline_edit")
     */
    public function mdaBaselineUpdateAction(Request $request)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var SecurityUser $loggedInUser
         */
        $loggedInUser = $this->getUser();

        $profileSummary = null; //for the left side bar display

        $mdaBaselineService = $this->get('app.mda_baseline_service');

        $validator = new Validator();
        $this->initializeValidationFields($validator);

        $alertNotification =  new AlertNotification();

        $mdaBaseline = new MdaBaseline();
        $outcome = false;

        try {
            $profileSummary = $this->get('app.user_profile_service')->getUserProfile($loggedInUser->getGuid());
        } catch (AppException $app_ex) {

        }

        if (!$request->request->has("btn_submit")) {//first page load

            try {
                $mdaBaseline = $mdaBaselineService->getMdaBaselineByOrganization($loggedInUser->getOrganizationId());
            } catch (AppException $e) {
                $this->get('logger')->info($e->getMessage());
                $alertNotification->addError('Baseline year could not be loaded, go back and try again.');
            }

            if (!$mdaBaseline) {
                $mdaBaseline = new MdaBaseline();
            }

        } else if ($request->request->has("btn_submit")) {

            $mdaBaseline = $this->fillModelFromRequest($request);
            $this->validateForm($validator, $mdaBaseline);

            if (!$validator->getFields()->hasErrors()) {

                $today = date("Y-m-d H:i:s");
                $mdaBaseline->setLastModified($today);
                $mdaBaseline->setLastModifiedByUserId($loggedInUser->getId());

                $mdaBaseline->setOrganizationId($loggedInUser->getOrganizationId());

                if($mdaBaseline->getYearOfEstablishment() <= 2008){
                    $mdaBaseline->setBaselineYear(2008);
                }else{
                    $mdaBaseline->setBaselineYear($mdaBaseline->getYearOfEstablishment());
                }

                try {
                    if($mdaBaseline->getSelector()){
                        $outcome = $this->get('app.mda_baseline_service')->editMdaBaseline($mdaBaseline);
                        $alertNotification->addSuccess('Baseline year updated successfully');
                    }else{
                        $recordSelectorHelper = new RecordSelectorHelper();
                        $mdaBaseline->setSelector($recordSelectorHelper->generateSelector());

                        $outcome = $this->get('app.mda_baseline_service')->addMdaBaseline($mdaBaseline);
                        $alertNotification->addSuccess('Baseline year saved successfully');
                    }

                    if($outcome){
                        $mdaBaseline = $mdaBaselineService->getMdaBaselineByOrganization($loggedInUser->getOrganizationId());
                    }
                } catch (AppException $app_exc) {
                    $this->get('logger')->alert($app_exc->getMessage());
                    switch ($app_exc->getMessage()) {
                        case AppConstants::DUPLICATE:
                            $errorMessage = "Duplicate baseline setup.";
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

        return $this->render("secure_area/user_account_profile/mda/baseline_edit.html.twig",
            array(
                'mdaBaseline' => $mdaBaseline,
                'profileSummary' => $profileSummary,
                'alertNotification' => $alertNotification,
                'outcome' => $outcome,
                'validator' => $validator
            ));
    }

    //helper methods
    private function initializeValidationFields(Validator $validator)
    {
        $validator->getFields()->addField('year_of_establishment', "Year of establishment is required");
        $validator->getFields()->addField('year_of_establishment_total_digits', "Invalid Input");
        $validator->getFields()->addField('confirm_year_of_establishment', "Please confirm year of establishment");
        $validator->getFields()->addField('confirm_year_of_establishment_total_digits', "Invalid Input");
        $validator->getFields()->addField('year_of_establishment_match', "Inputs do not match");
        $validator->getFields()->addField('selector', "Invalid record identifier");
    }

    private function validateForm(Validator $validator, MdaBaseline $mdaBaseline)
    {

        $validator->datePattern('year_of_establishment', $mdaBaseline->getYearOfEstablishment(), 'Y','Must be a valid year.');
        $validator->totalDigits('year_of_establishment_total_digits', $mdaBaseline->getYearOfEstablishment(), 4);
        $validator->datePattern('confirm_year_of_establishment', $mdaBaseline->getConfirmYearOfEstablishment(), 'Y','Must be a valid year.');
        $validator->totalDigits('confirm_year_of_establishment_total_digits', $mdaBaseline->getConfirmYearOfEstablishment(), 4);
        $validator->matches('year_of_establishment_match', $mdaBaseline->getYearOfEstablishment(), $mdaBaseline->getConfirmYearOfEstablishment());
        //$validator->textRequired('selector', $mdaBaseline->getGuid());

    }

    private function fillModelFromRequest(Request $request)
    {
        $mdaBaseline = new MdaBaseline();
        $mdaBaseline->setYearOfEstablishment($request->request->get("year_of_establishment"));
        $mdaBaseline->setConfirmYearOfEstablishment($request->request->get("confirm_year_of_establishment"));
        $mdaBaseline->setSelector($request->request->get("selector"));

        return $mdaBaseline;
    }

}