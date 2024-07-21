<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\String\ByteString;

class ResetPasswordService
{
    private $entityManager;
    private $mailer;
    private $params;

    public function __construct(EntityManagerInterface $entityManager, MailerInterface $mailer, ParameterBagInterface $params)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->params = $params;
    }

    public function sendResetPasswordEmail(User $user): void
    {
        $token = ByteString::fromRandom(32)->toString();
        $user->setResetToken($token);
        $user->setResetTokenExpiresAt(new \DateTimeImmutable('+1 hour'));

        $this->entityManager->flush();

        $baseUrl = $this->params->get('app.base_url');
        $resetUrl = sprintf($baseUrl.'/reset-password/%s', $token);
        $email = (new Email())
            ->from(new Address('bspineaucros@gmail.com', 'OC6'))
            ->to($user->getEmail())
            ->subject('Your password reset request')
            ->html('<p>To reset your password, please click the link below :</p><p><a href="' . $resetUrl . '">Reset password</a></p>');
        
        $this->mailer->send($email);
    }
}