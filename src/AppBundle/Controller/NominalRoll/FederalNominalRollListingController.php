<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/27/2016
 * Time: 8:41 AM
 */

namespace AppBundle\Controller\NominalRoll;


use AppBundle\AppException\AppException;
use AppBundle\Model\SearchCriteria\NominalRollSubmissionSearchCriteria;
use AppBundle\Model\Users\UserProfile;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class FederalNominalRollListingController extends Controller
{

    //******************************** FEDERAL MDA ******************************************************

    /**
     * @Route("/secure_area/federal/mda_admin/nominal_roll/main/submission/list", name="federal_mda_admin_nominal_roll_main_submission_list")
     */
    public function federalMdaAdminNominalRollMainSubmissionListAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var UserProfile $loggedInUser
         */
        $loggedInUser = $this->getUser();

        $searchFilter = new NominalRollSubmissionSearchCriteria();
        $searchFilter->setOrganizationId($loggedInUser->getOrganizationId());
        $searchFilter->setSubmissionYear($request->request->get('searchSubmissionYear'));
        $searchFilter->setSubmissionType(AppConstants::MAIN_SUBMISSION);

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
        try {
            $records = $federalNominalRollService->searchNominalRollUploads($searchFilter, $paginator, $pageDirection);
        } catch (AppException $e) {
            $this->get('logger')->info($e->getMessage());
        }


        return $this->render('secure_area/federal/nominal_roll/mda_admin/federal_mda_admin_nominal_roll_main_submission_list.html.twig',
            array(
                'records' => $records,
                'searchFilter' => $searchFilter,
                'paginator' => $paginator
            )
        );
    }

    /**
     * @Route("/secure_area/federal/mda_admin/nominal_roll/quarterly/return/list", name="federal_mda_admin_nominal_roll_quarterly_return_list")
     */
    public function federalMdaAdminNominalRollQuarterlyReturnListAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var UserProfile $loggedInUser
         */
        $loggedInUser = $this->getUser();

        $searchFilter = new NominalRollSubmissionSearchCriteria();
        $searchFilter->setOrganizationId($loggedInUser->getOrganizationId());
        $searchFilter->setSubmissionYear($request->request->get('searchSubmissionYear'));
        $searchFilter->setSubmissionType(AppConstants::QUARTERLY_RETURN);

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
        try {
            $records = $federalNominalRollService->searchNominalRollUploads($searchFilter, $paginator, $pageDirection);
        } catch (AppException $e) {
            $this->get('logger')->info($e->getMessage());
        }


        return $this->render('secure_area/federal/nominal_roll/mda_admin/federal_mda_admin_nominal_roll_quarterly_return_list.html.twig',
            array(
                'records' => $records,
                'searchFilter' => $searchFilter,
                'paginator' => $paginator
            )
        );
    }
}