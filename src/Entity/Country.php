<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\CountryRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CountryRepository::class)]
#[ApiResource(
    routePrefix: '/v1',
    operations: [
        new Get(uriTemplate: '/countries/{cca3}'),
        new GetCollection(uriTemplate: '/countries'),
        new Post(uriTemplate: '/countries'),
        new Patch(uriTemplate: '/countries/{cca3}'),
        new Delete(uriTemplate: '/countries/{cca3}'),
    ]
)]
class Country
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ApiProperty(identifier: false)]
    private ?string $uuid = null;

    #[ORM\Column(length: 3, unique: true)]
    #[ApiProperty(identifier: true)]
    private ?string $cca3 = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $region = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $subregion = null;

    #[ORM\Column(length: 255)]
    private array $demonym = [];

    #[ORM\Column]
    private ?int $population = null;

    #[ORM\Column]
    private ?bool $independant = null;

    #[ORM\Column(length: 255)]
    private ?string $flag = null;

    #[ORM\Column]
    private array $currency = [];

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(string $region): static
    {
        $this->region = $region;

        return $this;
    }

    public function getSubregion(): ?string
    {
        return $this->subregion;
    }

    public function setSubregion(?string $subregion): static
    {
        $this->subregion = $subregion;

        return $this;
    }

    public function getDemonym(): ?array
    {
        return $this->demonym;
    }

    public function setDemonym(array $demonym): static
    {
        $this->demonym = $demonym;

        return $this;
    }

    public function getPopulation(): ?int
    {
        return $this->population;
    }

    public function setPopulation(int $population): static
    {
        $this->population = $population;

        return $this;
    }

    public function isIndependant(): ?bool
    {
        return $this->independant;
    }

    public function setIndependant(bool $independant): static
    {
        $this->independant = $independant;

        return $this;
    }

    public function getFlag(): ?string
    {
        return $this->flag;
    }

    public function setFlag(string $flag): static
    {
        $this->flag = $flag;

        return $this;
    }

    public function getCurrency(): array
    {
        return $this->currency;
    }

    public function setCurrency(array $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getCca3(): ?string
    {
        return $this->cca3;
    }

    public function setCca3(string $cca3): static
    {
        $this->cca3 = $cca3;

        return $this;
    }
}
