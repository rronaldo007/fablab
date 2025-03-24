<?php

namespace App\Entity;

use App\Repository\CandidateProfileRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CandidateProfileRepository::class)]
class CandidateProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'yes', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_of_birth = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $nationality = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $school = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $course_name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $specialization = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $course_start_date = null;

    #[ORM\Column(nullable: true)]
    private ?int $course_year = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $student_card = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $research_subject = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cv = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $video_link = null;

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

    public function getDateOfBirth(): ?\DateTimeInterface
    {
        return $this->date_of_birth;
    }

    public function setDateOfBirth(?\DateTimeInterface $date_of_birth): static
    {
        $this->date_of_birth = $date_of_birth;

        return $this;
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    public function setNationality(?string $nationality): static
    {
        $this->nationality = $nationality;

        return $this;
    }

    public function getSchool(): ?string
    {
        return $this->school;
    }

    public function setSchool(?string $school): static
    {
        $this->school = $school;

        return $this;
    }

    public function getCourseName(): ?string
    {
        return $this->course_name;
    }

    public function setCourseName(?string $course_name): static
    {
        $this->course_name = $course_name;

        return $this;
    }

    public function getSpecialization(): ?string
    {
        return $this->specialization;
    }

    public function setSpecialization(?string $specialization): static
    {
        $this->specialization = $specialization;

        return $this;
    }

    public function getCourseStartDate(): ?\DateTimeInterface
    {
        return $this->course_start_date;
    }

    public function setCourseStartDate(?\DateTimeInterface $course_start_date): static
    {
        $this->course_start_date = $course_start_date;

        return $this;
    }

    public function getCourseYear(): ?int
    {
        return $this->course_year;
    }

    public function setCourseYear(?int $course_year): static
    {
        $this->course_year = $course_year;

        return $this;
    }

    public function getStudentCard(): ?string
    {
        return $this->student_card;
    }

    public function setStudentCard(?string $student_card): static
    {
        $this->student_card = $student_card;

        return $this;
    }

    public function getResearchSubject(): ?string
    {
        return $this->research_subject;
    }

    public function setResearchSubject(?string $research_subject): static
    {
        $this->research_subject = $research_subject;

        return $this;
    }

    public function getCv(): ?string
    {
        return $this->cv;
    }

    public function setCv(?string $cv): static
    {
        $this->cv = $cv;

        return $this;
    }

    public function getVideoLink(): ?string
    {
        return $this->video_link;
    }

    public function setVideoLink(?string $video_link): static
    {
        $this->video_link = $video_link;

        return $this;
    }
}
