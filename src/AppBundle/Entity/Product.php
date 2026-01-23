<?php

declare(strict_types=1);

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Product as BaseProduct;

class Product extends BaseProduct
{
    public const AVAILABILITY_IN_STOCK = 'en_stock';
    public const AVAILABILITY_OUT_OF_STOCK = 'rupture';

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $occasion;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, options={"default": "en_stock"})
     */
    private $availability = self::AVAILABILITY_IN_STOCK;

    public function getOccasion(): ?string
    {
        return $this->occasion;
    }

    public function setOccasion(?string $occasion): void
    {
        $this->occasion = $occasion;
    }

    public function getAvailability(): string
    {
        return $this->availability;
    }

    public function setAvailability(string $availability): void
    {
        $this->availability = $availability;
    }

    public function isInStock(): bool
    {
        return $this->availability === self::AVAILABILITY_IN_STOCK;
    }

    public function isOutOfStock(): bool
    {
        return $this->availability === self::AVAILABILITY_OUT_OF_STOCK;
    }
}