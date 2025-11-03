<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/1/2017
 * Time: 4:28 PM
 */

namespace AppBundle\Services\Recruitment;


use AppBundle\AppException\AppException;
use AppBundle\Model\Recruitment\CandidateValidation;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\ServiceHelper;
use Doctrine\DBAL\Connection;
use \Throwable;
use \PDO;

class RecruitmentListValidationTaskService extends ServiceHelper
{
    private $connection;
    private $rows_per_page;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->rows_per_page = AppConstants::ROWS_PER_PAGE;
    }

    /**
     * @param $listId
     * @param $whichList
     * @param $recruitmentId
     * @return bool
     * @throws AppException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function validate($listId, $whichList): bool
    {
        $outcome = false;
        $statement = null;

        try {

            $list_table_reference_id_column_name = '';
            $list_table_name = '';
            $staging_table_name = '';
            $failed_validation_table_name = '';
            $failed_validation_detail_table = '';

            $isLongList = false;
            $isShortList = false;
            $isCocCandidateList = false;
            $isCocAppointmentList = false;

            //get the proper table
            switch ($whichList){
                case AppConstants::LONG_LIST:
                    $isLongList = true;

                    $list_table_reference_id_column_name = 'long_list_id';
                    $list_table_name = 'recruitment_long_list';
                    $staging_table_name = '$staging_table_name';
                    $failed_validation_table_name = 'recruitment_long_list_failed_validations';
                    $failed_validation_detail_table = 'recruitment_long_list_failed_validations_detail';
                    break;

                case AppConstants::SHORT_LIST:
                    $isShortList = true;

                    $list_table_reference_id_column_name = 'short_list_id';
                    $list_table_name = 'recruitment_short_list';
                    $staging_table_name = 'recruitment_short_list_candidates_staging';
                    $failed_validation_table_name = 'recruitment_short_list_failed_validations';
                    $failed_validation_detail_table = 'recruitment_short_list_failed_validations_detail';
                    break;

                case AppConstants::COC_CANDIDATE_LIST:
                    $isCocCandidateList = true;

                    $list_table_reference_id_column_name = 'recruitment_coc_candidates_list_id';
                    $list_table_name = 'recruitment_coc_candidates_list';
                    $staging_table_name = 'recruitment_coc_candidates_list_entries_staging';
                    $failed_validation_table_name = 'recruitment_coc_candidates_list_failed_validations';
                    $failed_validation_detail_table = 'recruitment_coc_candidates_list_failed_validations_detail';
                    break;

                case AppConstants::COC_CANDIDATE_APPOINTMENT_LIST:
                    $isCocAppointmentList = true;

                    $list_table_reference_id_column_name = 'recruitment_coc_appointment_list_id';
                    $list_table_name = 'recruitment_coc_appointment_list';
                    $staging_table_name = 'recruitment_coc_appointment_list_entries_staging';
                    $failed_validation_table_name = 'recruitment_coc_appointment_list_failed_validations';
                    $failed_validation_detail_table = 'recruitment_coc_appointment_list_failed_validations_detail';
                    break;

                default:
                    break;
            }


            $totalInvalidSurname = 0;
            $totalInvalidFirstName = 0;
            $totalInvalidOtherName = 0;
            $totalInvalidDateOfBirth = 0;
            $totalInvalidAddress = 0;
            $totalInvalidCenter = 0;
            $totalInvalidPhoneNumber = 0;
            $totalInvalidEmailAddress = 0;
            $totalInvalidLga = 0;
            $totalInvalidGender = 0;
            $totalInvalidPostApplied = 0;
            $totalInvalidUniversity = 0;
            $totalInvalidCourse = 0;
            $totalInvalidStateOfOrigin = 0;
            $totalInvalidClassOfDegree = 0;

            $failedValidationRecords = array();

            //VALIDATE surname
            $query = "SELECT * 
                FROM $staging_table_name 
                WHERE $list_table_reference_id_column_name=:list_id 
                AND 
                ( 
                     (surname IS NULL) 
                     OR (trim(surname)='')  
                )";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':list_id', $listId);
            $statement->execute();
            $failedSurnameRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($failedSurnameRecords) {
                $totalInvalidSurname = count($failedSurnameRecords);

                foreach ($failedSurnameRecords as $invalidRecord) {
                    if (array_key_exists($invalidRecord['id'], $failedValidationRecords)) {
                        /**
                         * @var CandidateValidation $failedRecord
                         */
                        $failedRecord = $failedValidationRecords[$invalidRecord['id']];
                        $failedRecord->setFailedSurname(1);

                        $failedValidationRecords[$invalidRecord['id']] = $failedRecord;
                    } else {
                        $failedRecord = new CandidateValidation();
                        $failedRecord->setId($invalidRecord['id']);
                        $failedRecord->setWhichListId($invalidRecord[$list_table_reference_id_column_name]);

                        $failedRecord->setFailedSurname(1);
                        $failedValidationRecords[$failedRecord->getId()] = $failedRecord;
                    }
                }

            }

            //VALIDATE firstname
            $query = "SELECT * 
                FROM $staging_table_name 
                WHERE $list_table_reference_id_column_name=:list_id 
                AND 
                ( 
                     (first_name IS NULL) 
                     OR (trim(first_name)='')  
                )";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':list_id', $listId);
            $statement->execute();
            $failedFirstNameRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($failedFirstNameRecords) {
                $totalInvalidFirstName = count($failedFirstNameRecords);

                foreach ($failedFirstNameRecords as $invalidRecord) {
                    if (array_key_exists($invalidRecord['id'], $failedValidationRecords)) {
                        /**
                         * @var CandidateValidation $failedRecord
                         */
                        $failedRecord = $failedValidationRecords[$invalidRecord['id']];
                        $failedRecord->setFailedFirstName(1);

                        $failedValidationRecords[$invalidRecord['id']] = $failedRecord;
                    } else {
                        $failedRecord = new CandidateValidation();
                        $failedRecord->setId($invalidRecord['id']);
                        $failedRecord->setWhichListId($invalidRecord[$list_table_reference_id_column_name]);

                        $failedRecord->setFailedFirstName(1);
                        $failedValidationRecords[$failedRecord->getId()] = $failedRecord;
                    }
                }

            }

            //VALIDATE DATE OF BIRTH
            $query = "SELECT * 
                FROM $staging_table_name 
                WHERE $list_table_reference_id_column_name=:list_id 
                 AND (str_to_date(date_of_birth,'%e/%b/%Y') IS NULL)
                 OR (date_of_birth IS NULL) 
                 OR (trim(date_of_birth)='') ";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':list_id', $listId);
            $statement->execute();
            $failedDateOfBirthRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($failedDateOfBirthRecords) {
                $totalInvalidDateOfBirth = count($failedDateOfBirthRecords);

                foreach ($failedDateOfBirthRecords as $invalidRecord) {
                    if (array_key_exists($invalidRecord['id'], $failedValidationRecords)) {
                        /**
                         * @var CandidateValidation $failedRecord
                         */
                        $failedRecord = $failedValidationRecords[$invalidRecord['id']];
                        $failedRecord->setFailedDateOfBirth(1);

                        $failedValidationRecords[$invalidRecord['id']] = $failedRecord;
                    } else {
                        $failedRecord = new CandidateValidation();
                        $failedRecord->setId($invalidRecord['id']);
                        $failedRecord->setWhichListId($invalidRecord[$list_table_reference_id_column_name]);

                        $failedRecord->setFailedDateOfBirth(1);
                        $failedValidationRecords[$failedRecord->getId()] = $failedRecord;
                    }
                }
            }

            //VALIDATE STATE OF ORIGIN
            $query = "SELECT * 
                FROM $staging_table_name 
                WHERE $list_table_reference_id_column_name=:list_id 
                AND 
                ( 
                     (state_of_origin NOT IN (SELECT c.state_code FROM states c WHERE c.record_status='ACTIVE')) 
                     AND (state_of_origin IS NOT NULL AND TRIM(state_of_origin)<>'') 
                )";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':list_id', $listId);
            $statement->execute();
            $failedStateOfOriginRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($failedStateOfOriginRecords) {
                $totalInvalidStateOfOrigin = count($failedStateOfOriginRecords);

                foreach ($failedStateOfOriginRecords as $invalidRecord) {
                    if (array_key_exists($invalidRecord['id'], $failedValidationRecords)) {
                        /**
                         * @var CandidateValidation $failedRecord
                         */
                        $failedRecord = $failedValidationRecords[$invalidRecord['id']];
                        $failedRecord->setFailedStateOfOrigin(1);

                        $failedValidationRecords[$invalidRecord['id']] = $failedRecord;
                    } else {
                        $failedRecord = new CandidateValidation();
                        $failedRecord->setId($invalidRecord['id']);
                        $failedRecord->setWhichListId($invalidRecord[$list_table_reference_id_column_name]);

                        $failedRecord->setFailedStateOfOrigin(1);
                        $failedValidationRecords[$failedRecord->getId()] = $failedRecord;
                    }
                }
            }

            //VALIDATE LGA
            $query = "SELECT * 
                FROM $staging_table_name staging
                WHERE staging.$list_table_reference_id_column_name=:list_id 
                AND 
                ( 
                 (staging.lga NOT IN 
                        (
                          SELECT c.plate_code FROM lga c 
                          WHERE c.state_id = (select s.id from states s where s.state_code = staging.state_of_origin)
                          AND c.record_status='ACTIVE'
                        )
                 ) 
                 OR (staging.lga IS NULL OR trim(staging.lga)='') 
                )";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':list_id', $listId);
            $statement->execute();
            $failedLgaRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($failedLgaRecords) {
                $totalInvalidLga = count($failedLgaRecords);

                foreach ($failedLgaRecords as $invalidRecord) {
                    if (array_key_exists($invalidRecord['id'], $failedValidationRecords)) {
                        /**
                         * @var CandidateValidation $failedRecord
                         */
                        $failedRecord = $failedValidationRecords[$invalidRecord['id']];
                        $failedRecord->setFailedLga(1);

                        $failedValidationRecords[$invalidRecord['id']] = $failedRecord;
                    } else {
                        $failedRecord = new CandidateValidation();
                        $failedRecord->setId($invalidRecord['id']);
                        $failedRecord->setWhichListId($invalidRecord[$list_table_reference_id_column_name]);

                        $failedRecord->setFailedLga(1);
                        $failedValidationRecords[$failedRecord->getId()] = $failedRecord;
                    }
                }
            }

            //VALIDATE address
            $query = "SELECT * 
                FROM $staging_table_name 
                WHERE $list_table_reference_id_column_name=:list_id 
                AND 
                ( 
                     (address IS NULL) 
                     OR (trim(address)='')  
                )";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':list_id', $listId);
            $statement->execute();
            $failedAddressRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($failedAddressRecords) {
                $totalInvalidAddress = count($failedAddressRecords);

                foreach ($failedAddressRecords as $invalidRecord) {
                    if (array_key_exists($invalidRecord['id'], $failedValidationRecords)) {
                        /**
                         * @var CandidateValidation $failedRecord
                         */
                        $failedRecord = $failedValidationRecords[$invalidRecord['id']];
                        $failedRecord->setFailedAddress(1);

                        $failedValidationRecords[$invalidRecord['id']] = $failedRecord;
                    } else {
                        $failedRecord = new CandidateValidation();
                        $failedRecord->setId($invalidRecord['id']);
                        $failedRecord->setWhichListId($invalidRecord[$list_table_reference_id_column_name]);

                        $failedRecord->setFailedAddress(1);
                        $failedValidationRecords[$failedRecord->getId()] = $failedRecord;
                    }
                }

            }

            //VALIDATE center
            $query = "SELECT * 
                FROM $staging_table_name 
                WHERE $list_table_reference_id_column_name=:list_id 
                AND 
                ( 
                     (center IS NULL) 
                     OR (trim(center)='')  
                )";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':list_id', $listId);
            $statement->execute();
            $failedCenterRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($failedCenterRecords) {
                $totalInvalidCenter = count($failedCenterRecords);

                foreach ($failedCenterRecords as $invalidRecord) {
                    if (array_key_exists($invalidRecord['id'], $failedValidationRecords)) {
                        /**
                         * @var CandidateValidation $failedRecord
                         */
                        $failedRecord = $failedValidationRecords[$invalidRecord['id']];
                        $failedRecord->setFailedCenter(1);

                        $failedValidationRecords[$invalidRecord['id']] = $failedRecord;
                    } else {
                        $failedRecord = new CandidateValidation();
                        $failedRecord->setId($invalidRecord['id']);
                        $failedRecord->setWhichListId($invalidRecord[$list_table_reference_id_column_name]);

                        $failedRecord->setFailedCenter(1);
                        $failedValidationRecords[$failedRecord->getId()] = $failedRecord;
                    }
                }

            }

            //VALIDATE phone
            $query = "SELECT * 
                FROM $staging_table_name 
                WHERE $list_table_reference_id_column_name=:list_id 
                AND 
                ( 
                     (phone_number IS NULL) 
                     OR (trim(phone_number)='')  
                )";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':list_id', $listId);
            $statement->execute();
            $failedPhoneRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($failedPhoneRecords) {
                $totalInvalidPhoneNumber = count($failedPhoneRecords);

                foreach ($failedPhoneRecords as $invalidRecord) {
                    if (array_key_exists($invalidRecord['id'], $failedValidationRecords)) {
                        /**
                         * @var CandidateValidation $failedRecord
                         */
                        $failedRecord = $failedValidationRecords[$invalidRecord['id']];
                        $failedRecord->setFailedPhoneNumber(1);

                        $failedValidationRecords[$invalidRecord['id']] = $failedRecord;
                    } else {
                        $failedRecord = new CandidateValidation();
                        $failedRecord->setId($invalidRecord['id']);
                        $failedRecord->setWhichListId($invalidRecord[$list_table_reference_id_column_name]);

                        $failedRecord->setFailedPhoneNumber(1);
                        $failedValidationRecords[$failedRecord->getId()] = $failedRecord;
                    }
                }

            }

            //VALIDATE email
            $query = "SELECT * 
                FROM $staging_table_name 
                WHERE $list_table_reference_id_column_name=:list_id 
                AND 
                ( 
                     (email_address IS NULL) 
                     OR (trim(email_address)='')  
                )";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':list_id', $listId);
            $statement->execute();
            $failedEmailRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($failedEmailRecords) {
                $totalInvalidEmailAddress = count($failedEmailRecords);

                foreach ($failedEmailRecords as $invalidRecord) {
                    if (array_key_exists($invalidRecord['id'], $failedValidationRecords)) {
                        /**
                         * @var CandidateValidation $failedRecord
                         */
                        $failedRecord = $failedValidationRecords[$invalidRecord['id']];
                        $failedRecord->setFailedEmailAddress(1);

                        $failedValidationRecords[$invalidRecord['id']] = $failedRecord;
                    } else {
                        $failedRecord = new CandidateValidation();
                        $failedRecord->setId($invalidRecord['id']);
                        $failedRecord->setWhichListId($invalidRecord[$list_table_reference_id_column_name]);

                        $failedRecord->setFailedEmailAddress(1);
                        $failedValidationRecords[$failedRecord->getId()] = $failedRecord;
                    }
                }

            }

            //VALIDATE GENDER
            $query = "SELECT * 
                FROM $staging_table_name 
                WHERE $list_table_reference_id_column_name=:list_id 
                AND 
                ( 
                 (gender NOT IN (SELECT c.id FROM gender c WHERE c.record_status='ACTIVE')) 
                 OR (gender IS NULL OR trim(gender)='') 
                )";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':list_id', $listId);
            $statement->execute();
            $failedGenderRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($failedGenderRecords) {
                $totalInvalidGender = count($failedGenderRecords);

                foreach ($failedGenderRecords as $invalidRecord) {
                    if (array_key_exists($invalidRecord['id'], $failedValidationRecords)) {
                        /**
                         * @var CandidateValidation $failedRecord
                         */
                        $failedRecord = $failedValidationRecords[$invalidRecord['id']];
                        $failedRecord->setFailedGender(1);

                        $failedValidationRecords[$invalidRecord['id']] = $failedRecord;
                    } else {
                        $failedRecord = new CandidateValidation();
                        $failedRecord->setId($invalidRecord['id']);
                        $failedRecord->setWhichListId($invalidRecord[$list_table_reference_id_column_name]);

                        $failedRecord->setFailedGender(1);
                        $failedValidationRecords[$failedRecord->getId()] = $failedRecord;
                    }
                }
            }

            //VALIDATE post applied
            $query = "SELECT * 
                FROM $staging_table_name 
                WHERE $list_table_reference_id_column_name=:list_id 
                AND 
                ( 
                     (post_applied IS NULL) 
                     OR (trim(post_applied)='')  
                )";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':list_id', $listId);
            $statement->execute();
            $failedPostAppliedRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($failedPostAppliedRecords) {
                $totalInvalidPostApplied = count($failedPostAppliedRecords);

                foreach ($failedPostAppliedRecords as $invalidRecord) {
                    if (array_key_exists($invalidRecord['id'], $failedValidationRecords)) {
                        /**
                         * @var CandidateValidation $failedRecord
                         */
                        $failedRecord = $failedValidationRecords[$invalidRecord['id']];
                        $failedRecord->setFailedPostApplied(1);

                        $failedValidationRecords[$invalidRecord['id']] = $failedRecord;
                    } else {
                        $failedRecord = new CandidateValidation();
                        $failedRecord->setId($invalidRecord['id']);
                        $failedRecord->setWhichListId($invalidRecord[$list_table_reference_id_column_name]);

                        $failedRecord->setFailedPostApplied(1);
                        $failedValidationRecords[$failedRecord->getId()] = $failedRecord;
                    }
                }

            }

            //VALIDATE university
            $query = "SELECT * 
                FROM $staging_table_name 
                WHERE $list_table_reference_id_column_name=:list_id 
                AND 
                ( 
                     (university_of_study IS NULL) 
                     OR (trim(university_of_study)='')  
                )";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':list_id', $listId);
            $statement->execute();
            $failedUniversityRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($failedUniversityRecords) {
                $totalInvalidUniversity = count($failedUniversityRecords);

                foreach ($failedUniversityRecords as $invalidRecord) {
                    if (array_key_exists($invalidRecord['id'], $failedValidationRecords)) {
                        /**
                         * @var CandidateValidation $failedRecord
                         */
                        $failedRecord = $failedValidationRecords[$invalidRecord['id']];
                        $failedRecord->setFailedUniversity(1);

                        $failedValidationRecords[$invalidRecord['id']] = $failedRecord;
                    } else {
                        $failedRecord = new CandidateValidation();
                        $failedRecord->setId($invalidRecord['id']);
                        $failedRecord->setWhichListId($invalidRecord[$list_table_reference_id_column_name]);

                        $failedRecord->setFailedUniversity(1);
                        $failedValidationRecords[$failedRecord->getId()] = $failedRecord;
                    }
                }
            }

            //VALIDATE course
            $query = "SELECT * 
                FROM $staging_table_name 
                WHERE $list_table_reference_id_column_name=:list_id 
                AND 
                ( 
                     (course_of_study IS NULL) 
                     OR (trim(course_of_study)='')  
                )";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':list_id', $listId);
            $statement->execute();
            $failedCourseRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($failedCourseRecords) {
                $totalInvalidCourse = count($failedCourseRecords);

                foreach ($failedCourseRecords as $invalidRecord) {
                    if (array_key_exists($invalidRecord['id'], $failedValidationRecords)) {
                        /**
                         * @var CandidateValidation $failedRecord
                         */
                        $failedRecord = $failedValidationRecords[$invalidRecord['id']];
                        $failedRecord->setFailedCourse(1);

                        $failedValidationRecords[$invalidRecord['id']] = $failedRecord;
                    } else {
                        $failedRecord = new CandidateValidation();
                        $failedRecord->setId($invalidRecord['id']);
                        $failedRecord->setWhichListId($invalidRecord[$list_table_reference_id_column_name]);

                        $failedRecord->setFailedCourse(1);
                        $failedValidationRecords[$failedRecord->getId()] = $failedRecord;
                    }
                }
            }

            //VALIDATE degree
            $query = "SELECT * 
                FROM $staging_table_name 
                WHERE $list_table_reference_id_column_name=:list_id 
                AND 
                ( 
                     (class_of_degree IS NULL) 
                     OR (trim(class_of_degree)='')  
                )";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':list_id', $listId);
            $statement->execute();
            $failedDegreeRecords = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($failedDegreeRecords) {
                $totalInvalidClassOfDegree = count($failedDegreeRecords);

                foreach ($failedDegreeRecords as $invalidRecord) {
                    if (array_key_exists($invalidRecord['id'], $failedValidationRecords)) {
                        /**
                         * @var CandidateValidation $failedRecord
                         */
                        $failedRecord = $failedValidationRecords[$invalidRecord['id']];
                        $failedRecord->setFailedClassOfDegree(1);

                        $failedValidationRecords[$invalidRecord['id']] = $failedRecord;
                    } else {
                        $failedRecord = new CandidateValidation();
                        $failedRecord->setId($invalidRecord['id']);
                        $failedRecord->setWhichListId($invalidRecord[$list_table_reference_id_column_name]);

                        $failedRecord->setFailedClassOfDegree(1);
                        $failedValidationRecords[$failedRecord->getId()] = $failedRecord;
                    }
                }
            }

            //*************************************************************************************************

            //get the recruitment id from this list
            $query = "select recruitment_id 
                      from $list_table_name
                      where id=:id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':id', $listId);
            $statement->execute();

            $recruitmentId = $statement->fetchColumn(0);

            //if shortlist, check that entries are derived from long_list
            if($isShortList){

                $totalNotInLongList = 0;

                //get the ids of the long-lists of this recruitment
                $query = "select id from recruitment_long_list where recruitment_id=:recruitment_id";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':recruitment_id', $recruitmentId);
                $statement->execute();

                $idsOfRecruitmentLongLists = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

                if($idsOfRecruitmentLongLists){

                    $idsOfRecruitmentLongListsInString = implode(',', $idsOfRecruitmentLongLists);

                    $query = "select s.id as short_list_stage_id
                          from recruitment_long_list_candidates l 
                          left join recruitment_short_list_candidates_staging s
                                  on (l.id in ($idsOfRecruitmentLongListsInString))
                                  and 
                                  (
                                      s.phone_number=l.phone_number
                                      and s.surname = l.surname 
                                      and s.first_name=l.first_name 
                                  )
                          where s.surname is null";
                    $statement = $this->connection->prepare($query);
                    $statement->execute();

                    $shortListNotInLongListRecords = $statement->fetchAll();

                    if($shortListNotInLongListRecords){
                        $totalNotInLongList = count($shortListNotInLongListRecords);

                        foreach ($shortListNotInLongListRecords as $invalidRecord){
                            if (array_key_exists($invalidRecord['short_list_stage_id'], $failedValidationRecords)) {
                                /**
                                 * @var CandidateValidation $failedRecord
                                 */
                                $failedRecord = $failedValidationRecords[$invalidRecord['short_list_stage_id']];
                                $failedRecord->setFailedNotInLongList(1);

                                $failedValidationRecords[$invalidRecord['short_list_stage_id']] = $failedRecord;
                            } else {
                                $failedRecord = new CandidateValidation();
                                $failedRecord->setId($invalidRecord['short_list_stage_id']);
                                $failedRecord->setWhichListId($invalidRecord[$list_table_reference_id_column_name]);

                                $failedRecord->setFailedNotInLongList(1);
                                $failedValidationRecords[$failedRecord->getId()] = $failedRecord;
                            }
                        }
                    }

                }



            }

            //*******************************************************************************************************************

            //prepare summary validations
            $summaryFailedValidations = array();

            if ($totalInvalidSurname > 0) {
                $summaryFailedValidations[AppConstants::INVALID_SURNAME] = $totalInvalidSurname;
            }

            if ($totalInvalidFirstName > 0) {
                $summaryFailedValidations[AppConstants::INVALID_FIRST_NAME] = $totalInvalidFirstName;
            }

            if ($totalInvalidOtherName > 0) {
                $summaryFailedValidations[AppConstants::INVALID_OTHER_NAME] = $totalInvalidOtherName;
            }

            if ($totalInvalidDateOfBirth > 0) {
                $summaryFailedValidations[AppConstants::INVALID_DATE_OF_BIRTH] = $totalInvalidDateOfBirth;
            }

            if ($totalInvalidStateOfOrigin > 0) {
                $summaryFailedValidations[AppConstants::INVALID_STATE_OF_ORIGIN] = $totalInvalidStateOfOrigin;
            }

            if ($totalInvalidLga > 0) {
                $summaryFailedValidations[AppConstants::INVALID_LGA] = $totalInvalidLga;
            }

            if ($totalInvalidAddress > 0) {
                $summaryFailedValidations[AppConstants::INVALID_ADDRESS] = $totalInvalidAddress;
            }

            if ($totalInvalidCenter > 0) {
                $summaryFailedValidations[AppConstants::INVALID_CENTER] = $totalInvalidCenter;
            }

            if ($totalInvalidPhoneNumber > 0) {
                $summaryFailedValidations[AppConstants::INVALID_PHONE] = $totalInvalidPhoneNumber;
            }

            if ($totalInvalidEmailAddress > 0) {
                $summaryFailedValidations[AppConstants::INVALID_EMAIL] = $totalInvalidEmailAddress;
            }

            if ($totalInvalidGender > 0) {
                $summaryFailedValidations[AppConstants::INVALID_GENDER] = $totalInvalidGender;
            }

            if ($totalInvalidPostApplied > 0) {
                $summaryFailedValidations[AppConstants::INVALID_POST_APPLIED] = $totalInvalidPostApplied;
            }

            if ($totalInvalidUniversity > 0) {
                $summaryFailedValidations[AppConstants::INVALID_UNIVERSITY] = $totalInvalidUniversity;
            }

            if ($totalInvalidCourse > 0) {
                $summaryFailedValidations[AppConstants::INVALID_COURSE] = $totalInvalidCourse;
            }

            if ($totalInvalidClassOfDegree > 0) {
                $summaryFailedValidations[AppConstants::INVALID_DEGREE] = $totalInvalidClassOfDegree;
            }

            if($isShortList){
                if($totalNotInLongList){
                    $summaryFailedValidations[AppConstants::INVALID_NOT_IN_LONG_LIST] = $totalNotInLongList;
                }
            }


            //START A TRANSACTION
            $this->connection->beginTransaction();

            if (empty($summaryFailedValidations)) {
                //all validation passed
                $query = "UPDATE $list_table_name 
                    SET validation_status=:validation_status, date_last_validated=now() 
                    WHERE id=:id";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':validation_status', AppConstants::PASSED);
                $statement->bindValue(':id', $listId);
                $outcome = $statement->execute();
            } else {
                //update the table
                $query = "UPDATE $list_table_name 
                    SET validation_status=:validation_status, date_last_validated=now() 
                    WHERE id=:id";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':validation_status', AppConstants::FAILED);
                $statement->bindValue(':id', $listId);
                $outcome = $statement->execute();

                //PREPARE THE SUMMARY BATCH DATA
                $batchSummaryFailedData = array();
                foreach ($summaryFailedValidations as $validationKey => $totalFailed) {
                    $batchSummaryFailedRow = array();
                    $batchSummaryFailedRow[$list_table_reference_id_column_name] = $listId;
                    $batchSummaryFailedRow['validation_reason'] = $validationKey;
                    $batchSummaryFailedRow['total_failed'] = $totalFailed;

                    $batchSummaryFailedData[] = $batchSummaryFailedRow;
                }

                //PREPARE THE DETAILED BATCH DATA
                $batchDetailedFailedData = array();
                /**
                 * @var CandidateValidation $failedRecord
                 */
                foreach ($failedValidationRecords as $key => $failedRecord) {
                    $batchDetailedFailedRow = array();

                    $batchDetailedFailedRow['id'] = $failedRecord->getId();
                    $batchDetailedFailedRow[$list_table_reference_id_column_name] = $failedRecord->getWhichListId();
                    $batchDetailedFailedRow['failed_surname'] = $failedRecord->getFailedSurname();
                    $batchDetailedFailedRow['failed_first_name'] = $failedRecord->getFailedFirstName();
                    $batchDetailedFailedRow['failed_other_name'] = $failedRecord->getFailedOtherNames();
                    $batchDetailedFailedRow['failed_date_of_birth'] = $failedRecord->getFailedDateOfBirth();
                    $batchDetailedFailedRow['failed_state_of_origin'] = $failedRecord->getFailedStateOfOrigin();
                    $batchDetailedFailedRow['failed_lga'] = $failedRecord->getFailedLga();
                    $batchDetailedFailedRow['failed_address'] = $failedRecord->getFailedAddress();
                    $batchDetailedFailedRow['failed_center'] = $failedRecord->getFailedCenter();
                    $batchDetailedFailedRow['failed_phone'] = $failedRecord->getFailedPhoneNumber();
                    $batchDetailedFailedRow['failed_email'] = $failedRecord->getFailedEmailAddress();
                    $batchDetailedFailedRow['failed_gender'] = $failedRecord->getFailedGender();
                    $batchDetailedFailedRow['failed_post_applied'] = $failedRecord->getFailedPostApplied();
                    $batchDetailedFailedRow['failed_university'] = $failedRecord->getFailedUniversity();
                    $batchDetailedFailedRow['failed_course'] = $failedRecord->getFailedCourse();
                    $batchDetailedFailedRow['failed_class_of_degree'] = $failedRecord->getClassOfDegree();
                    $batchDetailedFailedRow['failed_appointment_status'] = $failedRecord->getFailedAppointmentStatus();
                    $batchDetailedFailedRow['failed_not_in_long_list'] = $failedRecord->getFailedNotInLongList();

                    $batchDetailedFailedData[] = $batchDetailedFailedRow;
                }

                //REMOVE THIS LATER AFTER KEEPING TRACK
                $query = "DELETE FROM $failed_validation_table_name WHERE $list_table_reference_id_column_name=:list_id";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':list_id', $listId);
                $delete_outcome = $statement->execute();

                //REMOVE THIS LATER AFTER KEEPING TRACK
                $query = "DELETE FROM $failed_validation_detail_table WHERE $list_table_reference_id_column_name=:list_id";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':list_id', $listId);
                $delete_outcome = $statement->execute();

                $outcome = $this->pdoMultiInsert($failed_validation_table_name, $batchSummaryFailedData);
                $outcome = $this->pdoMultiInsert($failed_validation_detail_table, $batchDetailedFailedData);
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

    /**
     * @param $tableName
     * @param $data
     * @return bool
     * @throws AppException
     */
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

}