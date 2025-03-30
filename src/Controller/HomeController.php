<?php

namespace App\Controller;

use App\Entity\CandidateProfile;
use App\Entity\Edition;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/', name: 'home_')]
class HomeController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $hasActiveCandidateProfile = false;

        if ($user) {
            $currentEdition = $entityManager->getRepository(Edition::class)->findOneBy(['current' => true]);

            if ($currentEdition) {
                $activeProfile = $entityManager->getRepository(CandidateProfile::class)->findOneBy([
                    'user' => $user,
                    'edition' => $currentEdition,
                ]);

                $hasActiveCandidateProfile = $activeProfile !== null;
            }
        }

        return $this->render('home/index.html.twig', [
            'hasActiveCandidateProfile' => $hasActiveCandidateProfile,
        ]);
    }
}
