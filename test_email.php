<?php
// direct_email_test.php
require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Mailer;

// Hardcoded DSN - no environment variables
$dsn = 'smtp://rukundoronaldo4@gmail.com:yyxk%20cine%20fjtz%20eurw@smtp.gmail.com:587?encryption=tls&auth_mode=login';
$transport = Transport::fromDsn($dsn);

// Create a simple email
$email = (new Email())
    ->from('rukundoronaldo4@gmail.com')
    ->to('rukundoronaldo4@gmail.com') // Send to yourself for testing
    ->subject('Direct Test Email')
    ->text('This is a direct test email sent from a standalone PHP script');

// Send Email
try {
    $mailer = new Mailer($transport);
    $result = $mailer->send($email);
    echo "Email sent successfully!\n";
} catch (\Exception $e) {
    echo "Failed to send email: " . $e->getMessage() . "\n";
    echo "Error type: " . get_class($e) . "\n";
}