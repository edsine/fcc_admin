<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 2/22/2017
 * Time: 1:17 PM
 */

namespace AppBundle\Controller\StaticCMS;

use AppBundle\AppException\AppException;
use AppBundle\Model\Notification\AlertNotification;
use AppBundle\Model\StaticCMS\StaticCMS;
use AppBundle\Utils\AppConstants;
use AppBundle\Validation\Validator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class StaticCMSController extends Controller
{

    /**
     * @Route("/super_admin/static_cms/list", name="static_cms_list")
     */
    public function listAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $records = array();
        $staticCMSService = $this->get('app.static_cms_service');
        try {
            $records = $staticCMSService->getStaticContents();
        } catch (AppException $e) {
        }

        return $this->render('super_admin/static_cms/static_cms_list.html.twig',
            array(
                'records' => $records
            )
        );
    }

    /**
     * @Route("/super_admin/static_cms/{guid}/edit",name="static_cms_edit")
     */
    public function editAction($guid, Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$loggedInUser) {
            throw $this->createAccessDeniedException();
        }

        $staticContent = new StaticCMS();

        $validator = new Validator();
        $this->initializeValidationFields($validator, AppConstants::EDIT);

        $alertNotification =  new AlertNotification();
        $outcome = false;

        $staticCMSService = $this->get('app.static_cms_service');

        if (!$request->request->has("btn_submit")) {//first page load

            try {
                $staticContent = $staticCMSService->getStaticContent($guid);
            } catch (AppException $app_ex) {
                $this->get('logger')->info($app_ex->getMessage());
            }

            if (!$staticContent) {
                $staticContent = new StaticCMS();
                $alertNotification->addError('Content could not be loaded, go back and try again.');
            }

        } else if ($request->request->has("btn_submit")) {

            $staticContent = $this->fillModelFromRequest($request);
            $this->validateForm($validator, $staticContent, AppConstants::EDIT);

            if (!$validator->getFields()->hasErrors()) {

                $today = date("Y-m-d H:i:s");
                $staticContent->setLastModified($today);
                $staticContent->setLastModifiedByUserId($loggedInUser->getId());

                try {
                    $outcome = $staticCMSService->editStaticContent($staticContent);
                    if ($outcome) {
                        $alertNotification->addSuccess('Static content updated successfully');
                        $this->get('app.shared_data_manager')->removeCacheItem(AppConstants::KEY_CACHED_STATIC_CONTENT);
                    }
                } catch (AppException $app_exc) {
                    $errorMessage = "An error occurred, Try again.";
                    $alertNotification->addError($errorMessage);
                } catch (\Exception $e) {

                }
            }

        }

        $htmlPrint = htmlspecialchars($staticContent->getContent());

        return $this->render('super_admin/static_cms/static_cms_edit.html.twig',
            array(
                'staticContent' => $staticContent,
                'validator' => $validator,
                'outcome' => $outcome,
                'alertNotification' => $alertNotification,
                'htmlPrint' => $htmlPrint
            )
        );
    }

    //helper methods
    private function initializeValidationFields(Validator $validator, string $which)
    {
        $validator->getFields()->addField('content', "Content is required");

        switch ($which) {
            case AppConstants::EDIT:
                $validator->getFields()->addField('guid', "Invalid record identifier");
                break;

            case AppConstants::DELETE:
                $validator->getFields()->addField('guid', "Invalid record identifier");
                break;
        }
    }

    private function validateForm(Validator $validator, StaticCMS $staticContent, string $which)
    {
        $validator->required('content', $staticContent->getContent());

        switch ($which) {
            case AppConstants::EDIT:
                $validator->required('guid', $staticContent->getGuid());
                break;

            case AppConstants::DELETE:
                $validator->required('guid', $staticContent->getGuid());
                break;
        }
    }

    private function fillModelFromRequest(Request $request)
    {
        $staticContent = new StaticCMS();
        $staticContent->setDescription($request->request->get("description", ""));
        $staticContent->setContent($request->request->get("content", ""));
        $staticContent->setRichText($request->request->get("is_rich_text", ""));
        $staticContent->setGuid($request->request->get("guid", ""));

        return $staticContent;
    }

}