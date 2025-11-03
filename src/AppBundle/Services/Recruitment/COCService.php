<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 2:11 PM
 */

namespace AppBundle\Services\Recruitment;


use AppBundle\AppException\AppException;
use AppBundle\Model\Document\AttachedDocument;
use AppBundle\Model\Recruitment\ApprovalComment;
use AppBundle\Model\Recruitment\COC;
use AppBundle\Model\Recruitment\FetchParam;
use AppBundle\Model\SearchCriteria\COCSearchCriteria;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\Paginator;
use AppBundle\Utils\ServiceHelper;
use Doctrine\DBAL\Connection;
use \PDO;
use \Throwable;

class COCService extends ServiceHelper
{
    private $connection;
    private $rows_per_page;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->rows_per_page = AppConstants::ROWS_PER_PAGE;
    }

    /**
     * @param COCSearchCriteria $searchCriteria
     * @param Paginator $paginator
     * @param $pageDirection
     * @return array
     * @throws AppException
     */
    public function searchRecords(COCSearchCriteria $searchCriteria, Paginator $paginator, $pageDirection): array
    {
        $searchResults = array();
        $statement = null;

        try {

            $searchRecruitment = $searchCriteria->getRecruitment();
            $searchOrganization = $searchCriteria->getOrganizationId();

            $where = array();

            if ($searchRecruitment) {
                $where[] = "d.recruitment_id = :recruitment_id";
            }
            if ($searchOrganization) {
                $where[] = "r.organization_id = :organization_id";
            }

            $where[] = "d.record_status='ACTIVE'";

            if ($where) {
                $where = implode(" AND ", $where);
            } else {
                $where = '';
            }

            //fetch total matching rows
            $countQuery = "SELECT COUNT(d.id) AS totalRows 
                          FROM recruitment_coc d 
                          JOIN recruitment r on d.recruitment_id = r.id
                          JOIN organization o on r.organization_id = o.id 
                          JOIN recruitment_coc_types t on d.coc_request_type_id = t.id
                          WHERE $where";
            $statement = $this->connection->prepare($countQuery);
            if ($searchRecruitment) {
                $statement->bindValue(':recruitment_id', $searchRecruitment);
            }
            if ($searchOrganization) {
                $statement->bindValue(':organization_id', $searchOrganization);
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
            $query = "SELECT d.id,d.recruitment_id , d.coc_request_type_id, d.selected_candidates_attachment_file_name
                ,date_format(d.date_of_selected_candidates_attachment,'%d %b, %Y') as date_of_selected_candidates_attachment_formatted
                , d.committee_chairman_recommendation
                ,date_format(d.date_of_committee_chairman_recommendation,'%d %b, %Y') as date_of_committee_chairman_recommendation_formatted
                ,d.committee_sec_confirmation
                , date_format(d.date_of_committee_sec_confirmation,'%d %b, %Y') as date_of_committee_sec_confirmation_formatted
                ,d.executive_chairman_approval
                , date_format(d.date_of_executive_chairman_approval,'%d %b, %Y') as date_of_executive_chairman_approval_formatted
                , d.dme_confirmation
                , date_format(d.date_of_dme_confirmation,'%d %b, %Y') as date_of_dme_confirmation_formatted
                , d.duration_in_months, d.duration_in_weeks
                , date_format(d.expected_date_of_expiration,'%d %b, %Y') as expected_date_of_expiration_formatted 
                , d.last_appointed_candidates_attachment_file_name
                , date_format(d.date_of_last_appointed_candidates_attachment,'%d %b, %Y') as date_of_last_appointed_candidates_attachment_formatted
                , date_format(d.created,'%d %b, %Y') as created_formatted
                , (curdate() > d.expected_date_of_expiration) as expired, d.selector 
                , r.organization_id,r.recruitment_year, r.selector as recruitment_selector
                , c.description as recruitment_category_name
                , o.organization_name , o.guid as organization_selector
                , t.description as coc_request_type_name
                FROM recruitment_coc d 
                JOIN recruitment r on d.recruitment_id = r.id
                JOIN recruitment_category_types c on r.recruitment_category_id = c.id 
                JOIN organization o on r.organization_id = o.id 
                JOIN recruitment_coc_types t on d.coc_request_type_id = t.id
                WHERE $where order by d.created desc LIMIT $limitStartRow ,$this->rows_per_page";
            $statement = $this->connection->prepare($query);

            if ($searchRecruitment) {
                $statement->bindValue(':recruitment_id', $searchRecruitment);
            }
            if ($searchOrganization) {
                $statement->bindValue(':organization_id', $searchOrganization);
            }

            $statement->execute();

            $records = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($records) {
                $displaySerialNo = $paginator->getStartRow() + 1;

                for ($i = 0; $i < count($records); $i++) {

                    $record = $records[$i];

                    $coc = new COC();
                    $coc->setId($record['id']);
                    $coc->setRecruitmentId($record['recruitment_id']);
                    $coc->setRecruitmentSelector($record['recruitment_selector']);
                    $coc->setRecruitmentYear($record['recruitment_year']);
                    $coc->setRecruitmentCategoryName($record['recruitment_category_name']);
                    $coc->setOrganizationId($record['organization_id']);
                    $coc->setOrganizationName($record['organization_name']);
                    $coc->setOrganizationSelector($record['organization_selector']);
                    $coc->setTypeOfCocId($record['coc_request_type_id']);
                    $coc->setTypeOfCocName($record['coc_request_type_name']);

                    $coc->setCommitteeChairRecommendation($record['committee_chairman_recommendation']);
                    $coc->setDateOfCommitteeChairRecommendation($record['date_of_committee_chairman_recommendation_formatted']);

                    $coc->setCommitteeSecConfirmation($record['committee_sec_confirmation']);
                    $coc->setDateOfCommitteeSecConfirmation($record['date_of_committee_sec_confirmation_formatted']);

                    $coc->setExecChairmanApproval($record['executive_chairman_approval']);
                    $coc->setDateOfExecChairmanApproval($record['date_of_executive_chairman_approval_formatted']);

                    $coc->setDmeConfirmationStatus($record['dme_confirmation_status']);
                    $coc->setDateOfDmeConfirmation($record['date_of_dme_confirmation_formatted']);

                    $coc->setDurationInMonths($record['duration_in_months']);
                    $coc->setDurationInWeeks($record['duration_in_weeks']);
                    $coc->setExpectedDateOfExpiration($record['expected_date_of_expiration_formatted']);

                    $coc->setCreated($record['created_formatted']);
                    $coc->setSelector($record['selector']);

                    $coc->setCommitteeChairRecommended($coc->getCommitteeChairRecommendation() == AppConstants::RECOMMENDED);
                    $coc->setCommitteeSecConfirmed($coc->getCommitteeSecConfirmation() == AppConstants::CONFIRMED);
                    $coc->setExecChairmanApproved($coc->getExecChairmanApproval() == AppConstants::APPROVED);
                    $coc->setDmeConfirmed($coc->getDmeConfirmationStatus() == AppConstants::CONFIRMED);
                    $coc->setExpired($record['expired'] == 1);

                    $coc->setDisplaySerialNo($displaySerialNo);
                    $displaySerialNo++;

                    $searchResults[] = $coc;
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
     * @param $selector
     * @param FetchParam|null $fetchParam
     * @return COC|null
     * @throws AppException
     */
    public function getCoc($selector, FetchParam $fetchParam = null):?COC
    {
        $coc = null;
        $statement = null;

        try {
            $query = "SELECT d.id,d.recruitment_id , d.coc_request_type_id, d.selected_candidates_attachment_file_name
                ,date_format(d.date_of_selected_candidates_attachment,'%d %b, %Y') as date_of_selected_candidates_attachment_formatted
                , d.committee_chairman_recommendation
                ,date_format(d.date_of_committee_chairman_recommendation,'%d %b, %Y') as date_of_committee_chairman_recommendation_formatted
                ,d.committee_chairman_recommendation_remarks
                ,d.committee_sec_confirmation
                , date_format(d.date_of_committee_sec_confirmation,'%d %b, %Y') as date_of_committee_sec_confirmation_formatted
                ,d.committee_sec_confirmation_remarks
                ,d.executive_chairman_approval
                , date_format(d.date_of_executive_chairman_approval,'%d %b, %Y') as date_of_executive_chairman_approval_formatted
                , d.executive_chairman_approval_remarks
                , d.dme_confirmation
                , date_format(d.date_of_dme_confirmation,'%d %b, %Y') as date_of_dme_confirmation_formatted
                , d.dme_confirmation_remarks
                , d.duration_in_months, d.duration_in_weeks
                , date_format(d.expected_date_of_expiration,'%d %b, %Y') as expected_date_of_expiration_formatted 
                , d.last_appointed_candidates_attachment_file_name
                , date_format(d.date_of_last_appointed_candidates_attachment,'%d %b, %Y') as date_of_last_appointed_candidates_attachment_formatted
                , date_format(d.created,'%d %b, %Y') as created_formatted
                , (curdate() > d.expected_date_of_expiration) as expired, d.selector 
                , r.organization_id,r.recruitment_year, r.selector as recruitment_selector
                , c.description as recruitment_category_name
                , o.organization_name , o.guid as organization_selector
                , t.description as coc_request_type_name
                FROM recruitment_coc d 
                JOIN recruitment r on d.recruitment_id = r.id
                JOIN recruitment_category_types c on r.recruitment_category_id = c.id 
                JOIN organization o on r.organization_id = o.id 
                JOIN recruitment_coc_types t on d.coc_request_type_id = t.id
                WHERE d.selector=:selector";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':selector', $selector);
            $statement->execute();

            $record = $statement->fetch(PDO::FETCH_ASSOC);
            if ($record) {
                $coc = new COC();
                $coc->setId($record['id']);
                $coc->setRecruitmentId($record['recruitment_id']);
                $coc->setRecruitmentSelector($record['recruitment_selector']);
                $coc->setRecruitmentYear($record['recruitment_year']);
                $coc->setRecruitmentCategoryName($record['recruitment_category_name']);
                $coc->setOrganizationId($record['organization_id']);
                $coc->setOrganizationName($record['organization_name']);
                $coc->setOrganizationSelector($record['organization_selector']);
                $coc->setTypeOfCocId($record['coc_request_type_id']);
                $coc->setTypeOfCocName($record['coc_request_type_name']);

                $selectedCandidatesAttachment = new AttachedDocument(null, $record['selected_candidates_attachment_file_name']);
                $coc->setSelectedCandidatesAttachment($selectedCandidatesAttachment);

                $coc->setCommitteeChairRecommendation($record['committee_chairman_recommendation']);
                $coc->setDateOfCommitteeChairRecommendation($record['date_of_committee_chairman_recommendation_formatted']);
                $coc->setCommitteeChairRecommendationRemarks($record['committee_chairman_recommendation_remarks']);

                $coc->setCommitteeSecConfirmation($record['committee_sec_confirmation']);
                $coc->setDateOfCommitteeSecConfirmation($record['date_of_committee_sec_confirmation_formatted']);
                $coc->setCommitteeSecConfirmationRemarks($record['committee_sec_confirmation_remarks']);

                $coc->setExecChairmanApproval($record['executive_chairman_approval']);
                $coc->setDateOfExecChairmanApproval($record['date_of_executive_chairman_approval_formatted']);
                $coc->setExecChairmanApprovalRemarks($record['executive_chairman_approval_remarks']);

                $coc->setDmeConfirmationStatus($record['dme_confirmation_status']);
                $coc->setDateOfDmeConfirmation($record['date_of_dme_confirmation_formatted']);
                $coc->setDmeConfirmationRemarks($record['dme_confirmation_remarks']);

                $coc->setDurationInMonths($record['duration_in_months']);
                $coc->setDurationInWeeks($record['duration_in_weeks']);
                $coc->setExpectedDateOfExpiration($record['expected_date_of_expiration_formatted']);

                $appointedCandidatesAttachment = new AttachedDocument(null, $record['last_appointed_candidates_attachment_file_name']);
                $coc->setAppointedCandidatesAttachment($appointedCandidatesAttachment);

                $coc->setCreated($record['created_formatted']);
                $coc->setSelector($record['selector']);

                $coc->setCommitteeChairRecommended($coc->getCommitteeChairRecommendation() == AppConstants::RECOMMENDED);
                $coc->setCommitteeSecConfirmed($coc->getCommitteeSecConfirmation() == AppConstants::CONFIRMED);
                $coc->setExecChairmanApproved($coc->getExecChairmanApproval() == AppConstants::APPROVED);
                $coc->setDmeConfirmed($coc->getDmeConfirmationStatus() == AppConstants::CONFIRMED);
                $coc->setExpired($record['expired'] == 1);

                if($fetchParam){
                    if($fetchParam->isFetchCocComments() || $fetchParam->isFetchAll()){
                        $query = "select c.id as comment_id, c.from_user_id, c.comments, c.to_user_id
                                  ,date_format(c.created,'%d %b, %Y') as created_formatted, c.selector
                                  , concat_ws(' ', from_user.last_name, from_user.first_name) as from_user_name
                                  , concat_ws(' ', to_user.last_name, to_user.first_name) as to_user_name
                                  from recruitment_coc_processing_comments c
                                  join user_profile from_user on c.from_user_id = from_user.id
                                  left join user_profile to_user on c.to_user_id = to_user.id
                                  where c.recruitment_coc_id = :recruitment_coc_id";
                        $statement = $this->connection->prepare($query);
                        $statement->bindValue(':recruitment_coc_id', $coc->getId());
                        $statement->execute();

                        $comments = $statement->fetchAll();

                        $approvalComments = array();

                        if($comments){
                            foreach ($comments as $comment){
                                $approvalComment = new ApprovalComment();
                                $approvalComment->setId($comment['comment_id']);
                                $approvalComment->setFromUserId($comment['from_user_id']);
                                $approvalComment->setFromUserName($comment['from_user_name']);
                                $approvalComment->setComment($comment['comments']);
                                $approvalComment->setToUserId($comment['to_user_id']);
                                $approvalComment->setToUserName($comment['to_user_name']);
                                $approvalComment->setCreated($comment['created_formatted']);
                                $approvalComment->setSelector($comment['selector']);

                                $approvalComments[] = $approvalComment;
                            }
                        }

                        $coc->setApprovalComments($approvalComments);

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

        return $coc;
    }

    /**
     * @param COC $coc
     * @return bool
     * @throws AppException
     */
    public function addCOC(COC $coc): bool
    {
        $outcome = false;
        $statement = null;

        try {
            //now insert
            $query = "INSERT INTO recruitment_coc 
                (
                recruitment_id,coc_request_type_id,record_status
                ,created,created_by,last_mod,last_mod_by,selector
                ) 
                VALUES 
                (
                :recruitment_id,:coc_request_type_id,:record_status
                ,:created,:created_by,:last_mod,:last_mod_by,:selector
                )";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':recruitment_id', $this->getValueOrNull($coc->getRecruitmentId()));
            $statement->bindValue(':coc_request_type_id', $this->getValueOrNull($coc->getTypeOfCocId()));
            $statement->bindValue(':record_status', $this->getValueOrNull($coc->getRecordStatus()));
            $statement->bindValue(':created', $this->getValueOrNull($coc->getLastModified()));
            $statement->bindValue(':created_by', $this->getValueOrNull($coc->getLastModifiedByUserId()));
            $statement->bindValue(':last_mod', $this->getValueOrNull($coc->getLastModified()));
            $statement->bindValue(':last_mod_by', $this->getValueOrNull($coc->getLastModifiedByUserId()));
            $statement->bindValue(':selector', $this->getValueOrNull($coc->getSelector()));
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
     * @param COC $coc
     * @return bool
     * @throws AppException
     */
    public function deleteCoc(COC $coc): bool
    {
        $outcome = false;
        $statement = null;

        try {

            //now delete
            $query = "UPDATE recruitment_coc 
                SET 
                record_status=:record_status
                ,last_mod=:last_mod
                ,last_mod_by=:last_mod_by
                WHERE selector=:selector";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':record_status', $coc->getRecordStatus());
            $statement->bindValue(':last_mod', $coc->getLastModified());
            $statement->bindValue(':last_mod_by', $coc->getLastModifiedByUserId());
            $statement->bindValue(':selector', $coc->getSelector());
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

    //***************************** APPROVALS ***************************************

    /**
     * @param COC $coc
     * @return bool
     * @throws AppException
     */
    public function committeeChairmanRecommendation(COC $coc): bool
    {
        $outcome = false;
        $statement = null;

        try {

            $query = "UPDATE recruitment_coc 
                SET 
                committee_chairman_recommendation=:committee_chairman_recommendation
                ,date_of_committee_chairman_recommendation=:date_of_committee_chairman_recommendation
                ,committee_chairman_recommendation_by=:committee_chairman_recommendation_by
                ,committee_chairman_recommendation_remarks=:committee_chairman_recommendation_remarks
                ,last_mod=:last_mod
                ,last_mod_by=:last_mod_by
                WHERE selector=:selector";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':committee_chairman_recommendation', $coc->getCommitteeChairRecommendation());
            $statement->bindValue(':date_of_committee_chairman_recommendation', $coc->getLastModified());
            $statement->bindValue(':committee_chairman_recommendation_by', $coc->getLastModifiedByUserId());
            $statement->bindValue(':committee_chairman_recommendation_remarks', $coc->getCommitteeChairRecommendationRemarks());
            $statement->bindValue(':last_mod', $coc->getLastModified());
            $statement->bindValue(':last_mod_by', $coc->getLastModifiedByUserId());
            $statement->bindValue(':selector', $coc->getSelector());
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
     * @param COC $coc
     * @return bool
     * @throws AppException
     */
    public function committeeSecretaryConfirmation(COC $coc): bool
    {
        $outcome = false;
        $statement = null;

        try {

            $query = "UPDATE recruitment_coc 
                SET 
                committee_sec_confirmation=:committee_sec_confirmation
                ,date_of_committee_sec_confirmation=:date_of_committee_sec_confirmation
                ,committee_sec_confirmation_by=:committee_sec_confirmation_by
                ,committee_sec_confirmation_remarks=:committee_sec_confirmation_remarks
                ,last_mod=:last_mod
                ,last_mod_by=:last_mod_by
                WHERE selector=:selector";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':committee_sec_confirmation', $coc->getCommitteeSecConfirmation());
            $statement->bindValue(':date_of_committee_sec_confirmation', $coc->getLastModified());
            $statement->bindValue(':committee_sec_confirmation_by', $coc->getLastModifiedByUserId());
            $statement->bindValue(':committee_sec_confirmation_remarks', $coc->getCommitteeSecConfirmationRemarks());
            $statement->bindValue(':last_mod', $coc->getLastModified());
            $statement->bindValue(':last_mod_by', $coc->getLastModifiedByUserId());
            $statement->bindValue(':selector', $coc->getSelector());
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
     * @param COC $coc
     * @return bool
     * @throws AppException
     */
    public function executiveChairmanApproval(COC $coc): bool
    {
        $outcome = false;
        $statement = null;

        try {

            $query = "UPDATE recruitment_coc 
                SET 
                executive_chairman_approval=:executive_chairman_approval
                ,date_of_executive_chairman_approval=:date_of_executive_chairman_approval
                ,executive_chairman_approval_by=:executive_chairman_approval_by
                ,executive_chairman_approval_remarks=:executive_chairman_approval_remarks
                ,last_mod=:last_mod
                ,last_mod_by=:last_mod_by
                WHERE selector=:selector";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':executive_chairman_approval', $coc->getExecChairmanApproval());
            $statement->bindValue(':date_of_executive_chairman_approval', $coc->getLastModified());
            $statement->bindValue(':executive_chairman_approval_by', $coc->getLastModifiedByUserId());
            $statement->bindValue(':executive_chairman_approval_remarks', $coc->getExecChairmanApprovalRemarks());
            $statement->bindValue(':last_mod', $coc->getLastModified());
            $statement->bindValue(':last_mod_by', $coc->getLastModifiedByUserId());
            $statement->bindValue(':selector', $coc->getSelector());
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
     * @param COC $coc
     * @return bool
     * @throws AppException
     */
    public function dmeConfirmation(COC $coc): bool
    {
        $outcome = false;
        $statement = null;

        try {

            $query = "UPDATE recruitment_coc 
                SET 
                dme_confirmation=:dme_confirmation
                ,date_of_dme_confirmation=:date_of_dme_confirmation
                ,dme_confirmation_by=:dme_confirmation_by
                ,dme_confirmation_remarks=:dme_confirmation_remarks
                ,last_mod=:last_mod
                ,last_mod_by=:last_mod_by
                WHERE selector=:selector";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':dme_confirmation', $coc->getDmeConfirmationStatus());
            $statement->bindValue(':date_of_dme_confirmation', $coc->getLastModified());
            $statement->bindValue(':dme_confirmation_by', $coc->getLastModifiedByUserId());
            $statement->bindValue(':dme_confirmation_remarks', $coc->getDmeConfirmationRemarks());
            $statement->bindValue(':last_mod', $coc->getLastModified());
            $statement->bindValue(':last_mod_by', $coc->getLastModifiedByUserId());
            $statement->bindValue(':selector', $coc->getSelector());
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

    //******************** COMMENTS ************************

    /**
     * @param ApprovalComment $comment
     * @return bool
     * @throws AppException
     */
    public function addCocComment(ApprovalComment $comment): bool
    {
        $outcome = false;
        $statement = null;

        try {
            //now insert
            $query = "INSERT INTO recruitment_coc_processing_comments 
                (
                recruitment_coc_id,from_user_id, comments, to_user_id,record_status
                ,created,created_by,last_mod,last_mod_by,selector
                ) 
                VALUES 
                (
                :recruitment_coc_id,:from_user_id, :comments , :to_user_id,:record_status
                ,:created,:created_by,:last_mod,:last_mod_by,:selector
                )";

            $statement = $this->connection->prepare($query);
            $statement->bindValue(':recruitment_coc_id', $this->getValueOrNull($comment->getContextId()));
            $statement->bindValue(':from_user_id', $this->getValueOrNull($comment->getFromUserId()));
            $statement->bindValue(':comments', $this->getValueOrNull($comment->getComment()));
            $statement->bindValue(':to_user_id', $this->getValueOrNull($comment->getToUserId()));
            $statement->bindValue(':record_status', $this->getValueOrNull($comment->getRecordStatus()));
            $statement->bindValue(':created', $this->getValueOrNull($comment->getLastModified()));
            $statement->bindValue(':created_by', $this->getValueOrNull($comment->getLastModifiedByUserId()));
            $statement->bindValue(':last_mod', $this->getValueOrNull($comment->getLastModified()));
            $statement->bindValue(':last_mod_by', $this->getValueOrNull($comment->getLastModifiedByUserId()));
            $statement->bindValue(':selector', $this->getValueOrNull($comment->getSelector()));
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

}