<?php

namespace App\EventSubscriber;

use App\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Twig\Environment;

class TwigEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Environment $twig,
        private readonly UserRepository $userRepository,
        private readonly string $rootDir,
        private readonly string $appEnv,
    ) {
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $this->twig->addGlobal(
            'systemUsers',
            $this->userRepository->findActiveUsers()
        );
        $this->twig->addGlobal('currentYear', date('Y'));
        $this->twig->addGlobal('rootDir', $this->rootDir.'/public');
        $this->twig->addGlobal('appEnv', $this->appEnv);
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.controller' => 'onKernelController',
        ];
    }
}
