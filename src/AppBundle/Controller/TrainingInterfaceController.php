<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 10/16/2017
 * Time: 4:56 PM
 */

namespace AppBundle\Controller;


use AppBundle\AppException\AppException;
use AppBundle\AppException\AppExceptionMessages;
use AppBundle\Model\Organizations\Organization;
use AppBundle\Model\Users\UserProfile;
use AppBundle\Utils\AppConstants;
use AppBundle\Utils\GUIDHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TrainingInterfaceController extends Controller
{

    /**
     * @Route("/integration/organization", name="integrate_attending_organization")
     */
    public function integrateOrganizationAction(Request $request)
    {

        $errorMessage = '';

        $mdaJson = $request->request->get('mda_json');

        $logger = $this->get('logger');

        if ($mdaJson) {

            $logger->alert("GOT MDA JSON: " . $mdaJson);

            try {

                //try to parse the json
                $mdaObjArray = json_decode($mdaJson, true);

                if ($mdaObjArray) {

                    $organization = new Organization();
                    $organization->setOrganizationName($mdaObjArray['mdaName']);
                    $organization->setEstablishmentCode($mdaObjArray['mdaCode']);
                    $organization->setEstablishmentMnemonic($mdaObjArray['mdaAbbrev']);
                    //$organization->setFccDeskOfficerId('478'); //demo training desk officer

                    $organization->setStatus(AppConstants::ACTIVE);
                    $organization->setLevelOfGovernment(AppConstants::FEDERAL);
                    $organization->setEstablishmentTypeId(AppConstants::FEDERAL_MINISTRY_ESTABLISHMENT);

                    $today = date("Y-m-d H:i:s");
                    $organization->setLastModified($today);
                    $organization->setLastModifiedByUserId('1'); //set it to sysadmin user id

                    $guidHelper = new GUIDHelper();
                    $organization->setGuid($guidHelper->getGUIDUsingMD5());

                    //SETUP THE MDA ADMIN
                    $userProfile = new UserProfile();
                    $userProfile->setProfileType(AppConstants::MDA_USER_PROFILE);
                    $userProfile->setUsername($organization->getEstablishmentCode());
                    $userProfile->setPlainPassword($userProfile->getUsername());
                    $userProfile->setPrimaryRole(AppConstants::ROLE_MDA_ADMIN);
                    $userProfile->setStatus(AppConstants::ACTIVE);
                    $userProfile->setFirstLogin(AppConstants::N);
                    $userProfile->setLastModified($today);
                    $userProfile->setLastModifiedByUserId('1');
                    $userProfile->setGuid($guidHelper->getGUIDUsingMD5());

                    $encoder = $this->get('security.password_encoder');
                    $encryptedPassword = $encoder->encodePassword($userProfile, $userProfile->getPlainPassword());
                    $userProfile->setPassword($encryptedPassword);
                    //END SETUP MDA ADMIN

                    $trainingIntegrationService = $this->get('app.training_integrator');

                    $outcome = $trainingIntegrationService->integrateOrganization($organization, $userProfile);
                    if ($outcome) {
                        $successResponse = new JsonResponse();
                        $successResponse->setData(array(
                            'status' => 'success',
                            'message' => 'Integration Successful'
                        ));
                        return $successResponse;
                    }


                } else {
                    $errorMessage = 'Malformed Json String';
                }

            } catch (AppException $e) {
                $errorMessage = $e->getMessage();
                /*switch ($errorMessage) {
                    case AppExceptionMessages::MDA_NAME_MISMATCH:
                        break;

                    default:
                        $errorMessage = AppExceptionMessages::GENERAL_ERROR_MESSAGE;
                        break;
                }*/
            }

        } else {
            $errorMessage = 'Empty Json Object';
        }

        $errorResponse = new JsonResponse();
        $errorResponse->setData(array(
            'status' => 'error',
            'message' => $errorMessage
        ));
        return $errorResponse;

    }

}