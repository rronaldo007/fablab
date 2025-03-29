<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

#[Route('/login', name: 'login_')]
final class LoginController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(
        Request $request,
        AuthenticationUtils $authenticationUtils,
        EntityManagerInterface $em,
        SessionInterface $session
    ): Response {
        if ($this->getUser()) {
            $this->addFlash('info', 'Vous êtes déjà connecté.');
            return $this->redirectToRoute('home_index');
        }

        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->createForm(LoginFormType::class, [
            'email' => $lastUsername
        ]);

        return $this->render('login/index.html.twig', [
            'controller_name' => 'LoginController',
            'form' => $form->createView(),
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/check', name: 'check')]
    public function check(): Response
    {
        // This method will be intercepted by the security system
        throw new \LogicException('This method should not be reached!');
    }

    #[Route('/success', name: 'success')]
    public function success(): Response
    {
        return $this->redirectToRoute('home_index');
    }

    #[Route('/logout', name: 'logout')]
    public function logout(): void
    {
        // This method will be intercepted by the security system
        // and never actually executed
        throw new \LogicException('This method should not be reached!');
    }
}