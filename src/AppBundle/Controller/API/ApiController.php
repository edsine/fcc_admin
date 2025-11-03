<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 2/3/2017
 * Time: 9:57 AM
 */

namespace AppBundle\Controller\API;


use AppBundle\AppException\AppException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends Controller
{

    /**
     * @Route("/notification/total/unread/messages/{userProfileId}", name="api_total_unread_messages", defaults={"userProfileId":"0"})
     */
    public function totalUnreadMessagesAction($userProfileId)
    {
        $notificationService = $this->get('app.notification_service');

        $totalUnreadMessages = '--';

        try{
            $totalUnreadMessages = $notificationService->getTotalUserUnreadMessage($userProfileId);
        }catch(AppException $e){

        }

        $response = new Response($totalUnreadMessages);
        return $response;
    }

    /**
     * @Route("/api/mda/processed/years/{organizationId}", name="api_mda_processed_years", defaults={"organizationId":"0"})
     */
    public function mdaProcessedYears($organizationId)
    {
        $processedYears = $this->get('app.shared_data_service')->getSubmissionYearsProcessedForOrganization($organizationId);

        if ($processedYears) {
            $options = '<option value="">Choose Year</option>';
            foreach ($processedYears as $processedYear) {
                $options .= "<option value=\"{$processedYear['submission_year']}\">{$processedYear['submission_year']}</option>";
            }
        }else{
            $options = '<option value="">No Processed Year Found</option>';
        }

        $response = new Response($options);
        return $response;
    }

}