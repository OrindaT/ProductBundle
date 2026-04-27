<?php declare(strict_types=1);

namespace ProductBundle\Core\Content\ProductBundle;

use ProductBundle\Core\Content\ProductBundleAssignedProducts\ProductBundleAssignedProductsCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ProductBundleEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $productId = null;
    protected ?ProductEntity $product = null;
    protected ?string $name;
    protected ?string $description;
    protected bool $active;
    protected ?ProductBundleAssignedProductsCollection $assignedProducts = null;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getAssignedProducts(): ?ProductBundleAssignedProductsCollection
    {
        return $this->assignedProducts;
    }

    public function setAssignedProducts(?ProductBundleAssignedProductsCollection $assignedProducts): void
    {
        $this->assignedProducts = $assignedProducts;
    }
}
