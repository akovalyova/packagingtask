<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a box available in the warehouse.
 *
 * Warehouse workers pack a set of products for a given order into one of these boxes.
 */
#[ORM\Entity]
class Packaging
{

    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(type: Types::FLOAT)]
    private float $width;

    #[ORM\Column(type: Types::FLOAT)]
    private float $height;

    #[ORM\Column(type: Types::FLOAT)]
    private float $length;

    #[ORM\Column(type: Types::FLOAT)]
    private float $maxWeight;

    public function __construct(float $width, float $height, float $length, float $maxWeight)
    {
        $this->width = $width;
        $this->height = $height;
        $this->length = $length;
        $this->maxWeight = $maxWeight;
    }

    public function getMaxWeight(): float
    {
        return $this->maxWeight;
    }

    public function setMaxWeight(float $maxWeight): void
    {
        $this->maxWeight = $maxWeight;
    }

    public function getLength(): float
    {
        return $this->length;
    }

    public function setLength(float $length): void
    {
        $this->length = $length;
    }

    public function getHeight(): float
    {
        return $this->height;
    }

    public function setHeight(float $height): void
    {
        $this->height = $height;
    }

    public function getWidth(): float
    {
        return $this->width;
    }

    public function setWidth(float $width): void
    {
        $this->width = $width;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

}
