<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'movies')]
class Movie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $synopsis = null;

    #[ORM\Column(type: 'string', length: 1024, nullable: true)]
    private ?string $posterUrl = null;

    #[ORM\Column(type: 'string', length: 1024, nullable: true)]
    private ?string $videoUrl = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToMany(targetEntity: Actor::class, cascade: ['persist'])]
    #[ORM\JoinTable(name: 'movie_actor')]
    private Collection $actors;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->actors = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }
    public function getSynopsis(): ?string { return $this->synopsis; }
    public function setSynopsis(?string $synopsis): self { $this->synopsis = $synopsis; return $this; }
    public function getPosterUrl(): ?string { return $this->posterUrl; }
    public function setPosterUrl(?string $posterUrl): self { $this->posterUrl = $posterUrl; return $this; }
    public function getVideoUrl(): ?string { return $this->videoUrl; }
    public function setVideoUrl(?string $videoUrl): self { $this->videoUrl = $videoUrl; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    /** @return Collection<int, Actor> */
    public function getActors(): Collection { return $this->actors; }
    public function addActor(Actor $actor): self { if (!$this->actors->contains($actor)) { $this->actors->add($actor);} return $this; }
    public function removeActor(Actor $actor): self { $this->actors->removeElement($actor); return $this; }
}