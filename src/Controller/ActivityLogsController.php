<?php

namespace App\Controller;

use App\Repository\ActivityLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/activity-logs')]
#[IsGranted('ROLE_ADMIN')]
class ActivityLogsController extends AbstractController
{
    #[Route('/', name: 'app_activity_logs', methods: ['GET'])]
    public function index(ActivityLogRepository $activityLogRepository): Response
    {
        $logs = $activityLogRepository->findBy([],['createdAt'=>'DESC']);

        return $this->render('activity_logs/index.html.twig', [
            'controller_name' => 'ActivityLogsController',
            'logs' => $logs,
        ]);
    }
}
    
