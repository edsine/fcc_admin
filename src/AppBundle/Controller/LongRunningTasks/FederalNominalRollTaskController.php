<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/30/2016
 * Time: 1:50 PM
 */

namespace AppBundle\Controller\LongRunningTasks;


use AppBundle\AppException\AppException;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\FileUploadHelper;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use \Throwable;

class FederalNominalRollTaskController extends Controller
{
    /**
     * @Route("/long_running/federal/nominal_roll/stage_and_validate", name="long_running_federal_nominal_roll_stage_and_validate")
     */
    public function longRunningFederalNominalRollStageAndValidateAction(Request $request)
    {
        //set_time_limit(1800);

        $logger = $this->get('logger');
        $submissionId = $request->request->get('submissionId');
        //$submissionId = $request->query->get('submissionId');

        $logger->alert('STAGE AND VALIDATE: RECEIVED ID FROM SCHEDULER ' . $submissionId);

        $internalServerError = new Response();
        $internalServerError->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $errorMessage = '';

        $submissionService = $this->get('app.federal_nominal_roll_service');

        if ($submissionId) {

            //return new Response("RECEIVED MAIN CONTROLLER POST: " . $submissionId);

            $nominalRoleSubmission = null;

            try {
                $nominalRoleSubmission = $submissionService->getSubmission($submissionId);
            } catch (AppException $e) {
            }

            $mdaEstablishmentCode = $nominalRoleSubmission->getOrganizationEstablishmentCode();

            if ($nominalRoleSubmission) {

                $presentValidationStatus = $nominalRoleSubmission->getValidationStatus();

                //only recheck validation if the status is pending
                //to avoid simultaneous double validation or other conditions
                if ($presentValidationStatus === AppConstants::PENDING) {

                    $logger = $this->get('logger');

                    $reader = null;
                    try {

                        //$logger->alert('STAGE AND VALIDATE: START TIME ' . date('H:i:s'));

                        $reader = ReaderFactory::create(Type::XLSX); // for XLSX files
                        //$reader = ReaderFactory::create(Type::CSV); // for CSV files
                        //$reader = ReaderFactory::create(Type::ODS); // for ODS files
                        $reader->setShouldFormatDates(true);

                        $fileUploadHelper = new FileUploadHelper();

                        $uploadFilePath = $fileUploadHelper->getNominalRollUploadDirectory($mdaEstablishmentCode) . $nominalRoleSubmission->getUploadedFileName();

                        $reader->open($uploadFilePath);

                        $data_rows = array();

                        $organizationMnemonic = $nominalRoleSubmission->getOrganizationMnemonic();

                        //$employeeNumberStart = $organizationMnemonic . "/";
                        //$employeeNumberPattern = "/^" . $organizationMnemonic . "[[.slash.]][0-9]{6}$/i";

                        $logger->alert('STAGE AND VALIDATE : START ITERATING LOOP: ' . $submissionId . ' : ');
                        foreach ($reader->getSheetIterator() as $sheet) {
                            //read only the first sheet
                            if ($sheet->getIndex() === 0) { // index is 0-based

                                foreach ($sheet->getRowIterator() as $row) {
                                    // check if its a data row
                                    //if the first cell is a number

                                    $serialNo = trim($row[0]);

                                    $is_data_row = filter_var($serialNo, FILTER_VALIDATE_INT);
                                    if ($is_data_row) {
                                        $data = array();
                                        $data[] = $submissionId;
                                        $data[] = $nominalRoleSubmission->getSubmissionYear();
                                        $data[] = trim($serialNo);

                                        $employeeStatus = $row[1];
                                        $data[] = trim($employeeStatus);

                                        $employeeNumber = $row[2];
                                        $data[] = trim($employeeNumber);

                                        $name = $row[3];
                                        $data[] = trim($name);

                                        $nationalityCode = $row[4];
                                        $data[] = trim($nationalityCode);

                                        $stateOfOrigin = $row[5];
                                        $data[] = trim($stateOfOrigin);

                                        $lga = $row[6];
                                        $data[] = trim($lga);

                                        $geoPoliticalZone = $row[7];
                                        $data[] = trim($geoPoliticalZone);

                                        $dateOfBirth = $row[8];
                                        $data[] = trim($dateOfBirth); //date of birth

                                        $dateOfEmployment = $row[9];
                                        $data[] = trim($dateOfEmployment); //date of employment

                                        $dateOfPresentAppointment = $row[10];
                                        $data[] = trim($dateOfPresentAppointment);

                                        $gradeLevel = $row[11];
                                        $data[] = trim($gradeLevel);

                                        $designation = $row[12];
                                        $data[] = trim($designation);

                                        $stateOfDeployment = $row[13];
                                        $data[] = trim($stateOfDeployment);

                                        $gender = $row[14];
                                        $data[] = trim($gender);

                                        $maritalStatus = $row[15];
                                        $data[] = trim($maritalStatus);

                                        $physicallyChallengedStatus = $row[16];
                                        $data[] = trim($physicallyChallengedStatus);

                                        if ($nominalRoleSubmission->isQuarterlyReturn()) {
                                            $quarterlyReturnEmploymentStatus = $row[17];
                                            $data[] = trim($quarterlyReturnEmploymentStatus);
                                        }

                                        $data_rows[] = $data;

                                        //set them to null
                                        $serialNo = null;
                                        $employeeStatus = null;
                                        $employeeNumber = null;
                                        $name = null;
                                        $nationalityCode = null;
                                        $stateOfOrigin = null;
                                        $dateOfBirth = null;
                                        $dateOfEmployment = null;
                                        $dateOfPresentAppointment = null;
                                        $gradeLevel = null;
                                        $designation = null;
                                        $stateOfDeployment = null;
                                        $gender = null;
                                        $maritalStatus = null;
                                        $lga = null;
                                        $geoPoliticalZone = null;
                                        $physicallyChallengedStatus = null;
                                        $quarterlyReturnEmploymentStatus = null;
                                    }

                                }

                                break; // no need to read more sheets
                            }
                        }

                        //$logger->alert('STAGE AND VALIDATE : FINISHED READING EXCEL');

                        if (!empty($data_rows)) {

                            //$logger->info(date('H:i:s') . ' DATA FOUND: ' . $data_rows[0]);
                            //$logger->alert('STAGE AND VALIDATE: TOTAL DATA FOUND: ' . count($data_rows) . ' ' . date('H:i:s'));
                            $totalRowsReadFromExcel = count($data_rows);

                            $nominalRoleBgTaskService = $this->get('app.nominal_role_bg_task_service');

                            $affectedRows = $nominalRoleBgTaskService->importNominalRoleMultiInsert($data_rows, $nominalRoleSubmission);

                            $data_rows = null;

                            //$logger->alert("STAGE AND VALIDATE: RECORDS MULTI-INSERT IMPORTED: Total Rows: " . $affectedRows . ' ' . date('H:i:s'));


                            if ($affectedRows === $totalRowsReadFromExcel) {

                                $validationResult = $nominalRoleBgTaskService->validateNominalRollSubmission($nominalRoleSubmission);

                                $logger->alert('STAGE AND VALIDATE: VALIDATION COMPLETED ' . $submissionId . ' : ' . date('H:i:s'));

                                //NOTIFY THE DESK OFFICER
                                //send message to MIS HEAD
                                if ($nominalRoleSubmission->getFccDeskOfficerPhone()) {
                                    $phones = array();
                                    $phones[] = $nominalRoleSubmission->getFccDeskOfficerPhone();
                                    $phoneNumbers = implode(',', $phones);

                                    $message = $nominalRoleSubmission->getOrganizationName() . " has uploaded a nominal roll pending your confirmation.";
                                    $this->get('app.notification_sender_service')->sendSms($message, "FCC PORTAL", $phoneNumbers);
                                }

                                $successResponse = new JsonResponse();
                                $successResponse->setData(array(
                                    'opStatus' => 'OK'
                                ));
                                return $successResponse;

                            } else {
                                $submissionService->updateNominalRoleValidationStatus($submissionId, AppConstants::FATAL_ERROR);

                                $errorMessage = 'INCOMPLETE DATA LOADED';
                                $logger->alert('STAGE AND VALIDATE: INCOMPLETE DATA LOADED ' . $submissionId . ' : ' . date('H:i:s'));
                            }

                        } else {
                            $submissionService->updateNominalRoleValidationStatus($submissionId, AppConstants::FATAL_ERROR);
                            $errorMessage = 'No Data Records Found';
                            $logger->alert('STAGE AND VALIDATE: NO DATA RECORDS FOUND ' . $submissionId . ' : ' . date('H:i:s'));
                        }


                        //$logger->alert('STAGE AND VALIDATE: ' . date('H:i:s') . ' CURRENT MEMORY USAGE: ' . (memory_get_usage(true) / 1024 / 1024) . " MB");
                        //$logger->alert('STAGE AND VALIDATE: ' . date('H:i:s') . " PEAK MEMORY USAGE: " . (memory_get_peak_usage(true) / 1024 / 1024) . " MB");

                    } catch(Throwable $t) {
                        try {
                            $submissionService->updateNominalRoleValidationStatus($submissionId, AppConstants::FATAL_ERROR);
                        } catch (AppException $e) {
                        }
                        $errorMessage = 'An error ocurred, Try Again. ' . $t->getMessage();
                        $logger->error('STAGE AND VALIDATE : THROWABLE: ' . $submissionId . ' : ' . $t->getMessage());
                    } finally {
                        if ($reader) {
                            $reader->close();
                            //$logger->alert('STAGE AND VALIDATE : READER CLOSED');
                        }
                    }

                } else {
                    $successResponse = new JsonResponse();
                    $successResponse->setData(array(
                        'opStatus' => 'OK'
                    ));
                    return $successResponse;
                }
            } else {
                try {
                    $submissionService->updateNominalRoleValidationStatus($submissionId, AppConstants::FATAL_ERROR);
                } catch (AppException $e) {
                }
                $errorMessage = 'Submission details could not be loaded';
                //$logger->error('STAGE AND VALIDATE: COULD NOT LOAD SUBMISSION DETAIL FROM DATABASE');
            }
        } else {
            $errorMessage = 'Invalid submission id';
            //$logger->error('STAGE AND VALIDATE: INVALID SUBMISSION ID');
        }

        $internalServerError->setContent($errorMessage);
        return $internalServerError;
    }

}