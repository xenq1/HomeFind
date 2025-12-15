<?php

namespace App\Controller;

use App\Repository\PropertyRepository;
use App\Entity\Property;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/public-property', name: 'public_property_')]
class PublicPropertyController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(Request $request, PropertyRepository $propertyRepository): Response
    {
        $selectedType = $request->query->get('type');

        // Build query
        $qb = $propertyRepository->createQueryBuilder('p');

        if ($selectedType) {
            $qb->andWhere('p.type = :type')
               ->setParameter('type', $selectedType);
        }

        $properties = $qb->getQuery()->getResult();

        return $this->render('public_property/index.html.twig', [
            'properties' => $properties,
            'selectedType' => $selectedType,
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(int $id, PropertyRepository $propertyRepository): Response
    {
        $property = $propertyRepository->find($id);

        if (!$property) {
            throw $this->createNotFoundException('Property not found');
        }

        return $this->render('public_property/show.html.twig', [
            'property' => $property,
        ]);
    }
}
