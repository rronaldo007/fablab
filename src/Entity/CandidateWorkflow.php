<?php

namespace App\Entity;

use App\Repository\CandidateWorkflowRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CandidateWorkflowRepository::class)]
class CandidateWorkflow
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'workflow')]
    #[ORM\JoinColumn(nullable: true)]
    private ?CandidateProfile $candidateProfile = null;

    #[ORM\Column(length: 50)]
    private ?string $currentState = 'step2_submitted';

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $step2SubmittedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $step2ReviewedAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $step2RejectionReason = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $selectedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $rejectedAt = null;

    #[ORM\ManyToOne]
    private ?User $reviewedBy = null;

    #[ORM\ManyToOne]
    private ?User $selectionDecidedBy = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->step2SubmittedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCandidateProfile(): ?CandidateProfile
    {
        return $this->candidateProfile;
    }

    public function setCandidateProfile(?CandidateProfile $candidateProfile): static
    {
        $this->candidateProfile = $candidateProfile;
        return $this;
    }

    public function getCurrentState(): ?string
    {
        return $this->currentState;
    }

    public function setCurrentState(string $currentState): static
    {
        $this->currentState = $currentState;
        $this->updatedAt = new \DateTimeImmutable();

        if ($currentState === 'step2_approved' || $currentState === 'step2_rejected') {
            $this->step2ReviewedAt = new \DateTimeImmutable();
        } elseif ($currentState === 'application_selected') {
            $this->selectedAt = new \DateTimeImmutable();
        } elseif ($currentState === 'application_rejected') {
            $this->rejectedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getStep2SubmittedAt(): ?\DateTimeImmutable
    {
        return $this->step2SubmittedAt;
    }

    public function setStep2SubmittedAt(?\DateTimeImmutable $step2SubmittedAt): static
    {
        $this->step2SubmittedAt = $step2SubmittedAt;
        return $this;
    }

    public function getStep2ReviewedAt(): ?\DateTimeImmutable
    {
        return $this->step2ReviewedAt;
    }

    public function getStep2RejectionReason(): ?string
    {
        return $this->step2RejectionReason;
    }

    public function setStep2RejectionReason(?string $step2RejectionReason): static
    {
        $this->step2RejectionReason = $step2RejectionReason;
        return $this;
    }

    public function getSelectedAt(): ?\DateTimeImmutable
    {
        return $this->selectedAt;
    }

    public function getRejectedAt(): ?\DateTimeImmutable
    {
        return $this->rejectedAt;
    }

    public function getReviewedBy(): ?User
    {
        return $this->reviewedBy;
    }

    public function setReviewedBy(?User $reviewedBy): static
    {
        $this->reviewedBy = $reviewedBy;
        return $this;
    }

    public function getSelectionDecidedBy(): ?User
    {
        return $this->selectionDecidedBy;
    }

    public function setSelectionDecidedBy(?User $selectionDecidedBy): static
    {
        $this->selectionDecidedBy = $selectionDecidedBy;
        return $this;
    }

    // Workflow helper methods
    public function isStep2Phase(): bool
    {
        return in_array($this->currentState, ['step2_submitted', 'step2_approved', 'step2_rejected']);
    }

    public function isApproved(): bool
    {
        return $this->currentState === 'step2_approved';
    }

    public function isRejected(): bool
    {
        return $this->currentState === 'step2_rejected';
    }

    public function isSelected(): bool
    {
        return $this->currentState === 'application_selected';
    }

    public function isApplicationRejected(): bool
    {
        return $this->currentState === 'application_rejected';
    }

    public function canEditStep2(): bool
    {
        return in_array($this->currentState, ['step2_submitted', 'step2_rejected']);
    }

    public function needsReview(): bool
    {
        return $this->currentState === 'step2_submitted';
    }

    public function canBeSelected(): bool
    {
        return $this->currentState === 'step2_approved';
    }
}
