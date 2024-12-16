<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "cache")]
class Cache
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:  Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, unique: true)]
    private string $requestHash;

    #[ORM\Column(type:  Types::JSON)]
    private array $response;

    #[ORM\Column(type: TYPES::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: TYPES::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $ttl;

    public function __construct(string $requestHash, array $response, ?\DateTime $ttl = null)
    {
        $this->requestHash = $requestHash;
        $this->response = $response;
        $this->createdAt = new \DateTimeImmutable();
        $this->ttl = $ttl;
    }

    public function getRequestHash(): string
    {
        return $this->requestHash;
    }

    public function getResponse(): array
    {
        return $this->response;
    }

    public function getTtl(): ?\DateTime
    {
        return $this->ttl;
    }
    public function setTtl(?\DateTime $ttl): void
    {
        $this->ttl = $ttl;
    }

    public function isExpired(): bool
    {
        return $this->ttl !== null && $this->ttl < new \DateTime();
    }

    public function setRequestHash(string $requestHash): void
    {
        $this->requestHash = $requestHash;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
