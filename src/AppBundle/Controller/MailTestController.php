<?php
// src/AppBundle/Controller/MailTestController.php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailTestController extends Controller
{
    /**
     * @Route("/mail-test", name="mail-test")
     */
    public function testMailAction()
    {
        // Load config from parameters.yml
        $smtpHost       = $this->getParameter('smtp_host');
        $smtpUser       = $this->getParameter('smtp_user');
        $smtpPassword   = $this->getParameter('smtp_password');
        $smtpPort       = $this->getParameter('smtp_port');
        $smtpEncryption = $this->getParameter('smtp_encryption');

        $mail = new PHPMailer(true); // Enable exceptions

        try {
            // SMTP settings from config
            $mail->isSMTP();
            $mail->Host       = $smtpHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtpUser;
            $mail->Password   = $smtpPassword;
            $mail->SMTPSecure = $smtpEncryption;
            $mail->Port       = $smtpPort;

            // Recipients
            $mail->setFrom($smtpUser, 'Admin');
            $mail->addAddress('no-reply@niwaoptima.com.ng', 'No Reply');

            // Email content
            $mail->isHTML(true);
            $mail->Subject = 'Test Email from Config';
            $mail->Body    = '<p>This is a test email using PHPMailer with config values.</p>';

            $mail->send();

            return new Response('✅ Email sent using config values!');
        } catch (Exception $e) {
            return new Response('❌ Mailer Error: ' . $mail->ErrorInfo);
        }
    }
}
