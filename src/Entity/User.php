<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var ?string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;
    #[ORM\ManyToMany(targetEntity: Track::class, inversedBy: "bookmarkedBy")]
    #[ORM\JoinTable(name: "user_track")]
    private Collection $bookmarkedTracks;
    #[ORM\ManyToMany(targetEntity: Artist::class, inversedBy: "bookmarkedBy")]
    #[ORM\JoinTable(name: "user_artist")]
    private Collection $bookmarkedArtists;

    public function __construct()
    {
        $this->bookmarkedTracks = new ArrayCollection();
        $this->bookmarkedArtists = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->id;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getBookmarkedTracks(): Collection
    {
        return $this->bookmarkedTracks;
    }

    public function addBookmarkedTrack(Track $track): void
    {
        if (!$this->bookmarkedTracks->contains($track)) {
            $this->bookmarkedTracks->add($track);
        }
    }

    public function removeBookmarkedTrack(Track $track): void
    {
        if ($this->bookmarkedTracks->contains($track)) {
            $this->bookmarkedTracks->removeElement($track);
        }
    }

    public function getBookmarkedArtists(): Collection
    {
        return $this->bookmarkedArtists;
    }

    public function addBookmarkedArtist(Artist $artist): void
    {
        if (!$this->bookmarkedArtists->contains($artist)) {
            $this->bookmarkedArtists->add($artist);
        }
    }

    public function removeBookmarkedArtist(Artist $artist): void
    {
        if ($this->bookmarkedArtists->contains($artist)) {
            $this->bookmarkedArtists->removeElement($artist);
        }
    }
}
