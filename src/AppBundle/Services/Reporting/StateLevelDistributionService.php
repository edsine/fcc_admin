<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/8/2017
 * Time: 3:43 PM
 */

namespace AppBundle\Services\Reporting;


use AppBundle\AppException\AppException;
use AppBundle\Model\Reporting\StateLevel\CareerDistribution\StateLevelPostDistributionAnalysis;
use AppBundle\Model\Reporting\StateLevel\CareerDistribution\StateLevelStage1Analysis;
use AppBundle\Model\Reporting\StateLevel\CareerDistribution\StateLevelStage1Entry;
use AppBundle\Model\Reporting\StateLevel\CareerDistribution\StateLevelStage2Analysis;
use AppBundle\Model\Reporting\StateLevel\CareerDistribution\StateLevelStage2Entry;
use AppBundle\Model\Reporting\StateLevel\CareerDistribution\StateLevelStage3Analysis;
use AppBundle\Model\Reporting\StateLevel\CareerDistribution\StateLevelStage4Analysis;
use AppBundle\Model\Reporting\StateLevel\CareerDistribution\StateLevelStage4SubAnalysis;
use AppBundle\Model\Reporting\StateLevel\CareerDistribution\StateLevelStage4SubAnalysisEntry;
use AppBundle\Model\Reporting\StateLevel\CareerDistribution\StateLevelStage5Analysis;
use AppBundle\Model\Reporting\StateLevel\CareerDistribution\StateLevelStage5SubAnalysis;
use AppBundle\Model\Reporting\StateLevel\CareerDistribution\StateLevelStage5SubAnalysisEntry;
use AppBundle\Model\Reporting\StateLevel\CareerDistribution\StateLevelStage6Analysis;
use AppBundle\Model\Reporting\StateLevel\CareerDistribution\StateLevelStage6SubAnalysis;
use AppBundle\Model\Reporting\StateLevel\CareerDistribution\StateLevelStage6SubAnalysisEntry;
use AppBundle\Model\Reporting\StateLevel\CareerDistribution\StateLevelStage7Analysis;
use AppBundle\Model\Reporting\StateLevel\CareerDistribution\StateLevelStage7SubAnalysis;
use AppBundle\Model\Reporting\StateLevel\CareerDistribution\StateLevelStage7SubAnalysisEntry;
use AppBundle\Model\Reporting\StateLevel\CareerDistribution\StateLevelStage8Analysis;
use AppBundle\Model\Reporting\StateLevel\CareerDistribution\StateLevelStage8CategoryAnalysis;
use AppBundle\Model\Reporting\StateLevel\CareerDistribution\StateLevelStage8SubAnalysis;
use AppBundle\Model\Reporting\StateLevel\CareerDistribution\StateLevelStage8SubAnalysisEntry;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\Paginator;
use Doctrine\DBAL\Connection;
use \Throwable;
use \PDO;

class StateLevelDistributionService
{
    private $connection;
    private $rows_per_page;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->rows_per_page = AppConstants::ROWS_PER_PAGE;
    }

    public function getStateLevelPostDistributionReport($organizationId, $submissionYear) : StateLevelPostDistributionAnalysis
    {
        $report = null;

        $statement = null;

        try {

            //check if data exists for that year
            $query = "select * from state_level_post_analysis "
                . "where organization_id=:organization_id AND submission_year=:submission_year limit 1";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':organization_id', $organizationId);
            $statement->bindValue(':submission_year', $submissionYear);
            $statement->execute();

            $exists = $statement->fetch();

            if ($exists) { //if so then continue

                $report = new StateLevelPostDistributionAnalysis();

                //get the organization name
                $query = "select organization_name, state_owned_establishment_state_id from organization where id=:id";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':id', $organizationId);
                $statement->execute();

                $organization = $statement->fetch();

                $organizationName = $organization['organization_name'];
                $stateOwnerId = $organization['state_owned_establishment_state_id'];
                $report->setOrganizationName($organizationName);
                $report->setSubmissionYear($submissionYear);
                $report->setStateOwnerId($stateOwnerId);

                //STAGE 1 ANALYSIS
                $stage1Analysis = new StateLevelStage1Analysis();

                $stage1Data = array(); //array of stage1 entries representing each state, totals and percentage

                $query = "SELECT d.organization_id,d.submission_year,d.lga_of_origin_id "
                    . ",d.total_gl_1,d.total_gl_2,d.total_gl_3,d.total_gl_4,d.total_gl_5,d.total_gl_6,d.total_gl_7 "
                    . ",d.total_gl_8,d.total_gl_9,d.total_gl_10,d.total_gl_11,d.total_gl_12,d.total_gl_13,d.total_gl_14 "
                    . ",d.total_gl_15,d.total_gl_16,d.total_gl_17 "
                    . ",l.lga_code,l.lga_name, s.id as _senatorial_district_id, s.senatorial_district_name "
                    . "FROM state_level_post_analysis d "
                    . "JOIN lga l on d.lga_of_origin_id = l.id "
                    . "JOIN senatorial_district s on l.senatorial_district_id = s.id "
                    . "WHERE d.organization_id=:organization_id AND d.submission_year=:submission_year order by l.lga_name ";
                $statement = $this->connection->prepare($query);
                $statement->bindValue(':organization_id', $organizationId);
                $statement->bindValue(':submission_year', $submissionYear);

                $statement->execute();

                $analysisRecords = $statement->fetchAll();
                if ($analysisRecords) {
                    //fill the stage 1 entries array

                    //calculate the overall totals and overall percentage
                    $stage1OverallGL1 = 0;
                    $stage1OverallGL2 = 0;
                    $stage1OverallGL3 = 0;
                    $stage1OverallGL4 = 0;
                    $stage1OverallGL5 = 0;
                    $stage1OverallGL6 = 0;
                    $stage1OverallGL7 = 0;
                    $stage1OverallGL8 = 0;
                    $stage1OverallGL9 = 0;
                    $stage1OverallGL10 = 0;
                    $stage1OverallGL11 = 0;
                    $stage1OverallGL12 = 0;
                    $stage1OverallGL13 = 0;
                    $stage1OverallGL14 = 0;
                    $stage1OverallGL15 = 0;
                    $stage1OverallGL16 = 0;
                    $stage1OverallGL17 = 0;

                    $overallTotal = 0;

                    foreach ($analysisRecords as $stateAnalysis) {
                        $stage1Entry = new StateLevelStage1Entry();
                        $stage1Entry->setLgaCode($stateAnalysis['lga_code']);
                        $stage1Entry->setLgaName($stateAnalysis['lga_name']);
                        $stage1Entry->setTotalGL1($stateAnalysis['total_gl_1']);
                        $stage1Entry->setTotalGL2($stateAnalysis['total_gl_2']);
                        $stage1Entry->setTotalGL3($stateAnalysis['total_gl_3']);
                        $stage1Entry->setTotalGL4($stateAnalysis['total_gl_4']);
                        $stage1Entry->setTotalGL5($stateAnalysis['total_gl_5']);
                        $stage1Entry->setTotalGL6($stateAnalysis['total_gl_6']);
                        $stage1Entry->setTotalGL7($stateAnalysis['total_gl_7']);
                        $stage1Entry->setTotalGL8($stateAnalysis['total_gl_8']);
                        $stage1Entry->setTotalGL9($stateAnalysis['total_gl_9']);
                        $stage1Entry->setTotalGL10($stateAnalysis['total_gl_10']);
                        $stage1Entry->setTotalGL11($stateAnalysis['total_gl_11']);
                        $stage1Entry->setTotalGL12($stateAnalysis['total_gl_12']);
                        $stage1Entry->setTotalGL13($stateAnalysis['total_gl_13']);
                        $stage1Entry->setTotalGL14($stateAnalysis['total_gl_14']);
                        $stage1Entry->setTotalGL15($stateAnalysis['total_gl_15']);
                        $stage1Entry->setTotalGL16($stateAnalysis['total_gl_16']);
                        $stage1Entry->setTotalGL17($stateAnalysis['total_gl_17']);

                        $stage1Entry->setSenatorialDistrictId($stateAnalysis['_senatorial_district_id']);
                        $stage1Entry->setSenatorialDistrictName($stateAnalysis['senatorial_district_name']);

                        $stage1Data[] = $stage1Entry;

                        //calculate totals
                        $stage1Entry->calculateTotal();

                        $stage1OverallGL1 += $stage1Entry->getTotalGL1();
                        $stage1OverallGL2 += $stage1Entry->getTotalGL2();
                        $stage1OverallGL3 += $stage1Entry->getTotalGL3();
                        $stage1OverallGL4 += $stage1Entry->getTotalGL4();
                        $stage1OverallGL5 += $stage1Entry->getTotalGL5();
                        $stage1OverallGL6 += $stage1Entry->getTotalGL6();
                        $stage1OverallGL7 += $stage1Entry->getTotalGL7();
                        $stage1OverallGL8 += $stage1Entry->getTotalGL8();
                        $stage1OverallGL9 += $stage1Entry->getTotalGL9();
                        $stage1OverallGL10 += $stage1Entry->getTotalGL10();
                        $stage1OverallGL11 += $stage1Entry->getTotalGL11();
                        $stage1OverallGL12 += $stage1Entry->getTotalGL12();
                        $stage1OverallGL13 += $stage1Entry->getTotalGL13();
                        $stage1OverallGL14 += $stage1Entry->getTotalGL14();
                        $stage1OverallGL15 += $stage1Entry->getTotalGL15();
                        $stage1OverallGL16 += $stage1Entry->getTotalGL16();
                        $stage1OverallGL17 += $stage1Entry->getTotalGL17();

                        $overallTotal += $stage1Entry->getTotal();

                    }

                    //calculate the percentages for each stage 1 entry and the overall precentage
                    $overallPercentage = 0;

                    foreach ($stage1Data as $stage1Entry) {
                        $stage1Entry->calculatePercentage($overallTotal);
                        $overallPercentage += $stage1Entry->getPercentage();
                    }

                    $stage1Analysis->setStage1Data($stage1Data);

                    //set overall totals
                    $stage1Analysis->setOverallGl1($stage1OverallGL1);
                    $stage1Analysis->setOverallGl2($stage1OverallGL2);
                    $stage1Analysis->setOverallGl3($stage1OverallGL3);
                    $stage1Analysis->setOverallGl4($stage1OverallGL4);
                    $stage1Analysis->setOverallGl5($stage1OverallGL5);
                    $stage1Analysis->setOverallGl6($stage1OverallGL6);
                    $stage1Analysis->setOverallGl7($stage1OverallGL7);
                    $stage1Analysis->setOverallGl8($stage1OverallGL8);
                    $stage1Analysis->setOverallGl9($stage1OverallGL9);
                    $stage1Analysis->setOverallGl10($stage1OverallGL10);
                    $stage1Analysis->setOverallGl11($stage1OverallGL11);
                    $stage1Analysis->setOverallGl12($stage1OverallGL12);
                    $stage1Analysis->setOverallGl13($stage1OverallGL13);
                    $stage1Analysis->setOverallGl14($stage1OverallGL14);
                    $stage1Analysis->setOverallGl15($stage1OverallGL15);
                    $stage1Analysis->setOverallGl16($stage1OverallGL16);
                    $stage1Analysis->setOverallGl17($stage1OverallGL17);

                    $stage1Analysis->setOverallTotal($overallTotal);
                    $stage1Analysis->setOverallPercentage(number_format($overallPercentage));

                    //
                    $report->setStage1($stage1Analysis);

                    //STAGE 2 ANALYSIS
                    $stage2Analysis = new StateLevelStage2Analysis();
                    $stage2Data = array();

                    $stage2OverallGL_1_to_4 = 0;
                    $stage2OverallPercentageGL_1_to_4 = 0;
                    $stage2OverallGL_5_to_7 = 0;
                    $stage2OverallPercentageGL_5_to_7 = 0;
                    $stage2OverallGL_1_to_7 = 0;
                    $stage2OverallPercentageGL_1_to_7 = 0;
                    $stage2OverallGL_8_to_10 = 0;
                    $stage2OverallPercentageGL_8_to_10 = 0;
                    $stage2OverallGL_11_to_14 = 0;
                    $stage2OverallPercentageGL_11_to_14 = 0;
                    $stage2OverallGL_15_And_Above = 0;
                    $stage2OverallPercentageGL_15_And_Above = 0;
                    $stage2OverallGL_8_to_15_And_Above = 0;
                    $stage2OverallPercentageGL_8_to_15_And_Above = 0;

                    $stage2OverallTotal = 0;
                    $stage2OverallPercentage = 0;


                    foreach ($stage1Analysis->getStage1Data() as $stage1StateData) {
                        $stage2Entry = new StateLevelStage2Entry();

                        //$stage1StateData = new FederalLevelStage1Entry(); //REMOVE THIS

                        $stage2Entry->setLgaCode($stage1StateData->getLgaCode());
                        $stage2Entry->setLgaName($stage1StateData->getLgaName());

                        $stage2Entry->setTotalGL1To4($stage1StateData->calculateGL_1_to_4());
                        $stage2Entry->setTotalGL5To7($stage1StateData->calculateGL_5_to_7());
                        $stage2Entry->setTotalGL1To7($stage1StateData->calculateGL_1_to_4() + $stage1StateData->calculateGL_5_to_7());

                        $stage2Entry->setTotalGL8To10($stage1StateData->calculateGL_8_to_10());
                        $stage2Entry->setTotalGL11To14($stage1StateData->calculateGL_11_to_14());
                        $stage2Entry->setTotalGL15AndAbove($stage1StateData->calculateGL_15_And_Above());
                        $stage2Entry->setTotalGL8To15AndAbove($stage1StateData->calculateGL_8_to_10() +
                            $stage1StateData->calculateGL_11_to_14() +
                            $stage1StateData->calculateGL_15_And_Above());

                        $stage2Data[] = $stage2Entry;

                        //do other totals
                        $stage2OverallGL_1_to_4 += $stage2Entry->getTotalGL1To4();
                        $stage2OverallGL_5_to_7 += $stage2Entry->getTotalGL5To7();
                        $stage2OverallGL_1_to_7 += $stage2Entry->getTotalGL1To7();
                        $stage2OverallGL_8_to_10 += $stage2Entry->getTotalGL8To10();
                        $stage2OverallGL_11_to_14 += $stage2Entry->getTotalGL11To14();
                        $stage2OverallGL_15_And_Above += $stage2Entry->getTotalGL15AndAbove();
                        $stage2OverallGL_8_to_15_And_Above += $stage2Entry->getTotalGL8To15AndAbove();

                        $stage2Entry->calculateTotal();
                        $stage2OverallTotal += $stage2Entry->getTotal();

                    }

                    $stage2CategoryTotals = array(
                        'overall_GL_1_to_4' => $stage2OverallGL_1_to_4,
                        'overall_GL_5_to_7' => $stage2OverallGL_5_to_7,
                        'overall_GL_1_to_7' => $stage2OverallGL_1_to_7,
                        'overall_GL_8_to_10' => $stage2OverallGL_8_to_10,
                        'overall_GL_11_to_14' => $stage2OverallGL_11_to_14,
                        'overall_GL_15_And_Above' => $stage2OverallGL_15_And_Above,
                        'overall_GL_8_to_15_And_Above' => $stage2OverallGL_8_to_15_And_Above,
                    );

                    foreach ($stage2Data as $stage2_data) {
                        $stage2_data->calculatePercentage($stage2OverallTotal);
                        $stage2_data->calculateCategoryPercentages($stage2CategoryTotals);

                        $stage2OverallPercentageGL_1_to_4 += $stage2_data->getPercentageGL1To4();
                        $stage2OverallPercentageGL_5_to_7 += $stage2_data->getPercentageGL5To7();
                        $stage2OverallPercentageGL_1_to_7 += $stage2_data->getPercentageGL1To7();
                        $stage2OverallPercentageGL_8_to_10 += $stage2_data->getPercentageGL8To10();
                        $stage2OverallPercentageGL_11_to_14 += $stage2_data->getPercentageGL11To14();
                        $stage2OverallPercentageGL_15_And_Above += $stage2_data->getPercentageGL15AndAbove();
                        $stage2OverallPercentageGL_8_to_15_And_Above += $stage2_data->getPercentageGL8To15AndAbove();

                        $stage2OverallPercentage += $stage2_data->getPercentage();
                    }

                    $stage2Analysis->setStage2Data($stage2Data);

                    $stage2Analysis->setOverallGL1To4($stage2OverallGL_1_to_4);
                    $stage2Analysis->setOverallPercentageGL1To4(number_format($stage2OverallPercentageGL_1_to_4));

                    $stage2Analysis->setOverallGL5To7($stage2OverallGL_5_to_7);
                    $stage2Analysis->setOverallPercentageGL5To7(number_format($stage2OverallPercentageGL_5_to_7));

                    $stage2Analysis->setOverallGL1To7($stage2OverallGL_1_to_7);
                    $stage2Analysis->setOverallPercentageGL1To7(number_format($stage2OverallPercentageGL_1_to_7));

                    $stage2Analysis->setOverallGL8To10($stage2OverallGL_8_to_10);
                    $stage2Analysis->setOverallPercentageGL8To10(number_format($stage2OverallPercentageGL_8_to_10));

                    $stage2Analysis->setOverallGL11To14($stage2OverallGL_11_to_14);
                    $stage2Analysis->setOverallPercentageGL11To14(number_format($stage2OverallPercentageGL_11_to_14));

                    $stage2Analysis->setOverallGL15AndAbove($stage2OverallGL_15_And_Above);
                    $stage2Analysis->setOverallPercentageGL15AndAbove(number_format($stage2OverallPercentageGL_15_And_Above));

                    $stage2Analysis->setOverallGL8To15AndAbove($stage2OverallGL_8_to_15_And_Above);
                    $stage2Analysis->setOverallPercentageGL8To15AndAbove(number_format($stage2OverallPercentageGL_8_to_15_And_Above));

                    $stage2Analysis->setOverallTotal($stage2OverallTotal);
                    $stage2Analysis->setOverallPercentage(number_format($stage2OverallPercentage));

                    $report->setStage2($stage2Analysis);

                    //STAGE 3 ANALYSIS
                    $stage3Analysis = new StateLevelStage3Analysis();

                    $stage3Data = array();

                    $stage3GL1to4Series = array();
                    $stage3GL5to7Series = array();
                    $stage3GL8to10Series = array();
                    $stage3GL11to14Series = array();
                    $stage3GL15AndAbove = array();

                    $stage3XAxisLabels = array();

                    foreach ($stage2Analysis->getStage2Data() as $stage2_Data) {

                        //$stage2_Data = new FederalLevelStage2Entry();

                        $stage3XAxisLabels[] = $stage2_Data->getLgaCode();

                        $stage3GL1to4Series[] = $stage2_Data->getTotalGL1To4();
                        $stage3GL5to7Series[] = $stage2_Data->getTotalGL5To7();
                        $stage3GL8to10Series[] = $stage2_Data->getTotalGL8To10();
                        $stage3GL11to14Series[] = $stage2_Data->getTotalGL11To14();
                        $stage3GL15AndAbove[] = $stage2_Data->getTotalGL15AndAbove();
                    }

                    $stage3Data[] = json_encode($stage3GL1to4Series);
                    $stage3Data[] = json_encode($stage3GL5to7Series);
                    $stage3Data[] = json_encode($stage3GL8to10Series);
                    $stage3Data[] = json_encode($stage3GL11to14Series);
                    $stage3Data[] = json_encode($stage3GL15AndAbove);

                    $stage3Analysis->setStage3Data($stage3Data);
                    $stage3Analysis->setXAxisLabels("['" . implode("','", $stage3XAxisLabels) . "']");

                    $report->setStage3($stage3Analysis);

                    //STAGE 4 ANALYSIS
                    //prepare a map of senatorial district and lga array
                    $senatorialDistrictLgaMap = array();

                    $stage4Analysis = new StateLevelStage4Analysis();

                    //select the geo-political zone ids and name
                    $query = "select id, senatorial_district_code, senatorial_district_name  "
                        . "from senatorial_district "
                        . "where state_id=:state_id and senatorial_district_code<>:senatorial_district_code and record_status=:record_status ";
                    $statement = $this->connection->prepare($query);
                    $statement->bindValue(':state_id', $report->getStateOwnerId());
                    $statement->bindValue(':senatorial_district_code', 'NON');
                    $statement->bindValue(':record_status', AppConstants::ACTIVE);

                    $statement->execute();

                    $senatorialDistricts = $statement->fetchAll();

                    $stage4TempData = array();

                    //setup the zonestatesmap and a temp map of subanalysis
                    foreach ($senatorialDistricts as $senatorialDistrict) {

                        $senatorialDistrictLgaMap[$senatorialDistrict['id']] = array();

                        $stage4SubAnalysis = new StateLevelStage4SubAnalysis();
                        $stage4SubAnalysis->setSenatorialDistrictCode($senatorialDistrict['id']);
                        $stage4SubAnalysis->setSenatorialDistrictCode($senatorialDistrict['senatorial_district_code']);
                        $stage4SubAnalysis->setSenatorialDistrictName($senatorialDistrict['senatorial_district_name']);

                        $stage4TempData[$senatorialDistrict['id']] = $stage4SubAnalysis;

                    }

                    //fill the senatorial dist states map
                    foreach ($stage1Analysis->getStage1Data() as $stage_1_data) {
                        $senatorialDistrictLgaMap[$stage_1_data->getSenatorialDistrictId()][] = $stage_1_data;
                    }

                    //now do the sub analysis entries
                    foreach ($stage4TempData as $k => $subAnalysis) {

                        $stage4SubAnalysisData = array();

                        $stage4SubOverallGL_1_to_3 = 0;
                        $stage4SubOverallPercentageGL_1_to_3 = 0;
                        $stage4SubOverallGL_4_to_5 = 0;
                        $stage4SubOverallPercentageGL_4_to_5 = 0;
                        $stage4SubOverallGL6to7 = 0;
                        $stage4SubOverallPercentageGL6to7 = 0;
                        $stage4SubOverallTotal = 0;
                        $stage4SubOverallPercentage = 0;

                        foreach ($senatorialDistrictLgaMap[$k] as $stage_1_zonestate) {
                            $stage4SubAnalysisEntry = new StateLevelStage4SubAnalysisEntry();
                            $stage4SubAnalysisEntry->setLgaCode($stage_1_zonestate->getLgaCode());
                            $stage4SubAnalysisEntry->setLgaName($stage_1_zonestate->getLgaName());

                            //$stage_1_zonestate = new FederalLevelStage1Entry(); //REMOVE THIS

                            $stage4SubAnalysisEntry->setTotalGL1To3($stage_1_zonestate->calculateGL_1_to_3());
                            $stage4SubAnalysisEntry->setTotalGL4To5($stage_1_zonestate->calculateGL_4_to_5());
                            $stage4SubAnalysisEntry->setTotalGL6To7($stage_1_zonestate->calculateGL_6_to_7());

                            $stage4SubOverallGL_1_to_3 += $stage4SubAnalysisEntry->getTotalGL1To3();
                            $stage4SubOverallGL_4_to_5 += $stage4SubAnalysisEntry->getTotalGL4To5();
                            $stage4SubOverallGL6to7 += $stage4SubAnalysisEntry->getTotalGL6To7();

                            $stage4SubAnalysisEntry->calculateTotal();
                            $stage4SubOverallTotal += $stage4SubAnalysisEntry->getTotal();

                            $stage4SubAnalysisData[] = $stage4SubAnalysisEntry;
                        }

                        //sort sub analysis entries
                        usort($stage4SubAnalysisData, array("\\AppBundle\\Model\\Reporting\\StateLevel\\CareerDistribution\\StateLevelStage4SubAnalysisEntry", "cmp_obj"));

                        //$subAnalysis = new FederalLevelStage4SubAnalysis(); //REMOVE LATER
                        $subAnalysis->setOverallGL1To3($stage4SubOverallGL_1_to_3);
                        $subAnalysis->setOverallGL4To5($stage4SubOverallGL_4_to_5);
                        $subAnalysis->setOverallGL6To7($stage4SubOverallGL6to7);
                        $subAnalysis->setOverallTotal($stage4SubOverallTotal);

                        $stage4SubCategoryTotals = array(
                            'overall_GL_1_to_3' => $subAnalysis->getOverallGL1To3(),
                            'overall_GL_4_to_5' => $subAnalysis->getOverallGL4To5(),
                            'overall_GL_6_to_7' => $subAnalysis->getOverallGL6To7()
                        );

                        foreach ($stage4SubAnalysisData as $stage_4_sub_entry) {

                            //$stage_4_sub_entry = new FederalLevelStage4SubAnalysisEntry(); //REMOVE LATER

                            $stage_4_sub_entry->calculatePercentage($subAnalysis->getOverallTotal());
                            $stage_4_sub_entry->calculateCategoryPercentages($stage4SubCategoryTotals);

                            $stage4SubOverallPercentageGL_1_to_3 += $stage_4_sub_entry->getPercentageGL1To3();
                            $stage4SubOverallPercentageGL_4_to_5 += $stage_4_sub_entry->getPercentageGL4To5();
                            $stage4SubOverallPercentageGL6to7 += $stage_4_sub_entry->getPercentageGL6To7();
                            $stage4SubOverallPercentage += $stage_4_sub_entry->getPercentage();

                        }

                        $subAnalysis->setOverallPercentageGL1To3(number_format($stage4SubOverallPercentageGL_1_to_3));
                        $subAnalysis->setOverallPercentageGL4To5(number_format($stage4SubOverallPercentageGL_4_to_5));
                        $subAnalysis->setOverallPercentageGL6To7(number_format($stage4SubOverallPercentageGL6to7));
                        $subAnalysis->setOverallPercentage(number_format($stage4SubOverallPercentage));

                        $subAnalysis->setSubAnalysisData($stage4SubAnalysisData);
                    }

                    $stage4Data = array_values($stage4TempData);

                    //sort sub analysis
                    usort($stage4Data, array("\\AppBundle\\Model\\Reporting\\StateLevel\\CareerDistribution\\StateLevelStage4SubAnalysis", "cmp_obj"));

                    $stage4Analysis->setStage4Data($stage4Data);

                    $report->setStage4($stage4Analysis);

                    //STAGE 5 ANALYSIS

                    $stage5Analysis = new StateLevelStage5Analysis();

                    $stage5TempData = array();

                    //setup the temp map of stage 5 subanalysis
                    foreach ($senatorialDistricts as $senatorialDistrict) {

                        $stage5SubAnalysis = new StateLevelStage5SubAnalysis();
                        $stage5SubAnalysis->setSenatorialDistrictCode($senatorialDistrict['id']);
                        $stage5SubAnalysis->setSenatorialDistrictCode($senatorialDistrict['senatorial_district_code']);
                        $stage5SubAnalysis->setSenatorialDistrictName($senatorialDistrict['senatorial_district_name']);

                        $stage5TempData[$senatorialDistrict['id']] = $stage5SubAnalysis;

                    }

                    //now do the stage 5 sub analysis entries
                    foreach ($stage5TempData as $k => $stage5_SubAnalysis) {

                        $stage5SubAnalysisData = array();

                        $stage5SubOverallGL_8_to_10 = 0;
                        $stage5SubOverallPercentageGL_8_to_10 = 0;
                        $stage5SubOverallGL_12_to_14 = 0;
                        $stage5SubOverallPercentageGL_12_to_14 = 0;
                        $stage5SubOverallGL_15_And_Above = 0;
                        $stage5SubOverallPercentageGL_15_And_Above = 0;
                        $stage5SubOverallTotal = 0;
                        $stage5SubOverallPercentage = 0;

                        foreach ($senatorialDistrictLgaMap[$k] as $stage_1_zonestate) {
                            $stage5SubAnalysisEntry = new StateLevelStage5SubAnalysisEntry();
                            $stage5SubAnalysisEntry->setLgaCode($stage_1_zonestate->getLgaCode());
                            $stage5SubAnalysisEntry->setLgaName($stage_1_zonestate->getLgaName());

                            //$stage_1_zonestate = new FederalLevelStage1Entry(); //REMOVE THIS

                            $stage5SubAnalysisEntry->setTotalGL8To10($stage_1_zonestate->calculateGL_8_to_10());
                            $stage5SubAnalysisEntry->setTotalGL12To14($stage_1_zonestate->calculateGL_12_to_14());
                            $stage5SubAnalysisEntry->setTotalGL15AndAbove($stage_1_zonestate->calculateGL_15_And_Above());

                            $stage5SubOverallGL_8_to_10 += $stage5SubAnalysisEntry->getTotalGL8To10();
                            $stage5SubOverallGL_12_to_14 += $stage5SubAnalysisEntry->getTotalGL12To14();
                            $stage5SubOverallGL_15_And_Above += $stage5SubAnalysisEntry->getTotalGL15AndAbove();

                            $stage5SubAnalysisEntry->calculateTotal();
                            $stage5SubOverallTotal += $stage5SubAnalysisEntry->getTotal();

                            $stage5SubAnalysisData[] = $stage5SubAnalysisEntry;
                        }

                        //sort sub analysis entries
                        usort($stage5SubAnalysisData, array("\\AppBundle\\Model\\Reporting\\StateLevel\\CareerDistribution\\StateLevelStage5SubAnalysisEntry", "cmp_obj"));

                        //$stage5_SubAnalysis = new FederalLevelStage5SubAnalysis(); //REMOVE LATER
                        $stage5_SubAnalysis->setOverallGL8To10($stage5SubOverallGL_8_to_10);
                        $stage5_SubAnalysis->setOverallGL12To14($stage5SubOverallGL_12_to_14);
                        $stage5_SubAnalysis->setOverallGL15AndAbove($stage5SubOverallGL_15_And_Above);
                        $stage5_SubAnalysis->setOverallTotal($stage5SubOverallTotal);

                        $stage5SubCategoryTotals = array(
                            'overall_GL_8_to_10' => $stage5_SubAnalysis->getOverallGL8To10(),
                            'overall_GL_12_to_14' => $stage5_SubAnalysis->getOverallGL12To14(),
                            'overall_GL_15_And_Above' => $stage5_SubAnalysis->getOverallGL15AndAbove()
                        );

                        foreach ($stage5SubAnalysisData as $stage_5_sub_entry) {

                            //$stage_5_sub_entry = new FederalLevelStage5SubAnalysisEntry(); //REMOVE LATER

                            $stage_5_sub_entry->calculatePercentage($stage5_SubAnalysis->getOverallTotal());
                            $stage_5_sub_entry->calculateCategoryPercentages($stage5SubCategoryTotals);

                            $stage5SubOverallPercentageGL_8_to_10 += $stage_5_sub_entry->getPercentageGL8To10();
                            $stage5SubOverallPercentageGL_12_to_14 += $stage_5_sub_entry->getPercentageGL12To14();
                            $stage5SubOverallPercentageGL_15_And_Above += $stage_5_sub_entry->getPercentageGL15AndAbove();
                            $stage5SubOverallPercentage += $stage_5_sub_entry->getPercentage();

                        }

                        $stage5_SubAnalysis->setOverallPercentageGL8To10(number_format($stage5SubOverallPercentageGL_8_to_10));
                        $stage5_SubAnalysis->setOverallPercentageGL12To14(number_format($stage5SubOverallPercentageGL_12_to_14));
                        $stage5_SubAnalysis->setOverallPercentageGL15AndAbove(number_format($stage5SubOverallPercentageGL_15_And_Above));
                        $stage5_SubAnalysis->setOverallPercentage(number_format($stage5SubOverallPercentage));

                        $stage5_SubAnalysis->setSubAnalysisData($stage5SubAnalysisData);
                    }

                    $stage5Data = array_values($stage5TempData);

                    //sort sub analysis
                    usort($stage5Data, array("\\AppBundle\\Model\\Reporting\\StateLevel\\CareerDistribution\\StateLevelStage5SubAnalysis", "cmp_obj"));
                    
                    $stage5Analysis->setStage5Data($stage5Data);

                    $report->setStage5($stage5Analysis);

                    //STAGE 6 ANALYSIS

                    $stage6Analysis = new StateLevelStage6Analysis();

                    $stage6_8pt0_Or_More_SubAnalysis = new StateLevelStage6SubAnalysis("8.0% or more");
                    $stage6_8pt0_Or_More_SubAnalysis_Entries = array();
                    $stage6_7pt1_to_7pt9_SubAnalysis = new StateLevelStage6SubAnalysis("between 7.1% and 7.9%");
                    $stage6_7pt1_to_7pt9_SubAnalysis_Entries = array();
                    $stage6_4pt0_to_7_SubAnalysis = new StateLevelStage6SubAnalysis("between 4.0% and 7.0%");
                    $stage6_4pt0_to_7_SubAnalysis_Entries = array();
                    $stage6_3pt1_to_3pt9_SubAnalysis = new StateLevelStage6SubAnalysis("between 3.1% and 3.9%");
                    $stage6_3pt1_to_3pt9_SubAnalysis_Entries = array();
                    $stage6_LessThan_3pt1_SubAnalysis = new StateLevelStage6SubAnalysis("less than 3.1%");
                    $stage6_LessThan_3pt1_Entries = array();

                    $stage6AllEntriesTempData = array();

                    //setup the temp array of stage 6 entries
                    foreach ($stage1Analysis->getStage1Data() as $stage1_entry) {

                        $stage6SubAnalysisEntry = new StateLevelStage6SubAnalysisEntry();
                        $stage6SubAnalysisEntry->setLgaCode($stage1_entry->getLgaCode());
                        $stage6SubAnalysisEntry->setLgaName($stage1_entry->getLgaName());

                        $stage6SubAnalysisEntry->setTotalGL8AndAbove($stage1_entry->calculateGL_8_And_Above());
                        $stage6SubAnalysisEntry->calculatePercentage($stage2Analysis->getOverallGL8To15AndAbove()); //calculate with stage 2 overal 7-15+

                        $stage6AllEntriesTempData[] = $stage6SubAnalysisEntry;

                    }

                    foreach ($stage6AllEntriesTempData as $stage6_SubAnalysis_Entry) {
                        //$stage6_SubAnalysis_Entry = new FederalLevelStage6SubAnalysisEntry(); //REMOVE LATER

                        $percentageGL15AndAbove = $stage6_SubAnalysis_Entry->getPercentageGL8AndAbove();
                        if ($percentageGL15AndAbove >= 8.0) {
                            $stage6_8pt0_Or_More_SubAnalysis_Entries[] = $stage6_SubAnalysis_Entry;
                        } else if ($percentageGL15AndAbove >= 7.1 && $percentageGL15AndAbove <= 7.9) {
                            $stage6_7pt1_to_7pt9_SubAnalysis_Entries[] = $stage6_SubAnalysis_Entry;
                        } else if ($percentageGL15AndAbove >= 4.0 && $percentageGL15AndAbove <= 7.0) {
                            $stage6_4pt0_to_7_SubAnalysis_Entries[] = $stage6_SubAnalysis_Entry;
                        } else if ($percentageGL15AndAbove >= 3.1 && $percentageGL15AndAbove <= 3.9) {
                            $stage6_3pt1_to_3pt9_SubAnalysis_Entries[] = $stage6_SubAnalysis_Entry;
                        } else if ($percentageGL15AndAbove < 3.1) {
                            $stage6_LessThan_3pt1_Entries[] = $stage6_SubAnalysis_Entry;
                        }

                        //$stage6_4pt0_Or_More_SubAnalysis_Entries[] = $stage6_SubAnalysis_Entry;
                    }

                    //sort sub analysis entries
                    usort($stage6_8pt0_Or_More_SubAnalysis_Entries, array("\\AppBundle\\Model\\Reporting\\StateLevel\\CareerDistribution\\StateLevelStage6SubAnalysisEntry", "cmp_obj"));
                    usort($stage6_7pt1_to_7pt9_SubAnalysis_Entries, array("\\AppBundle\\Model\\Reporting\\StateLevel\\CareerDistribution\\StateLevelStage6SubAnalysisEntry", "cmp_obj"));
                    usort($stage6_4pt0_to_7_SubAnalysis_Entries, array("\\AppBundle\\Model\\Reporting\\StateLevel\\CareerDistribution\\StateLevelStage6SubAnalysisEntry", "cmp_obj"));
                    usort($stage6_3pt1_to_3pt9_SubAnalysis_Entries, array("\\AppBundle\\Model\\Reporting\\StateLevel\\CareerDistribution\\StateLevelStage6SubAnalysisEntry", "cmp_obj"));
                    usort($stage6_LessThan_3pt1_Entries, array("\\AppBundle\\Model\\Reporting\\StateLevel\\CareerDistribution\\StateLevelStage6SubAnalysisEntry", "cmp_obj"));

                    $stage6_8pt0_Or_More_SubAnalysis->setSubAnalysisData($stage6_8pt0_Or_More_SubAnalysis_Entries);
                    $stage6_7pt1_to_7pt9_SubAnalysis->setSubAnalysisData($stage6_7pt1_to_7pt9_SubAnalysis_Entries);
                    $stage6_4pt0_to_7_SubAnalysis->setSubAnalysisData($stage6_4pt0_to_7_SubAnalysis_Entries);
                    $stage6_3pt1_to_3pt9_SubAnalysis->setSubAnalysisData($stage6_3pt1_to_3pt9_SubAnalysis_Entries);
                    $stage6_LessThan_3pt1_SubAnalysis->setSubAnalysisData($stage6_LessThan_3pt1_Entries);

                    $stage6Data = array();
                    $stage6Data[] = $stage6_8pt0_Or_More_SubAnalysis;
                    $stage6Data[] = $stage6_7pt1_to_7pt9_SubAnalysis;
                    $stage6Data[] = $stage6_4pt0_to_7_SubAnalysis;
                    $stage6Data[] = $stage6_3pt1_to_3pt9_SubAnalysis;
                    $stage6Data[] = $stage6_LessThan_3pt1_SubAnalysis;

                    $stage6OverallSubAnalysisTotal = 0;

                    foreach($stage6Data as $stage6_TempSubAnalysis){
                        $stage6SubOverallGL_8_And_Above = 0;
                        $stage6SubOverallPercentageGL_8_And_Above = 0;

                        foreach($stage6_TempSubAnalysis->getSubAnalysisData() as $stage6_Category_Entry){
                            $stage6SubOverallGL_8_And_Above += $stage6_Category_Entry->getTotalGL8AndAbove();
                            $stage6SubOverallPercentageGL_8_And_Above += $stage6_Category_Entry->getPercentageGL8AndAbove();
                        }

                        $stage6_TempSubAnalysis->setOverallGL8AndAbove($stage6SubOverallGL_8_And_Above);
                        $stage6_TempSubAnalysis->setOverallPercentageGL8AndAbove($stage6SubOverallPercentageGL_8_And_Above);

                        $stage6OverallSubAnalysisTotal += $stage6_TempSubAnalysis->getOverallGL8AndAbove();
                    }

                    $stage6Analysis->setStage6Data($stage6Data);
                    $stage6Analysis->setOverallSubAnalysisTotal($stage6OverallSubAnalysisTotal);

                    $report->setStage6($stage6Analysis);
                                       
                    //STAGE 7 ANALYSIS

                    $stage7Analysis = new StateLevelStage7Analysis();

                    $stage7_8pt0_Or_More_SubAnalysis = new StateLevelStage7SubAnalysis("8.0% or more");
                    $stage7_8pt0_Or_More_SubAnalysis_Entries = array();
                    $stage7_7pt1_to_7pt9_SubAnalysis = new StateLevelStage7SubAnalysis("between 7.1% and 7.9%");
                    $stage7_7pt1_to_7pt9_SubAnalysis_Entries = array();
                    $stage7_4pt0_to_7_SubAnalysis = new StateLevelStage7SubAnalysis("between 4.0% and 7.0%");
                    $stage7_4pt0_to_7_SubAnalysis_Entries = array();
                    $stage7_3pt1_to_3pt9_SubAnalysis = new StateLevelStage7SubAnalysis("between 3.1% and 3.9%");
                    $stage7_3pt1_to_3pt9_SubAnalysis_Entries = array();
                    $stage7_LessThan_3pt1_SubAnalysis = new StateLevelStage7SubAnalysis("less than 3.1%");
                    $stage7_LessThan_3pt1_Entries = array();

                    $stage7AllEntriesTempData = array();

                    //setup the temp array of stage 7 entries
                    foreach ($stage1Analysis->getStage1Data() as $stage1_entry) {

                        $stage7SubAnalysisEntry = new StateLevelStage7SubAnalysisEntry();
                        $stage7SubAnalysisEntry->setLgaCode($stage1_entry->getLgaCode());
                        $stage7SubAnalysisEntry->setLgaName($stage1_entry->getLgaName());

                        $stage7SubAnalysisEntry->setTotalGL15AndAbove($stage1_entry->calculateGL_15_And_Above());
                        $stage7SubAnalysisEntry->calculatePercentage($stage2Analysis->getOverallGL15AndAbove()); //calculate with stage 2 overal 7-15+

                        $stage7AllEntriesTempData[] = $stage7SubAnalysisEntry;

                    }

                    foreach ($stage7AllEntriesTempData as $stage7_SubAnalysis_Entry) {
                        //$stage7_SubAnalysis_Entry = new FederalLevelStage7SubAnalysisEntry(); //REMOVE LATER

                        $percentageGL15AndAbove = $stage7_SubAnalysis_Entry->getPercentageGL15AndAbove();
                        if ($percentageGL15AndAbove >= 8.0) {
                            $stage7_8pt0_Or_More_SubAnalysis_Entries[] = $stage7_SubAnalysis_Entry;
                        } else if ($percentageGL15AndAbove >= 7.1 && $percentageGL15AndAbove <= 7.9) {
                            $stage7_7pt1_to_7pt9_SubAnalysis_Entries[] = $stage7_SubAnalysis_Entry;
                        } else if ($percentageGL15AndAbove >= 4.0 && $percentageGL15AndAbove <= 7.0) {
                            $stage7_4pt0_to_7_SubAnalysis_Entries[] = $stage7_SubAnalysis_Entry;
                        } else if ($percentageGL15AndAbove >= 3.1 && $percentageGL15AndAbove <= 3.9) {
                            $stage7_3pt1_to_3pt9_SubAnalysis_Entries[] = $stage7_SubAnalysis_Entry;
                        } else if ($percentageGL15AndAbove < 3.1) {
                            $stage7_LessThan_3pt1_Entries[] = $stage7_SubAnalysis_Entry;
                        }

                        //$stage7_4pt0_Or_More_SubAnalysis_Entries[] = $stage7_SubAnalysis_Entry;
                    }

                    //sort sub analysis entries
                    usort($stage7_8pt0_Or_More_SubAnalysis_Entries, array("\\AppBundle\\Model\\Reporting\\StateLevel\\CareerDistribution\\StateLevelStage7SubAnalysisEntry", "cmp_obj"));
                    usort($stage7_7pt1_to_7pt9_SubAnalysis_Entries, array("\\AppBundle\\Model\\Reporting\\StateLevel\\CareerDistribution\\StateLevelStage7SubAnalysisEntry", "cmp_obj"));
                    usort($stage7_4pt0_to_7_SubAnalysis_Entries, array("\\AppBundle\\Model\\Reporting\\StateLevel\\CareerDistribution\\StateLevelStage7SubAnalysisEntry", "cmp_obj"));
                    usort($stage7_3pt1_to_3pt9_SubAnalysis_Entries, array("\\AppBundle\\Model\\Reporting\\StateLevel\\CareerDistribution\\StateLevelStage7SubAnalysisEntry", "cmp_obj"));
                    usort($stage7_LessThan_3pt1_Entries, array("\\AppBundle\\Model\\Reporting\\StateLevel\\CareerDistribution\\StateLevelStage7SubAnalysisEntry", "cmp_obj"));

                    $stage7_8pt0_Or_More_SubAnalysis->setSubAnalysisData($stage7_8pt0_Or_More_SubAnalysis_Entries);
                    $stage7_7pt1_to_7pt9_SubAnalysis->setSubAnalysisData($stage7_7pt1_to_7pt9_SubAnalysis_Entries);
                    $stage7_4pt0_to_7_SubAnalysis->setSubAnalysisData($stage7_4pt0_to_7_SubAnalysis_Entries);
                    $stage7_3pt1_to_3pt9_SubAnalysis->setSubAnalysisData($stage7_3pt1_to_3pt9_SubAnalysis_Entries);
                    $stage7_LessThan_3pt1_SubAnalysis->setSubAnalysisData($stage7_LessThan_3pt1_Entries);

                    $stage7Data = array();
                    $stage7Data[] = $stage7_8pt0_Or_More_SubAnalysis;
                    $stage7Data[] = $stage7_7pt1_to_7pt9_SubAnalysis;
                    $stage7Data[] = $stage7_4pt0_to_7_SubAnalysis;
                    $stage7Data[] = $stage7_3pt1_to_3pt9_SubAnalysis;
                    $stage7Data[] = $stage7_LessThan_3pt1_SubAnalysis;

                    $stage7OverallSubAnalysisTotal = 0;

                    foreach($stage7Data as $stage7_TempSubAnalysis){
                        $stage7SubOverallGL_15_And_Above = 0;
                        $stage7SubOverallPercentageGL_15_And_Above = 0;

                        foreach($stage7_TempSubAnalysis->getSubAnalysisData() as $stage7_Category_Entry){
                            $stage7SubOverallGL_15_And_Above += $stage7_Category_Entry->getTotalGL15AndAbove();
                            $stage7SubOverallPercentageGL_15_And_Above += $stage7_Category_Entry->getPercentageGL15AndAbove();
                        }

                        $stage7_TempSubAnalysis->setOverallGL15AndAbove($stage7SubOverallGL_15_And_Above);
                        $stage7_TempSubAnalysis->setOverallPercentageGL15AndAbove($stage7SubOverallPercentageGL_15_And_Above);

                        $stage7OverallSubAnalysisTotal += $stage7_TempSubAnalysis->getOverallGL15AndAbove();
                    }

                    $stage7Analysis->setStage7Data($stage7Data);
                    $stage7Analysis->setOverallSubAnalysisTotal($stage7OverallSubAnalysisTotal);

                    $report->setStage7($stage7Analysis);

                    //STAGE 8 ANALYSIS

                    $stage8Analysis = new StateLevelStage8Analysis();

                    $stage8GL8AndAboveAnalysis = new StateLevelStage8CategoryAnalysis();

                    $stage8GL8AndAboveTempData = array();

                    //setup the temp map of stage 8 subanalysis
                    foreach ($senatorialDistricts as $senatorialDistrict) {

                        $stage8GL7AndAboveSubAnalysis = new StateLevelStage8SubAnalysis();
                        $stage8GL7AndAboveSubAnalysis->setSenatorialDistrictCode($senatorialDistrict['id']);
                        $stage8GL7AndAboveSubAnalysis->setSenatorialDistrictCode($senatorialDistrict['senatorial_district_code']);
                        $stage8GL7AndAboveSubAnalysis->setSenatorialDistrictName($senatorialDistrict['senatorial_district_name']);

                        $stage8GL8AndAboveTempData[$senatorialDistrict['id']] = $stage8GL7AndAboveSubAnalysis;

                    }

                    //now do the stage 8 gl8 and above sub analysis entries
                    $overallStage8GL8AndAboveSubAnalysisTotal = 0;
                    foreach ($stage8GL8AndAboveTempData as $k => $stage8_GL8AndAboveSubAnalysis) {

                        $stage8GL8AndAboveSubAnalysisData = array();

                        $stage8GL8AndAboveSubOverallTotal = 0;
                        $stage8GL8AndAboveSubOverallPercentage = 0;

                        foreach ($senatorialDistrictLgaMap[$k] as $stage_1_zonestate) {
                            $stage8GL8AndAboveSubAnalysisEntry = new StateLevelStage8SubAnalysisEntry();
                            $stage8GL8AndAboveSubAnalysisEntry->setLgaCode($stage_1_zonestate->getLgaCode());
                            $stage8GL8AndAboveSubAnalysisEntry->setLgaName($stage_1_zonestate->getLgaName());

                            //$stage_1_zonestate = new FederalLevelStage1Entry(); //REMOVE THIS

                            $stage8GL8AndAboveSubAnalysisEntry->setTotal($stage_1_zonestate->calculateGL_8_And_Above());
                            $stage8GL8AndAboveSubAnalysisEntry->calculatePercentage($stage2Analysis->getOverallGL8To15AndAbove());

                            $stage8GL8AndAboveSubOverallTotal += $stage8GL8AndAboveSubAnalysisEntry->getTotal();
                            $stage8GL8AndAboveSubOverallPercentage += $stage8GL8AndAboveSubAnalysisEntry->getPercentage();

                            $stage8GL8AndAboveSubAnalysisData[] = $stage8GL8AndAboveSubAnalysisEntry;
                        }

                        //sort sub analysis entries
                        usort($stage8GL8AndAboveSubAnalysisData, array("\\AppBundle\\Model\\Reporting\\StateLevel\\CareerDistribution\\StateLevelStage8SubAnalysisEntry", "cmp_obj"));

                        $stage8_GL8AndAboveSubAnalysis->setOverallTotal($stage8GL8AndAboveSubOverallTotal);
                        $stage8_GL8AndAboveSubAnalysis->setOverallPercentage(number_format($stage8GL8AndAboveSubOverallPercentage));

                        $overallStage8GL8AndAboveSubAnalysisTotal += $stage8_GL8AndAboveSubAnalysis->getOverallTotal();

                        $stage8_GL8AndAboveSubAnalysis->setSubAnalysisData($stage8GL8AndAboveSubAnalysisData);
                    }

                    $stage8GL8AndAboveData = array_values($stage8GL8AndAboveTempData);

                    //sort sub analysis
                    usort($stage8GL8AndAboveData, array("\\AppBundle\\Model\\Reporting\\StateLevel\\CareerDistribution\\StateLevelStage8SubAnalysis", "cmp_obj"));

                    $stage8GL8AndAboveAnalysis->setStage8CategoryData($stage8GL8AndAboveData);
                    $stage8GL8AndAboveAnalysis->setOverallCategorySubAnalysisTotal($overallStage8GL8AndAboveSubAnalysisTotal);

                    $stage8Analysis->setStage8GL8AndAboveData($stage8GL8AndAboveAnalysis);

                    //now calculate for STAGE 8 GL 15 AND ABOVE
                    $stage8GL15AndAboveAnalysis = new StateLevelStage8CategoryAnalysis();

                    $stage8GL15AndAboveTempData = array();

                    //setup the temp map of stage 8 subanalysis
                    foreach ($senatorialDistricts as $senatorialDistrict) {

                        $stage8GL15AndAboveSubAnalysis = new StateLevelStage8SubAnalysis();
                        $stage8GL15AndAboveSubAnalysis->setSenatorialDistrictCode($senatorialDistrict['id']);
                        $stage8GL15AndAboveSubAnalysis->setSenatorialDistrictCode($senatorialDistrict['senatorial_district_code']);
                        $stage8GL15AndAboveSubAnalysis->setSenatorialDistrictName($senatorialDistrict['senatorial_district_name']);

                        $stage8GL15AndAboveTempData[$senatorialDistrict['id']] = $stage8GL15AndAboveSubAnalysis;

                    }

                    //now do the stage 8 gl5 and above sub analysis entries
                    $overallStage8GL15AndAboveSubAnalysisTotal = 0;
                    foreach ($stage8GL15AndAboveTempData as $k => $stage8_GL15AndAboveSubAnalysis) {

                        $stage8GL15AndAboveSubAnalysisData = array();

                        $stage8GL15AndAboveSubOverallTotal = 0;
                        $stage8GL15AndAboveSubOverallPercentage = 0;

                        foreach ($senatorialDistrictLgaMap[$k] as $stage_1_zonestate) {
                            $stage8GL15AndAboveSubAnalysisEntry = new StateLevelStage8SubAnalysisEntry();
                            $stage8GL15AndAboveSubAnalysisEntry->setLgaCode($stage_1_zonestate->getLgaCode());
                            $stage8GL15AndAboveSubAnalysisEntry->setLgaName($stage_1_zonestate->getLgaName());

                            //$stage_1_zonestate = new FederalLevelStage1Entry(); //REMOVE THIS

                            $stage8GL15AndAboveSubAnalysisEntry->setTotal($stage_1_zonestate->calculateGL_15_And_Above());
                            $stage8GL15AndAboveSubAnalysisEntry->calculatePercentage($stage2Analysis->getOverallGL15AndAbove());

                            $stage8GL15AndAboveSubOverallTotal += $stage8GL15AndAboveSubAnalysisEntry->getTotal();
                            $stage8GL15AndAboveSubOverallPercentage += $stage8GL15AndAboveSubAnalysisEntry->getPercentage();

                            $stage8GL15AndAboveSubAnalysisData[] = $stage8GL15AndAboveSubAnalysisEntry;
                        }
                        //sort sub analysis entries
                        usort($stage8GL15AndAboveSubAnalysisData, array("\\AppBundle\\Model\\Reporting\\StateLevel\\CareerDistribution\\StateLevelStage8SubAnalysisEntry", "cmp_obj"));

                        $stage8_GL15AndAboveSubAnalysis->setOverallTotal($stage8GL15AndAboveSubOverallTotal);
                        $stage8_GL15AndAboveSubAnalysis->setOverallPercentage(number_format($stage8GL15AndAboveSubOverallPercentage));

                        $overallStage8GL15AndAboveSubAnalysisTotal += $stage8_GL15AndAboveSubAnalysis->getOverallTotal();

                        $stage8_GL15AndAboveSubAnalysis->setSubAnalysisData($stage8GL15AndAboveSubAnalysisData);
                    }

                    $stage8GL15AndAboveData = array_values($stage8GL15AndAboveTempData);

                    //sort sub analysis
                    usort($stage8GL15AndAboveData, array("\\AppBundle\\Model\\Reporting\\StateLevel\\CareerDistribution\\StateLevelStage8SubAnalysis", "cmp_obj"));

                    $stage8GL15AndAboveAnalysis->setStage8CategoryData($stage8GL15AndAboveData);
                    $stage8GL15AndAboveAnalysis->setOverallCategorySubAnalysisTotal($overallStage8GL15AndAboveSubAnalysisTotal);

                    $stage8Analysis->setStage8GL15AndAboveData($stage8GL15AndAboveAnalysis);

                    $report->setStage8($stage8Analysis);
                }
            }

        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        } finally {
            if ($statement) {
                $statement->closeCursor();
            }
        }

        return $report;
    }
}