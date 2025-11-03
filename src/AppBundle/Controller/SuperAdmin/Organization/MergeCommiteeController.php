<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 12/19/2016
 * Time: 8:47 PM
 */

namespace AppBundle\Controller\SuperAdmin\Organization;

use AppBundle\Model\Notification\AlertNotification;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use \Exception;

class MergeCommiteeController extends Controller
{
    /**
     * @Route("/super_admin/committee/merge",name="merge_committee")
     */
    public function mergeAction(Request $request)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->getUser();

        $alertNotification = new AlertNotification();

        $sharedDataManager = $this->get('app.shared_data_manager');

        $committees = $sharedDataManager->getCommittees();

        return $this->render('super_admin/organization/merge_committees.html.twig',
            array(
                'committees' => $committees,
                'alertNotification' => $alertNotification
            )
        );
    }

}