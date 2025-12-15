<?php

namespace App\Controller;

use App\Repository\PropertyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/payment', name: 'app_payment')]
#[IsGranted('ROLE_USER')]
final class PaymentController extends AbstractController
{
    #[Route('/{propertyId}', name: '', requirements: ['propertyId' => '\d+'])]
    public function index(int $propertyId, PropertyRepository $propertyRepository): Response
    {
        $property = $propertyRepository->find($propertyId);

        if (!$property) {
            throw $this->createNotFoundException('Property not found');
        }

        return $this->render('payment/index.html.twig', [
            'property' => $property,
        ]);
    }

    #[Route('/{propertyId}/complete', name: '_complete', methods: ['POST'], requirements: ['propertyId' => '\d+'])]
    public function completePayment(int $propertyId, PropertyRepository $propertyRepository, EntityManagerInterface $em): JsonResponse
    {
        $property = $propertyRepository->find($propertyId);

        if (!$property) {
            return new JsonResponse(['success' => false, 'message' => 'Property not found'], 404);
        }

        try {
            // Update property status to 'sold'
            $property->setStatus('sold');
            $em->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Payment completed successfully and property status updated',
                'propertyId' => $propertyId,
                'newStatus' => 'sold'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error updating property status: ' . $e->getMessage()
            ], 500);
        }
    }
}

