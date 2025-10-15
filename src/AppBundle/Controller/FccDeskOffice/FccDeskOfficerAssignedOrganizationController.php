<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/22/2017
 * Time: 8:35 AM
 */

namespace AppBundle\Controller\FccDeskOffice;


use AppBundle\AppException\AppException;
use AppBundle\Model\SearchCriteria\OrganizationSearchCriteria;
use AppBundle\Model\Users\UserProfile;
use AppBundle\Utils\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class FccDeskOfficerAssignedOrganizationController extends Controller
{

    /**
     * @Route("/secure_area/federal/fcc_desk_officer/assigned_organizations/list", name="fcc_desk_officer_assigned_organizations_list")
     */
    public function listAssignedOrganizationsAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var UserProfile $loggedInUser
         */
        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        $searchFilter = new OrganizationSearchCriteria('');
        $searchFilter->setFccCommittee($loggedInUser->getFccCommitteeId());

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

        $records = array();
        $organizationService = $this->get('app.fcc_desk_office_assigned_organization_service');
        try {
            $records = $organizationService->searchRecords($searchFilter, $paginator, $pageDirection);
        } catch (AppException $app_exc) {

        }

        return $this->render('secure_area/fcc_desk_officer/fcc_desk_officer_assigned_organizations_list.html.twig',
            array('records' => $records,
                'searchFilter' => $searchFilter,
                'paginator' => $paginator
            )
        );
    }

}