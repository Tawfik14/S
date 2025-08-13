<?php

namespace App\Entity;

use App\Repository\ReviewReportRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewReportRepository::class)]
#[ORM\Table(name: 'review_report')]
class ReviewReport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Review $review = null;

    #[ORM\ManyToOne(fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(length: 140)]
    private string $reason;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getReview(): ?Review { return $this->review; }
    public function setReview(Review $r): self { $this->review = $r; return $this; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(User $u): self { $this->user = $u; return $this; }
    public function getReason(): string { return $this->reason; }
    public function setReason(string $reason): self { $this->reason = $reason; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}

