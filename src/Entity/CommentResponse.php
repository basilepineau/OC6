<?php

namespace App\Entity;

use App\Repository\CommentResponseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentResponseRepository::class)]
class CommentResponse extends Comment
{
    #[ORM\ManyToOne(inversedBy: 'commentResponses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CommentMain $commentMain = null;

    #[ORM\ManyToOne(inversedBy: 'commentResponses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getCommentMain(): ?CommentMain
    {
        return $this->commentMain;
    }

    public function setCommentMain(?CommentMain $commentMain): static
    {
        $this->commentMain = $commentMain;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
