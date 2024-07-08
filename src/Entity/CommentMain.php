<?php

namespace App\Entity;

use App\Repository\CommentMainRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentMainRepository::class)]
class CommentMain extends Comment
{
    /**
     * @var Collection<int, CommentResponse>
     */
    #[ORM\OneToMany(targetEntity: CommentResponse::class, mappedBy: 'commentMain', orphanRemoval: true)]
    private Collection $commentResponses;

    #[ORM\ManyToOne(inversedBy: 'commentMains')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Trick $trick = null;

    #[ORM\ManyToOne(inversedBy: 'commentMains')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function __construct()
    {
        $this->commentResponses = new ArrayCollection();
    }

    /**
     * @return Collection<int, CommentResponse>
     */
    public function getCommentResponses(): Collection
    {
        return $this->commentResponses;
    }

    public function addCommentResponse(CommentResponse $commentResponse): static
    {
        if (!$this->commentResponses->contains($commentResponse)) {
            $this->commentResponses->add($commentResponse);
            $commentResponse->setCommentMain($this);
        }

        return $this;
    }

    public function removeCommentResponse(CommentResponse $commentResponse): static
    {
        if ($this->commentResponses->removeElement($commentResponse)) {
            // set the owning side to null (unless already changed)
            if ($commentResponse->getCommentMain() === $this) {
                $commentResponse->setCommentMain(null);
            }
        }

        return $this;
    }

    public function getTrick(): ?Trick
    {
        return $this->trick;
    }

    public function setTrick(?Trick $trick): static
    {
        $this->trick = $trick;

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
