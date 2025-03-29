<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

class DirectEmailTestController extends AbstractController
{
    /**
     * Route to test direct email functionality
     */
    #[Route('/admin/direct-test-email', name: 'admin_direct_test_email')]
    public function testEmail(MailerInterface $mailer): Response
    {
        $testEmail = 'ronald.rukund@gmail.com';

        $output = "Starting direct email test...\n";
        $output .= "Testing email to: " . $testEmail . "\n";

        try {
            // Create a simple email
            $email = (new Email())
                ->from('rukundoronaldo4@gmail.com')
                ->to($testEmail)
                ->subject('Direct Test from Symfony Controller')
                ->text('This is a direct test email from Symfony controller, bypassing the EmailService.');

            // Send email
            $mailer->send($email);

            $output .= "Email sent successfully!";

            return new Response('<html><body><pre>' . $output . '</pre></body></html>');
        } catch (\Exception $e) {
            $output .= "Email test failed: " . $e->getMessage() . "\n";
            $output .= "Stack trace: \n" . $e->getTraceAsString();

            return new Response(
                '<html><body><h1>Email Test Failed</h1><pre>' . $output . '</pre></body></html>',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}