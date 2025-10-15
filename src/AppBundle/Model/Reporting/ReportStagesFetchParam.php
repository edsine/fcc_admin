<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 7/30/2018
 * Time: 12:01 PM
 */

namespace AppBundle\Model\Reporting;


class ReportStagesFetchParam
{
    private $fetchAll = false;
    private $fetchStage1 = false;
    private $fetchStage2 = false;
    private $fetchStage3 = false;
    private $fetchStage4 = false;
    private $fetchStage5 = false;
    private $fetchStage6 = false;
    private $fetchStage7 = false;
    private $fetchStage8 = false;
    private $fetchStage9 = false;

    /**
     * ReportStagesFetchParam constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return bool
     */
    public function isFetchAll(): bool
    {
        return $this->fetchAll;
    }

    /**
     * @param bool $fetchAll
     */
    public function setFetchAll(bool $fetchAll): void
    {
        $this->fetchAll = $fetchAll;
    }

    /**
     * @return bool
     */
    public function isFetchStage1(): bool
    {
        return $this->fetchStage1;
    }

    /**
     * @param bool $fetchStage1
     */
    public function setFetchStage1(bool $fetchStage1): void
    {
        $this->fetchStage1 = $fetchStage1;
    }

    /**
     * @return bool
     */
    public function isFetchStage2(): bool
    {
        return $this->fetchStage2;
    }

    /**
     * @param bool $fetchStage2
     */
    public function setFetchStage2(bool $fetchStage2): void
    {
        $this->fetchStage2 = $fetchStage2;
    }

    /**
     * @return bool
     */
    public function isFetchStage3(): bool
    {
        return $this->fetchStage3;
    }

    /**
     * @param bool $fetchStage3
     */
    public function setFetchStage3(bool $fetchStage3): void
    {
        $this->fetchStage3 = $fetchStage3;
    }

    /**
     * @return bool
     */
    public function isFetchStage4(): bool
    {
        return $this->fetchStage4;
    }

    /**
     * @param bool $fetchStage4
     */
    public function setFetchStage4(bool $fetchStage4): void
    {
        $this->fetchStage4 = $fetchStage4;
    }

    /**
     * @return bool
     */
    public function isFetchStage5(): bool
    {
        return $this->fetchStage5;
    }

    /**
     * @param bool $fetchStage5
     */
    public function setFetchStage5(bool $fetchStage5): void
    {
        $this->fetchStage5 = $fetchStage5;
    }

    /**
     * @return bool
     */
    public function isFetchStage6(): bool
    {
        return $this->fetchStage6;
    }

    /**
     * @param bool $fetchStage6
     */
    public function setFetchStage6(bool $fetchStage6): void
    {
        $this->fetchStage6 = $fetchStage6;
    }

    /**
     * @return bool
     */
    public function isFetchStage7(): bool
    {
        return $this->fetchStage7;
    }

    /**
     * @param bool $fetchStage7
     */
    public function setFetchStage7(bool $fetchStage7): void
    {
        $this->fetchStage7 = $fetchStage7;
    }

    /**
     * @return bool
     */
    public function isFetchStage8(): bool
    {
        return $this->fetchStage8;
    }

    /**
     * @param bool $fetchStage8
     */
    public function setFetchStage8(bool $fetchStage8): void
    {
        $this->fetchStage8 = $fetchStage8;
    }

    /**
     * @return bool
     */
    public function isFetchStage9(): bool
    {
        return $this->fetchStage9;
    }

    /**
     * @param bool $fetchStage9
     */
    public function setFetchStage9(bool $fetchStage9): void
    {
        $this->fetchStage9 = $fetchStage9;
    }


}