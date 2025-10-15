<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/1/2018
 * Time: 12:57 PM
 */

namespace AppBundle\Services\Formula;


use AppBundle\AppException\AppException;
use AppBundle\Model\Formula\LGAFormula;
use AppBundle\Utils\ServiceHelper;
use Doctrine\DBAL\Connection;
use \Throwable;

class FormulaGenerationService extends ServiceHelper
{

    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function generateLGAFormula()
    {
        $lgaFormulas = array();
        $statement = null;

        try {
            //*************************************** 0, 60
            $query = "SELECT state.state_name,lga.lga_name, lga.plate_code
                      FROM lga 
                      JOIN states AS state ON lga.state_id = state.id
                      WHERE state.state_code<> 'NON' ORDER BY lga.id LIMIT 0, 60";
            $statement = $this->connection->prepare($query);
            $statement->execute();

            $lgaDetails = $statement->fetchAll();

            if ($lgaDetails) {
                $lgaFormula = new LGAFormula();

                $excelFormula = '';
                $closingString = '';
                for ($i = 0; $i < count($lgaDetails); $i++) {
                    $stateName = $lgaDetails[$i]['state_name'];
                    $lgaName = $lgaDetails[$i]['lga_name'];
                    $lgaPlateCode = $lgaDetails[$i]['plate_code'];
                    if ($i == 0) {
                        $excelFormula .= "=IF(AND(S1=\"$stateName\",L1=\"$lgaName\"),\"$lgaPlateCode\"";
                    } else {
                        $excelFormula .= ", IF(AND(S1=\"$stateName\",L1=\"$lgaName\"),\"$lgaPlateCode\"";
                    }

                    $closingString .= ')';
                }

                $excelFormula .= $closingString;
                $lgaFormula->setFormula($excelFormula);

                $lgaFormulas[] = $lgaFormula;
            }

            //*************************************** 60, 60
            $query = "SELECT state.state_name,lga.lga_name, lga.plate_code
                      FROM lga 
                      JOIN states AS state ON lga.state_id = state.id
                      WHERE state.state_code<> 'NON' ORDER BY lga.id LIMIT 60, 60";
            $statement = $this->connection->prepare($query);
            $statement->execute();

            $lgaDetails = $statement->fetchAll();

            if ($lgaDetails) {
                $lgaFormula = new LGAFormula();

                $excelFormula = '';
                $closingString = '';
                for ($i = 0; $i < count($lgaDetails); $i++) {
                    $stateName = $lgaDetails[$i]['state_name'];
                    $lgaName = $lgaDetails[$i]['lga_name'];
                    $lgaPlateCode = $lgaDetails[$i]['plate_code'];
                    if ($i == 0) {
                        $excelFormula .= "=IF(AND(S1=\"$stateName\",L1=\"$lgaName\"),\"$lgaPlateCode\"";
                    } else {
                        $excelFormula .= ", IF(AND(S1=\"$stateName\",L1=\"$lgaName\"),\"$lgaPlateCode\"";
                    }

                    $closingString .= ')';
                }

                $excelFormula .= $closingString;
                $lgaFormula->setFormula($excelFormula);

                $lgaFormulas[] = $lgaFormula;
            }

            //*************************************** 120, 60
            $query = "SELECT state.state_name,lga.lga_name, lga.plate_code
                      FROM lga 
                      JOIN states AS state ON lga.state_id = state.id
                      WHERE state.state_code<> 'NON' ORDER BY lga.id LIMIT 120, 60";
            $statement = $this->connection->prepare($query);
            $statement->execute();

            $lgaDetails = $statement->fetchAll();

            if ($lgaDetails) {
                $lgaFormula = new LGAFormula();

                $excelFormula = '';
                $closingString = '';
                for ($i = 0; $i < count($lgaDetails); $i++) {
                    $stateName = $lgaDetails[$i]['state_name'];
                    $lgaName = $lgaDetails[$i]['lga_name'];
                    $lgaPlateCode = $lgaDetails[$i]['plate_code'];
                    if ($i == 0) {
                        $excelFormula .= "=IF(AND(S1=\"$stateName\",L1=\"$lgaName\"),\"$lgaPlateCode\"";
                    } else {
                        $excelFormula .= ", IF(AND(S1=\"$stateName\",L1=\"$lgaName\"),\"$lgaPlateCode\"";
                    }

                    $closingString .= ')';
                }

                $excelFormula .= $closingString;
                $lgaFormula->setFormula($excelFormula);

                $lgaFormulas[] = $lgaFormula;
            }

            //*************************************** 180, 60
            $query = "SELECT state.state_name,lga.lga_name, lga.plate_code
                      FROM lga 
                      JOIN states AS state ON lga.state_id = state.id
                      WHERE state.state_code<> 'NON' ORDER BY lga.id LIMIT 180, 60";
            $statement = $this->connection->prepare($query);
            $statement->execute();

            $lgaDetails = $statement->fetchAll();

            if ($lgaDetails) {
                $lgaFormula = new LGAFormula();

                $excelFormula = '';
                $closingString = '';
                for ($i = 0; $i < count($lgaDetails); $i++) {
                    $stateName = $lgaDetails[$i]['state_name'];
                    $lgaName = $lgaDetails[$i]['lga_name'];
                    $lgaPlateCode = $lgaDetails[$i]['plate_code'];
                    if ($i == 0) {
                        $excelFormula .= "=IF(AND(S1=\"$stateName\",L1=\"$lgaName\"),\"$lgaPlateCode\"";
                    } else {
                        $excelFormula .= ", IF(AND(S1=\"$stateName\",L1=\"$lgaName\"),\"$lgaPlateCode\"";
                    }

                    $closingString .= ')';
                }

                $excelFormula .= $closingString;
                $lgaFormula->setFormula($excelFormula);

                $lgaFormulas[] = $lgaFormula;
            }

            //*************************************** 240, 60
            $query = "SELECT state.state_name,lga.lga_name, lga.plate_code
                      FROM lga 
                      JOIN states AS state ON lga.state_id = state.id
                      WHERE state.state_code<> 'NON' ORDER BY lga.id LIMIT 240, 60";
            $statement = $this->connection->prepare($query);
            $statement->execute();

            $lgaDetails = $statement->fetchAll();

            if ($lgaDetails) {
                $lgaFormula = new LGAFormula();

                $excelFormula = '';
                $closingString = '';
                for ($i = 0; $i < count($lgaDetails); $i++) {
                    $stateName = $lgaDetails[$i]['state_name'];
                    $lgaName = $lgaDetails[$i]['lga_name'];
                    $lgaPlateCode = $lgaDetails[$i]['plate_code'];
                    if ($i == 0) {
                        $excelFormula .= "=IF(AND(S1=\"$stateName\",L1=\"$lgaName\"),\"$lgaPlateCode\"";
                    } else {
                        $excelFormula .= ", IF(AND(S1=\"$stateName\",L1=\"$lgaName\"),\"$lgaPlateCode\"";
                    }

                    $closingString .= ')';
                }

                $excelFormula .= $closingString;
                $lgaFormula->setFormula($excelFormula);

                $lgaFormulas[] = $lgaFormula;
            }

            //*************************************** 300, 60
            $query = "SELECT state.state_name,lga.lga_name, lga.plate_code
                      FROM lga 
                      JOIN states AS state ON lga.state_id = state.id
                      WHERE state.state_code<> 'NON' ORDER BY lga.id LIMIT 300, 60";
            $statement = $this->connection->prepare($query);
            $statement->execute();

            $lgaDetails = $statement->fetchAll();

            if ($lgaDetails) {
                $lgaFormula = new LGAFormula();

                $excelFormula = '';
                $closingString = '';
                for ($i = 0; $i < count($lgaDetails); $i++) {
                    $stateName = $lgaDetails[$i]['state_name'];
                    $lgaName = $lgaDetails[$i]['lga_name'];
                    $lgaPlateCode = $lgaDetails[$i]['plate_code'];
                    if ($i == 0) {
                        $excelFormula .= "=IF(AND(S1=\"$stateName\",L1=\"$lgaName\"),\"$lgaPlateCode\"";
                    } else {
                        $excelFormula .= ", IF(AND(S1=\"$stateName\",L1=\"$lgaName\"),\"$lgaPlateCode\"";
                    }

                    $closingString .= ')';
                }

                $excelFormula .= $closingString;
                $lgaFormula->setFormula($excelFormula);

                $lgaFormulas[] = $lgaFormula;
            }

            //*************************************** 360, 60
            $query = "SELECT state.state_name, state.state_code ,lga.lga_name, lga.plate_code
                      FROM lga 
                      JOIN states AS state ON lga.state_id = state.id
                      WHERE state.state_code<> 'NON' ORDER BY lga.id LIMIT 360, 60";
            $statement = $this->connection->prepare($query);
            $statement->execute();

            $lgaDetails = $statement->fetchAll();

            if ($lgaDetails) {
                $lgaFormula = new LGAFormula();

                $excelFormula = '';
                $closingString = '';
                for ($i = 0; $i < count($lgaDetails); $i++) {
                    $stateName = $lgaDetails[$i]['state_code'];
                    $lgaName = $lgaDetails[$i]['lga_name'];
                    $lgaPlateCode = $lgaDetails[$i]['plate_code'];
                    if ($i == 0) {
                        $excelFormula .= "=IF(AND(S1=\"$stateName\",L1=\"$lgaName\"),\"$lgaPlateCode\"";
                    } else {
                        $excelFormula .= ", IF(AND(S1=\"$stateName\",L1=\"$lgaName\"),\"$lgaPlateCode\"";
                    }

                    $closingString .= ')';
                }

                $excelFormula .= $closingString;
                $lgaFormula->setFormula($excelFormula);

                $lgaFormulas[] = $lgaFormula;
            }


            //*************************************** 420, 60
            $query = "SELECT state.state_name, state.state_code ,lga.lga_name, lga.plate_code
                      FROM lga 
                      JOIN states AS state ON lga.state_id = state.id
                      WHERE state.state_code<> 'NON' ORDER BY lga.id LIMIT 420, 60";
            $statement = $this->connection->prepare($query);
            $statement->execute();

            $lgaDetails = $statement->fetchAll();

            if ($lgaDetails) {
                $lgaFormula = new LGAFormula();

                $excelFormula = '';
                $closingString = '';
                for ($i = 0; $i < count($lgaDetails); $i++) {
                    $stateName = $lgaDetails[$i]['state_code'];
                    $lgaName = $lgaDetails[$i]['lga_name'];
                    $lgaPlateCode = $lgaDetails[$i]['plate_code'];
                    if ($i == 0) {
                        $excelFormula .= "=IF(AND(S1=\"$stateName\",L1=\"$lgaName\"),\"$lgaPlateCode\"";
                    } else {
                        $excelFormula .= ", IF(AND(S1=\"$stateName\",L1=\"$lgaName\"),\"$lgaPlateCode\"";
                    }

                    $closingString .= ')';
                }

                $excelFormula .= $closingString;
                $lgaFormula->setFormula($excelFormula);

                $lgaFormulas[] = $lgaFormula;
            }

            //*************************************** 480, 60
            $query = "SELECT state.state_name, state.state_code ,lga.lga_name, lga.plate_code
                      FROM lga 
                      JOIN states AS state ON lga.state_id = state.id
                      WHERE state.state_code<> 'NON' ORDER BY lga.id LIMIT 480, 60";
            $statement = $this->connection->prepare($query);
            $statement->execute();

            $lgaDetails = $statement->fetchAll();

            if ($lgaDetails) {
                $lgaFormula = new LGAFormula();

                $excelFormula = '';
                $closingString = '';
                for ($i = 0; $i < count($lgaDetails); $i++) {
                    $stateName = $lgaDetails[$i]['state_code'];
                    $lgaName = $lgaDetails[$i]['lga_name'];
                    $lgaPlateCode = $lgaDetails[$i]['plate_code'];
                    if ($i == 0) {
                        $excelFormula .= "=IF(AND(S1=\"$stateName\",L1=\"$lgaName\"),\"$lgaPlateCode\"";
                    } else {
                        $excelFormula .= ", IF(AND(S1=\"$stateName\",L1=\"$lgaName\"),\"$lgaPlateCode\"";
                    }

                    $closingString .= ')';
                }

                $excelFormula .= $closingString;
                $lgaFormula->setFormula($excelFormula);

                $lgaFormulas[] = $lgaFormula;
            }

            //*************************************** 540, 60
            $query = "SELECT state.state_name, state.state_code ,lga.lga_name, lga.plate_code
                      FROM lga 
                      JOIN states AS state ON lga.state_id = state.id
                      WHERE state.state_code<> 'NON' ORDER BY lga.id LIMIT 540, 60";
            $statement = $this->connection->prepare($query);
            $statement->execute();

            $lgaDetails = $statement->fetchAll();

            if ($lgaDetails) {
                $lgaFormula = new LGAFormula();

                $excelFormula = '';
                $closingString = '';
                for ($i = 0; $i < count($lgaDetails); $i++) {
                    $stateName = $lgaDetails[$i]['state_code'];
                    $lgaName = $lgaDetails[$i]['lga_name'];
                    $lgaPlateCode = $lgaDetails[$i]['plate_code'];
                    if ($i == 0) {
                        $excelFormula .= "=IF(AND(S1=\"$stateName\",L1=\"$lgaName\"),\"$lgaPlateCode\"";
                    } else {
                        $excelFormula .= ", IF(AND(S1=\"$stateName\",L1=\"$lgaName\"),\"$lgaPlateCode\"";
                    }

                    $closingString .= ')';
                }

                $excelFormula .= $closingString;
                $lgaFormula->setFormula($excelFormula);

                $lgaFormulas[] = $lgaFormula;
            }

            //*************************************** 600, 60
            $query = "SELECT state.state_name, state.state_code ,lga.lga_name, lga.plate_code
                      FROM lga 
                      JOIN states AS state ON lga.state_id = state.id
                      WHERE state.state_code<> 'NON' ORDER BY lga.id LIMIT 600, 60";
            $statement = $this->connection->prepare($query);
            $statement->execute();

            $lgaDetails = $statement->fetchAll();

            if ($lgaDetails) {
                $lgaFormula = new LGAFormula();

                $excelFormula = '';
                $closingString = '';
                for ($i = 0; $i < count($lgaDetails); $i++) {
                    $stateName = $lgaDetails[$i]['state_code'];
                    $lgaName = $lgaDetails[$i]['lga_name'];
                    $lgaPlateCode = $lgaDetails[$i]['plate_code'];
                    if ($i == 0) {
                        $excelFormula .= "=IF(AND(S1=\"$stateName\",L1=\"$lgaName\"),\"$lgaPlateCode\"";
                    } else {
                        $excelFormula .= ", IF(AND(S1=\"$stateName\",L1=\"$lgaName\"),\"$lgaPlateCode\"";
                    }

                    $closingString .= ')';
                }

                $excelFormula .= $closingString;
                $lgaFormula->setFormula($excelFormula);

                $lgaFormulas[] = $lgaFormula;
            }

            //*************************************** 660, 60
            $query = "SELECT state.state_name, state.state_code ,lga.lga_name, lga.plate_code
                      FROM lga 
                      JOIN states AS state ON lga.state_id = state.id
                      WHERE state.state_code<> 'NON' ORDER BY lga.id LIMIT 660, 60";
            $statement = $this->connection->prepare($query);
            $statement->execute();

            $lgaDetails = $statement->fetchAll();

            if ($lgaDetails) {
                $lgaFormula = new LGAFormula();

                $excelFormula = '';
                $closingString = '';
                for ($i = 0; $i < count($lgaDetails); $i++) {
                    $stateName = $lgaDetails[$i]['state_code'];
                    $lgaName = $lgaDetails[$i]['lga_name'];
                    $lgaPlateCode = $lgaDetails[$i]['plate_code'];
                    if ($i == 0) {
                        $excelFormula .= "=IF(AND(S1=\"$stateName\",L1=\"$lgaName\"),\"$lgaPlateCode\"";
                    } else {
                        $excelFormula .= ", IF(AND(S1=\"$stateName\",L1=\"$lgaName\"),\"$lgaPlateCode\"";
                    }

                    $closingString .= ')';
                }

                $excelFormula .= $closingString;
                $lgaFormula->setFormula($excelFormula);

                $lgaFormulas[] = $lgaFormula;
            }

            //*************************************** 720, 60
            $query = "SELECT state.state_name, state.state_code ,lga.lga_name, lga.plate_code
                      FROM lga 
                      JOIN states AS state ON lga.state_id = state.id
                      WHERE state.state_code<> 'NON' ORDER BY lga.id LIMIT 720, 60";
            $statement = $this->connection->prepare($query);
            $statement->execute();

            $lgaDetails = $statement->fetchAll();

            if ($lgaDetails) {
                $lgaFormula = new LGAFormula();

                $excelFormula = '';
                $closingString = '';
                for ($i = 0; $i < count($lgaDetails); $i++) {
                    $stateName = $lgaDetails[$i]['state_code'];
                    $lgaName = $lgaDetails[$i]['lga_name'];
                    $lgaPlateCode = $lgaDetails[$i]['plate_code'];
                    if ($i == 0) {
                        $excelFormula .= "=IF(AND(S1=\"$stateName\",L1=\"$lgaName\"),\"$lgaPlateCode\"";
                    } else {
                        $excelFormula .= ", IF(AND(S1=\"$stateName\",L1=\"$lgaName\"),\"$lgaPlateCode\"";
                    }

                    $closingString .= ')';
                }

                $excelFormula .= $closingString;
                $lgaFormula->setFormula($excelFormula);

                $lgaFormulas[] = $lgaFormula;
            }

            //*************************************** 780, 60
            $query = "SELECT state.state_name, state.state_code ,lga.lga_name, lga.plate_code
                      FROM lga 
                      JOIN states AS state ON lga.state_id = state.id
                      WHERE state.state_code<> 'NON' ORDER BY lga.id LIMIT 780, 60";
            $statement = $this->connection->prepare($query);
            $statement->execute();

            $lgaDetails = $statement->fetchAll();

            if ($lgaDetails) {
                $lgaFormula = new LGAFormula();

                $excelFormula = '';
                $closingString = '';
                for ($i = 0; $i < count($lgaDetails); $i++) {
                    $stateName = $lgaDetails[$i]['state_code'];
                    $lgaName = $lgaDetails[$i]['lga_name'];
                    $lgaPlateCode = $lgaDetails[$i]['plate_code'];
                    if ($i == 0) {
                        $excelFormula .= "=IF(AND(S1=\"$stateName\",L1=\"$lgaName\"),\"$lgaPlateCode\"";
                    } else {
                        $excelFormula .= ", IF(AND(S1=\"$stateName\",L1=\"$lgaName\"),\"$lgaPlateCode\"";
                    }

                    $closingString .= ')';
                }

                $excelFormula .= $closingString;
                $lgaFormula->setFormula($excelFormula);

                $lgaFormulas[] = $lgaFormula;
            }

            //*************************************** NON
            $query = "SELECT state.state_name, state.state_code ,lga.lga_name, lga.plate_code
                      FROM lga 
                      JOIN states AS state ON lga.state_id = state.id
                      WHERE state.state_code = 'NON'";
            $statement = $this->connection->prepare($query);
            $statement->execute();

            $lgaDetails = $statement->fetchAll();

            if ($lgaDetails) {
                $lgaFormula = new LGAFormula();

                $excelFormula = '';
                $closingString = '';
                for ($i = 0; $i < count($lgaDetails); $i++) {
                    $stateName = $lgaDetails[$i]['state_code'];
                    $lgaName = $lgaDetails[$i]['plate_code'];
                    $lgaPlateCode = $lgaDetails[$i]['plate_code'];
                    if ($i == 0) {
                        $excelFormula .= "=IF(AND(S1=\"$stateName\",L1=\"$lgaName\"),\"$lgaPlateCode\"";
                    } else {
                        $excelFormula .= ", IF(AND(S1=\"$stateName\",L1=\"$lgaName\"),\"$lgaPlateCode\"";
                    }

                    $closingString .= ')';
                }

                $excelFormula .= $closingString;
                $lgaFormula->setFormula($excelFormula);

                $lgaFormulas[] = $lgaFormula;
            }


        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $lgaFormulas;

    }

}