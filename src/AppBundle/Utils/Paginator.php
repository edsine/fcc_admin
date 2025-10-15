<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 9/17/2016
 * Time: 10:46 PM
 */

namespace AppBundle\Utils;


class Paginator
{

    private $startRow = 0;
    private $rowsPerPage;
    private $totalRows;
    private $paginationDescription;

    /**
     * Paginator constructor.
     */
    public function __construct()
    {
    }

    public function pageFirst(){
        $this->page(0);
    }

    public function pagePrevious(){
        if($this->startRow <= 0){
            $this->startRow = 0;
            $this->page($this->startRow);
        }else{
            $this->page($this->startRow - $this->rowsPerPage);
        }
    }

    public function pageNext(){
        $nextPage = $this->startRow + $this->rowsPerPage;
        if($nextPage < $this->totalRows){
            $this->page($nextPage);
        }else{
            $this->page($this->startRow);
        }
    }

    public function pageLast(){
        if($this->totalRows > $this->rowsPerPage){
            $this->page($this->totalRows - (($this->totalRows % $this->rowsPerPage != 0) ? $this->totalRows % $this->rowsPerPage : $this->rowsPerPage));
        }else{
            $this->page($this->startRow);
        }
    }

    public function page(int $startRow){
        $this->startRow = $startRow;

        //build the pagination description
        if ($this->totalRows > 0) {
            $this->paginationDescription = "Displaying " . ($this->startRow + 1);
            if (($this->startRow + 1) < $this->totalRows) {
                $this->paginationDescription .= "-";

                if (($this->startRow + $this->rowsPerPage) < $this->totalRows) {
                    $this->paginationDescription .= ($this->startRow + $this->rowsPerPage);
                } else {
                    $this->paginationDescription .= $this->totalRows;
                }
            }
            $this->paginationDescription .= " of " . $this->totalRows;
        } else {
            $this->paginationDescription = "No Records Found";
        }
    }

    /**
     * @return mixed
     */
    public function getStartRow()
    {
        return $this->startRow;
    }

    /**
     * @param mixed $startRow
     */
    public function setStartRow($startRow)
    {
        $this->startRow = $startRow;
    }

    /**
     * @return mixed
     */
    public function getTotalRows()
    {
        return $this->totalRows;
    }

    /**
     * @param mixed $totalRows
     */
    public function setTotalRows($totalRows)
    {
        $this->totalRows = $totalRows;
    }

    /**
     * @return mixed
     */
    public function getRowsPerPage()
    {
        return $this->rowsPerPage;
    }

    /**
     * @param mixed $rowsPerPage
     */
    public function setRowsPerPage($rowsPerPage)
    {
        $this->rowsPerPage = $rowsPerPage;
    }

    /**
     * @return mixed
     */
    public function getPaginationDescription()
    {
        return $this->paginationDescription;
    }

    /**
     * @param mixed $paginationDescription
     */
    public function setPaginationDescription($paginationDescription)
    {
        $this->paginationDescription = $paginationDescription;
    }



}