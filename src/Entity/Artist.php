<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Artist
{
    #[ORM\Id]
    #[ORM\Column]
    private string $id;
    #[ORM\Column]
    private bool $isFavorite;
    #[ORM\Column]
    private string $name;
    #[ORM\Column]
    private string $spotifyUrl;
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: "artist_user")]
    private Collection $favoritedBy;
    #[ORM\Column(nullable: true)]
    private ?string $pictureLink;
    #[ORM\Column(nullable: true)]
    private ?int $popularity;

    public function __construct(
        string $id,
        string $name,
        string $spotifyUrl,
        string $pictureLink = null,
        int $popularity = null,
        Collection $favoritedBy = new ArrayCollection()
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->spotifyUrl = $spotifyUrl;
        $this->pictureLink = $pictureLink;
        $this->popularity = $popularity;
        $this->favoritedBy = $favoritedBy;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getFavoritedByUsers(): Collection
    {
        return $this->favoritedBy;
    }

    public function setFavoritedByUsers(Collection $favoritedBy): void
    {
        $this->favoritedBy = $favoritedBy;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSpotifyUrl(): string
    {
        return $this->spotifyUrl;
    }

    public function setSpotifyUrl(string $spotifyUrl): void
    {
        $this->spotifyUrl = $spotifyUrl;
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
}
