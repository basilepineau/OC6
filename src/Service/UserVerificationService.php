<?php

namespace App\Service;

use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class UserVerificationService
{
    private $emailVerifier;
    private $userRepository;

    public function __construct(EmailVerifier $emailVerifier, UserRepository $userRepository)
    {
        $this->emailVerifier = $emailVerifier;
        $this->userRepository = $userRepository;
    }

    public function sendEmailConfirmation(UserInterface $user, string $template = 'registration/confirmation_email.html.twig')
    {
        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            (new TemplatedEmail())
                ->from(new Address('bspineaucros@gmail.com', 'OC6'))
                ->to($user->getEmail())
                ->subject('Please confirm your email')
                ->htmlTemplate($template)
        );
    }

    public function verifyUserEmail($request)
    {
        $id = $request->query->get('id');

        if (null === $id) {
            return 'No ID provided';
        }

        $user = $this->userRepository->find($id);

        if (null === $user) {
            return 'User not found';
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
            return $user;
        } catch (VerifyEmailExceptionInterface $exception) {
            return $exception->getReason(); // Return the raw error message without translation
        }
    }
}
