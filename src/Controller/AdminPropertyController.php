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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Service\ActivityLogger;

#[Route('/admin/property', name: 'admin_property_')]
class AdminPropertyController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(PropertyRepository $propertyRepository): Response
    {
        return $this->render('admin_property/index.html.twig', [
            'properties' => $propertyRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger, ActivityLogger $activityLogger): Response
    {
        $property = new Property();
        $property->setStatus('available'); // Set default status
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
                    // optionally handle the exception (log it), but continue
                }

                // store path used in templates
                $property->setImage('/uploads/'.$newFilename);
            }

            // Set the creator for access control
            if (!$property->getCreatedBy()) {
                $property->setCreatedBy($this->getUser());
            }

            $em->persist($property);
            $em->flush();

            $activityLogger->log(
                'Created Property',
                'Property ID '.$property->getId()
            );

            $this->addFlash('success', 'Property added successfully!');
            return $this->redirectToRoute('admin_property_index');
        }

        return $this->render('admin_property/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Property $property): Response
    {
        return $this->render('admin_property/show.html.twig', [
            'property' => $property,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit')]
    public function edit(Request $request, Property $property, EntityManagerInterface $em, SluggerInterface $slugger, ActivityLogger $activityLogger): Response
    {
        // Check if user has access: admin can edit any, staff can only edit their own
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_STAFF')) {
            throw $this->createAccessDeniedException('Access denied');
        }

        // Check ownership for ROLE_STAFF
        if ($this->isGranted('ROLE_STAFF') && !$this->isGranted('ROLE_ADMIN')) {
            if ($property->getCreatedBy() !== $this->getUser()) {
                throw $this->createAccessDeniedException('You can only edit properties you created');
            }
        }

        $form = $this->createForm(PropertyType::class, $property);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $imageFile */
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                // remove old file if exists
                $oldImage = $property->getImage();
                if ($oldImage) {
                    $oldPath = $this->getParameter('kernel.project_dir').'/public'.$oldImage;
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }

                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move($this->getParameter('kernel.project_dir').'/public/uploads', $newFilename);
                } catch (FileException $e) {
                    // ignore or log
                }

                $property->setImage('/uploads/'.$newFilename);
            }

            $em->flush();
            $activityLogger->log('Property Updated', $property->getName());
            $this->addFlash('success', 'Property updated successfully!');
            return $this->redirectToRoute('admin_property_index');
        }

        return $this->render('admin_property/edit.html.twig', [
            'property' => $property,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Property $property, EntityManagerInterface $em, ActivityLogger $activityLogger): Response
    {
        // Check if user has access: admin can delete any, staff can only delete their own
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_STAFF')) {
            throw $this->createAccessDeniedException('Access denied');
        }

        // Check ownership for ROLE_STAFF
        if ($this->isGranted('ROLE_STAFF') && !$this->isGranted('ROLE_ADMIN')) {
            if ($property->getCreatedBy() !== $this->getUser()) {
                throw $this->createAccessDeniedException('You can only delete properties you created');
            }
        }

        if ($this->isCsrfTokenValid('delete'.$property->getId(), $request->request->get('_token'))) {
            $activityLogger->log('Property Deleted', $property->getName());
            $em->remove($property);
            $em->flush();
            $this->addFlash('success', 'Property deleted successfully!');
        }

        return $this->redirectToRoute('admin_property_index');
    }
}
