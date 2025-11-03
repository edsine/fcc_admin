<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 2/23/2017
 * Time: 3:02 AM
 */

namespace AppBundle\Controller\Reporting;


use AppBundle\AppException\AppException;
use AppBundle\Model\Notification\AlertNotification;
use AppBundle\Model\SearchCriteria\RecruitmentAdvertSearchCriteria;
use AppBundle\Model\Security\SecurityUser;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\FileUploadHelper;
use AppBundle\Utils\GUIDHelper;
use AppBundle\Utils\Paginator;
use AppBundle\Validation\Field;
use AppBundle\Validation\Validator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PublicVacancyController extends Controller
{

    /**
     * @Route("/public/vacancy/list", name="public_vacancy_list")
     */
    public function listAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /**
         * @var SecurityUser $loggedInUser
         */

        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        $searchFilter = new RecruitmentAdvertSearchCriteria();
        $searchFilter->setTitle($request->request->get('search_title'));
        $searchFilter->setOrganizationId($request->request->get('search_organization'));
        $searchFilter->setStartDate($request->request->get('search_start_date'));
        $searchFilter->setEndDate($request->request->get('search_end_date'));

        $paginator = new Paginator();
        $paginator->setStartRow($request->request->get('start_row', 0));

        $pageDirection = '';
        if ($request->request->has('btn_search')) {
            $pageDirection = "FIRST";
        } else if ($request->request->has('btn_page_first')) {
            $pageDirection = "FIRST";
        } else if ($request->request->has('btn_page_prev')) {
            $pageDirection = "PREVIOUS";
        } else if ($request->request->has('btn_page_next')) {
            $pageDirection = "NEXT";
        } else if ($request->request->has('btn_page_last')) {
            $pageDirection = "LAST";
        }

        $records = array();
        $mdaVacancyService = $this->get('app.mda_vacancy_service');
        try {
            $records = $mdaVacancyService->searchRecords($searchFilter, $paginator, $pageDirection);
        } catch (AppException $e) {
            $this->get('logger')->info($e->getMessage());
        }

        $organizations = $this->get('app.shared_data_manager')->getOrganizationByType2("FEDERAL");

        return $this->render('reporting/vacancy/public_vacancy_list.html.twig',
            array('records' => $records,
                'organizations' => $organizations,
                'searchFilter' => $searchFilter,
                'paginator' => $paginator
            )
        );
    }

}