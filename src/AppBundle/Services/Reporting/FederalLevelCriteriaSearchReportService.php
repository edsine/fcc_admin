<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:43 PM
 */

namespace AppBundle\Services\Reporting;


use AppBundle\AppException\AppException;
use AppBundle\Model\SearchCriteria\NominalRoleSearchCriteria;
use AppBundle\Model\Submission\NominalRoll;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\Paginator;
use Doctrine\DBAL\Connection;
use \Throwable;
use \PDO;

class FederalLevelCriteriaSearchReportService
{
    private $connection;
    private $rows_per_page;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->rows_per_page = AppConstants::ROWS_PER_PAGE;
    }

    public function searchApprovedFedLevelNominalRoll(NominalRoleSearchCriteria $searchCriteria, Paginator $paginator, $pageDirection, $limitResult = true): array
    {
        $nominalRoles = array();
        $statement = null;

        try {

            $searchOrganization = $searchCriteria->getOrganization();

            $searchEmployeeNumber = $searchCriteria->getEmployeeNumber();
            $searchName = $searchCriteria->getName();

            $searchNationality = $searchCriteria->getNationality();
            $searchStateOfOrigin = $searchCriteria->getStateOfOrigin();
            $searchSubmissionYear = $searchCriteria->getSubmissionYear();
            //$searchDateOfEmployment = $searchCriteria->getDateOfEmployment();

            $searchGradeLevel = $searchCriteria->getGradeLevel();
            $searchDesignation = $searchCriteria->getDesignation();
            //$searchStateOfLocation = $searchCriteria->getStateOfLocation();
            $searchGender = $searchCriteria->getGender();

            $searchMaritalStatus = $searchCriteria->getMaritalStatus();
            //$searchLga = $searchCriteria->getLga();
            $searchGeoPoliticalZone = $searchCriteria->getGeoPoliticalZone();

            $where = array();

            if ($searchSubmissionYear) {
                $where[] = "d.submission_year = :submission_year";
            }
            if ($searchOrganization) {
                $where[] = "mda_submission.organization_id = :organization_id";
            }
            if ($searchEmployeeNumber) {
                $where[] = "d.employee_number = :employee_number";
            }
            if ($searchName) {
                $where[] = "d.employee_name LIKE :employee_name";
            }
            if ($searchNationality) {
                $where[] = "d.nationality_code = :nationality_code";
            }
            if ($searchStateOfOrigin) {
                $where[] = "d.state_of_origin_code = :state_of_origin_code";
            }
            if ($searchGradeLevel) {
                $where[] = "d.grade_level = :grade_level";
            }
            if ($searchDesignation) {
                $where[] = "d.designation = :designation";
            }
            if ($searchGender) {
                $where[] = "d.gender = :gender";
            }
            if ($searchMaritalStatus) {
                $where[] = "d.marital_status = :marital_status";
            }
            if ($searchGeoPoliticalZone) {
                $where[] = "d.region_code = :region_code";
            }

            if ($where) {
                $where = implode(" AND ", $where);
            } else {
                $where = 'd.id=0';
            }

            $limitStartRow = 0;
            if($limitResult){

                //fetch total matching rows
                $countQuery = "SELECT COUNT(d.id) AS totalRows 
                    FROM confirmed_federal_level_nominal_roll_submission d 
                    LEFT JOIN federal_level_nominal_roll_submissions mda_submission on d.submission_id = mda_submission.submission_id 
                    LEFT JOIN organization as organization on mda_submission.organization_id = organization.id 
                    WHERE $where ";
                $statement = $this->connection->prepare($countQuery);

                if ($searchSubmissionYear) {
                    $statement->bindValue(':submission_year', $searchSubmissionYear);
                }
                if ($searchOrganization) {
                    $statement->bindValue(':organization_id', $searchOrganization);
                }
                if ($searchEmployeeNumber) {
                    $statement->bindValue(':employee_number', $searchEmployeeNumber);
                }
                if ($searchName) {
                    $statement->bindValue(':employee_name', "%" . $searchName . "%");
                }
                if ($searchNationality) {
                    $statement->bindValue(':nationality_code', $searchNationality);
                }
                if ($searchStateOfOrigin) {
                    $statement->bindValue(':state_of_origin_code', $searchStateOfOrigin);
                }
                if ($searchGradeLevel) {
                    $statement->bindValue(':grade_level', $searchGradeLevel);
                }
                if ($searchDesignation) {
                    $statement->bindValue(':designation', $searchDesignation);
                }
                if ($searchGender) {
                    $statement->bindValue(':gender', $searchGender);
                }
                if ($searchMaritalStatus) {
                    $statement->bindValue(':marital_status', $searchMaritalStatus);
                }

                if ($searchGeoPoliticalZone) {
                    $statement->bindValue(':region_code', $searchGeoPoliticalZone);
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
            }

            //now exec SELECT with limit clause
            $query = "SELECT d.id,d.submission_id,d.submission_year,d.serial_number,d.employee_number 
                ,d.employee_name,d.nationality_code,d.state_of_origin_code,d.date_of_birth,d.date_of_employment 
                ,d.grade_level,d.designation,d.state_of_deployment_code,d.gender,d.marital_status,d.lga_code,d.region_code 
                ,organization.organization_name 
                FROM confirmed_federal_level_nominal_roll_submission d 
                LEFT JOIN federal_level_nominal_roll_submissions mda_submission on d.submission_id = mda_submission.submission_id 
                LEFT JOIN organization as organization on mda_submission.organization_id = organization.id 
                WHERE $where ";

            if($limitResult){
                $query = $query . "ORDER BY d.id LIMIT $limitStartRow ,$this->rows_per_page ";
            }

            $statement = $this->connection->prepare($query);

            if ($searchSubmissionYear) {
                $statement->bindValue(':submission_year', $searchSubmissionYear);
            }
            if ($searchOrganization) {
                $statement->bindValue(':organization_id', $searchOrganization);
            }
            if ($searchEmployeeNumber) {
                $statement->bindValue(':employee_number', $searchEmployeeNumber);
            }
            if ($searchName) {
                $statement->bindValue(':employee_name', "%" . $searchName . "%");
            }
            if ($searchNationality) {
                $statement->bindValue(':nationality_code', $searchNationality);
            }
            if ($searchStateOfOrigin) {
                $statement->bindValue(':state_of_origin_code', $searchStateOfOrigin);
            }
            if ($searchGradeLevel) {
                $statement->bindValue(':grade_level', $searchGradeLevel);
            }
            if ($searchDesignation) {
                $statement->bindValue(':designation', $searchDesignation);
            }
            if ($searchGender) {
                $statement->bindValue(':gender', $searchGender);
            }
            if ($searchMaritalStatus) {
                $statement->bindValue(':marital_status', $searchMaritalStatus);
            }
            if ($searchGeoPoliticalZone) {
                $statement->bindValue(':region_code', $searchGeoPoliticalZone);
            }

            $statement->execute();

            $records = $statement->fetchAll(PDO::FETCH_ASSOC);
            if ($records) {
                foreach ($records as $record) {
                    $nominalRole = new NominalRoll();
                    $nominalRole->setId($record['id']);
                    $nominalRole->setSubmissionId($record['submission_id']);
                    $nominalRole->setSubmissionYear($record['submission_year']);
                    $nominalRole->setOrganizationName($record['organization_name']);

                    $nominalRole->setSerialNo($record['serial_number']);
                    $nominalRole->setEmployeeNumber($record['employee_number']);
                    $nominalRole->setName($record['employee_name']);
                    $nominalRole->setNationality($record['nationality_code']);
                    $nominalRole->setStateOfOrigin($record['state_of_origin_code']);
                    $nominalRole->setDateOfBirth($record['date_of_birth']);
                    $nominalRole->setDateOfEmployment($record['date_of_employment']);
                    $nominalRole->setGradeLevel($record['grade_level']);
                    $nominalRole->setDesignation($record['designation']);
                    $nominalRole->setStateOfLocation($record['state_of_deployment_code']);
                    $nominalRole->setGender($record['gender']);
                    $nominalRole->setMaritalStatus($record['marital_status']);
                    $nominalRole->setLga($record['lga_code']);
                    $nominalRole->setGeoPoliticalZone($record['region_code']);

                    $nominalRoles[] = $nominalRole;
                }
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $nominalRoles;
    }
}