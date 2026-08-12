<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ManufacturerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ManufacturerRepository::class)]
class Manufacturer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Manufacturer name is required.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Manufacturer name cannot exceed {{ limit }} characters.',
    )]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: 'Enter a valid website URL.', requireTld: true)]
    #[Assert\Length(max: 255)]
    private ?string $website = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Assert\Email(message: 'Enter a valid support email address.')]
    #[Assert\Length(max: 180)]
    private ?string $supportEmail = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    private ?string $supportPhone = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    private bool $isActive = true;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): static
    {
        $this->website = $website;

        return $this;
    }

    public function getSupportEmail(): ?string
    {
        return $this->supportEmail;
    }

    public function setSupportEmail(?string $supportEmail): static
    {
        $this->supportEmail = $supportEmail;

        return $this;
    }

    public function getSupportPhone(): ?string
    {
        return $this->supportPhone;
    }

    public function setSupportPhone(?string $supportPhone): static
    {
        $this->supportPhone = $supportPhone;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function activate(): static
    {
        $this->isActive = true;

        return $this;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function deactivate(): static
    {
        $this->isActive = false;

        return $this;
    }
}
