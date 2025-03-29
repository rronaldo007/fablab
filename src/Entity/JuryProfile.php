<?php

namespace App\Entity;

use App\Repository\JuryProfileRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: JuryProfileRepository::class)]
#[Broadcast]
class JuryProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: '$juryProfile', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $proffession = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mini_cv = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getProffession(): ?string
    {
        return $this->proffession;
    }

    public function setProffession(?string $proffession): static
    {
        $this->proffession = $proffession;

        return $this;
    }

    public function getMiniCv(): ?string
    {
        return $this->mini_cv;
    }

    public function setMiniCv(?string $mini_cv): static
    {
        $this->mini_cv = $mini_cv;

        return $this;
    }
}
