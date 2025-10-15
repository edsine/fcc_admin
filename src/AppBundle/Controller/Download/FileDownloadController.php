<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 1/21/2017
 * Time: 5:14 PM
 */

namespace AppBundle\Controller\Download;


use AppBundle\Utils\FileUploadHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class FileDownloadController extends Controller
{

    /**
     * @Route("/downloads/nominal_role/{submissionId}/download", name="nominal_role_download")
     */
    public function downloadNominalRoleAction(Request $request, $submissionId)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $mdaSubmissionService = $this->get('app.federal_nominal_roll_service');

        $nominalRoleSubmission = $mdaSubmissionService->getSubmission($submissionId);

        if($nominalRoleSubmission){

            $fileUploadHelper = new FileUploadHelper();

            $establishmentCode = $nominalRoleSubmission->getOrganizationEstablishmentCode();

            $file = $fileUploadHelper->getNominalRollUploadDirectory($establishmentCode) . $nominalRoleSubmission->getUploadedFileName();
            $response = new BinaryFileResponse($file);

            $response->headers->set('Cache-Control', 'no-cache, must-revalidate');
            $response->headers->set('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT');
            //$response->headers->set('Content-Type', 'application/force-download');
            $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $response->headers->set('Content-Description', 'File Transfer');
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $nominalRoleSubmission->getSimpleFileName()
            );

            return $response;

        }

        return new Response('hi there');
    }

    /**
     * @Route("/downloads/template", name="template_download")
     */
    public function nominalRoleTemplateDownloadAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('downloads/template_download.html.twig');
    }

}