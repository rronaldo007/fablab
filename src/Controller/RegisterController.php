<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Role;
use App\Entity\RegistrationWorkflow;
use App\Form\RegistrationFormType;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Workflow\Registry as WorkflowRegistry;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

#[Route('/register', name: 'register_')]
final class RegisterController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        EmailService $emailService,
        WorkflowRegistry $workflowRegistry,
        SessionInterface $session
    ): Response {
        // Redirect if user is already logged in
        if ($this->getUser()) {
            $this->addFlash('info', 'Vous êtes déjà inscrit et connecté.');
            return $this->redirectToRoute('home_index');
        }

        // Create a new User entity
        $user = new User();

        // Create and process the registration form
        $form = $this->createForm(RegistrationFormType::class, $user);

        try {
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                // Check for form validation errors
                if (!$form->isValid()) {
                    foreach ($form->getErrors(true) as $error) {
                        $this->addFlash('danger', $error->getMessage());
                    }
                    throw new BadRequestException('Le formulaire contient des erreurs.');
                }

                // Check if email already exists
                $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
                if ($existingUser) {
                    $this->addFlash('danger', 'Cette adresse email est déjà utilisée.');
                    throw new BadRequestException('Email already exists');
                }

                // Retrieve the plain password from the form (unmapped field) and hash it
                $plainPassword = $form->get('plainPassword')->getData();
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);

                // Generate validation token (valid for 24 hours)
                // Assuming you've added the validation token and expiration date properties to User entity
                $token = bin2hex(random_bytes(16));
                $user->setValidationToken($token);
                $user->setValidationTokenExpiresAt(new \DateTime('+24 hours'));

                // Assign the "candidate" role
                $role = $em->getRepository(Role::class)->findOneBy(['role_name' => 'candidate']);
                if (!$role) {
                    $this->addFlash('danger', 'Erreur système: Rôle "candidate" non trouvé.');
                    throw new \Exception('Candidate role not found.');
                }
                $user->setRole($role);

                // Begin database transaction
                $em->beginTransaction();

                try {
                    $em->persist($user);
                    $em->flush();

                    // Create a new RegistrationWorkflow record in the "registered" state
                    $workflowEntity = new RegistrationWorkflow();
                    // Store initial data (user email) in the workflow
                    $workflowEntity->setData(['email' => $user->getEmail()]);
                    $workflowEntity->setUser($user);
                    $em->persist($workflowEntity);
                    $em->flush();

                    // Retrieve the registration workflow from the WorkflowRegistry and apply a transition
                    $workflow = $workflowRegistry->get($workflowEntity, 'registration');
                    if ($workflow->can($workflowEntity, 'send_validation_email')) {
                        $workflow->apply($workflowEntity, 'send_validation_email');
                    } else {
                        throw new \Exception('Unable to transition workflow to send validation email.');
                    }
                    $em->flush();

                    // Commit transaction
                    $em->commit();

                    // Generate the absolute URL for email validation
                    $validationLink = $this->generateUrl(
                        'register_validate_email',
                        ['token' => $token],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );

                    // Send the validation email using EmailService
                    try {
                        $emailService->sendEmail(
                            $user->getEmail(),
                            'Validez votre compte Fablab Électron',
                            'emails/validate_email.html.twig',
                            [
                                'user' => $user,
                                'validationLink' => $validationLink,
                                'expirationDate' => $user->getValidationTokenExpiresAt()->format('d/m/Y H:i')
                            ],
                            'Fablab Électron'
                        );
                    } catch (\Exception $emailException) {
                        $this->addFlash('warning', 'Votre compte a été créé, mais nous avons rencontré un problème lors de l\'envoi de l\'email de validation. Veuillez contacter le support.');
                        // Log email error
                        error_log('Email sending error during registration: ' . $emailException->getMessage());
                        return $this->redirectToRoute('home_index');
                    }

                    $this->addFlash('success', 'Inscription réussie! Veuillez vérifier votre email pour valider votre compte.');
                    return $this->redirectToRoute('home_index');

                } catch (\Exception $exception) {
                    // Rollback transaction in case of error
                    if ($em->getConnection()->isTransactionActive()) {
                        $em->rollback();
                    }
                    throw $exception;
                }
            }
        } catch (\Exception $exception) {
            // Log the exception for debugging
            error_log('Registration error: ' . $exception->getMessage());

            // Don't add another flash message if we've already added specific ones
            if (!($exception instanceof BadRequestException) &&
                !$session->getFlashBag()->has('danger')) {
                $this->addFlash('danger', 'Une erreur s\'est produite lors de l\'inscription. Veuillez réessayer.');
            }
        }

        return $this->render('register/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/validate-email/{token}', name: 'validate_email')]
    public function validateEmail(
        string $token,
        EntityManagerInterface $em,
        WorkflowRegistry $workflowRegistry
    ): Response {
        try {
            // Validate token format (hexadecimal string of proper length)
            if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
                throw $this->createNotFoundException('Format de token invalide.');
            }

            // Find user by validation token
            $userRepository = $em->getRepository(User::class);
            $user = $userRepository->findOneBy(['validationToken' => $token]);

            if (!$user) {
                throw $this->createNotFoundException('Token invalide ou expiré.');
            }

            // Check if token is expired
            if ($user->getValidationTokenExpiresAt() < new \DateTime()) {
                $this->addFlash('danger', 'Le lien de validation a expiré. Veuillez demander un nouveau lien.');
                return $this->redirectToRoute('register_resend_validation', ['email' => $user->getEmail()]);
            }

            // Check if already validated
            if ($user->isEmailValidated()) {
                $this->addFlash('info', 'Votre email a déjà été validé.');
                return $this->redirectToRoute('home_index');
            }

            // Start transaction
            $em->beginTransaction();

            try {
                // Find the workflow for this user
                $workflowEntity = $em->getRepository(RegistrationWorkflow::class)
                    ->findOneBy(['user' => $user]);

                if (!$workflowEntity) {
                    throw new \Exception('Workflow non trouvé pour cet utilisateur.');
                }

                // Use the workflow to transition from "email_validation_sent" to "email_validated"
                $workflow = $workflowRegistry->get($workflowEntity, 'registration');
                if ($workflow->can($workflowEntity, 'email_validated')) {
                    $workflow->apply($workflowEntity, 'email_validated');
                } else {
                    // This might happen if the workflow is in an unexpected state
                    throw new \Exception('Impossible de passer au statut "email validé" dans le workflow.');
                }

                // Mark the user's email as validated
                $user->setEmailValidated(true);

                // Clear the validation token after successful validation
                $user->setValidationToken(null);
                $user->setValidationTokenExpiresAt(null);

                $em->flush();
                $em->commit();

                $this->addFlash('success', 'Votre email a été validé avec succès. Vous pouvez maintenant vous connecter.');
                return $this->redirectToRoute('login_index');

            } catch (\Exception $exception) {
                // Rollback transaction in case of error
                if ($em->getConnection()->isTransactionActive()) {
                    $em->rollback();
                }

                // Log the error
                error_log('Email validation error: ' . $exception->getMessage());
                $this->addFlash('danger', 'Une erreur s\'est produite lors de la validation de votre email.');
                return $this->redirectToRoute('home_index');
            }
        } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            throw $this->createNotFoundException('Token invalide ou expiré.');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Une erreur inattendue s\'est produite. Veuillez réessayer plus tard.');
            return $this->redirectToRoute('home_index');
        }
    }

    #[Route('/resend-validation', name: 'resend_validation')]
    public function resendValidation(
        Request $request,
        EntityManagerInterface $em,
        EmailService $emailService,
        WorkflowRegistry $workflowRegistry
    ): Response {
        $email = $request->query->get('email');

        if (!$email) {
            return $this->render('register/resend_validation.html.twig');
        }

        try {
            // Find user by email
            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$user) {
                $this->addFlash('danger', 'Aucun compte associé à cette adresse email.');
                return $this->render('register/resend_validation.html.twig');
            }

            // Check if already validated
            if ($user->isEmailValidated()) {
                $this->addFlash('info', 'Votre email a déjà été validé. Vous pouvez vous connecter.');
                return $this->redirectToRoute('login_index');
            }

            // Find the workflow for this user
            $workflowEntity = $em->getRepository(RegistrationWorkflow::class)
                ->findOneBy(['user' => $user]);

            if (!$workflowEntity) {
                throw new \Exception('Workflow not found for user.');
            }

            // Generate a new token
            $token = bin2hex(random_bytes(16));
            $user->setValidationToken($token);
            $user->setValidationTokenExpiresAt(new \DateTime('+24 hours'));

            // Reset workflow if needed and send email again
            $workflow = $workflowRegistry->get($workflowEntity, 'registration');
            $currentPlace = $workflow->getMarking($workflowEntity)->getPlaces();

            // Only reset if we're not already in the right state
            if (!isset($currentPlace['email_validation_sent']) || !$currentPlace['email_validation_sent']) {
                if ($workflow->can($workflowEntity, 'reset_validation')) {
                    $workflow->apply($workflowEntity, 'reset_validation');
                }

                if ($workflow->can($workflowEntity, 'send_validation_email')) {
                    $workflow->apply($workflowEntity, 'send_validation_email');
                }
            }

            $em->flush();

            // Generate validation link
            $validationLink = $this->generateUrl(
                'register_validate_email',
                ['token' => $token],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            // Send the email
            $emailSent = $emailService->trySendEmail(
                $user->getEmail(),
                'Validez votre compte Fablab Électron',
                'emails/validate_email.html.twig',
                [
                    'user' => $user,
                    'validationLink' => $validationLink,
                    'expirationDate' => $user->getValidationTokenExpiresAt()->format('d/m/Y H:i'),
                    'isResend' => true
                ],
                'Fablab Électron'
            );

        } catch (\Exception $e) {
            $this->addFlash('danger', 'Une erreur s\'est produite. Veuillez réessayer ou contacter le support.');
            error_log('Email resend error: ' . $e->getMessage());
        }

        return $this->redirectToRoute('home_index');
    }
}