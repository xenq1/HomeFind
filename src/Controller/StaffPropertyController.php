<?php

namespace App\Controller;

use App\Entity\Property;
use App\Form\PropertyType;
use App\Repository\PropertyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\ActivityLogger;

#[Route('/staff/property', name: 'staff_property_')]
#[IsGranted('ROLE_STAFF')]
class StaffPropertyController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(PropertyRepository $propertyRepository): Response
    {
        $user = $this->getUser();
        // Get only properties created by this staff member
        $properties = $propertyRepository->findBy(['createdBy' => $user]);

        return $this->render('staff_property/index.html.twig', [
            'properties' => $properties,
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger, ActivityLogger $activityLogger): Response
    {
        $property = new Property();
        $form = $this->createForm(PropertyType::class, $property);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $imageFile */
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move($this->getParameter('kernel.project_dir').'/public/uploads', $newFilename);
                } catch (FileException $e) {
                    // Handle exception
                }

                $property->setImage($newFilename);
            }

            $property->setCreatedBy($this->getUser());
            $em->persist($property);
            $em->flush();

            $activityLogger->log('Property Created', $property->getName());
            $this->addFlash('success', 'Property created successfully');

            return $this->redirectToRoute('staff_property_index');
        }

        return $this->render('staff_property/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'])]
    public function edit(int $id, Request $request, PropertyRepository $propertyRepository, EntityManagerInterface $em, SluggerInterface $slugger, ActivityLogger $activityLogger): Response
    {
        $property = $propertyRepository->find($id);

        if (!$property) {
            throw $this->createNotFoundException('Property not found');
        }

        // Check ownership
        if ($property->getCreatedBy() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only edit your own properties');
        }

        $form = $this->createForm(PropertyType::class, $property);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $imageFile */
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move($this->getParameter('kernel.project_dir').'/public/uploads', $newFilename);
                } catch (FileException $e) {
                    // Handle exception
                }

                $property->setImage($newFilename);
            }

            $em->flush();
            $activityLogger->log('Property Updated', $property->getName());
            $this->addFlash('success', 'Property updated successfully');

            return $this->redirectToRoute('staff_property_index');
        }

        return $this->render('staff_property/edit.html.twig', [
            'property' => $property,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(int $id, PropertyRepository $propertyRepository, EntityManagerInterface $em, ActivityLogger $activityLogger): Response
    {
        $property = $propertyRepository->find($id);

        if (!$property) {
            throw $this->createNotFoundException('Property not found');
        }

        // Check ownership
        if ($property->getCreatedBy() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only delete your own properties');
        }

        $activityLogger->log('Property Deleted', $property->getName());
        $em->remove($property);
        $em->flush();

        $this->addFlash('success', 'Property deleted successfully');
        return $this->redirectToRoute('staff_property_index');
    }
}
