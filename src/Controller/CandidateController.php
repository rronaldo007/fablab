<?php

namespace App\Controller;

use App\Entity\CandidateProfile;
use App\Form\CandidateProfileStep2Type;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/candidate', name: 'candidate_')]
class CandidateController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('candidate/index.html.twig', [
            'controller_name' => 'CandidateController',
        ]);
    }

    #[Route('/start_candidate', name: 'start_candidate')]
    public function profile(Request $request, EntityManagerInterface $entityManager, EmailService $emailService): Response
    {
        // Retrieve the currently logged-in user
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        // Check if user has ROLE_CANDIDATE
        if (!in_array('ROLE_CANDIDATE', $user->getRoles())) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette page.');
        }

        // Check if user already has a profile
        $candidateProfile = $entityManager->getRepository(CandidateProfile::class)->findOneBy(['user' => $user]);

        // If no profile exists, create a new one (Step 2)
        if (!$candidateProfile) {
            $candidateProfile = new CandidateProfile();
            $candidateProfile->setUser($user);

            // Create form for Step 2
            $form = $this->createForm(CandidateProfileStep2Type::class, $candidateProfile);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // Handle student card upload
                $studentCardFile = $form->get('studentCardFile')->getData();
                if ($studentCardFile) {
                    $originalFilename = pathinfo($studentCardFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $studentCardFile->guessExtension();

                    try {
                        $studentCardFile->move(
                            $this->getParameter('student_card_directory'),
                            $newFilename
                        );
                        $candidateProfile->setStudentCard($newFilename);
                    } catch (FileException $e) {
                        $this->addFlash('danger', 'Une erreur est survenue lors du téléchargement de votre carte d\'étudiant.');
                        return $this->redirectToRoute('candidate_profile');
                    }
                }

                // Set application status to "Step 2 Completed"
                $candidateProfile->setStatus('step2_completed');

                // Save the profile
                $entityManager->persist($candidateProfile);
                $entityManager->flush();

                // Send thank you email
                try {
                    $emailService->sendThankYouForStep2Email($user->getEmail(), $user->getFirstName());
                    $this->addFlash('success', 'Votre profil a été créé avec succès ! Un email de confirmation vous a été envoyé.');
                } catch (\Exception $e) {
                    $this->addFlash('warning', 'Votre profil a été créé avec succès mais nous n\'avons pas pu vous envoyer l\'email de confirmation.');
                }

                return $this->redirectToRoute('candidate_profile');
            }

            return $this->render('candidate/candidate_profile.html.twig', [
                'user' => $user,
                'form' => $form->createView(),
            ]);
        }

        // If profile exists but Step 2 is rejected, let them edit and resubmit
        if ($candidateProfile->getStatus() === 'step2_rejected') {
            $form = $this->createForm(CandidateProfileStep2Type::class, $candidateProfile);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // Handle student card upload
                $studentCardFile = $form->get('studentCardFile')->getData();
                if ($studentCardFile) {
                    $originalFilename = pathinfo($studentCardFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $studentCardFile->guessExtension();

                    try {
                        $studentCardFile->move(
                            $this->getParameter('student_card_directory'),
                            $newFilename
                        );
                        $candidateProfile->setStudentCard($newFilename);
                    } catch (FileException $e) {
                        $this->addFlash('danger', 'Une erreur est survenue lors du téléchargement de votre carte d\'étudiant.');
                        return $this->redirectToRoute('candidate_profile');
                    }
                }

                $candidateProfile->setStatus('step2_completed');
                $entityManager->flush();

                try {
                    $emailService->sendThankYouForStep2Email($user->getEmail(), $user->getFirstName());
                    $this->addFlash('success', 'Votre profil a été mis à jour avec succès ! Un email de confirmation vous a été envoyé.');
                } catch (\Exception $e) {
                    $this->addFlash('warning', 'Votre profil a été mis à jour avec succès mais nous n\'avons pas pu vous envoyer l\'email de confirmation.');
                }

                return $this->redirectToRoute('candidate_profile');
            }

            return $this->render('candidate/candidate_profile.html.twig', [
                'user' => $user,
                'form' => $form->createView(),
            ]);
        }

        // If profile exists and status is Step 2 Completed or higher, display it
        return $this->render('candidate/candidate_profile.html.twig', [
            'user' => $user,
            'profile' => $candidateProfile,
        ]);
    }
}