<?php

namespace App\Entity;

use App\Repository\TrackRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TrackRepository::class)]
class Track
{
    #[ORM\Id]
    #[ORM\Column]
    private string $id;
    #[ORM\Column]
    private int $discNumber;
    #[ORM\Column]
    private int $durationMs;
    #[ORM\Column]
    private bool $explicit;
    #[ORM\Column]
    private string $isrc;
    #[ORM\Column]
    private string $spotifyUrl;
    #[ORM\Column]
    private string $href;
    #[ORM\Column]
    private bool $isLocal;
    #[ORM\Column]
    private string $name;
    #[ORM\Column]
    private int $popularity;
    #[ORM\Column(nullable: true)]
    private ?string $previewUrl;
    #[ORM\Column]
    private int $trackNumber;
    #[ORM\Column]
    private string $type;
    #[ORM\Column]
    private string $uri;
    #[ORM\Column(nullable: true)]
    private ?string $pictureLink;
    #[ORM\ManyToMany(targetEntity: Artist::class)]
    #[ORM\JoinTable(name: "track_artist")]
    private Collection $artists;
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: "track_user")]
    private Collection $favoritedBy;

    public function __construct(
        int $discNumber,
        int $durationMs,
        bool $explicit,
        string $isrc,
        string $spotifyUrl,
        string $href,
        string $id,
        bool $isLocal,
        string $name,
        int $popularity,
        ?string $previewUrl,
        int $trackNumber,
        string $type,
        string $uri,
        ?string $pictureLink,
        Collection $artists = new ArrayCollection(),
        Collection $favoritedBy = new ArrayCollection()
    ) {
        $this->discNumber = $discNumber;
        $this->durationMs = $durationMs;
        $this->explicit = $explicit;
        $this->isrc = $isrc;
        $this->spotifyUrl = $spotifyUrl;
        $this->href = $href;
        $this->id = $id;
        $this->isLocal = $isLocal;
        $this->name = $name;
        $this->popularity = $popularity;
        $this->previewUrl = $previewUrl;
        $this->trackNumber = $trackNumber;
        $this->type = $type;
        $this->uri = $uri;
        $this->pictureLink = $pictureLink;
        $this->artists = $artists;
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

    public function getDiscNumber(): int
    {
        return $this->discNumber;
    }

    public function setDiscNumber(int $discNumber): void
    {
        $this->discNumber = $discNumber;
    }

    public function getDurationMs(): int
    {
        return $this->durationMs;
    }

    public function setDurationMs(int $durationMs): void
    {
        $this->durationMs = $durationMs;
    }

    public function isExplicit(): bool
    {
        return $this->explicit;
    }

    public function setExplicit(bool $explicit): void
    {
        $this->explicit = $explicit;
    }

    public function getIsrc(): string
    {
        return $this->isrc;
    }

    public function setIsrc(string $isrc): void
    {
        $this->isrc = $isrc;
    }

    public function getSpotifyUrl(): string
    {
        return $this->spotifyUrl;
    }

    public function setSpotifyUrl(string $spotifyUrl): void
    {
        $this->spotifyUrl = $spotifyUrl;
    }

    public function getHref(): string
    {
        return $this->href;
    }

    public function setHref(string $href): void
    {
        $this->href = $href;
    }

    public function isLocal(): bool
    {
        return $this->isLocal;
    }

    public function setIsLocal(bool $isLocal): void
    {
        $this->isLocal = $isLocal;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPopularity(): int
    {
        return $this->popularity;
    }

    public function setPopularity(int $popularity): void
    {
        $this->popularity = $popularity;
    }

    public function getPreviewUrl(): ?string
    {
        return $this->previewUrl;
    }

    public function setPreviewUrl(?string $previewUrl): void
    {
        $this->previewUrl = $previewUrl;
    }

    public function getTrackNumber(): int
    {
        return $this->trackNumber;
    }

    public function setTrackNumber(int $trackNumber): void
    {
        $this->trackNumber = $trackNumber;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function setUri(string $uri): void
    {
        $this->uri = $uri;
    }

    public function getPictureLink(): ?string
    {
        return $this->pictureLink;
    }

    public function setPictureLink(?string $pictureLink): void
    {
        $this->pictureLink = $pictureLink;
    }

    public function getArtists(): Collection
    {
        return $this->artists;
    }

    public function setArtists(Collection $artists): void
    {
        $this->artists = $artists;
    }

    public function getFavoritedBy(): Collection
    {
        return $this->favoritedBy;
    }

    public function setFavoritedBy(Collection $favoritedBy): void
    {
        $this->favoritedBy = $favoritedBy;
    }

    public function addArtist(Artist $artist): void
    {
        $this->artists->add($artist);
    }

    public function removeArtist(Artist $artist): void
    {
        $this->artists->removeElement($artist);
    }
}
