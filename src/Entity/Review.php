<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
#[ORM\Table(name: 'review', indexes: [
    new ORM\Index(name: 'idx_review_media', columns: ['media_type', 'tmdb_id']),
    new ORM\Index(name: 'idx_review_user_media', columns: ['user_id', 'media_type', 'tmdb_id']),
])]
class Review
{
    public const MEDIA_MOVIE = 'movie';
    public const MEDIA_TV = 'tv';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(length: 10)]
    #[Assert\Choice(choices: [self::MEDIA_MOVIE, self::MEDIA_TV])]
    private string $mediaType;

    #[ORM\Column(type: 'integer')]
    private int $tmdbId;

    #[ORM\Column(length: 140)]
    #[Assert\NotBlank]
    private string $title;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private string $body;

    #[ORM\Column(type: 'smallint', nullable: true)]
    #[Assert\Range(min: 1, max: 10)]
    private ?int $rating = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $helpfulCount = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $reportedCount = 0;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isDeleted = false;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    // --- Getters/Setters (générés rapidement) ---
    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(User $user): self { $this->user = $user; return $this; }

    public function getMediaType(): string { return $this->mediaType; }
    public function setMediaType(string $mediaType): self { $this->mediaType = $mediaType; return $this; }

    public function getTmdbId(): int { return $this->tmdbId; }
    public function setTmdbId(int $tmdbId): self { $this->tmdbId = $tmdbId; return $this; }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }

    public function getBody(): string { return $this->body; }
    public function setBody(string $body): self { $this->body = $body; return $this; }

    public function getRating(): ?int { return $this->rating; }
    public function setRating(?int $rating): self { $this->rating = $rating; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): self { $this->createdAt = $createdAt; return $this; }

    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self { $this->updatedAt = $updatedAt; return $this; }

    public function getHelpfulCount(): int { return $this->helpfulCount; }
    public function setHelpfulCount(int $c): self { $this->helpfulCount = $c; return $this; }
    public function incHelpfulCount(int $by = 1): self { $this->helpfulCount += $by; return $this; }

    public function getReportedCount(): int { return $this->reportedCount; }
    public function setReportedCount(int $c): self { $this->reportedCount = $c; return $this; }
    public function incReportedCount(int $by = 1): self { $this->reportedCount += $by; return $this; }

    public function isDeleted(): bool { return $this->isDeleted; }
    public function setIsDeleted(bool $deleted): self { $this->isDeleted = $deleted; return $this; }
}

