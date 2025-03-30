<?php

namespace App\EventSubscriber;

use App\Entity\Edition;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class TwigGlobalCandidateProfileSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;
    private Environment $twig;
    private RequestStack $requestStack;

    public function __construct(
        EntityManagerInterface $entityManager,
        Environment $twig,
        RequestStack $requestStack
    ) {
        $this->entityManager = $entityManager;
        $this->twig = $twig;
        $this->requestStack = $requestStack;
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $user = $request?->getUser();

        $hasActiveCandidateProfile = false;

        if ($user instanceof User) {
            $currentEdition = $this->entityManager->getRepository(Edition::class)->findOneBy(['current' => true]);

            if ($currentEdition) {
                $activeProfile = $user->getCandidateProfileForCurrentEdition();
                $hasActiveCandidateProfile = $activeProfile !== null;
            }
        }

        $this->twig->addGlobal('hasActiveCandidateProfile', $hasActiveCandidateProfile);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
