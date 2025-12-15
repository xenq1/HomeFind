<?php

namespace App\Service;

use App\Entity\ActivityLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class ActivityLogger
{
    private $em;
    private $security;
    private $requestStack;

    public function __construct(
        EntityManagerInterface $em, 
        Security $security, 
        RequestStack $requestStack
    ){
        $this->em = $em;
        $this->security = $security;
        $this->requestStack = $requestStack;
    }

    public function log(string $action, ?string $details = null): void
    {
        $user = $this->security->getUser();
        $request = $this->requestStack->getCurrentRequest();
        $ipAddress = $request ? $request->getClientIp() : 'unknown';

        $log = new ActivityLog();
        $log->setUserId($user);
        $log->setAction($action);
        $log->setTargetData($details ?? '');
        $log->setIpAddress($ipAddress);
        $log->setCreatedAt(new \DateTime());

        $this->em->persist($log);
        $this->em->flush();
    }
}