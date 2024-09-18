<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditEmailType;
use App\Form\EditPasswordType;
use App\Repository\UserRepository;
use App\Service\AvatarService;
use App\Service\UserVerificationService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class UserController extends AbstractController
{
    private $entityManager;
    private $avatarService;
    private $userVerificationService;

    public function __construct(EntityManagerInterface $entityManager, AvatarService $avatarService, UserVerificationService $userVerificationService) {
        $this->entityManager = $entityManager;
        $this->avatarService = $avatarService;
        $this->userVerificationService = $userVerificationService;
    }

    #[Route('/profile', name: 'app_user_profile')]
    public function index(UserInterface $user): Response
    {
        $tricks = $user->getTricks();
        $commentMains = $user->getCommentMains();

        return $this->render('user/index.html.twig', [
            'user' => $user,
            'tricks' => $tricks,
            'commentMains' => $commentMains
        ]);
    }

    #[Route('/user/avatar/edit', name: 'app_user_avatar_edit')]
    public function editAvatar(UserInterface $user, Request $request, ParameterBagInterface $params): Response
    {

        if (!$request->files->get('avatar')) {
            $this->addFlash('danger', 'Error when updating avatar.');
            return $this->redirectToRoute('app_user_profile');
        }

        // Delete the old avatar from the server
        $oldAvatarName = $user->getAvatar();
        $oldAvatarPath = $params->get('avatar_directory') . '/' . $oldAvatarName;
        if (file_exists($oldAvatarPath)) {
            unlink($oldAvatarPath);
        }

        // Upload the new avatar
        $newAvatarName = $this->avatarService->upload($request->files->get('avatar'));
        $user->setAvatar($newAvatarName);

        $this->entityManager->flush();

        $this->addFlash('success', 'Avatar successfully updated !');

        return $this->redirectToRoute('app_user_profile'); 
    }

    #[Route('/user/username/edit', name: 'app_user_username_edit')]
    public function editUsername(UserInterface $user, Request $request): Response
    {
        // Récupérer le nouveau nom d'utilisateur depuis la requête
        $newUsername = $request->request->get('username');

        if (!$newUsername) {
            $this->addFlash('danger', 'No username provided.');
            return $this->redirectToRoute('app_user_profile');
        }

        // Vérification de la disponibilité du nouveau nom d'utilisateur
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $newUsername]);
        if ($existingUser && $existingUser !== $user) {
            $this->addFlash('danger', 'Username already taken.');
            return $this->redirectToRoute('app_user_profile');
        }

        // Mise à jour du nom d'utilisateur
        $user->setUsername($newUsername);
        $this->entityManager->flush();

        $this->addFlash('success', 'Username successfully updated !');

        return $this->redirectToRoute('app_user_profile');
    }

    #[Route('/user/password/edit', name: 'app_user_password_edit')]
    public function editPassword(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserInterface $user): Response
    {
        $form = $this->createForm(EditPasswordType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Vérification du mot de passe actuel
            if (!$userPasswordHasher->isPasswordValid($user, $data['oldPassword'])) {
                $form->get('oldPassword')->addError(new FormError('Old password is incorrect.'));
            } elseif ($data['newPassword'] !== $data['confirmPassword']) {
                $form->get('newPassword')->addError(new FormError('New password and confirmation do not match.'));
            } else {
                // Encoder le nouveau mot de passe
                $newHashedPassword = $userPasswordHasher->hashPassword($user, $data['newPassword']);
                $user->setPassword($newHashedPassword);

                // Sauvegarder les changements
                $this->entityManager->flush();

                $this->addFlash('success', 'Password changed successfully.');

                return $this->redirectToRoute('app_user_profile'); // Remplacez par la route appropriée
            }
        }

        return $this->render('user/edit_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/user/email/edit', name: 'app_user_email_edit')]
    public function editEmail(Request $request, UserRepository $userRepository, UserInterface $user): Response
    {
        $form = $this->createForm(EditEmailType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $newEmail = $data['newEmail'];

            // Vérifier si l'email existe déjà en BDD
            $existingUser = $userRepository->findOneBy(['email' => $newEmail]);
            if ($existingUser) {
                $form->get('newEmail')->addError(new FormError('This email is already used.'));
            } else {
                // Mettre à jour l'email et isVerified à false
                $user->setEmail($newEmail);
                $user->setVerified(false);
                $this->entityManager->flush();

                // Envoyer un email de confirmation via le UserVerificationService
                $this->userVerificationService->sendEmailConfirmation($user);

                // Déconnecter l'utilisateur
                $this->container->get('security.token_storage')->setToken(null);
                $request->getSession()->invalidate();

                $this->addFlash('success', 'We just sent you an email. Please click on the link to verify your account.');

                return $this->redirectToRoute('app_homepage');
            }
        }

        return $this->render('user/edit_email.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/user/verify/email', name: 'app_verify_user_email')]
    public function verifyUserEmail(Request $request): Response
    {
        $result = $this->userVerificationService->verifyUserEmail($request);

        if ($result instanceof User) {
            $this->addFlash('success', 'Your email address has been verified. You can now login.');
        } else {
            $this->addFlash('verify_email_error', $result);
        }

        return $this->redirectToRoute('app_login');
    }
}
