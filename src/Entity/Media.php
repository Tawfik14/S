<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
#[ORM\Table(name: 'media')]
#[ORM\Index(columns: ['type', 'release_date'], name: 'idx_type_release')]
class Media
{
    public const TYPE_MOVIE  = 'movie';
    public const TYPE_SERIES = 'series';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    private string $type = self::TYPE_MOVIE;

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(length: 255, unique: true)]
    private string $slug = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $synopsis = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tagline = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $genres = null;

    #[ORM\Column(nullable: true)]
    private ?int $runtime = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $rating = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $posterUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $backdropUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $trailerUrl = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?DateTimeInterface $releaseDate = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getType(): string { return $this->type; }
    public function setType(string $type): self { $this->type = $type; return $this; }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }

    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): self { $this->slug = $slug; return $this; }

    public function getSynopsis(): ?string { return $this->synopsis; }
    public function setSynopsis(?string $synopsis): self { $this->synopsis = $synopsis; return $this; }

    public function getTagline(): ?string { return $this->tagline; }
    public function setTagline(?string $tagline): self { $this->tagline = $tagline; return $this; }

    public function getGenres(): ?array { return $this->genres; }
    public function setGenres(?array $genres): self { $this->genres = $genres; return $this; }

    public function getRuntime(): ?int { return $this->runtime; }
    public function setRuntime(?int $runtime): self { $this->runtime = $runtime; return $this; }

    public function getRating(): ?float { return $this->rating; }
    public function setRating(?float $rating): self { $this->rating = $rating; return $this; }

    public function getPosterUrl(): ?string { return $this->posterUrl; }
    public function setPosterUrl(?string $posterUrl): self { $this->posterUrl = $posterUrl; return $this; }

    public function getBackdropUrl(): ?string { return $this->backdropUrl; }
    public function setBackdropUrl(?string $backdropUrl): self { $this->backdropUrl = $backdropUrl; return $this; }

    public function getTrailerUrl(): ?string { return $this->trailerUrl; }
    public function setTrailerUrl(?string $trailerUrl): self { $this->trailerUrl = $trailerUrl; return $this; }

    public function getReleaseDate(): ?DateTimeInterface { return $this->releaseDate; }
    public function setReleaseDate(?DateTimeInterface $releaseDate): self { $this->releaseDate = $releaseDate; return $this; }

    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
}

