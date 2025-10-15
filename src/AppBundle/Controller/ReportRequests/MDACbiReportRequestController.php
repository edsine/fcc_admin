<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 8:47 PM
 */

namespace AppBundle\Controller\ReportRequests;


use AppBundle\AppException\AppException;
use AppBundle\AppException\AppExceptionMessages;
use AppBundle\Model\ReportRequests\CBIReportRequest;
use AppBundle\Model\Notification\AlertNotification;
use AppBundle\Model\SearchCriteria\CBIReportRequestSearchCriteria;
use AppBundle\Model\Security\SecurityUser;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\Paginator;
use AppBundle\Utils\RecordSelectorHelper;
use AppBundle\Validation\Validator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;

class MDACbiReportRequestController extends Controller
{

    /**
     * @Route("/secure_area/mda_desk_office/cbi_report_request/list", name="mda_cbi_report_request_list")
     */
    public function listAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var SecurityUser $loggedInUser
         */
        $loggedInUser = $this->getUser();

        $searchFilter = new CBIReportRequestSearchCriteria();
        $searchFilter->setOrganizationId($loggedInUser->getOrganizationId());

        $paginator = new Paginator();
        $paginator->setStartRow($request->request->get('startRow', 0));

        $pageDirection = '';
        if ($request->request->has('btnSearch')) {
            $pageDirection = "FIRST";
        } else if ($request->request->has('btnPageFirst')) {
            $pageDirection = "FIRST";
        } else if ($request->request->has('btnPagePrev')) {
            $pageDirection = "PREVIOUS";
        } else if ($request->request->has('btnPageNext')) {
            $pageDirection = "NEXT";
        } else if ($request->request->has('btnPageLast')) {
            $pageDirection = "LAST";
        }

        $records = array();
        $submissionPermissionRequestService = $this->get('app.cbi_report_request');
        try {
            $records = $submissionPermissionRequestService->searchRecords($searchFilter, $paginator, $pageDirection);
        } catch (AppException $e) {
            $this->get('logger')->info($e->getMessage());
        }

        return $this->render('secure_area/report_requests/mda_admin/mda_cbi_report_request_list.html.twig',
            array('records' => $records,
                'searchFilter' => $searchFilter,
                'paginator' => $paginator
            )
        );
    }

    /**
     * @Route("/secure_area/mda_desk_office/cbi_report_request/new", name="mda_cbi_report_request_new")
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

        $cbiReportRequest = new CBIReportRequest();

        $validator = new Validator();
        $this->initializeValidationFields($validator, AppConstants::NEW);

        $alertNotification = new AlertNotification();
        $outcome = false;

        //check if the form was submitted and process else render empty form
        if ($request->request->has("btnSubmit")) {

            $cbiReportRequest = $this->fillModelFromRequest($request);
            $this->validateForm($validator, $cbiReportRequest, AppConstants::NEW);

            if (!$validator->getFields()->hasErrors()) {

                $cbiReportRequest->setOrganizationId($loggedInUser->getOrganizationId());
                $cbiReportRequest->setApprovalStatus(AppConstants::PENDING);

                $today = date("Y-m-d H:i:s");
                $cbiReportRequest->setLastModified($today);
                $cbiReportRequest->setLastModifiedByUserId($loggedInUser->getId());

                $recordSelectorHelper = new RecordSelectorHelper();
                $cbiReportRequest->setSelector($recordSelectorHelper->generateSelector());


                $cbiReportRequestService = $this->get('app.cbi_report_request');
                try {
                    $outcome = $cbiReportRequestService->addCbiReportRequest($cbiReportRequest);
                    if ($outcome) {

                        //send message to MIS HEAD
                        $misHeads = $this->get('app.shared_data_manager')->getFCCUserByRole(AppConstants::ROLE_MIS_HEAD);
                        if($misHeads){
                            $phones = array();
                            foreach ($misHeads as $misHead){
                                $phones[] = $misHead['primary_phone'];
                            }
                            $phoneNumbers = implode(',', $phones);

                            $message = $loggedInUser->getOrganizationName() . " has sent a request for Character Balancing Index Report.";
                            $this->get('app.notification_sender_service')->sendSms($message, "FCC PORTAL", $phoneNumbers);
                        }

                        $alertNotification->addSuccess('Request sent successfully');
                        $cbiReportRequest = new CBIReportRequest();

                    }
                } catch (AppException $app_exc) {
                    $errorMessage = $app_exc->getMessage();
                    switch ($errorMessage) {
                        case AppExceptionMessages::DUPLICATE_OPEN_PERMISSION:
                        case AppExceptionMessages::NO_PROCESSED_MDA_REPORT:
                            break;

                        default:
                            $errorMessage = AppExceptionMessages::GENERAL_ERROR_MESSAGE;
                            break;
                    }
                    $alertNotification->addError($errorMessage);
                    $this->get('logger')->info($app_exc->getMessage());
                } catch (Exception $e) {

                }
            }

        }

        $cbiReportGradeLevels = $this->get('app.shared_data_manager')->getCharacterBalancingReportGradeLevelCategories();

        return $this->render('secure_area/report_requests/mda_admin/mda_cbi_report_request_new.html.twig',
            array(
                'cbiReportRequest' => $cbiReportRequest,
                'validator' => $validator,
                'outcome' => $outcome,
                'alertNotification' => $alertNotification,
                'cbiReportGradeLevels' => $cbiReportGradeLevels
            )
        );

    }

    //helper methods
    private function initializeValidationFields(Validator $validator, string $which)
    {
        $validator->getFields()->addField('recruitment_value', "Invalid recruitment value");
        $validator->getFields()->addField('cbi_grade_level_category', "Recruitment category is required");
        $validator->getFields()->addField('remark', "Remark is required");

        switch ($which) {
            case AppConstants::EDIT:
                $validator->getFields()->addField('selector', "Invalid record identifier");
                break;

            case AppConstants::CANCEL:
                $validator->getFields()->addField('selector', "Invalid record identifier");
                break;
        }
    }

    private function validateForm(Validator $validator, CBIReportRequest $cbiReportRequest, string $which)
    {
        $validator->integer('recruitment_value', $cbiReportRequest->getRecruitmentValue());
        $validator->required('cbi_grade_level_category', $cbiReportRequest->getCbiGradeLevelCategory());
        $validator->textRequiredMax('remark', $cbiReportRequest->getRemarks(), true, 1, 255);

        switch ($which) {
            case AppConstants::EDIT:
                $validator->required('selector', $cbiReportRequest->getSelector());
                break;

            case AppConstants::CANCEL:
                $validator->required('selector', $cbiReportRequest->getSelector());
                break;
        }
    }

    private function fillModelFromRequest(Request $request)
    {
        $cbiReportRequest = new CBIReportRequest();
        $cbiReportRequest->setRecruitmentValue($request->request->get("recruitment_value"));
        $cbiReportRequest->setCbiGradeLevelCategory($request->request->get("cbi_grade_level_category"));
        $cbiReportRequest->setRemarks($request->request->get("remark"));
        $cbiReportRequest->setSelector($request->request->get("selector"));

        return $cbiReportRequest;
    }

}