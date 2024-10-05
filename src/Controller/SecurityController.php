<?php

namespace App\Controller;

use App\Form\ForgotPasswordType;
use App\Form\ResetPasswordType;
use App\Repository\UserRepository;
use App\Service\ResetPasswordService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * Gère la connexion des utilisateurs.
     * Cette méthode affiche le formulaire de connexion et traite les erreurs d'authentification éventuelles.
     */
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * Gère la procédure de récupération de mot de passe oublié.
     * Cette méthode permet à un utilisateur de demander un lien pour réinitialiser son mot de passe.
     */
    #[Route(path:'/forgot-password', name:'app_forgot_password')]
    public function forgotPassword(Request $request, UserRepository $userRepository, ResetPasswordService $resetPasswordService): Response
    {
        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $username = $form->get('username')->getData();
            $user = $userRepository->findOneBy(['username' => $username]);

            if ($user) {
                $resetPasswordService->sendResetPasswordEmail($user);
                $this->addFlash('success', 'Reset link sent successfully.');
            } else {
                $this->addFlash('danger', 'Email address not found.');
            }
        }

        return $this->render('security/forgot_password.html.twig', [
            'forgotPasswordForm' => $form,
        ]);
    }

    /**
     * Gère la réinitialisation du mot de passe via un lien contenant un jeton.
     * Cette méthode permet à l'utilisateur de définir un nouveau mot de passe après validation du jeton.
     */
    #[Route(path: '/reset-password/{token}', name: 'app_reset_password')]
    public function resetPassword(Request $request, UserRepository $userRepository, string $token, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = $userRepository->findOneBy(['resetToken' => $token]);
    
        if (!$user) {
            throw $this->createNotFoundException('User not found for this token');
        }
    

        $form = $this->createForm(ResetPasswordType::class, );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('plainPassword')->getData();

            $hashedPassword = $userPasswordHasher->hashPassword($user, $newPassword);

            $user->setPassword($hashedPassword);
            $user->setResetToken(null);
            $user->setResetTokenExpiresAt(null);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Password successfully reset.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', [
            'resetPasswordForm' => $form->createView(),
        ]);
    }

    /**
     * Gère la déconnexion de l'utilisateur.
     * Cette méthode est interceptée par le firewall de sécurité de Symfony et ne doit pas contenir de logique.
     */
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
