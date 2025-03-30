<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Twig\Environment;
use Twig\Error\Error as TwigError;

class EmailService
{
    private MailerInterface $mailer;
    private Environment $twig;
    private string $fromEmail;
    private ?LoggerInterface $logger;

    public function __construct(
        MailerInterface $mailer,
        Environment $twig,
        string $fromEmail,
        LoggerInterface $logger = null
    ) {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->fromEmail = $fromEmail;
        $this->logger = $logger;
    }

    /**
     * Send an email using a Twig template
     *
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $template Path to Twig template
     * @param array $context Context variables for the template
     * @param string|null $fromName Optional sender name
     * @param string|null $replyTo Optional reply-to address
     * @param array $attachments Optional attachments [['path' => string, 'name' => string, 'mimeType' => string]]
     * @throws \Exception Wraps any errors in sending the email
     */
    public function sendEmail(
        string $to,
        string $subject,
        string $template,
        array $context = [],
        ?string $fromName = null,
        ?string $replyTo = null,
        array $attachments = []
    ): void {
        try {
            // Debug output (remove in production)
            error_log("Attempting to send email to: {$to}");
            error_log("Using template: {$template}");
            error_log("From email configured as: {$this->fromEmail}");

            // Render the HTML content
            try {
                $htmlBody = $this->twig->render($template, $context);
                // Debug output (remove in production)
                error_log("Template rendered successfully");
            } catch (TwigError $e) {
                if ($this->logger) {
                    $this->logger->error('Failed to render email template', [
                        'template' => $template,
                        'error' => $e->getMessage(),
                        'context' => array_keys($context)
                    ]);
                }
                throw new \Exception('Failed to render email template: ' . $e->getMessage(), 0, $e);
            }

            // Create a simple text version from the HTML
            $textBody = strip_tags($htmlBody);

            // Configure the email
            $email = (new Email())
                ->subject($subject)
                ->to($to)
                ->html($htmlBody)
                ->text($textBody);

            // Set the sender
            if ($fromName) {
                $email->from(new Address($this->fromEmail, $fromName));
                error_log("From with name: {$this->fromEmail}, {$fromName}");
            } else {
                $email->from($this->fromEmail);
                error_log("From email: {$this->fromEmail}");
            }

            // Set reply-to if provided
            if ($replyTo) {
                $email->replyTo($replyTo);
            }

            // Add attachments if any
            foreach ($attachments as $attachment) {
                if (isset($attachment['path']) && file_exists($attachment['path'])) {
                    $email->attachFromPath(
                        $attachment['path'],
                        $attachment['name'] ?? basename($attachment['path']),
                        $attachment['mimeType'] ?? null
                    );
                }
            }

            // Send the email
            try {
                error_log("About to send email...");
                $this->mailer->send($email);
                error_log("Email sent successfully according to mailer");

                if ($this->logger) {
                    $this->logger->info('Email sent successfully', [
                        'to' => $to,
                        'subject' => $subject
                    ]);
                }
            } catch (TransportExceptionInterface $e) {
                if ($this->logger) {
                    $this->logger->error('Transport error while sending email', [
                        'to' => $to,
                        'error' => $e->getMessage(),
                        'code' => $e->getCode()
                    ]);
                }
                throw new \Exception('Failed to send email: ' . $e->getMessage(), 0, $e);
            }
        } catch (TwigError $e) {
            if ($this->logger) {
                $this->logger->error('Email template error', [
                    'template' => $template,
                    'error' => $e->getMessage()
                ]);
            }
            throw new \Exception('Failed to render email template: ' . $e->getMessage(), 0, $e);
        } catch (TransportExceptionInterface $e) {
            if ($this->logger) {
                $this->logger->error('Email transport error', [
                    'to' => $to,
                    'error' => $e->getMessage()
                ]);
            }
            throw new \Exception('Failed to send email: ' . $e->getMessage(), 0, $e);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Unexpected error sending email', [
                    'to' => $to,
                    'error' => $e->getMessage()
                ]);
            }
            throw $e;
        }
    }

    /**
     * Try to send an email without throwing exceptions
     *
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $template Path to Twig template
     * @param array $context Context variables for the template
     * @param string|null $fromName Optional sender name
     * @param string|null $replyTo Optional reply-to address
     * @param array $attachments Optional attachments
     * @return bool Whether the email was sent successfully
     */
    public function trySendEmail(
        string $to,
        string $subject,
        string $template,
        array $context = [],
        ?string $fromName = null,
        ?string $replyTo = null,
        array $attachments = []
    ): bool {
        try {
            $this->sendEmail($to, $subject, $template, $context, $fromName, $replyTo, $attachments);
            return true;
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->warning('Non-critical email sending failed', [
                    'to' => $to,
                    'subject' => $subject,
                    'error' => $e->getMessage()
                ]);
            }
            return false;
        }
    }

    /**
     * Send a thank you email after completing Step 2 of the application
     *
     * @param string $email The candidate's email address
     * @param string $firstName The candidate's first name
     * @return bool Whether the email was sent successfully
     */
    public function sendThankYouForStep2Email(string $email, string $firstName): bool
    {
        $subject = 'Merci pour votre candidature - Étape 2 complétée';
        $template = 'emails/candidate/step2_completed.html.twig';
        $context = [
            'firstName' => $firstName,
            'date' => new \DateTime(),
        ];

        return $this->trySendEmail(
            $email,
            $subject,
            $template,
            $context
        );
    }

    /**
     * Notify candidate of successful verification (accepted)
     */
    public function sendStep2AcceptedEmail(string $email, string $firstName): bool
    {
        $subject = 'Votre candidature a été acceptée !';
        $template = 'emails/candidate/step2_accepted.html.twig';
        $context = [
            'firstName' => $firstName,
            'date' => new \DateTime(),
        ];

        return $this->trySendEmail(
            $email,
            $subject,
            $template,
            $context
        );
    }

    /**
     * Notify candidate of failed verification (rejected)
     */
    public function sendStep2RejectedEmail(string $email, string $firstName): bool
    {
        $subject = 'Votre candidature n’a pas pu être validée';
        $template = 'emails/candidate/step2_rejected.html.twig';
        $context = [
            'firstName' => $firstName,
            'date' => new \DateTime(),
        ];

        return $this->trySendEmail(
            $email,
            $subject,
            $template,
            $context
        );
    }

}