<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Disables CSRF origin validation for ngrok tunneling in dev environment
 */
class DisableCsrfOriginCheckListener implements EventSubscriberInterface
{
    public function __construct(private string $environment) {}

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onKernelRequest', 256]];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($this->environment !== 'dev' || !$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        
        // For ngrok tunneling in dev, bypass CSRF origin validation by setting a matching referer
        if ($request->getMethod() === 'POST') {
            if (!$request->headers->has('Referer')) {
                $request->headers->set('Referer', $request->getSchemeAndHttpHost() . $request->getRequestUri());
            }
        }
    }
}
