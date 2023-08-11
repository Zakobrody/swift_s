<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

class PasswordChangeSubscriber implements EventSubscriberInterface
{
    private $security;
    private $urlGenerator;

    public function __construct(Security $security, UrlGeneratorInterface $urlGenerator)
    {
        $this->security = $security;
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['forcePasswordChange', 0]
            ],
        ];
    }

    public function forcePasswordChange(RequestEvent $event): void
    {
        $user = $this->security->getUser();
        // if you do not have a valid user, it means it's not an authenticated request, so it's not our concern
        if (!$user instanceof User) {
            return;
        }

        // if it's not their first login, and they do not need to change their password, move on
        if (!$user->isForcePasswordChange()) {
            return;
        }

        // if we get here, it means we need to redirect them to the password change view.
        $redirectTo = $this->urlGenerator->generate('app_changepassword_force');
        if ($event->getRequest()->getRequestUri() != $redirectTo){
            $event->setResponse(new RedirectResponse($redirectTo));
        }
    }
}