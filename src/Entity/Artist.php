<?php

namespace App\Entity;

use App\Repository\ArtistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArtistRepository::class)]
class Artist
{
    #[ORM\Id]
    #[ORM\Column]
    private string $id;
    private bool $isFavorite;
    #[ORM\Column]
    private string $name;
    #[ORM\Column(nullable: true)]
    private ?string $pictureLink;
    #[ORM\Column(nullable: true)]
    private ?int $popularity;
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: "bookmarkedArtists")]
    #[ORM\JoinTable(name: "user_artist")]
    private Collection $bookmarkedBy;

    public function __construct(
        string $id,
        string $name,
        string $pictureLink = null,
        int $popularity = null,
        Collection $bookmarkedBy = new ArrayCollection(),
        bool $isFavorite = false
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->pictureLink = $pictureLink;
        $this->popularity = $popularity;
        $this->bookmarkedBy = $bookmarkedBy;
        $this->isFavorite = $isFavorite;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPictureLink(): ?string
    {
        return $this->pictureLink;
    }

    public function setPictureLink(?string $pictureLink): void
    {
        $this->pictureLink = $pictureLink;
    }

    public function getPopularity(): ?int
    {
        return $this->popularity;
    }

    public function setPopularity(?int $popularity): void
    {
        $this->popularity = $popularity;
    }

    public function isFavorite(): bool
    {
        return $this->isFavorite;
    }

    public function setIsFavorite(bool $isFavorite): void
    {
        $this->isFavorite = $isFavorite;
    }

    public function getBookmarkedBy(): Collection
    {
        return $this->bookmarkedBy;
    }
}
