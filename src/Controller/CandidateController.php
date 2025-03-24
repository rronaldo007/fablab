<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;  // Make sure this import is correct

#[Route('/candidate', name: 'candidate_')]  // Make sure this attribute is correct
final class CandidateController extends AbstractController
{
    #[Route('/', name: 'index')]  // Make sure this attribute is correct
    public function index(): Response
    {
        return $this->render('candidate/index.html.twig', [
            'controller_name' => 'CandidateController',
        ]);
    }
}