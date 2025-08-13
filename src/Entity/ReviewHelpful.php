<?php

namespace App\Entity;

use App\Repository\ReviewHelpfulRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewHelpfulRepository::class)]
#[ORM\Table(name: 'review_helpful', uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'uniq_helpful_review_user', columns: ['review_id', 'user_id'])
])]
class ReviewHelpful
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

    #[ORM\Column(type: 'boolean')]
    private bool $value = true; // true = utile

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
    public function getValue(): bool { return $this->value; }
    public function setValue(bool $v): self { $this->value = $v; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}

