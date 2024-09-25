<?php

namespace App\Entity;

class Artist
{
    private string $id;
    private string $name;
    private string $uri;

    public function __construct(
        string $id,
        string $name,
        string $uri,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->uri = $uri;
    }

    // Getters for all properties
    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUri(): string
    {
        return $this->uri;
    }
}
