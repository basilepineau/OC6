<?php

namespace App\Security\Voter;

use App\Entity\Trick;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class TrickVoter extends Voter
{
    public const EDIT = 'TRICK_EDIT';
    public const DELETE = 'TRICK_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
       // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::DELETE])
            && $subject instanceof \App\Entity\Trick;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        $trick = $subject;

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($trick, $user);
                break;
            case self::DELETE:
                return $this->canDelete($trick, $user);
                break;
            default :
             throw new \LogicException('This code should not be reached!');
        }

        return false;
    }

    private function canEdit(Trick $trick, User $user): bool
    {
        return $user === $trick->getUser();
    }

    private function canDelete(Trick $trick, User $user): bool
    {
        // if they can edit, they can view
        if ($this->canEdit($trick, $user)) {
            return true;
        }
        return false;
    }

}
