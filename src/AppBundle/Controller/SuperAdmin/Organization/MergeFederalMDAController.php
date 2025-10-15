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

class MergeFederalMDAController extends Controller
{
    /**
     * @Route("/super_admin/federal_mda/merge",name="merge_federal_mda")
     */
    public function mergeAction(Request $request)
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $loggedInUser = $this->getUser();

        $alertNotification = new AlertNotification();

        $sharedDataManager = $this->get('app.shared_data_manager');

        $organizations = $sharedDataManager->getOrganizationByType2('FEDERAL');

        return $this->render('super_admin/organization/merge_federal_mda.html.twig',
            array(
                'organizations' => $organizations,
                'alertNotification' => $alertNotification
            )
        );
    }

}