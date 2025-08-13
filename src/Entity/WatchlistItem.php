<?php

namespace App\Entity;

use App\Repository\WatchlistItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WatchlistItemRepository::class)]
#[ORM\Table(name: 'watchlist_items')]
#[ORM\UniqueConstraint(name: 'uniq_user_media_tmdb', fields: ['userId', 'mediaType', 'tmdbId'])]
class WatchlistItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $userId;

    #[ORM\Column(type: 'string', length: 10)]
    private string $mediaType; // 'movie' | 'tv'

    #[ORM\Column(type: 'integer')]
    private int $tmdbId;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $posterPath = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $backdropPath = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $addedAt;

    public function __construct()
    {
        $this->addedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getUserId(): int { return $this->userId; }
    public function setUserId(int $userId): self { $this->userId = $userId; return $this; }

    public function getMediaType(): string { return $this->mediaType; }
    public function setMediaType(string $mediaType): self { $this->mediaType = $mediaType; return $this; }

    public function getTmdbId(): int { return $this->tmdbId; }
    public function setTmdbId(int $tmdbId): self { $this->tmdbId = $tmdbId; return $this; }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }

    public function getPosterPath(): ?string { return $this->posterPath; }
    public function setPosterPath(?string $posterPath): self { $this->posterPath = $posterPath; return $this; }

    public function getBackdropPath(): ?string { return $this->backdropPath; }
    public function setBackdropPath(?string $backdropPath): self { $this->backdropPath = $backdropPath; return $this; }

    public function getAddedAt(): \DateTimeImmutable { return $this->addedAt; }
    public function setAddedAt(\DateTimeImmutable $addedAt): self { $this->addedAt = $addedAt; return $this; }
}

