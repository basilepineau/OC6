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

    #[ORM\ManyToOne(inversedBy: 'CommentResponses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    public function getCommentMain(): ?CommentMain
    {
        return $this->commentMain;
    }

    public function setCommentMain(?CommentMain $commentMain): static
    {
        $this->commentMain = $commentMain;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }
}
