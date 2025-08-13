<?php

namespace App\Entity;

use App\Repository\RatingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RatingRepository::class)]
#[ORM\Table(name: 'ratings')]
#[ORM\UniqueConstraint(name: 'uniq_user_media_tmdb', fields: ['userId', 'mediaType', 'tmdbId'])]
class Rating
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:'integer')]
    private ?int $id = null;

    #[ORM\Column(type:'integer')]
    private int $userId;

    #[ORM\Column(type:'string', length:10)]
    private string $mediaType; // 'movie' | 'tv'

    #[ORM\Column(type:'integer')]
    private int $tmdbId;

    #[ORM\Column(type:'smallint')]
    private int $value; // 1..10

    #[ORM\Column(type:'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type:'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function setUserId(int $userId): self { $this->userId = $userId; return $this; }

    public function getMediaType(): string { return $this->mediaType; }
    public function setMediaType(string $mediaType): self { $this->mediaType = $mediaType; return $this; }

    public function getTmdbId(): int { return $this->tmdbId; }
    public function setTmdbId(int $tmdbId): self { $this->tmdbId = $tmdbId; return $this; }

    public function getValue(): int { return $this->value; }
    public function setValue(int $value): self { $this->value = $value; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $dt): self { $this->createdAt = $dt; return $this; }

    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeImmutable $dt): self { $this->updatedAt = $dt; return $this; }
}

