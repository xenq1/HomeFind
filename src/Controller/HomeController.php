<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\PropertyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboard(UserRepository $userRepository, PropertyRepository $propertyRepository): Response
    {
        // Allow both admin and staff
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_STAFF')) {
            throw $this->createAccessDeniedException('You must be an admin or staff member');
        }

        $totalUsers = count($userRepository->findAll());
        $totalProperties = count($propertyRepository->findAll());
        $availableProperties = count($propertyRepository->findBy(['status' => 'available']));
        $soldProperties = count($propertyRepository->findBy(['status' => 'sold']));
        $rentProperties = count($propertyRepository->findBy(['status' => 'on rent']));

        return $this->render('admin/dashboard.html.twig', [
            'totalUsers' => $totalUsers,
            'totalProperties' => $totalProperties,
            'availableProperties' => $availableProperties,
            'soldProperties' => $soldProperties,
            'rentProperties' => $rentProperties,
        ]);
    }
}
