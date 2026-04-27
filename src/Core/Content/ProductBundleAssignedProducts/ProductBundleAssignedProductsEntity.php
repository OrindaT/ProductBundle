<?php

declare(strict_types=1);

namespace ProductBundle\Core\Content\ProductBundleAssignedProducts;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Content\Product\ProductEntity;
use ProductBundle\Core\Content\ProductBundle\ProductBundleEntity;

class ProductBundleAssignedProductsEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $bundleId;
    protected ?ProductBundleEntity $bundle;

    protected ?string $productId;
    protected ?ProductEntity $product;

    protected int $quantity = 1;

    public function getBundleId(): ?string
    {
        return $this->bundleId;
    }
    public function setBundleId(?string $bundleId): void
    {
        $this->bundleId = $bundleId;
    }

    public function getBundle(): ?ProductBundleEntity
    {
        return $this->bundle;
    }
    public function setBundle(?ProductBundleEntity $bundle): void
    {
        $this->bundle = $bundle;
    }

    public function getProductId(): ?string
    {
        return $this->productId;
    }
    public function setProductId(?string $productId): void
    {
        $this->productId = $productId;
    }

    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }
    public function setProduct(?ProductEntity $product): void
    {
        $this->product = $product;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }
    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

}
