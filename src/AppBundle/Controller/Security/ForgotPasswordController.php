<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/22/2017
 * Time: 8:49 AM
 */

namespace AppBundle\Controller\Security;


use AppBundle\AppException\AppException;
use AppBundle\Model\Notification\AlertNotification;
use AppBundle\Validation\Validator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ForgotPasswordController extends Controller
{

    /**
     * @Route("/public/reset_password",name="reset_password")
     */
    public function resetPasswordAction(Request $request)
    {

        $validator = new Validator();
        $validator->getFields()->addField('email', "Invalid email address");

        $alertNotification =  new AlertNotification();

        $userEmail = '';
        $outcome = false;

        if ($request->request->has("btnSubmit")) {

            $userEmail = $request->request->get('email','');
            $validator->email('email', $userEmail);

            if (!$validator->getFields()->hasErrors()) {

                try {
                    $outcome = $this->get('app.manage_profile_service')->checkUserEmail($userEmail);
                    if ($outcome) {
                        $alertNotification->addSuccess('You new password has been reset and sent to you email successfully');
                    }else{
                        $alertNotification->addError('A user with this email address was not found');
                    }
                } catch (AppException $app_exc) {
                    $errorMessage = "An error occured, Try again.";
                    $alertNotification->addError($errorMessage);
                }
            }

        }

        return $this->render("security/reset_password.v3.html.twig",
            array(
                'userEmail' => $userEmail,
                'alertNotification' => $alertNotification,
                'outcome' => $outcome,
                'validator' => $validator
            ));
    }

}