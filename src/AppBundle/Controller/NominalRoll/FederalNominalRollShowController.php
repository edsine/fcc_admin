<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/27/2016
 * Time: 8:41 AM
 */

namespace AppBundle\Controller\NominalRoll;


use AppBundle\AppException\AppException;
use AppBundle\Model\SearchCriteria\NominalRoleSearchCriteria;
use AppBundle\Model\Users\UserProfile;
use AppBundle\Utils\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class FederalNominalRollShowController extends Controller
{

    /**
     * @Route("secure_area/federal/nominal_roll/show/{submissionId}", name="federal_nominal_roll_show", defaults={"submissionId" : ""})
     */
    public function showFederalNominalRollAction(Request $request, $submissionId)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var UserProfile $loggedInUser
         */
        $loggedInUser = $this->getUser();

        //if there is no sbumission id route param, fetch from the form
        if(!$submissionId){
            $submissionId = $request->request->get('submissionId');
            if(!$submissionId){
                $submissionId = $request->query->get('submissionId');
            }
            if(!$submissionId){
                return $this->redirectToRoute('dashboard');
            }
        }

        $searchFilter = new NominalRoleSearchCriteria();
        $searchFilter->setSubmissionId($submissionId);
        $searchFilter->setEmployeeNumber($request->request->get('searchEmployeeNumber'));
        $searchFilter->setName($request->request->get('searchName'));
        $searchFilter->setNationality($request->request->get('searchNationality'));
        $searchFilter->setStateOfOrigin($request->request->get('searchStateOfOrigin'));
        $searchFilter->setGradeLevel($request->request->get('searchGradeLevel'));
        $searchFilter->setGeoPoliticalZone($request->request->get('searchGeoPoliticalZone'));

        $paginator = new Paginator();
        $paginator->setStartRow($request->request->get('startRow', 0));

        $pageDirection = '';
        if ($request->request->has('btnSearch')) {
            $pageDirection = "FIRST";
        } else if ($request->request->has('btnPageFirst')) {
            $pageDirection = "FIRST";
        } else if ($request->request->has('btnPagePrev')) {
            $pageDirection = "PREVIOUS";
        } else if ($request->request->has('btnPageNext')) {
            $pageDirection = "NEXT";
        } else if ($request->request->has('btnPageLast')) {
            $pageDirection = "LAST";
        }

        $federalNominalRollService = $this->get('app.federal_nominal_roll_service');

        $records = array();
        $nominalRollSubmission = $federalNominalRollService->getSubmission($submissionId);
        try {
            $records = $federalNominalRollService->searchPassedOrConfirmedNominalRollSubmissionDetail($nominalRollSubmission,$searchFilter, $paginator, $pageDirection);
        } catch (AppException $e) {
        }

        return $this->render('secure_area/federal/nominal_roll/federal_nominal_roll_show.html.twig',
            array(
                'nominalRollSubmission' => $nominalRollSubmission,
                'records' => $records,
                'paginator' => $paginator
            )
        );
    }


    /**
     * @Route("secure_area/federal/nominal_roll/failed_validation/show/{submissionId}", name="federal_nominal_roll_failed_validation_show", defaults={"submissionId" : ""})
     */
    public function showFailedValidationAction(Request $request, $submissionId)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        //if there is no sbumission id route param, fetch from the form
        if(!$submissionId){
            $submissionId = $request->request->get('submissionId');
            if(!$submissionId){
                $submissionId = $request->query->get('submissionId');
            }
            if(!$submissionId){
                return $this->redirectToRoute('dashboard');
            }
        }

        $paginator = new Paginator();
        $paginator->setStartRow($request->request->get('startRow', 0));

        $pageDirection = '';
        if ($request->request->has('btnSearch')) {
            $pageDirection = "FIRST";
        } else if ($request->request->has('btnPageFirst')) {
            $pageDirection = "FIRST";
        } else if ($request->request->has('btnPagePrev')) {
            $pageDirection = "PREVIOUS";
        } else if ($request->request->has('btnPageNext')) {
            $pageDirection = "NEXT";
        } else if ($request->request->has('btnPageLast')) {
            $pageDirection = "LAST";
        }

        $federalNominalRollService = $this->get('app.federal_nominal_roll_service');

        $records = array();
        $nominalRollSubmission = $federalNominalRollService->getSubmission($submissionId);
        try {
            $records = $federalNominalRollService->searchFailedValidationDetail($submissionId, $paginator, $pageDirection);
        } catch (AppException $e) {
        }

        return $this->render('secure_area/federal/nominal_roll/federal_nominal_roll_failed_validation_show.html.twig',
            array(
                'nominalRollSubmission' => $nominalRollSubmission,
                'records' => $records,
                'paginator' => $paginator
            )
        );
    }
}