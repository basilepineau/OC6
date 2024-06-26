<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 500)]
    private ?string $avatar = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?bool $emailConfirmed = null;

    #[ORM\Column(length: 255)]
    private ?string $token = null;

    /**
     * @var Collection<int, Trick>
     */
    #[ORM\OneToMany(targetEntity: Trick::class, mappedBy: 'author', orphanRemoval: true)]
    private Collection $tricks;

    /**
     * @var Collection<int, CommentMain>
     */
    #[ORM\OneToMany(targetEntity: CommentMain::class, mappedBy: 'author', orphanRemoval: true)]
    private Collection $commentMains;

    /**
     * @var Collection<int, CommentResponse>
     */
    #[ORM\OneToMany(targetEntity: CommentResponse::class, mappedBy: 'author', orphanRemoval: true)]
    private Collection $CommentResponses;

    public function __construct()
    {
        $this->tricks = new ArrayCollection();
        $this->commentMains = new ArrayCollection();
        $this->CommentResponses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(string $avatar): static
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function isEmailConfirmed(): ?bool
    {
        return $this->emailConfirmed;
    }

    public function setEmailConfirmed(bool $emailConfirmed): static
    {
        $this->emailConfirmed = $emailConfirmed;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return Collection<int, Trick>
     */
    public function getTricks(): Collection
    {
        return $this->tricks;
    }

    public function addTrick(Trick $trick): static
    {
        if (!$this->tricks->contains($trick)) {
            $this->tricks->add($trick);
            $trick->setAuthor($this);
        }

        return $this;
    }

    public function removeTrick(Trick $trick): static
    {
        if ($this->tricks->removeElement($trick)) {
            // set the owning side to null (unless already changed)
            if ($trick->getAuthor() === $this) {
                $trick->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CommentMain>
     */
    public function getCommentMains(): Collection
    {
        return $this->commentMains;
    }

    public function addCommentMain(CommentMain $commentMain): static
    {
        if (!$this->commentMains->contains($commentMain)) {
            $this->commentMains->add($commentMain);
            $commentMain->setAuthor($this);
        }

        return $this;
    }

    public function removeCommentMain(CommentMain $commentMain): static
    {
        if ($this->commentMains->removeElement($commentMain)) {
            // set the owning side to null (unless already changed)
            if ($commentMain->getAuthor() === $this) {
                $commentMain->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CommentResponse>
     */
    public function getCommentResponses(): Collection
    {
        return $this->CommentResponses;
    }

    public function addCommentResponse(CommentResponse $commentResponse): static
    {
        if (!$this->CommentResponses->contains($commentResponse)) {
            $this->CommentResponses->add($commentResponse);
            $commentResponse->setAuthor($this);
        }

        return $this;
    }

    public function removeCommentResponse(CommentResponse $commentResponse): static
    {
        if ($this->CommentResponses->removeElement($commentResponse)) {
            // set the owning side to null (unless already changed)
            if ($commentResponse->getAuthor() === $this) {
                $commentResponse->setAuthor(null);
            }
        }

        return $this;
    }
}
