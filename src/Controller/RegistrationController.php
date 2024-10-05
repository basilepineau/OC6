<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Service\UserVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private $userVerificationService;

    public function __construct(UserVerificationService $userVerificationService)
    {
        $this->userVerificationService = $userVerificationService;
    }

    /**
     * Gère l'inscription des nouveaux utilisateurs.
     * Cette méthode permet de traiter le formulaire d'inscription, de hasher le mot de passe de l'utilisateur
     * et d'enregistrer les informations dans la base de données.
     */
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifiez si l'email existe déjà
            $existingUser = $userRepository->findOneBy(['email' => $user->getEmail()]);
            if ($existingUser) {
                // Ajouter une erreur au formulaire
                $form->get('email')->addError(new FormError('This email is already used.'));
            } else {
                // Si l'email n'existe pas, continuez avec la création de l'utilisateur
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
    
                $entityManager->persist($user);
                $entityManager->flush();
    
                // Envoyer l'email de confirmation
                $this->userVerificationService->sendEmailConfirmation($user);
    
                $this->addFlash('info', 'We just sent you an email. Please click on the link to verify your account.');
    
                return $this->redirectToRoute('app_homepage');
            }
        }
    
        return $this->render('registration/register.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * Vérifie l'email de l'utilisateur après l'inscription via un lien de confirmation.
     * Cette méthode traite la vérification du jeton envoyé par email pour confirmer l'adresse électronique de l'utilisateur.
     */
    #[Route('/register/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request): Response
    {
        $result = $this->userVerificationService->verifyUserEmail($request);

        if ($result instanceof User) {
            // Success
            $this->addFlash('success', 'Your email address has been verified. You can now login.');
        } else {
            // Error
            $this->addFlash('verify_email_error', $result);
        }

        return $this->redirectToRoute('app_login');
    }
}
