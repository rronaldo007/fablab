<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;
use DateTimeInterface;
use App\Entity\User;

#[ORM\Entity(repositoryClass: "App\Repository\RegistrationWorkflowRepository")]
#[ORM\Table(name: "registration_workflow")]
class RegistrationWorkflow
{
    // Available workflow places (states)
    public const PLACE_REGISTERED = 'registered';
    public const PLACE_EMAIL_VALIDATION_SENT = 'email_validation_sent';
    public const PLACE_EMAIL_VALIDATED = 'email_validated';
    public const PLACE_COMPLETED = 'completed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string')]
    private string $currentPlace;

    #[ORM\Column(type: 'json', nullable: true)]
    private array $data = [];

    #[ORM\Column(type: 'datetime')]
    private DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTimeInterface $updatedAt = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->currentPlace = self::PLACE_REGISTERED; // Starting place
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCurrentPlace(): string
    {
        return $this->currentPlace;
    }

    public function setCurrentPlace(string $currentPlace): self
    {
        $this->currentPlace = $currentPlace;
        $this->updatedAt = new DateTime();
        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * This method is used by the workflow component to get the current marking.
     */
    public function getMarking(): array
    {
        return [$this->currentPlace => 1];
    }

    /**
     * This method is used by the workflow component to set the current marking.
     */
    public function setMarking(array $marking): void
    {
        $this->currentPlace = key($marking);
    }
}