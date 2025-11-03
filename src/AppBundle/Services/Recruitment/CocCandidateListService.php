<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 2:11 PM
 */

namespace AppBundle\Services\Recruitment;


use AppBundle\AppException\AppException;
use AppBundle\AppException\AppExceptionMessages;
use AppBundle\Model\Recruitment\Candidate;
use AppBundle\Model\Recruitment\CandidateValidation;
use AppBundle\Model\Recruitment\CocCandidateList;
use AppBundle\Model\SearchCriteria\CandidateSearchCriteria;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\Paginator;
use AppBundle\Utils\ServiceHelper;
use Doctrine\DBAL\Connection;
use \PDO;
use \Throwable;

class CocCandidateListService extends ServiceHelper
{
    private $connection;
    private $rows_per_page;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->rows_per_page = AppConstants::ROWS_PER_PAGE;
    }

    /**
     * @param CocCandidateList $cocCandidateList
     * @return bool
     * @throws AppException
     */
    public function addList(CocCandidateList $cocCandidateList): bool
    {
        $outcome = false;
        $statement = null;

        try {
            //now insert
            $query = "INSERT INTO recruitment_coc_candidates_list 
                (
                recruitment_id,total_candidates,attachment_file_name,validation_status,data_transfer_status
                ,record_status,created,created_by,last_mod,last_mod_by,selector
                ) 
                VALUES 
                (
                :recruitment_id,:total_candidates,:attachment_file_name,:validation_status,:data_transfer_status
                ,:record_status,:created,:created_by,:last_mod,:last_mod_by,:selector
                )";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':recruitment_id', $this->getValueOrNull($cocCandidateList->getRecruitmentId()));
            $statement->bindValue(':total_candidates', $this->getValueOrNull($cocCandidateList->getTotalCandidates()));
            $statement->bindValue(':attachment_file_name', $this->getValueOrNull($cocCandidateList->getAttachment()->getFileName()));
            $statement->bindValue(':validation_status', $this->getValueOrNull($cocCandidateList->getValidationStatus()));
            $statement->bindValue(':data_transfer_status', $this->getValueOrNull($cocCandidateList->getDataTransferStatus()));
            $statement->bindValue(':record_status', $this->getValueOrNull($cocCandidateList->getRecordStatus()));
            $statement->bindValue(':created', $this->getValueOrNull($cocCandidateList->getLastModified()));
            $statement->bindValue(':created_by', $this->getValueOrNull($cocCandidateList->getLastModifiedByUserId()));
            $statement->bindValue(':last_mod', $this->getValueOrNull($cocCandidateList->getLastModified()));
            $statement->bindValue(':last_mod_by', $this->getValueOrNull($cocCandidateList->getLastModifiedByUserId()));
            $statement->bindValue(':selector', $this->getValueOrNull($cocCandidateList->getSelector()));

            $statement->execute();

            $outcome = true;

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $outcome;

    }

    /**
     * @param $recruitmentId
     * @return array
     * @throws AppException
     */
    public function getListsForRecruitment($recruitmentId)
    {
        $recruitmentLongLists = array();
        $statement = null;

        try {
            $query = "SELECT d.id,d.recruitment_id,d.total_candidates,d.attachment_file_name
                ,d.validation_status,date_format(d.date_last_validated,'%d %d, %Y') as date_last_validated_formatted
                ,d.data_transfer_status,date_format(d.date_data_transfered,'%d %d, %Y') as date_data_transfered_formatted
                ,d.record_status,date_format(d.created,'%d %b, %Y') as created_formatted ,d.selector                 
                FROM recruitment_coc_candidates_list d 
                WHERE d.recruitment_id=:recruitment_id and d.record_status=:record_status";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':recruitment_id', $recruitmentId);
            $statement->bindValue(':record_status', AppConstants::ACTIVE);
            $statement->execute();

            $record = $statement->fetch(PDO::FETCH_ASSOC);
            if ($record) {
                $cocCandidateList = new CocCandidateList();
                $cocCandidateList->setId($record['id']);
                $cocCandidateList->setRecruitmentId($record['recruitment_id']);
                $cocCandidateList->setTotalCandidates($record['total_candidates']);
                $cocCandidateList->setValidationStatus($record['validation_status']);
                $cocCandidateList->setDateLastValidated($record['date_last_validated_formatted']);
                $cocCandidateList->setDataTransferStatus($record['date_data_transfered_formatted']);
                $cocCandidateList->setRecordStatus($record['record_status']);
                $cocCandidateList->setCreated($record['created_formatted']);

                $cocCandidateList->setSelector($record['selector']);

                $recruitmentLongLists[] = $cocCandidateList;
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $recruitmentLongLists;
    }

    /**
     * @param $listId
     * @return bool|null|string
     * @throws AppException
     */
    public function getValidationStatus($listId) //return string
    {
        $validationStatus = null;
        $statement = null;

        try {

            $query = "SELECT validation_status FROM recruitment_coc_candidates_list WHERE id=:id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':id', $listId);
            $statement->execute();

            $validationStatus = $statement->fetchColumn(0);

            if ($validationStatus == AppConstants::FATAL_ERROR) {
                try {
                    $this->connection->beginTransaction();
                    //BACKUP SUBMISSION WITH FATAL ERROR
                    $query = "INSERT INTO recruitment_coc_candidates_list_with_fatal_error
                              (
                                  SELECT l.* 
                                  FROM recruitment_coc_candidates_list l
                                  WHERE l.id=:id
                              )";
                    $statement = $this->connection->prepare($query);
                    $statement->bindValue(':id', $listId);
                    $statement->execute();

                    //NOW DELETE THE SUBMISSION WITH FATAL ERROR
                    $query = "DELETE FROM recruitment_coc_candidates_list 
                              WHERE id=:id";
                    $statement = $this->connection->prepare($query);
                    $statement->bindValue(':id', $listId);
                    $statement->execute();

                    $this->connection->commit();

                } catch (AppException $e) {
                    if ($this->connection->isTransactionActive()) {
                        $this->connection->rollBack();
                    }
                }
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }
        return $validationStatus;
    }

    /**
     * @param $listId
     * @param $validationStatus
     * @return bool
     * @throws AppException
     */
    public function updateValidationStatus($listId, $validationStatus): bool
    {
        $outcome = false;
        $statement = null;

        try {

            $query = "UPDATE recruitment_coc_candidates_list 
                 SET validation_status=:validation_status 
                 WHERE id=:id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':validation_status', $validationStatus);
            $statement->bindValue(':id', $listId);
            $statement->execute();

            $outcome = true;

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $outcome;
    }

    /**
     * @param $listId
     * @param $userProfileId
     * @return bool
     * @throws AppException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function transferFromStagingArea($listId, $userProfileId): bool
    {
        $outcome = false;
        $statement = null;

        try {

            if ($listId) {

                $this->connection->beginTransaction();

                //now move the records from the staging section to the main table
                //CONSIDER RE-IMPLEMENTING THIS, maybe
                $query = "insert into recruitment_coc_candidates_list_entries 
                            (
                             id, recruitment_coc_candidates_list_id, serial_no, surname, first_name, other_names, date_of_birth
                             , state_of_origin, lga, address, center, phone_number, email_address, gender, post_applied
                             , university_of_study, course_of_study, class_of_degree
                            )  
                            ( 
                             select 
                             s.id, s.recruitment_coc_candidates_list_id, s.serial_no, s.surname, s.first_name, s.other_names, s.date_of_birth
                             , s.state_of_origin, s.lga, s.address, s.center, s.phone_number, s.email_address, s.gender, s.post_applied
                             , s.university_of_study, s.course_of_study, s.class_of_degree
                             from recruitment_coc_candidates_list_entries_staging s 
                             where s.recruitment_coc_candidates_list_id = $listId
                            )";
                $statement = $this->connection->prepare($query);
                $statement->execute();

                //update the transfer status to completed
                $query = "UPDATE recruitment_coc_candidates_list 
                 SET data_transfer_status=:data_transfer_status 
                 WHERE id=:id";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':data_transfer_status', AppConstants::COMPLETED);
                $statement->bindValue(':id', $listId);
                $statement->execute();


                $this->connection->commit();
            }

            $outcome = true;

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
     * @param $listId
     * @return bool|null|string
     * @throws AppException
     */
    public function getDataTransferStatus($listId)
    {
        $dataTransferStatus = null;
        $statement = null;

        try {

            $query = "SELECT data_transfer_status FROM recruitment_coc_candidates_list WHERE id=:id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':id', $listId);
            $statement->execute();

            $dataTransferStatus = $statement->fetchColumn(0);

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }
        return $dataTransferStatus;
    }


    /**
     * @param $listId
     * @param $dataTransferStatus
     * @return bool
     * @throws AppException
     */
    public function updateDataTransferStatus($listId, $dataTransferStatus): bool
    {
        $outcome = false;
        $statement = null;

        try {

            $query = "UPDATE recruitment_coc_candidates_list 
                 SET data_transfer_status=:data_transfer_status 
                 WHERE id=:id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':data_transfer_status', $dataTransferStatus);
            $statement->bindValue(':id', $listId);
            $statement->execute();

            $outcome = true;

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $outcome;
    }

    /**
     * @param CocCandidateList $cocCandidateList
     * @return bool
     * @throws AppException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function deleteList(CocCandidateList $cocCandidateList): bool
    {
        $outcome = false;
        $statement = null;

        try {

            $this->connection->beginTransaction();

            //check that no shortlist has been uploaded
            $query = "SELECT attachment_file_name FROM recruitment_coc_candidates_list WHERE recruitment_id = :recruitment_id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':recruitment_id', $cocCandidateList->getRecruitmentId());
            $statement->execute();

            $shortListFileName = $statement->fetchColumn(0);

            if ($shortListFileName) {
                throw new AppException(AppExceptionMessages::OPERATION_NOT_ALLOWED);
            }

            //delete the long list candidates
            $query = "DELETE FROM recruitment_coc_candidates_list_entries WHERE recruitment_coc_candidates_list_id = :recruitment_coc_candidates_list_id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':recruitment_coc_candidates_list_id', $cocCandidateList->getId());
            $statement->execute();

            //delete the long list staging
            $query = "DELETE FROM recruitment_coc_candidates_list_entries_staging WHERE recruitment_coc_candidates_list_id = :recruitment_coc_candidates_list_id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':recruitment_coc_candidates_list_id', $cocCandidateList->getId());
            $statement->execute();

            //delete failed validation
            $query = "DELETE FROM recruitment_coc_candidates_list_failed_validations WHERE recruitment_coc_candidates_list_id = :recruitment_coc_candidates_list_id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':recruitment_coc_candidates_list_id', $cocCandidateList->getId());
            $statement->execute();

            //delete failed validation detail
            $query = "DELETE FROM recruitment_coc_candidates_list_failed_validations_detail WHERE recruitment_coc_candidates_list_id = :recruitment_coc_candidates_list_id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':recruitment_coc_candidates_list_id', $cocCandidateList->getId());
            $statement->execute();

            //delete the main list itself
            $query = "DELETE FROM recruitment_coc_candidates_list WHERE id = :id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':id', $cocCandidateList->getId());
            $statement->execute();

            $this->connection->commit();
            $outcome = true;

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

    /*******************************************CANDIDATES*********************************************************/

    /**
     * @param CandidateSearchCriteria $searchCriteria
     * @param Paginator $paginator
     * @param $pageDirection
     * @return array
     * @throws AppException
     */
    public function searchCandidates(CandidateSearchCriteria $searchCriteria, Paginator $paginator, $pageDirection): array
    {
        $searchResults = array();
        $statement = null;

        try {

            $searchListId = $searchCriteria->getListId();

            if (!$searchListId) {
                $searchListId = 0;
            }

            $where = array("d.recruitment_coc_candidates_list_id=$searchListId");

            $searchSurname = $searchCriteria->getSurname();
            $searchPhone = $searchCriteria->getPhoneNumber();
            $searchPostApplied = $searchCriteria->getPostApplied();

            if ($searchSurname) {
                $where[] = "d.surname = :surname";
            }

            if ($searchPhone) {
                $where[] = "d.phone_number = :phone_number";
            }

            if ($searchPostApplied) {
                $where[] = "d.post_applied LIKE :post_applied";
            }

            if ($where) {
                $where = implode(" AND ", $where);
            } else {
                $where = '';
            }

            //fetch total matching rows
            $countQuery = "SELECT COUNT(d.id) AS totalRows 
                          FROM recruitment_coc_candidates_list_entries d 
                          JOIN recruitment_coc_candidates_list l on d.recruitment_coc_candidates_list_id = l.id
                          JOIN recruitment r on l.recruitment_id = r.id
                          JOIN organization o on r.organization_id = o.id 
                          WHERE $where";
            $statement = $this->connection->prepare($countQuery);

            if ($searchSurname) {
                $statement->bindValue(':surname', $searchSurname);
            }

            if ($searchPhone) {
                $statement->bindValue(':phone_number', $searchPhone);
            }

            if ($searchPostApplied) {
                $statement->bindValue(':post_applied', "%$searchPostApplied%");
            }

            $statement->execute();
            $totalRows = $statement->fetchColumn();

            $paginator->setTotalRows($totalRows);
            $paginator->setRowsPerPage($this->rows_per_page);

            switch ($pageDirection) {
                case 'FIRST':
                    $paginator->pageFirst();
                    break;
                case 'PREVIOUS':
                    $paginator->pagePrevious();
                    break;
                case 'NEXT':
                    $paginator->pageNext();
                    break;
                case 'LAST':
                    $paginator->pageLast();
                    break;
                default:
                    $paginator->pageFirst();
                    break;
            }

            $limitStartRow = $paginator->getStartRow();

            //now exec SELECT with limit clause
            $query = "SELECT d.id,d.recruitment_coc_candidates_list_id
                ,d.serial_no, d.surname, d.first_name, d.other_names, d.date_of_birth, d.address, d.center
                , d.phone_number, d.email_address, d.lga, d.gender, d.post_applied
                , d.university_of_study, d.course_of_study, d.state_of_origin, d.class_of_degree
                , l.recruitment_id, r.organization_id,r.recruitment_year, r.selector as recruitment_selector
                , c.description as recruitment_category_name
                , o.organization_name , o.guid as organization_selector
                FROM recruitment_coc_candidates_list_entries d 
                JOIN recruitment_coc_candidates_list l on d.recruitment_coc_candidates_list_id = l.id
                JOIN recruitment r on l.recruitment_id = r.id
                JOIN recruitment_category_types c on r.recruitment_category_id = c.id 
                JOIN organization o on r.organization_id = o.id 
                WHERE $where order by d.serial_no LIMIT $limitStartRow ,$this->rows_per_page";
            $statement = $this->connection->prepare($query);

            if ($searchSurname) {
                $statement->bindValue(':surname', $searchSurname);
            }

            if ($searchPhone) {
                $statement->bindValue(':phone_number', $searchPhone);
            }

            if ($searchPostApplied) {
                $statement->bindValue(':post_applied', "%$searchPostApplied%");
            }

            $statement->execute();

            $records = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($records) {
                $displaySerialNo = $paginator->getStartRow() + 1;

                for ($i = 0; $i < count($records); $i++) {

                    $record = $records[$i];

                    $candidate = new Candidate();
                    $candidate->setId($record['id']);
                    $candidate->setRecruitmentId($record['recruitment_id']);
                    $candidate->setRecruitmentSelector($record['recruitment_selector']);
                    $candidate->setRecruitmentYear($record['recruitment_year']);
                    $candidate->setSerialNo($record['serial_no']);
                    $candidate->setSurname($record['surname']);
                    $candidate->setFirstName($record['first_name']);
                    $candidate->setOtherNames($record['other_names']);
                    $candidate->setDateOfBirth($record['date_of_birth']);
                    $candidate->setStateOfOrigin($record['state_of_origin']);
                    $candidate->setLga($record['lga']);
                    $candidate->setAddress($record['address']);
                    $candidate->setCenterState($record['center']);
                    $candidate->setPhoneNumber($record['phone_number']);
                    $candidate->setEmailAddress($record['email_address']);
                    $candidate->setGender($record['gender']);
                    $candidate->setPostApplied($record['post_applied']);
                    $candidate->setUniversity($record['university_of_study']);
                    $candidate->setCourse($record['course_of_study']);
                    $candidate->setClassOfDegree($record['class_of_degree']);

                    $candidate->setDisplaySerialNo($displaySerialNo);
                    $displaySerialNo++;

                    $searchResults[] = $candidate;
                }
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $searchResults;
    }

    /**
     * @param $listId
     * @param Paginator $paginator
     * @param $pageDirection
     * @return array
     * @throws AppException
     */
    public function searchFailedValidationDetail($listId, Paginator $paginator, $pageDirection): array
    {
        $failedValidations = array();
        $statement = null;

        try {

            if (!$listId) {
                $listId = '-1'; //match nothing
            }

            //fetch total matching rows
            $countQuery = "SELECT COUNT(d.id) AS totalRows 
                FROM recruitment_coc_candidates_list_failed_validations_detail d 
                WHERE d.recruitment_coc_candidates_list_id=:recruitment_coc_candidates_list_id ";
            $statement = $this->connection->prepare($countQuery);
            $statement->bindValue(':recruitment_coc_candidates_list_id', $listId);

            $statement->execute();
            $totalRows = $statement->fetchColumn();

            $paginator->setTotalRows($totalRows);
            $paginator->setRowsPerPage($this->rows_per_page);

            switch ($pageDirection) {
                case 'FIRST':
                    $paginator->pageFirst();
                    break;
                case 'PREVIOUS':
                    $paginator->pagePrevious();
                    break;
                case 'NEXT':
                    $paginator->pageNext();
                    break;
                case 'LAST':
                    $paginator->pageLast();
                    break;
                default:
                    $paginator->pageFirst();
                    break;
            }

            $limitStartRow = $paginator->getStartRow();

            //now exec SELECT with limit clause
            $query = "SELECT d.id,d.recruitment_coc_candidates_list_id,d.failed_surname,d.failed_first_name,d.failed_other_name,d.failed_date_of_birth,d.failed_state_of_origin
                ,d.failed_lga,d.failed_address,d.failed_center,d.failed_phone,d.failed_email,d.failed_gender,d.failed_post_applied,d.failed_university
                ,d.failed_course,d.failed_class_of_degree,d.failed_appointment_status
                , s.serial_no, s.surname, s.first_name, s.other_names, s.date_of_birth, s.address, s.center
                , s.phone_number, s.email_address, s.lga, s.gender, s.post_applied
                , s.university_of_study, s.course_of_study, s.state_of_origin, s.class_of_degree
                FROM recruitment_coc_candidates_list_failed_validations_detail d 
                JOIN recruitment_coc_candidates_list_entries_staging s on (d.id = s.id and d.recruitment_coc_candidates_list_id = s.recruitment_coc_candidates_list_id)
                WHERE d.recruitment_coc_candidates_list_id=:recruitment_coc_candidates_list_id 
                ORDER BY d.id LIMIT $limitStartRow ,$this->rows_per_page";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':recruitment_coc_candidates_list_id', $listId);

            $statement->execute();

            $records = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($records) {
                foreach ($records as $record) {
                    $failedRecord = new CandidateValidation();
                    $failedRecord->setId($record['id']);
                    $failedRecord->setSerialNo($record['serial_no']);
                    $failedRecord->setSurname($record['surname']);
                    $failedRecord->setFirstName($record['first_name']);
                    $failedRecord->setOtherNames($record['other_names']);
                    $failedRecord->setDateOfBirth($record['date_of_birth']);
                    $failedRecord->setStateOfOrigin($record['state_of_origin']);
                    $failedRecord->setLga($record['lga']);
                    $failedRecord->setAddress($record['address']);
                    $failedRecord->setCenterState($record['center']);
                    $failedRecord->setPhoneNumber($record['phone_number']);
                    $failedRecord->setEmailAddress($record['email_address']);
                    $failedRecord->setGender($record['gender']);
                    $failedRecord->setPostApplied($record['post_applied']);
                    $failedRecord->setUniversity($record['university_of_study']);
                    $failedRecord->setCourse($record['course_of_study']);
                    $failedRecord->setClassOfDegree($record['class_of_degree']);

                    $failedRecord->setFailedSurname($record['failed_surname']);
                    $failedRecord->setFailedFirstName($record['failed_first_name']);
                    $failedRecord->setFailedOtherNames($record['failed_other_name']);
                    $failedRecord->setFailedDateOfBirth($record['failed_date_of_birth']);
                    $failedRecord->setFailedStateOfOrigin($record['failed_state_of_origin']);
                    $failedRecord->setFailedLga($record['failed_lga']);
                    $failedRecord->setFailedAddress($record['failed_address']);
                    $failedRecord->setFailedCenter($record['failed_center']);
                    $failedRecord->setFailedPhoneNumber($record['failed_phone']);
                    $failedRecord->setFailedEmailAddress($record['failed_email']);
                    $failedRecord->setFailedGender($record['failed_gender']);
                    $failedRecord->setFailedPostApplied($record['failed_post_applied']);
                    $failedRecord->setFailedUniversity($record['failed_university']);
                    $failedRecord->setFailedCourse($record['failed_course']);
                    $failedRecord->setFailedClassOfDegree($record['failed_class_of_degree']);
                    $failedRecord->setFailedAppointmentStatus($record['failed_appointment_status']);

                    $failedValidations[] = $failedRecord;
                }
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $failedValidations;
    }

    /**
     * @param $id
     * @return Candidate|null
     * @throws AppException
     */
    public function getCandidate($id): ?Candidate
    {
        $candidate = null;
        $statement = null;

        try {
            $query = "SELECT d.id,d.recruitment_coc_candidates_list_id
                ,d.serial_no, d.surname, d.first_name, d.other_names, d.date_of_birth, d.address, d.center
                , d.phone_number, d.email_address, d.lga, d.gender, d.post_applied
                , d.university_of_study, d.course_of_study, d.state_of_origin, d.class_of_degree
                , l.recruitment_id, r.organization_id,r.recruitment_year, r.selector AS recruitment_selector
                , c.description AS recruitment_category_name
                , o.organization_name , o.guid AS organization_selector
                FROM recruitment_coc_candidates_list_entries d 
                JOIN recruitment_coc_candidates_list l ON d.recruitment_coc_candidates_list_id = l.id
                JOIN recruitment r ON l.recruitment_id = r.id
                JOIN recruitment_category_types c ON r.recruitment_category_id = c.id 
                JOIN organization o ON r.organization_id = o.id 
                WHERE d.id=:id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':id', $id);
            $statement->execute();

            $record = $statement->fetch(PDO::FETCH_ASSOC);
            if ($record) {
                $candidate = new Candidate();
                $candidate->setId($record['id']);
                $candidate->setRecruitmentId($record['recruitment_id']);
                $candidate->setRecruitmentSelector($record['recruitment_selector']);
                $candidate->setRecruitmentYear($record['recruitment_year']);
                $candidate->setSerialNo($record['serial_no']);
                $candidate->setSurname($record['surname']);
                $candidate->setFirstName($record['first_name']);
                $candidate->setOtherNames($record['other_names']);
                $candidate->setDateOfBirth($record['date_of_birth']);
                $candidate->setStateOfOrigin($record['state_of_origin']);
                $candidate->setLga($record['lga']);
                $candidate->setAddress($record['address']);
                $candidate->setCenterState($record['center']);
                $candidate->setPhoneNumber($record['phone_number']);
                $candidate->setEmailAddress($record['email_address']);
                $candidate->setGender($record['gender']);
                $candidate->setPostApplied($record['post_applied']);
                $candidate->setUniversity($record['university_of_study']);
                $candidate->setCourse($record['course_of_study']);
                $candidate->setClassOfDegree($record['class_of_degree']);
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $candidate;
    }

}