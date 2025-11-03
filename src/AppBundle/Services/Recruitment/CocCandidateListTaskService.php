<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/1/2017
 * Time: 4:28 PM
 */

namespace AppBundle\Services\Recruitment;


use AppBundle\AppException\AppException;
use AppBundle\Model\Recruitment\CocCandidateList;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\ServiceHelper;
use Doctrine\DBAL\Connection;
use \Throwable;

class CocCandidateListTaskService extends ServiceHelper
{
    private $connection;
    private $rows_per_page;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->rows_per_page = AppConstants::ROWS_PER_PAGE;
    }

    /**
     * @param $mysqlLoadCSVPath
     * @param CocCandidateList $cocCandidateList
     * @return int
     * @throws AppException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function importFromCSV($mysqlLoadCSVPath, CocCandidateList $cocCandidateList): int
    {
        $affectedRows = 0;
        $statement = null;

        try {

            //start transaction
            $this->connection->beginTransaction();

            //now load csv into database
            $query = "LOAD DATA INFILE '$mysqlLoadCSVPath'
                    INTO TABLE recruitment_coc_candidates_list_entries_staging 
                    FIELDS TERMINATED BY ',' 
                    OPTIONALLY ENCLOSED BY '\"' 
                    LINES TERMINATED BY '\n' 
                    (recruitment_coc_candidates_list_id, serial_no, surname, first_name, other_names, date_of_birth
                    , address, center_state, phone_number, email_address, location, gender, post_applied
                    , university_of_study, course_of_study, state_of_origin, class_of_degree
                    , created, created_by, last_mod, last_mod_by, selector)";

            $totalCsvRowsInserted = $this->connection->exec($query);

            //re-count total affected dont rely on load data
            $query = "SELECT count(*) FROM recruitment_coc_candidates_list_entries_staging WHERE recruitment_coc_candidates_list_id=:recruitment_coc_candidates_list_id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':recruitment_coc_candidates_list_id', $cocCandidateList->getId());
            $statement->execute();

            $affectedRows = $statement->fetchColumn(0);

            //update the total rows column in the table
            $query = "UPDATE recruitment_coc_candidates_list SET total_candidates=:total_candidates "
                . " WHERE id=:id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':total_candidates', $affectedRows);
            $statement->bindValue(':id', $cocCandidateList->getId());
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

    /**
     * @param $candidates
     * @param CocCandidateList $cocCandidateList
     * @return int
     * @throws AppException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function importMultiInsert($candidates, CocCandidateList $cocCandidateList): int
    {
        $affectedRows = 0;
        $statement = null;

        try {

            //start transaction
            $this->connection->beginTransaction();

            /*$query = "delete FROM recruitment_long_list_candidates WHERE recruitment_id=:recruitment_id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':recruitment_id', $recruitment->getId());
            $statement->execute();*/


            //PREPARE THE DETAILED BATCH DATA
            $batchCandidateData = array();
            foreach ($candidates as $candidate) {
                $candidateDataRecord = array();

                $candidateDataRecord['recruitment_coc_candidates_list_id'] = $this->getValueOrNull($candidate[0]);

                $candidateDataRecord['serial_no'] = $this->getValueOrNull($candidate[1]);
                $candidateDataRecord['surname'] = $this->getValueOrNull($candidate[2]);
                $candidateDataRecord['first_name'] = $this->getValueOrNull($candidate[3]);
                $candidateDataRecord['other_names'] = $this->getValueOrNull($candidate[4]);
                $candidateDataRecord['date_of_birth'] = $this->getValueOrNull($candidate[5]);
                $candidateDataRecord['state_of_origin'] = $this->getValueOrNull($candidate[6]);
                $candidateDataRecord['lga'] = $this->getValueOrNull($candidate[7]);
                $candidateDataRecord['address'] = $this->getValueOrNull($candidate[8]);
                $candidateDataRecord['center'] = $this->getValueOrNull($candidate[9]);
                $candidateDataRecord['phone_number'] = $this->getValueOrNull($candidate[10]);
                $candidateDataRecord['email_address'] = $this->getValueOrNull($candidate[11]);
                $candidateDataRecord['gender'] = $this->getValueOrNull($candidate[12]);
                $candidateDataRecord['post_applied'] = $this->getValueOrNull($candidate[13]);
                $candidateDataRecord['university_of_study'] = $this->getValueOrNull($candidate[14]);
                $candidateDataRecord['course_of_study'] = $this->getValueOrNull($candidate[15]);
                $candidateDataRecord['class_of_degree'] = $this->getValueOrNull($candidate[16]);
                $candidateDataRecord['created'] = $this->getValueOrNull($candidate[17]);
                $candidateDataRecord['created_by'] = $this->getValueOrNull($candidate[18]);
                $candidateDataRecord['last_mod'] = $this->getValueOrNull($candidate[19]);
                $candidateDataRecord['last_mod_by'] = $this->getValueOrNull($candidate[20]);

                $batchCandidateData[] = $candidateDataRecord;
            }

            $outcome = $this->pdoMultiInsertReturnTotal('recruitment_coc_candidates_list_entries_staging', $batchCandidateData);

            $query = "SELECT count(*) FROM recruitment_coc_candidates_list_entries_staging WHERE recruitment_coc_candidates_list_id=:recruitment_coc_candidates_list_id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':recruitment_coc_candidates_list_id', $cocCandidateList->getId());
            $statement->execute();

            $affectedRows = $statement->fetchColumn(0);


            $query = "UPDATE 
                    recruitment_coc_candidates_list 
                    SET total_candidates=:total_candidates 
                    WHERE id =:id";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':total_candidates', $affectedRows);
            $statement->bindValue(':id', $cocCandidateList->getId());
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

    /**
     * @param $tableName
     * @param $data
     * @return bool|int
     * @throws AppException
     */
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