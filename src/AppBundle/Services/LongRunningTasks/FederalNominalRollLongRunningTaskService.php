<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/1/2017
 * Time: 4:28 PM
 */

namespace AppBundle\Services\LongRunningTasks;


use AppBundle\AppException\AppException;
use AppBundle\Model\Submission\NominalRollValidation;
use AppBundle\Model\Submission\NominalRollSubmission;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\ServiceHelper;
use Doctrine\DBAL\Connection;
use \Throwable;
use \PDO;

class FederalNominalRollLongRunningTaskService extends ServiceHelper
{
    private $connection;
    private $rows_per_page;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->rows_per_page = AppConstants::ROWS_PER_PAGE;
    }

    public function importNominalRoleFromCSV($mysqlLoadCSVPath, NominalRollSubmission $nominalRollSubmission): int
    {
        $affectedRows = 0;
        $statement = null;

        try {

            //start transaction
            $this->connection->beginTransaction();

            //now load csv into database
            $query = "LOAD DATA INFILE '$mysqlLoadCSVPath'
                    INTO TABLE federal_level_nominal_roll_submission_staging 
                    FIELDS TERMINATED BY ',' 
                    OPTIONALLY ENCLOSED BY '\"' 
                    LINES TERMINATED BY '\n' 
                    (submission_id,submission_year,serial_number,staff_disposition,staff_type,employee_status
                    ,employee_number,employee_name,nationality_code,state_of_origin_code,lga_code,region_code
                    ,date_of_birth,date_of_employment,date_of_present_appointment,grade_level,designation
                    ,state_of_deployment_code,gender,marital_status
                    ,physically_challenged_status,quarterly_return_employment_status)";

            $totalCsvRowsInserted = $this->connection->exec($query);

            //re-count total affected dont rely on load data
            $query = "SELECT count(*) FROM federal_level_nominal_roll_submission_staging WHERE submission_id=:submission_id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':submission_id', $nominalRollSubmission->getSubmissionId());
            $statement->execute();

            $affectedRows = $statement->fetchColumn(0);

            //update the total rows column in the table
            $query = "UPDATE federal_level_nominal_roll_submissions SET total_rows_imported=:total_rows_imported "
                . " WHERE submission_id=:submission_id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':total_rows_imported', $affectedRows);
            $statement->bindValue(':submission_id', $nominalRollSubmission->getSubmissionId());
            $statement->execute();

            //commit transaction
            $this->connection->commit();

        } catch (Throwable $e) {

            if ($this->connection->isTransactionActive()) {
                $this->connection->rollBack();
            }

            throw new AppException($e->getMessage());

        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $affectedRows;
    }

    public function importNominalRoleMultiInsert($nominalRollSubmissionRows, NominalRollSubmission $nominalRoleSubmission): int
    {
        $affectedRows = 0;
        $statement = null;

        try {

            //start transaction
            $this->connection->beginTransaction();

            //PREPARE THE DETAILED BATCH DATA
            $batchNominalRollData = array();
            foreach ($nominalRollSubmissionRows as $nominalRollRow) {
                $nominalRollDataRecord = array();

                $nominalRollDataRecord['submission_id'] = $this->getValueOrNull($nominalRollRow[0]);
                $nominalRollDataRecord['submission_year'] = $this->getValueOrNull($nominalRollRow[1]);

                $nominalRollDataRecord['serial_number'] = $this->getValueOrNull($nominalRollRow[2]);
                $nominalRollDataRecord['employee_status'] = $this->getValueOrNull($nominalRollRow[3]);
                $nominalRollDataRecord['employee_number'] = $this->getValueOrNull($nominalRollRow[4]);
                $nominalRollDataRecord['employee_name'] = $this->getValueOrNull($nominalRollRow[5]);
                $nominalRollDataRecord['nationality_code'] = $this->getValueOrNull($nominalRollRow[6]);
                $nominalRollDataRecord['state_of_origin_code'] = $this->getValueOrNull($nominalRollRow[7]);
                $nominalRollDataRecord['lga_code'] = $this->getValueOrNull($nominalRollRow[8]);
                $nominalRollDataRecord['region_code'] = $this->getValueOrNull($nominalRollRow[9]);
                $nominalRollDataRecord['date_of_birth'] = $this->getValueOrNull($nominalRollRow[10]);
                $nominalRollDataRecord['date_of_employment'] = $this->getValueOrNull($nominalRollRow[11]);
                $nominalRollDataRecord['date_of_present_appointment'] = $this->getValueOrNull($nominalRollRow[12]);
                $nominalRollDataRecord['grade_level'] = $this->getValueOrNull($nominalRollRow[13]);
                $nominalRollDataRecord['designation'] = $this->getValueOrNull($nominalRollRow[14]);
                $nominalRollDataRecord['state_of_deployment_code'] = $this->getValueOrNull($nominalRollRow[15]);
                $nominalRollDataRecord['gender'] = $this->getValueOrNull($nominalRollRow[16]);
                $nominalRollDataRecord['marital_status'] = $this->getValueOrNull($nominalRollRow[17]);
                $nominalRollDataRecord['physically_challenged_status'] = $this->getValueOrNull($nominalRollRow[18]);

                if($nominalRoleSubmission->isQuarterlyReturn()){
                    $nominalRollDataRecord['quarterly_return_employment_status'] = $this->getValueOrNull($nominalRollRow[19]);
                }

                $batchNominalRollData[] = $nominalRollDataRecord;
            }

            $outcome = $this->pdoMultiInsertReturnTotal('federal_level_nominal_roll_submission_staging', $batchNominalRollData);

            //re-count total affected dont rely on load data
            $query = "SELECT count(*) FROM federal_level_nominal_roll_submission_staging WHERE submission_id=:submission_id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':submission_id', $nominalRoleSubmission->getSubmissionId());
            $statement->execute();

            $affectedRows = $statement->fetchColumn(0);

            //update the total rows column in the table
            $query = "UPDATE federal_level_nominal_roll_submissions SET total_rows_imported=:total_rows_imported "
                . " WHERE submission_id=:submission_id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':total_rows_imported', $affectedRows);
            $statement->bindValue(':submission_id', $nominalRoleSubmission->getSubmissionId());
            $statement->execute();

            //commit transaction
            $this->connection->commit();

        } catch (Throwable $e) {

            if ($this->connection->isTransactionActive()) {
                $this->connection->rollBack();
            }

            throw new AppException($e->getMessage());

        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $affectedRows;
    }

    public function validateNominalRollSubmission(NominalRollSubmission $nominalRollSubmission): bool
    {
        $outcome = false;
        $statement = null;

        try {

            $totalInvalidEmployeeStatus = 0;
            $totalInvalidEmployeeNumber = 0;
            $totalInvalidName = 0;
            $totalInvalidNationalityCode = 0;
            $totalInvalidStateOfOrigin = 0;
            $totalInvalidDateOfBirth = 0;
            $totalInvalidDateOfEmployment = 0;
            $totalInvalidDateOfPresentAppointment = 0;
            $totalInvalidGradeLevel = 0;
            $totalInvalidDesignation = 0;
            $totalInvalidStateOfLocation = 0;
            $totalInvalidGender = 0;
            $totalInvalidMaritalStatus = 0;
            $totalInvalidLga = 0;
            $totalInvalidGeopoliticalZone = 0;
            $totalInvalidPhysicallyChallenged = 0;
            $totalInvalidQuarterlyReturnEmploymentStatus = 0;

            $nominalRoleFailedRecords = array();

            //VALIDATE EMPLOYEE STATUS
            $query = "SELECT * 
                FROM federal_level_nominal_roll_submission_staging 
                WHERE submission_id=:submission_id 
                AND 
                 (
                     (employee_status NOT IN (SELECT c.status_code FROM employee_status c WHERE c.record_status='ACTIVE')) 
                     AND (employee_status IS NOT NULL AND TRIM(employee_status)<>'')   
                ) ";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':submission_id', $nominalRollSubmission->getSubmissionId());
            $statement->execute();
            $employeeStatusFailedRecords = $statement->fetchAll(PDO::FETCH_ASSOC);

            /**
             * @var NominalRollValidation $failedRecord
             */
            if ($employeeStatusFailedRecords) {
                $totalInvalidEmployeeNumber = count($employeeStatusFailedRecords);

                foreach ($employeeStatusFailedRecords as $invalidRecord) {
                    $failedRecord = new NominalRollValidation();
                    $failedRecord->setId($invalidRecord['id']);
                    $failedRecord->setSubmissionId($invalidRecord['submission_id']);

                    $failedRecord->setFailedEmployeeStatus(1);

                    $nominalRoleFailedRecords[$failedRecord->getId()] = $failedRecord;
                }

            }


            //VALIDATE EMPLOYEE NUMBER
            $query = "SELECT * 
                FROM federal_level_nominal_roll_submission_staging 
                WHERE submission_id=:submission_id 
                AND 
                 (
                     (employee_number IS NULL) 
                     OR (TRIM(employee_number))=''  
                ) ";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':submission_id', $nominalRollSubmission->getSubmissionId());
            $statement->execute();

            $employeeNumberFailedRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($employeeNumberFailedRecords) {
                $totalInvalidEmployeeNumber = count($employeeNumberFailedRecords);

                foreach ($employeeNumberFailedRecords as $invalidRecord) {
                    if (array_key_exists($invalidRecord['id'], $nominalRoleFailedRecords)) {
                        $failedRecord = $nominalRoleFailedRecords[$invalidRecord['id']];
                        $failedRecord->setFailedEmployeeNumber(1);

                        $nominalRoleFailedRecords[$invalidRecord['id']] = $failedRecord;
                    } else {
                        $failedRecord = new NominalRollValidation();
                        $failedRecord->setId($invalidRecord['id']);
                        $failedRecord->setSubmissionId($invalidRecord['submission_id']);

                        $failedRecord->setFailedEmployeeNumber(1);
                        $nominalRoleFailedRecords[$failedRecord->getId()] = $failedRecord;
                    }
                }

            }

            //VALIDATE EMPLOYEE NAME
            $query = "SELECT * 
                FROM federal_level_nominal_roll_submission_staging 
                WHERE submission_id=:submission_id 
                AND 
                ( 
                     (employee_name IS NULL) 
                     OR (trim(employee_name)='')  
                )";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':submission_id', $nominalRollSubmission->getSubmissionId());
            $statement->execute();
            $employeeNameFailedRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($employeeNameFailedRecords) {
                $totalInvalidName = count($employeeNameFailedRecords);

                foreach ($employeeNameFailedRecords as $invalidRecord) {
                    if (array_key_exists($invalidRecord['id'], $nominalRoleFailedRecords)) {
                        $failedRecord = $nominalRoleFailedRecords[$invalidRecord['id']];
                        $failedRecord->setFailedName(1);

                        $nominalRoleFailedRecords[$invalidRecord['id']] = $failedRecord;
                    } else {
                        $failedRecord = new NominalRollValidation();
                        $failedRecord->setId($invalidRecord['id']);
                        $failedRecord->setSubmissionId($invalidRecord['submission_id']);

                        $failedRecord->setFailedName(1);
                        $nominalRoleFailedRecords[$failedRecord->getId()] = $failedRecord;
                    }
                }

            }

            //VALIDATE NATIONALITY CODE
            $query = "SELECT * 
                FROM federal_level_nominal_roll_submission_staging 
                WHERE submission_id=:submission_id 
                AND 
                (
                     (nationality_code NOT IN (SELECT c.id FROM nationality_code c WHERE c.record_status='ACTIVE')) 
                     AND (nationality_code IS NOT NULL AND TRIM(nationality_code)<>'') 
                )";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':submission_id', $nominalRollSubmission->getSubmissionId());
            $statement->execute();
            $nationalityCodeFailedRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($nationalityCodeFailedRecords) {
                $totalInvalidNationalityCode = count($nationalityCodeFailedRecords);

                foreach ($nationalityCodeFailedRecords as $invalidRecord) {
                    if (array_key_exists($invalidRecord['id'], $nominalRoleFailedRecords)) {
                        $failedRecord = $nominalRoleFailedRecords[$invalidRecord['id']];
                        $failedRecord->setFailedNationality(1);

                        $nominalRoleFailedRecords[$invalidRecord['id']] = $failedRecord;
                    } else {
                        $failedRecord = new NominalRollValidation();
                        $failedRecord->setId($invalidRecord['id']);
                        $failedRecord->setSubmissionId($invalidRecord['submission_id']);

                        $failedRecord->setFailedNationality(1);
                        $nominalRoleFailedRecords[$failedRecord->getId()] = $failedRecord;
                    }
                }
            }

            //VALIDATE STATE OF ORIGIN
            $query = "SELECT * 
                FROM federal_level_nominal_roll_submission_staging 
                WHERE submission_id=:submission_id 
                AND 
                ( 
                     (state_of_origin_code NOT IN (SELECT c.state_code FROM states c WHERE c.record_status='ACTIVE')) 
                     AND (state_of_origin_code IS NOT NULL AND TRIM(state_of_origin_code)<>'') 
                )";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':submission_id', $nominalRollSubmission->getSubmissionId());
            $statement->execute();
            $stateOfOriginFailedRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($stateOfOriginFailedRecords) {
                $totalInvalidStateOfOrigin = count($stateOfOriginFailedRecords);

                foreach ($stateOfOriginFailedRecords as $invalidRecord) {
                    if (array_key_exists($invalidRecord['id'], $nominalRoleFailedRecords)) {
                        $failedRecord = $nominalRoleFailedRecords[$invalidRecord['id']];
                        $failedRecord->setFailedStateOfOrigin(1);

                        $nominalRoleFailedRecords[$invalidRecord['id']] = $failedRecord;
                    } else {
                        $failedRecord = new NominalRollValidation();
                        $failedRecord->setId($invalidRecord['id']);
                        $failedRecord->setSubmissionId($invalidRecord['submission_id']);

                        $failedRecord->setFailedStateOfOrigin(1);
                        $nominalRoleFailedRecords[$failedRecord->getId()] = $failedRecord;
                    }
                }
            }

            //VALIDATE DATE OF BIRTH FOR CAREER CIVIL SERVANTS
            $query = "SELECT * 
                FROM federal_level_nominal_roll_submission_staging 
                WHERE submission_id=:submission_id 
                AND 
                ( 
                 ((grade_level IN (SELECT g.grade_level_code FROM grade_level_codes g WHERE g.post_category=:post_category AND g.record_status=:record_status) 
                  ) 
                 AND (str_to_date(date_of_birth,'%e/%b/%Y') IS NULL)) 
                 OR (date_of_birth IS NULL) 
                 OR (trim(date_of_birth)='') 
                )";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':submission_id', $nominalRollSubmission->getSubmissionId());
            $statement->bindValue(':post_category', AppConstants::CAREER_CIVIL_SERVANT);
            $statement->bindValue(':record_status', AppConstants::ACTIVE);
            $statement->execute();
            $dateOfBirthFailedRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($dateOfBirthFailedRecords) {
                $totalInvalidDateOfBirth = count($dateOfBirthFailedRecords);

                foreach ($dateOfBirthFailedRecords as $invalidRecord) {
                    if (array_key_exists($invalidRecord['id'], $nominalRoleFailedRecords)) {
                        $failedRecord = $nominalRoleFailedRecords[$invalidRecord['id']];
                        $failedRecord->setFailedDateOfBirth(1);

                        $nominalRoleFailedRecords[$invalidRecord['id']] = $failedRecord;
                    } else {
                        $failedRecord = new NominalRollValidation();
                        $failedRecord->setId($invalidRecord['id']);
                        $failedRecord->setSubmissionId($invalidRecord['submission_id']);

                        $failedRecord->setFailedDateOfBirth(1);
                        $nominalRoleFailedRecords[$failedRecord->getId()] = $failedRecord;
                    }
                }
            }

            //VALIDATE DATE OF BIRTH FOR POLITICAL CIVIL SERVANTS
            $query = "SELECT * 
                FROM federal_level_nominal_roll_submission_staging 
                WHERE submission_id=:submission_id 
                AND 
                ( 
                 ((grade_level IN (SELECT g.grade_level_code FROM grade_level_codes g WHERE g.post_category=:post_category AND g.record_status=:record_status)) 
                 AND (date_of_birth IS NOT NULL OR trim(date_of_birth)<>'') 
                 AND (str_to_date(date_of_birth,'%e/%b/%Y') IS NULL)) 
                )";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':submission_id', $nominalRollSubmission->getSubmissionId());
            $statement->bindValue(':post_category', AppConstants::POLITICAL_OFFICE_HOLDER);
            $statement->bindValue(':record_status', AppConstants::ACTIVE);
            $statement->execute();
            $dateOfBirthFailedRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($dateOfBirthFailedRecords) {
                $totalInvalidDateOfBirth += count($dateOfBirthFailedRecords);

                foreach ($dateOfBirthFailedRecords as $invalidRecord) {
                    if (array_key_exists($invalidRecord['id'], $nominalRoleFailedRecords)) {
                        $failedRecord = $nominalRoleFailedRecords[$invalidRecord['id']];
                        $failedRecord->setFailedDateOfBirth(1);

                        $nominalRoleFailedRecords[$invalidRecord['id']] = $failedRecord;
                    } else {
                        $failedRecord = new NominalRollValidation();
                        $failedRecord->setId($invalidRecord['id']);
                        $failedRecord->setSubmissionId($invalidRecord['submission_id']);

                        $failedRecord->setFailedDateOfBirth(1);
                        $nominalRoleFailedRecords[$failedRecord->getId()] = $failedRecord;
                    }
                }
            }


            //VALIDATE DATE OF EMPLOYMENT
            $query = "SELECT * 
                FROM federal_level_nominal_roll_submission_staging 
                WHERE submission_id=:submission_id 
                AND 
                ( 
                 (str_to_date(date_of_employment,'%e/%b/%Y') IS NULL) 
                 OR (date_of_employment IS NULL) 
                 OR (trim(date_of_employment)='') 
                )";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':submission_id', $nominalRollSubmission->getSubmissionId());
            $statement->execute();
            $dateOfEmploymentFailedRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($dateOfEmploymentFailedRecords) {
                $totalInvalidDateOfEmployment = count($dateOfEmploymentFailedRecords);

                foreach ($dateOfEmploymentFailedRecords as $invalidRecord) {
                    if (array_key_exists($invalidRecord['id'], $nominalRoleFailedRecords)) {
                        $failedRecord = $nominalRoleFailedRecords[$invalidRecord['id']];
                        $failedRecord->setFailedDateOfEmployment(1);

                        $nominalRoleFailedRecords[$invalidRecord['id']] = $failedRecord;
                    } else {
                        $failedRecord = new NominalRollValidation();
                        $failedRecord->setId($invalidRecord['id']);
                        $failedRecord->setSubmissionId($invalidRecord['submission_id']);

                        $failedRecord->setFailedDateOfEmployment(1);
                        $nominalRoleFailedRecords[$failedRecord->getId()] = $failedRecord;
                    }
                }

            }

            //VALIDATE DATE OF PRESENT APPOINTMENT
            $query = "SELECT * 
                FROM federal_level_nominal_roll_submission_staging 
                WHERE submission_id=:submission_id 
                AND 
                ( 
                 (str_to_date(date_of_present_appointment,'%e/%b/%Y') IS NULL) 
                 OR (date_of_present_appointment IS NULL) 
                 OR (trim(date_of_present_appointment)='') 
                )";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':submission_id', $nominalRollSubmission->getSubmissionId());
            $statement->execute();
            $dateOfPresentAppointmentFailedRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($dateOfPresentAppointmentFailedRecords) {
                $totalInvalidDateOfPresentAppointment = count($dateOfPresentAppointmentFailedRecords);

                foreach ($dateOfPresentAppointmentFailedRecords as $invalidRecord) {
                    if (array_key_exists($invalidRecord['id'], $nominalRoleFailedRecords)) {
                        $failedRecord = $nominalRoleFailedRecords[$invalidRecord['id']];
                        $failedRecord->setFailedDateOfPresentAppointment(1);

                        $nominalRoleFailedRecords[$invalidRecord['id']] = $failedRecord;
                    } else {
                        $failedRecord = new NominalRollValidation();
                        $failedRecord->setId($invalidRecord['id']);
                        $failedRecord->setSubmissionId($invalidRecord['submission_id']);

                        $failedRecord->setFailedDateOfPresentAppointment(1);
                        $nominalRoleFailedRecords[$failedRecord->getId()] = $failedRecord;
                    }
                }

            }

            //VALIDATE GRADE LEVEL
            $query = "SELECT * 
                FROM federal_level_nominal_roll_submission_staging 
                WHERE submission_id=:submission_id 
                AND 
                ( 
                 (grade_level NOT IN (SELECT c.grade_level_code FROM grade_level_codes c WHERE c.record_status='ACTIVE')) 
                 AND (grade_level IS NOT NULL AND trim(grade_level)<>'' ) 
                )";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':submission_id', $nominalRollSubmission->getSubmissionId());
            $statement->execute();
            $gradeLevelFailedRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($gradeLevelFailedRecords) {
                $totalInvalidGradeLevel = count($gradeLevelFailedRecords);

                foreach ($gradeLevelFailedRecords as $invalidRecord) {
                    if (array_key_exists($invalidRecord['id'], $nominalRoleFailedRecords)) {
                        $failedRecord = $nominalRoleFailedRecords[$invalidRecord['id']];
                        $failedRecord->setFailedGradeLevel(1);

                        $nominalRoleFailedRecords[$invalidRecord['id']] = $failedRecord;
                    } else {
                        $failedRecord = new NominalRollValidation();
                        $failedRecord->setId($invalidRecord['id']);
                        $failedRecord->setSubmissionId($invalidRecord['submission_id']);

                        $failedRecord->setFailedGradeLevel(1);
                        $nominalRoleFailedRecords[$failedRecord->getId()] = $failedRecord;
                    }
                }

            }

            //VALIDATE DESIGNATION
            $query = "SELECT * 
                FROM federal_level_nominal_roll_submission_staging 
                WHERE submission_id=:submission_id 
                AND 
                (
                     (designation IS NULL) 
                     OR (trim(designation)='')  
                )";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':submission_id', $nominalRollSubmission->getSubmissionId());
            $statement->execute();
            $designationFailedRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($designationFailedRecords) {
                $totalInvalidDesignation = count($designationFailedRecords);

                foreach ($designationFailedRecords as $invalidRecord) {
                    if (array_key_exists($invalidRecord['id'], $nominalRoleFailedRecords)) {
                        $failedRecord = $nominalRoleFailedRecords[$invalidRecord['id']];
                        $failedRecord->setFailedDesignation(1);

                        $nominalRoleFailedRecords[$invalidRecord['id']] = $failedRecord;
                    } else {
                        $failedRecord = new NominalRollValidation();
                        $failedRecord->setId($invalidRecord['id']);
                        $failedRecord->setSubmissionId($invalidRecord['submission_id']);

                        $failedRecord->setFailedDesignation(1);
                        $nominalRoleFailedRecords[$failedRecord->getId()] = $failedRecord;
                    }
                }
            }

            //VALIDATE STATE OF DEPLOYMENT
            $query = "SELECT * 
                FROM federal_level_nominal_roll_submission_staging 
                WHERE submission_id=:submission_id 
                AND 
                ( 
                 (state_of_deployment_code NOT IN (SELECT c.state_code FROM states c WHERE c.record_status='ACTIVE')) 
                 OR (state_of_deployment_code IS NULL OR trim(state_of_deployment_code)='') 
                )";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':submission_id', $nominalRollSubmission->getSubmissionId());
            $statement->execute();
            $stateOfLocationFailedRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($stateOfLocationFailedRecords) {
                $totalInvalidStateOfLocation = count($stateOfLocationFailedRecords);

                foreach ($stateOfLocationFailedRecords as $invalidRecord) {
                    if (array_key_exists($invalidRecord['id'], $nominalRoleFailedRecords)) {
                        $failedRecord = $nominalRoleFailedRecords[$invalidRecord['id']];
                        $failedRecord->setFailedStateOfLocation(1);

                        $nominalRoleFailedRecords[$invalidRecord['id']] = $failedRecord;
                    } else {
                        $failedRecord = new NominalRollValidation();
                        $failedRecord->setId($invalidRecord['id']);
                        $failedRecord->setSubmissionId($invalidRecord['submission_id']);

                        $failedRecord->setFailedStateOfLocation(1);
                        $nominalRoleFailedRecords[$failedRecord->getId()] = $failedRecord;
                    }
                }

            }

            //VALIDATE GENDER
            $query = "SELECT * 
                FROM federal_level_nominal_roll_submission_staging 
                WHERE submission_id=:submission_id 
                AND 
                ( 
                 (gender NOT IN (SELECT c.id FROM gender c WHERE c.record_status='ACTIVE')) 
                 OR (gender IS NULL OR trim(gender)='') 
                )";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':submission_id', $nominalRollSubmission->getSubmissionId());
            $statement->execute();
            $genderFailedRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($genderFailedRecords) {
                $totalInvalidGender = count($genderFailedRecords);

                foreach ($genderFailedRecords as $invalidRecord) {
                    if (array_key_exists($invalidRecord['id'], $nominalRoleFailedRecords)) {
                        $failedRecord = $nominalRoleFailedRecords[$invalidRecord['id']];
                        $failedRecord->setFailedGender(1);

                        $nominalRoleFailedRecords[$invalidRecord['id']] = $failedRecord;
                    } else {
                        $failedRecord = new NominalRollValidation();
                        $failedRecord->setId($invalidRecord['id']);
                        $failedRecord->setSubmissionId($invalidRecord['submission_id']);

                        $failedRecord->setFailedGender(1);
                        $nominalRoleFailedRecords[$failedRecord->getId()] = $failedRecord;
                    }
                }
            }

            //VALIDATE MARITAL STATUS
            $query = "SELECT * 
                FROM federal_level_nominal_roll_submission_staging 
                WHERE submission_id=:submission_id 
                AND 
                ( 
                 (marital_status NOT IN (SELECT c.id FROM marital_status c WHERE c.record_status='ACTIVE')) 
                 OR (marital_status IS NULL OR trim(marital_status)='') 
                )";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':submission_id', $nominalRollSubmission->getSubmissionId());
            $statement->execute();
            $maritalStatusFailedRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($maritalStatusFailedRecords) {
                $totalInvalidMaritalStatus = count($maritalStatusFailedRecords);

                foreach ($maritalStatusFailedRecords as $invalidRecord) {
                    if (array_key_exists($invalidRecord['id'], $nominalRoleFailedRecords)) {
                        $failedRecord = $nominalRoleFailedRecords[$invalidRecord['id']];
                        $failedRecord->setFailedMaritalStatus(1);

                        $nominalRoleFailedRecords[$invalidRecord['id']] = $failedRecord;
                    } else {
                        $failedRecord = new NominalRollValidation();
                        $failedRecord->setId($invalidRecord['id']);
                        $failedRecord->setSubmissionId($invalidRecord['submission_id']);

                        $failedRecord->setFailedMaritalStatus(1);
                        $nominalRoleFailedRecords[$failedRecord->getId()] = $failedRecord;
                    }
                }
            }

            //VALIDATE LGA
            $query = "SELECT * 
                FROM federal_level_nominal_roll_submission_staging staging
                WHERE staging.submission_id=:submission_id 
                AND 
                ( 
                 (staging.lga_code NOT IN 
                        (
                          SELECT c.plate_code FROM lga c 
                          WHERE c.state_id = (select s.id from states s where s.state_code = staging.state_of_origin_code)
                          AND c.record_status='ACTIVE'
                        )
                 ) 
                 OR (staging.lga_code IS NULL OR trim(staging.lga_code)='') 
                )";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':submission_id', $nominalRollSubmission->getSubmissionId());
            $statement->execute();
            $lgaFailedRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($lgaFailedRecords) {
                $totalInvalidLga = count($lgaFailedRecords);

                foreach ($lgaFailedRecords as $invalidRecord) {
                    if (array_key_exists($invalidRecord['id'], $nominalRoleFailedRecords)) {
                        $failedRecord = $nominalRoleFailedRecords[$invalidRecord['id']];
                        $failedRecord->setFailedLga(1);

                        $nominalRoleFailedRecords[$invalidRecord['id']] = $failedRecord;
                    } else {
                        $failedRecord = new NominalRollValidation();
                        $failedRecord->setId($invalidRecord['id']);
                        $failedRecord->setSubmissionId($invalidRecord['submission_id']);

                        $failedRecord->setFailedLga(1);
                        $nominalRoleFailedRecords[$failedRecord->getId()] = $failedRecord;
                    }
                }
            }

            //VALIDATE GEO-POLITICAL ZONE
            $query = "SELECT * 
                FROM federal_level_nominal_roll_submission_staging 
                WHERE submission_id=:submission_id 
                AND 
                ( 
                 (region_code NOT IN (SELECT c.zone_code FROM geo_political_zone c WHERE c.record_status='ACTIVE')) 
                 OR (region_code IS NULL OR trim(region_code)='') 
                )";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':submission_id', $nominalRollSubmission->getSubmissionId());
            $statement->execute();
            $geoPoliticalZoneFailedRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($geoPoliticalZoneFailedRecords) {
                $totalInvalidGeopoliticalZone = count($geoPoliticalZoneFailedRecords);

                foreach ($geoPoliticalZoneFailedRecords as $invalidRecord) {
                    if (array_key_exists($invalidRecord['id'], $nominalRoleFailedRecords)) {
                        $failedRecord = $nominalRoleFailedRecords[$invalidRecord['id']];
                        $failedRecord->setFailedGeopoliticalZone(1);

                        $nominalRoleFailedRecords[$invalidRecord['id']] = $failedRecord;
                    } else {
                        $failedRecord = new NominalRollValidation();
                        $failedRecord->setId($invalidRecord['id']);
                        $failedRecord->setSubmissionId($invalidRecord['submission_id']);

                        $failedRecord->setFailedGeopoliticalZone(1);
                        $nominalRoleFailedRecords[$failedRecord->getId()] = $failedRecord;
                    }
                }
            }

            //VALIDATE PHYSICALLY CHALLENGED STATUS
            $query = "SELECT * 
                FROM federal_level_nominal_roll_submission_staging 
                WHERE submission_id=:submission_id 
                AND 
                ( 
                 (physically_challenged_status NOT IN (SELECT d.status_code FROM physically_challenged_status d WHERE d.record_status='ACTIVE')) 
                 OR (physically_challenged_status IS NULL OR trim(physically_challenged_status)='') 
                )";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':submission_id', $nominalRollSubmission->getSubmissionId());
            $statement->execute();
            $physicallyChallengedFailedRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($physicallyChallengedFailedRecords) {
                $totalInvalidPhysicallyChallenged = count($physicallyChallengedFailedRecords);

                foreach ($physicallyChallengedFailedRecords as $invalidRecord) {
                    if (array_key_exists($invalidRecord['id'], $nominalRoleFailedRecords)) {
                        $failedRecord = $nominalRoleFailedRecords[$invalidRecord['id']];
                        $failedRecord->setFailedPhysicallyChallenged(1);

                        $nominalRoleFailedRecords[$invalidRecord['id']] = $failedRecord;
                    } else {
                        $failedRecord = new NominalRollValidation();
                        $failedRecord->setId($invalidRecord['id']);
                        $failedRecord->setSubmissionId($invalidRecord['submission_id']);

                        $failedRecord->setFailedPhysicallyChallenged(1);
                        $nominalRoleFailedRecords[$failedRecord->getId()] = $failedRecord;
                    }
                }
            }

            if($nominalRollSubmission->isQuarterlyReturn()){
                //VALIDATE QUARTERLY RETURN EMPLOYMENT STATUS
                $query = "SELECT * 
                FROM federal_level_nominal_roll_submission_staging 
                WHERE submission_id=:submission_id 
                AND 
                ( 
                 (quarterly_return_employment_status NOT IN (SELECT d.status_code FROM employment_status d WHERE d.record_status='ACTIVE')) 
                 OR (quarterly_return_employment_status IS NULL OR trim(quarterly_return_employment_status)='') 
                )";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':submission_id', $nominalRollSubmission->getSubmissionId());
                $statement->execute();
                $quarterlyReturnEmploymentStatusFailedRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
                if ($quarterlyReturnEmploymentStatusFailedRecords) {
                    $totalInvalidQuarterlyReturnEmploymentStatus = count($quarterlyReturnEmploymentStatusFailedRecords);

                    foreach ($quarterlyReturnEmploymentStatusFailedRecords as $invalidRecord) {
                        if (array_key_exists($invalidRecord['id'], $nominalRoleFailedRecords)) {
                            $failedRecord = $nominalRoleFailedRecords[$invalidRecord['id']];
                            $failedRecord->setFailedQuarterlyReturnStatus(1);

                            $nominalRoleFailedRecords[$invalidRecord['id']] = $failedRecord;
                        } else {
                            $failedRecord = new NominalRollValidation();
                            $failedRecord->setId($invalidRecord['id']);
                            $failedRecord->setSubmissionId($invalidRecord['submission_id']);

                            $failedRecord->setFailedQuarterlyReturnStatus(1);
                            $nominalRoleFailedRecords[$failedRecord->getId()] = $failedRecord;
                        }
                    }
                }
            }


            //prepare summary validations
            $summaryFailedValidations = array();

            if ($totalInvalidEmployeeStatus > 0) {
                $summaryFailedValidations[AppConstants::INVALID_EMPLOYEE_STATUS] = $totalInvalidEmployeeStatus;
            }

            if ($totalInvalidEmployeeNumber > 0) {
                $summaryFailedValidations[AppConstants::INVALID_EMPLOYEE_NUMBER] = $totalInvalidEmployeeNumber;
            }

            if ($totalInvalidName > 0) {
                $summaryFailedValidations[AppConstants::INVALID_NAME] = $totalInvalidName;
            }

            if ($totalInvalidNationalityCode > 0) {
                $summaryFailedValidations[AppConstants::INVALID_NATIONALITY_CODE] = $totalInvalidNationalityCode;
            }

            if ($totalInvalidStateOfOrigin > 0) {
                $summaryFailedValidations[AppConstants::INVALID_STATE_OF_ORIGIN] = $totalInvalidStateOfOrigin;
            }

            if ($totalInvalidDateOfBirth > 0) {
                $summaryFailedValidations[AppConstants::INVALID_DATE_OF_BIRTH] = $totalInvalidDateOfBirth;
            }

            if ($totalInvalidDateOfEmployment > 0) {
                $summaryFailedValidations[AppConstants::INVALID_DATE_OF_EMPLOYMENT] = $totalInvalidDateOfEmployment;
            }

            if ($totalInvalidDateOfPresentAppointment > 0) {
                $summaryFailedValidations[AppConstants::INVALID_DATE_OF_PRESENT_APPOINTMENT] = $totalInvalidDateOfPresentAppointment;
            }

            if ($totalInvalidGradeLevel > 0) {
                $summaryFailedValidations[AppConstants::INVALID_GRADE_LEVEL] = $totalInvalidGradeLevel;
            }

            if ($totalInvalidDesignation > 0) {
                $summaryFailedValidations[AppConstants::INVALID_DESIGNATION] = $totalInvalidDesignation;
            }

            if ($totalInvalidStateOfLocation > 0) {
                $summaryFailedValidations[AppConstants::INVALID_STATE_OF_LOCATION] = $totalInvalidStateOfLocation;
            }

            if ($totalInvalidGender > 0) {
                $summaryFailedValidations[AppConstants::INVALID_GENDER] = $totalInvalidGender;
            }

            if ($totalInvalidMaritalStatus > 0) {
                $summaryFailedValidations[AppConstants::INVALID_MARITAL_STATUS] = $totalInvalidMaritalStatus;
            }

            if ($totalInvalidLga > 0) {
                $summaryFailedValidations[AppConstants::INVALID_LGA] = $totalInvalidLga;
            }

            if ($totalInvalidGeopoliticalZone > 0) {
                $summaryFailedValidations[AppConstants::INVALID_GEO_POLITICAL_ZONE] = $totalInvalidGeopoliticalZone;
            }

            if ($totalInvalidPhysicallyChallenged > 0) {
                $summaryFailedValidations[AppConstants::INVALID_PHYSICALLY_CHALLENGED_STATUS] = $totalInvalidPhysicallyChallenged;
            }

            if ($totalInvalidQuarterlyReturnEmploymentStatus > 0) {
                $summaryFailedValidations[AppConstants::INVALID_QUARTERLY_RETURN_EMPLOYMENT_STATUS] = $totalInvalidQuarterlyReturnEmploymentStatus;
            }


            //START A TRANSACTION
            $this->connection->beginTransaction();

            if (empty($summaryFailedValidations)) {
                //all validation passed
                $query = "UPDATE federal_level_nominal_roll_submissions 
                    SET validation_status=:validation_status, date_last_validated=now() 
                    WHERE submission_id=:submission_id";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':validation_status', AppConstants::PASSED);
                $statement->bindValue(':submission_id', $nominalRollSubmission->getSubmissionId());
                $outcome = $statement->execute();

                //update any permissions that exist
                $query = "UPDATE submission_upload_permission_requests 
                    SET expired=:expired, date_expired=now() 
                    WHERE submission_year=:submission_year
                    AND organization_id=:organization_id";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':expired', AppConstants::Y);
                $statement->bindValue(':submission_year', $nominalRollSubmission->getSubmissionYear());
                $statement->bindValue(':organization_id', $nominalRollSubmission->getOrganizationId());
                $outcome = $statement->execute();

            } else {

                //update the table
                $query = "UPDATE federal_level_nominal_roll_submissions 
                    SET validation_status=:validation_status, date_last_validated=now() 
                    WHERE submission_id=:submission_id";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':validation_status', AppConstants::FAILED);
                $statement->bindValue(':submission_id', $nominalRollSubmission->getSubmissionId());
                $outcome = $statement->execute();

                //PREPARE THE SUMMARY BATCH DATA
                $batchSummaryFailedData = array();
                foreach ($summaryFailedValidations as $validationKey => $totalFailed) {
                    $batchSummaryFailedRow = array();
                    $batchSummaryFailedRow['submission_id'] = $nominalRollSubmission->getSubmissionId();
                    $batchSummaryFailedRow['validation_reason'] = $validationKey;
                    $batchSummaryFailedRow['total_failed'] = $totalFailed;

                    $batchSummaryFailedData[] = $batchSummaryFailedRow;
                }

                //PREPARE THE DETAILED BATCH DATA
                $batchDetailedFailedData = array();
                foreach ($nominalRoleFailedRecords as $key => $failedRecord) {
                    $batchDetailedFailedRow = array();

                    $batchDetailedFailedRow['id'] = $failedRecord->getId();
                    $batchDetailedFailedRow['submission_id'] = $failedRecord->getSubmissionId();
                    $batchDetailedFailedRow['failed_employee_status'] = $failedRecord->getFailedEmployeeStatus();
                    $batchDetailedFailedRow['failed_employee_number'] = $failedRecord->getFailedEmployeeNumber();
                    $batchDetailedFailedRow['failed_employee_name'] = $failedRecord->getFailedName();
                    $batchDetailedFailedRow['failed_nationality_code'] = $failedRecord->getFailedNationality();
                    $batchDetailedFailedRow['failed_state_of_origin_code'] = $failedRecord->getFailedStateOfOrigin();
                    $batchDetailedFailedRow['failed_lga_code'] = $failedRecord->getFailedLga();
                    $batchDetailedFailedRow['failed_region_code'] = $failedRecord->getFailedGeopoliticalZone();
                    $batchDetailedFailedRow['failed_date_of_birth'] = $failedRecord->getFailedDateOfBirth();
                    $batchDetailedFailedRow['failed_date_of_employment'] = $failedRecord->getFailedDateOfEmployment();
                    $batchDetailedFailedRow['failed_date_of_present_appointment'] = $failedRecord->getFailedDateOfPresentAppointment();
                    $batchDetailedFailedRow['failed_grade_level'] = $failedRecord->getFailedGradeLevel();
                    $batchDetailedFailedRow['failed_designation'] = $failedRecord->getFailedDesignation();
                    $batchDetailedFailedRow['failed_state_of_deployment_code'] = $failedRecord->getFailedStateOfLocation();
                    $batchDetailedFailedRow['failed_gender'] = $failedRecord->getFailedGender();
                    $batchDetailedFailedRow['failed_marital_status'] = $failedRecord->getFailedMaritalStatus();
                    $batchDetailedFailedRow['failed_physically_challenged_status'] = $failedRecord->getFailedPhysicallyChallenged();
                    $batchDetailedFailedRow['failed_quarterly_return_employment_status'] = $failedRecord->getFailedQuarterlyReturnStatus();

                    $batchDetailedFailedData[] = $batchDetailedFailedRow;
                }

                //REMOVE THIS LATER AFTER KEEPING TRACK
                $query = "DELETE FROM federal_level_nominal_roll_failed_validations WHERE submission_id=:submission_id";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':submission_id', $nominalRollSubmission->getSubmissionId());
                $delete_outcome = $statement->execute();

                //REMOVE THIS LATER AFTER KEEPING TRACK
                $query = "DELETE FROM federal_level_nominal_roll_failed_validations_detail WHERE submission_id=:submission_id";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':submission_id', $nominalRollSubmission->getSubmissionId());
                $delete_outcome = $statement->execute();

                $outcome = $this->pdoMultiInsert('federal_level_nominal_roll_failed_validations', $batchSummaryFailedData);
                $outcome = $this->pdoMultiInsert('federal_level_nominal_roll_failed_validations_detail', $batchDetailedFailedData);
            }


            $this->connection->commit();

        } catch (Throwable $e) {
            if ($this->connection->isTransactionActive()) {
                $this->connection->rollBack();
            }

            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $outcome;
    }

    public function processSubmissionAnalysis(NominalRollSubmission $nominalRollSubmission): bool
    {
        $outcome = false;
        $statement = null;

        try {

            //get the submission id of the most recent active MAIN and QUARTERLY confirmed submission from same organization of same year
            $query = "SELECT submission_id 
                    FROM federal_level_nominal_roll_submissions 
                    WHERE 
                    organization_id=:organization_id 
                    AND submission_year=:submission_year
                    AND is_active=:is_active
                    AND fcc_desk_officer_confirmation_status=:fcc_desk_officer_confirmation_status
                    AND fcc_mis_head_approval_status=:fcc_mis_head_approval_status
                    order by submission_type";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':organization_id', $nominalRollSubmission->getOrganizationId());
            $statement->bindValue(':submission_year', $nominalRollSubmission->getSubmissionYear());
            $statement->bindValue(':is_active', AppConstants::Y);
            $statement->bindValue(':fcc_desk_officer_confirmation_status', AppConstants::CONFIRMED);
            $statement->bindValue(':fcc_mis_head_approval_status', AppConstants::APPROVED);
            $statement->execute();

            $allActiveSubmissionIds = $statement->fetchAll(PDO::FETCH_COLUMN,0);

            if ($allActiveSubmissionIds) {

                $inStringOfAllActiveSubmissionIdsToProcess = "'" . implode("','", $allActiveSubmissionIds) . "'";

                //get the ids of the active quarterly submissions if any
                $query = "SELECT submission_id 
                    FROM federal_level_nominal_roll_submissions 
                    WHERE 
                    organization_id=:organization_id 
                    AND submission_year=:submission_year
                    AND submission_type=:submission_type
                    AND is_active=:is_active
                    AND fcc_desk_officer_confirmation_status=:fcc_desk_officer_confirmation_status
                    AND fcc_mis_head_approval_status=:fcc_mis_head_approval_status";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':organization_id', $nominalRollSubmission->getOrganizationId());
                $statement->bindValue(':submission_year', $nominalRollSubmission->getSubmissionYear());
                $statement->bindValue(':submission_type', AppConstants::QUARTERLY_RETURN);
                $statement->bindValue(':is_active', AppConstants::Y);
                $statement->bindValue(':fcc_desk_officer_confirmation_status', AppConstants::CONFIRMED);
                $statement->bindValue(':fcc_mis_head_approval_status', AppConstants::APPROVED);
                $statement->execute();

                $activeQuarterlySubmissionIds = $statement->fetchAll(PDO::FETCH_COLUMN,0);

                $inStringOfActiveQuarterlySubmissions = '';
                $notExitedServiceQuery = ' ';

                if($activeQuarterlySubmissionIds){

                    $inStringOfActiveQuarterlySubmissions = "'" . implode("','", $activeQuarterlySubmissionIds) . "'";

                    //fetch the value list of exit employment status
                    $query = "select status_code from employment_status where status_type=:status_type";
                    $statement = $this->connection->prepare($query);
                    $statement->bindValue(':status_type', AppConstants::EXIT);
                    $statement->execute();

                    $exitEmploymentStatus = $statement->fetchAll(PDO::FETCH_COLUMN, 0);
                    $inStringOfExitStatus = "'" . implode("','", $exitEmploymentStatus) . "'";

                    $notExitedServiceQuery = " AND ((select q.quarterly_return_employment_status 
                                            from confirmed_federal_level_nominal_roll_submission q 
                                            where q.submission_id in ($inStringOfActiveQuarterlySubmissions)
                                            and q.employee_number = d.employee_number order by submission_upload_date desc LIMIT 1) 
                                            NOT IN ($inStringOfExitStatus))";
                }

                //now fetch all state codes
                $query = "SELECT id,state_code FROM states WHERE record_status = :record_status";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':record_status', AppConstants::ACTIVE);
                $statement->execute();

                $stateCodesArray = $statement->fetchAll();

                $batchStateAnalysisData = array();
                $todayWithTime = date("Y-m-d H:i:s");

                $processingStartedTime = date("Y-m-d H:i:s");
                //update the status to started
                $query = "update federal_level_nominal_roll_submissions 
                    set 
                    processing_status=:processing_status 
                    ,date_processing_started=:date_processing_started
                    where submission_id in ($inStringOfAllActiveSubmissionIdsToProcess)";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':processing_status', AppConstants::STARTED);
                $statement->bindValue(':date_processing_started', $processingStartedTime);
                $statement->execute();

                foreach ($stateCodesArray as $stateDetail) {

                    $stateOfOriginId = $stateDetail['id'];
                    $stateOfOriginCode = $stateDetail['state_code'];

                    $query = "select 'abc' 
                        ,(
                             select count(distinct(d.employee_number)) from confirmed_federal_level_nominal_roll_submission d
                             where d.submission_id IN ($inStringOfAllActiveSubmissionIdsToProcess) and d.state_of_origin_code = '$stateOfOriginCode' 
                             and d.grade_level='1' $notExitedServiceQuery
                                  ) as total_gl_1
                        ,(
                             select count(distinct(d.employee_number)) from confirmed_federal_level_nominal_roll_submission d 
                             where d.submission_id IN ($inStringOfAllActiveSubmissionIdsToProcess) and d.state_of_origin_code = '$stateOfOriginCode' 
                             and d.grade_level='2' $notExitedServiceQuery
                                  ) as total_gl_2
                        ,(
                             select count(distinct(d.employee_number)) from confirmed_federal_level_nominal_roll_submission d 
                             where d.submission_id IN ($inStringOfAllActiveSubmissionIdsToProcess) and d.state_of_origin_code = '$stateOfOriginCode' 
                             and d.grade_level='3' $notExitedServiceQuery
                                  ) as total_gl_3
                        ,(
                             select count(distinct(d.employee_number)) from confirmed_federal_level_nominal_roll_submission d 
                             where d.submission_id IN ($inStringOfAllActiveSubmissionIdsToProcess) and d.state_of_origin_code = '$stateOfOriginCode' 
                             and d.grade_level='4' $notExitedServiceQuery
                                  ) as total_gl_4
                        ,(
                             select count(distinct(d.employee_number)) from confirmed_federal_level_nominal_roll_submission d 
                             where d.submission_id IN ($inStringOfAllActiveSubmissionIdsToProcess) and d.state_of_origin_code = '$stateOfOriginCode' 
                             and d.grade_level='5' $notExitedServiceQuery
                                  ) as total_gl_5
                        ,(
                             select count(distinct(d.employee_number)) from confirmed_federal_level_nominal_roll_submission d 
                             where d.submission_id IN ($inStringOfAllActiveSubmissionIdsToProcess) and d.state_of_origin_code = '$stateOfOriginCode' 
                             and d.grade_level='6' $notExitedServiceQuery 
                         )          as total_gl_6
                        ,(
                             select count(distinct(d.employee_number)) from confirmed_federal_level_nominal_roll_submission d 
                             where d.submission_id IN ($inStringOfAllActiveSubmissionIdsToProcess) and d.state_of_origin_code = '$stateOfOriginCode' 
                             and d.grade_level='7' $notExitedServiceQuery
                                  ) as total_gl_7
                        ,(
                             select count(distinct(d.employee_number)) from confirmed_federal_level_nominal_roll_submission d 
                             where d.submission_id IN ($inStringOfAllActiveSubmissionIdsToProcess) and d.state_of_origin_code = '$stateOfOriginCode' 
                             and d.grade_level='8' $notExitedServiceQuery 
                                  ) as total_gl_8
                        ,(
                             select count(distinct(d.employee_number)) from confirmed_federal_level_nominal_roll_submission d 
                             where d.submission_id IN ($inStringOfAllActiveSubmissionIdsToProcess) and d.state_of_origin_code = '$stateOfOriginCode' 
                             and d.grade_level='9' $notExitedServiceQuery 
                                  ) as total_gl_9
                        ,(
                             select count(distinct(d.employee_number)) from confirmed_federal_level_nominal_roll_submission d 
                             where d.submission_id IN ($inStringOfAllActiveSubmissionIdsToProcess) and d.state_of_origin_code = '$stateOfOriginCode' 
                             and d.grade_level='10' $notExitedServiceQuery 
                                  ) as total_gl_10
                        ,(
                             select count(distinct(d.employee_number)) from confirmed_federal_level_nominal_roll_submission d 
                             where d.submission_id IN ($inStringOfAllActiveSubmissionIdsToProcess) and d.state_of_origin_code = '$stateOfOriginCode' 
                             and d.grade_level='11' $notExitedServiceQuery 
                                  ) as total_gl_11
                        ,(
                             select count(distinct(d.employee_number)) from confirmed_federal_level_nominal_roll_submission d 
                             where d.submission_id IN ($inStringOfAllActiveSubmissionIdsToProcess) and d.state_of_origin_code = '$stateOfOriginCode' 
                             and d.grade_level='12' $notExitedServiceQuery 
                                  ) as total_gl_12
                        ,(
                             select count(distinct(d.employee_number)) from confirmed_federal_level_nominal_roll_submission d 
                             where d.submission_id IN ($inStringOfAllActiveSubmissionIdsToProcess) and d.state_of_origin_code = '$stateOfOriginCode' 
                             and d.grade_level='13' $notExitedServiceQuery 
                                  ) as total_gl_13
                        ,(
                             select count(distinct(d.employee_number)) from confirmed_federal_level_nominal_roll_submission d 
                             where d.submission_id IN ($inStringOfAllActiveSubmissionIdsToProcess) and d.state_of_origin_code = '$stateOfOriginCode' 
                             and d.grade_level='14' $notExitedServiceQuery 
                                  ) as total_gl_14
                        ,(
                             select count(distinct(d.employee_number)) from confirmed_federal_level_nominal_roll_submission d 
                             where d.submission_id IN ($inStringOfAllActiveSubmissionIdsToProcess) and d.state_of_origin_code = '$stateOfOriginCode' 
                             and d.grade_level='15' $notExitedServiceQuery 
                                  ) as total_gl_15
                        ,(
                             select count(distinct(d.employee_number)) from confirmed_federal_level_nominal_roll_submission d 
                             where d.submission_id IN ($inStringOfAllActiveSubmissionIdsToProcess) and d.state_of_origin_code = '$stateOfOriginCode' 
                             and d.grade_level='16' $notExitedServiceQuery 
                                  ) as total_gl_16
                        ,(
                             select count(distinct(d.employee_number)) from confirmed_federal_level_nominal_roll_submission d 
                             where d.submission_id IN ($inStringOfAllActiveSubmissionIdsToProcess) and d.state_of_origin_code = '$stateOfOriginCode' 
                             and d.grade_level='17' $notExitedServiceQuery 
                                  ) as total_gl_17
                        ,(
                             select count(distinct(d.employee_number)) from confirmed_federal_level_nominal_roll_submission d 
                             where d.submission_id IN ($inStringOfAllActiveSubmissionIdsToProcess) and d.state_of_origin_code = '$stateOfOriginCode' 
                             and d.grade_level='CON' $notExitedServiceQuery 
                                  ) as total_gl_consolidated
                        ,(
                             select count(distinct(d.employee_number)) from confirmed_federal_level_nominal_roll_submission d 
                             where d.submission_id IN ($inStringOfAllActiveSubmissionIdsToProcess) and d.state_of_origin_code = '$stateOfOriginCode' 
                             and d.grade_level='89' $notExitedServiceQuery 
                                  ) as total_gl_89
                        ,(
                             select count(distinct(d.employee_number)) from confirmed_federal_level_nominal_roll_submission d 
                             where d.submission_id IN ($inStringOfAllActiveSubmissionIdsToProcess) and d.state_of_origin_code = '$stateOfOriginCode' 
                             and d.grade_level='90' $notExitedServiceQuery 
                                  ) as total_gl_90
                        ,(
                             select count(distinct(d.employee_number)) from confirmed_federal_level_nominal_roll_submission d 
                             where d.submission_id IN ($inStringOfAllActiveSubmissionIdsToProcess) and d.state_of_origin_code = '$stateOfOriginCode' 
                             and d.grade_level='91' $notExitedServiceQuery 
                                  ) as total_gl_91
                        ,(
                             select count(distinct(d.employee_number)) from confirmed_federal_level_nominal_roll_submission d 
                             where d.submission_id IN ($inStringOfAllActiveSubmissionIdsToProcess) and d.state_of_origin_code = '$stateOfOriginCode' 
                             and d.grade_level='92' $notExitedServiceQuery 
                                  ) as total_gl_92
                        ,(
                             select count(distinct(d.employee_number)) from confirmed_federal_level_nominal_roll_submission d 
                             where d.submission_id IN ($inStringOfAllActiveSubmissionIdsToProcess) and d.state_of_origin_code = '$stateOfOriginCode' 
                             and d.grade_level='93' $notExitedServiceQuery 
                                  ) as total_gl_93
                        ,(
                             select count(distinct(d.employee_number)) from confirmed_federal_level_nominal_roll_submission d 
                             where d.submission_id IN ($inStringOfAllActiveSubmissionIdsToProcess) and d.state_of_origin_code = '$stateOfOriginCode' 
                             and d.grade_level='94' $notExitedServiceQuery 
                                  ) as total_gl_94
                        ,(
                             select count(distinct(d.employee_number)) from confirmed_federal_level_nominal_roll_submission d 
                             where d.submission_id IN ($inStringOfAllActiveSubmissionIdsToProcess) and d.state_of_origin_code = '$stateOfOriginCode' 
                             and d.grade_level='95' $notExitedServiceQuery 
                                  ) as total_gl_95
                        ,(
                             select count(distinct(d.employee_number)) from confirmed_federal_level_nominal_roll_submission d 
                             where d.submission_id IN ($inStringOfAllActiveSubmissionIdsToProcess) and d.state_of_origin_code = '$stateOfOriginCode' 
                             and d.grade_level='96' $notExitedServiceQuery 
                                  ) as total_gl_96
                        ,(
                             select count(distinct(d.employee_number)) from confirmed_federal_level_nominal_roll_submission d 
                             where d.submission_id IN ($inStringOfAllActiveSubmissionIdsToProcess) and d.state_of_origin_code = '$stateOfOriginCode' 
                             and d.grade_level='97' $notExitedServiceQuery 
                                  ) as total_gl_97
                        ,(
                             select count(distinct(d.employee_number)) from confirmed_federal_level_nominal_roll_submission d 
                             where d.submission_id IN ($inStringOfAllActiveSubmissionIdsToProcess) and d.state_of_origin_code = '$stateOfOriginCode' 
                             and d.grade_level='98' $notExitedServiceQuery 
                                  ) as total_gl_98
                        ,(
                             select count(distinct(d.employee_number)) from confirmed_federal_level_nominal_roll_submission d 
                             where d.submission_id IN ($inStringOfAllActiveSubmissionIdsToProcess) and d.state_of_origin_code = '$stateOfOriginCode' 
                             and d.grade_level='99' $notExitedServiceQuery 
                                  ) as total_gl_99";

                    $statement = $this->connection->executeQuery($query);

                    $stateAnalysisRecord = $statement->fetch(); //1 row

                    //prepare it for batch insert
                    $currentStateAnalysis = array();
                    $currentStateAnalysis['organization_id'] = $nominalRollSubmission->getOrganizationId();
                    $currentStateAnalysis['submission_year'] = $nominalRollSubmission->getSubmissionYear();
                    $currentStateAnalysis['state_of_origin_id'] = $stateOfOriginId;
                    $currentStateAnalysis['total_gl_1'] = $stateAnalysisRecord['total_gl_1'];
                    $currentStateAnalysis['total_gl_2'] = $stateAnalysisRecord['total_gl_2'];
                    $currentStateAnalysis['total_gl_3'] = $stateAnalysisRecord['total_gl_3'];
                    $currentStateAnalysis['total_gl_4'] = $stateAnalysisRecord['total_gl_4'];
                    $currentStateAnalysis['total_gl_5'] = $stateAnalysisRecord['total_gl_5'];
                    $currentStateAnalysis['total_gl_6'] = $stateAnalysisRecord['total_gl_6'];
                    $currentStateAnalysis['total_gl_7'] = $stateAnalysisRecord['total_gl_7'];
                    $currentStateAnalysis['total_gl_8'] = $stateAnalysisRecord['total_gl_8'];
                    $currentStateAnalysis['total_gl_9'] = $stateAnalysisRecord['total_gl_9'];
                    $currentStateAnalysis['total_gl_10'] = $stateAnalysisRecord['total_gl_10'];
                    $currentStateAnalysis['total_gl_11'] = $stateAnalysisRecord['total_gl_11'];
                    $currentStateAnalysis['total_gl_12'] = $stateAnalysisRecord['total_gl_12'];
                    $currentStateAnalysis['total_gl_13'] = $stateAnalysisRecord['total_gl_13'];
                    $currentStateAnalysis['total_gl_14'] = $stateAnalysisRecord['total_gl_14'];
                    $currentStateAnalysis['total_gl_15'] = $stateAnalysisRecord['total_gl_15'];
                    $currentStateAnalysis['total_gl_16'] = $stateAnalysisRecord['total_gl_16'];
                    $currentStateAnalysis['total_gl_17'] = $stateAnalysisRecord['total_gl_17'];

                    $currentStateAnalysis['total_consolidated'] = $stateAnalysisRecord['total_gl_consolidated'];

                    $currentStateAnalysis['total_gl_89'] = $stateAnalysisRecord['total_gl_89'];
                    $currentStateAnalysis['total_gl_90'] = $stateAnalysisRecord['total_gl_90'];
                    $currentStateAnalysis['total_gl_91'] = $stateAnalysisRecord['total_gl_91'];
                    $currentStateAnalysis['total_gl_92'] = $stateAnalysisRecord['total_gl_92'];
                    $currentStateAnalysis['total_gl_93'] = $stateAnalysisRecord['total_gl_93'];
                    $currentStateAnalysis['total_gl_94'] = $stateAnalysisRecord['total_gl_94'];
                    $currentStateAnalysis['total_gl_95'] = $stateAnalysisRecord['total_gl_95'];
                    $currentStateAnalysis['total_gl_96'] = $stateAnalysisRecord['total_gl_96'];
                    $currentStateAnalysis['total_gl_97'] = $stateAnalysisRecord['total_gl_97'];
                    $currentStateAnalysis['total_gl_98'] = $stateAnalysisRecord['total_gl_98'];
                    $currentStateAnalysis['total_gl_99'] = $stateAnalysisRecord['total_gl_99'];

                    $currentStateAnalysis['last_mod'] = $todayWithTime;

                    $batchStateAnalysisData[] = $currentStateAnalysis;
                }

                //start transaction
                $this->connection->beginTransaction();

                //delete first
                $query = "DELETE FROM federal_level_nominal_roll_career_post_analysis "
                    . "WHERE organization_id=:organization_id AND submission_year=:submission_year";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':organization_id', $nominalRollSubmission->getOrganizationId());
                $statement->bindValue(':submission_year', $nominalRollSubmission->getSubmissionYear());
                $statement->execute();


                //insert the batch
                $multiInsertOutcome = $this->pdoMultiInsert('federal_level_nominal_roll_career_post_analysis', $batchStateAnalysisData);

                //update the status to completed
                $processingCompletionTime = date("Y-m-d H:i:s");
                $query = "update federal_level_nominal_roll_submissions 
                    set 
                    processing_status=:processing_status 
                    ,date_processing_completed=:date_processing_completed
                    where submission_id in ($inStringOfAllActiveSubmissionIdsToProcess)";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':processing_status', AppConstants::COMPLETED);
                $statement->bindValue(':date_processing_completed', $processingCompletionTime);
                $statement->execute();

                //commit transaction
                $this->connection->commit();

            }

            $outcome = true;

        } catch (Throwable $e) {

            //rollback if there is an error
            if ($this->connection->isTransactionActive()) {
                $this->connection->rollBack();
            }

            throw new AppException($e->getMessage());

        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $outcome;
    }

    private function pdoMultiInsert($tableName, $data)
    {
        $outcome = false;
        $pdoStatement = null;

        try {
            //Will contain SQL snippets.
            $rowsSQL = array();

            //Will contain the values that we need to bind.
            $toBind = array();

            //Get a list of column names to use in the SQL statement.
            $columnNames = array_keys($data[0]);

            //$_columnNames = print_r($columnNames, true);

            //Loop through our $data array.
            foreach ($data as $arrayIndex => $row) {
                $params = array();
                foreach ($row as $columnName => $columnValue) {
                    $param = ":" . $columnName . '_row_' . $arrayIndex;
                    $params[] = $param;
                    $toBind[$param] = $columnValue;
                }
                $rowsSQL[] = "(" . implode(", ", $params) . ")";
            }

            //$_toBind = print_r($toBind, true);

            //$_rowsSQL = print_r($rowsSQL, true);

            //Construct our SQL statement
            $sql = "INSERT INTO `$tableName` (" . implode(", ", $columnNames) . ") VALUES " . implode(", ", $rowsSQL);

            //Prepare our PDO statement.
            $pdoStatement = $this->connection->prepare($sql);

            //Bind our values.
            foreach ($toBind as $param => $val) {
                $pdoStatement->bindValue($param, $val);
            }

            //Execute our statement (i.e. insert the data).
            $totalRows = $pdoStatement->execute();

            $outcome = true;
        } catch (Throwable $t) {
            throw new AppException($t->getMessage());
        } finally {
            if ($pdoStatement) {
                $pdoStatement->closeCursor();
            }
        }

        return $outcome;

    }

    private function pdoMultiInsertReturnTotal($tableName, $data)
    {
        $totalRows = 0;
        $pdoStatement = null;

        try {
            //Will contain SQL snippets.
            $rowsSQL = array();

            //Will contain the values that we need to bind.
            $toBind = array();

            //Get a list of column names to use in the SQL statement.
            $columnNames = array_keys($data[0]);

            //$_columnNames = print_r($columnNames, true);

            //Loop through our $data array.
            foreach ($data as $arrayIndex => $row) {
                $params = array();
                foreach ($row as $columnName => $columnValue) {
                    $param = ":" . $columnName . '_row_' . $arrayIndex;
                    $params[] = $param;
                    $toBind[$param] = $columnValue;
                }
                $rowsSQL[] = "(" . implode(", ", $params) . ")";
            }

            //$_toBind = print_r($toBind, true);

            //$_rowsSQL = print_r($rowsSQL, true);

            //Construct our SQL statement
            $sql = "INSERT INTO `$tableName` (" . implode(", ", $columnNames) . ") VALUES " . implode(", ", $rowsSQL);

            //Prepare our PDO statement.
            $pdoStatement = $this->connection->prepare($sql);

            //Bind our values.
            foreach ($toBind as $param => $val) {
                $pdoStatement->bindValue($param, $val);
            }

            //Execute our statement (i.e. insert the data).
            $totalRows = $pdoStatement->execute();

            $outcome = true;
        } catch (Throwable $t) {
            throw new AppException($t->getMessage());
        } finally {
            if ($pdoStatement) {
                $pdoStatement->closeCursor();
            }
        }

        return $totalRows;
    }
}