<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserFormType;
use App\Repository\UserRepository;
use App\Service\ActivityLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user', name: 'app_user_')]
#[IsGranted('ROLE_ADMIN')]
final class UserController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/new', name: 'new')]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, ActivityLogger $activityLogger): Response
    {
        $user = new User();
        $form = $this->createForm(UserFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ensure username is set
            $username = $form->get('username')->getData();
            if ($username) {
                $user->setUsername($username);
            }

            // Hash the password
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $em->persist($user);
            $em->flush();

            $activityLogger->log('User Created', $user->getUsername());
            $this->addFlash('success', 'User created successfully!');
            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, int $id, UserRepository $userRepository, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, ActivityLogger $activityLogger): Response
    {
        $user = $userRepository->find($id);
        
        if (!$user) {
            $this->addFlash('error', sprintf('User with ID %d not found', $id));
            return $this->redirectToRoute('app_user_index');
        }
        
        $oldUsername = $user->getUsername();
        
        $form = $this->createForm(UserFormType::class, $user, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get the new username from form data
            $newUsername = $form->get('username')->getData();
            
            // Check if username changed and if it's already taken
            if ($newUsername && $newUsername !== $oldUsername) {
                $existingUser = $userRepository->findOneBy(['username' => $newUsername]);
                if ($existingUser) {
                    $this->addFlash('error', 'Username is already taken');
                    return $this->redirectToRoute('app_user_edit', ['id' => $id]);
                }
                $user->setUsername($newUsername);
            }
            
            // Hash the password if provided
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            // Handle roles from request
            $roles = [];
            if ($request->request->has('roles_user') && $request->request->get('roles_user') === 'on') {
                $roles[] = 'ROLE_USER';
            }
            if ($request->request->has('roles_admin') && $request->request->get('roles_admin') === 'on') {
                $roles[] = 'ROLE_ADMIN';
            }
            if ($request->request->has('roles_staff') && $request->request->get('roles_staff') === 'on') {
                $roles[] = 'ROLE_STAFF';
            }
            
            // Ensure at least one role is selected
            if (empty($roles)) {
                $roles[] = 'ROLE_USER';
            }
            
            $user->setRoles(array_unique($roles));

            $em->persist($user);
            $em->flush();
            
            $activityLogger->log('User Updated', $user->getUsername());
            $this->addFlash('success', 'User updated successfully!');
            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, int $id, UserRepository $userRepository, EntityManagerInterface $em, ActivityLogger $activityLogger): Response
    {
        $user = $userRepository->find($id);
        
        if (!$user) {
            $this->addFlash('error', sprintf('User with ID %d not found', $id));
            return $this->redirectToRoute('app_user_index');
        }
        
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $activityLogger->log('User Deleted', $user->getUsername());
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'User deleted successfully!');
        }

        return $this->redirectToRoute('app_user_index');
    }

    #[Route('/{id}/toggle-enabled', name: 'toggle_enabled', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function toggleEnabled(Request $request, int $id, UserRepository $userRepository, EntityManagerInterface $em, ActivityLogger $activityLogger): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            $this->addFlash('error', sprintf('User with ID %d not found', $id));
            return $this->redirectToRoute('app_user_index');
        }

        if (!$this->isCsrfTokenValid('toggle_enabled'.$user->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token');
            return $this->redirectToRoute('app_user_index');
        }

        $user->setEnabled(!$user->isEnabled());
        $em->persist($user);
        $em->flush();

        $activityLogger->log($user->isEnabled() ? 'User Enabled' : 'User Disabled', $user->getUsername());
        $this->addFlash('success', sprintf('User %s successfully', $user->isEnabled() ? 'enabled' : 'disabled'));

        return $this->redirectToRoute('app_user_index');
    }
}
