<?php

declare(strict_types=1);

namespace ProductBundle\Core\Content\ProductBundle;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use ProductBundle\Core\Content\ProductBundleAssignedProducts\ProductBundleAssignedProductsDefinition;
use Shopware\Core\Content\Product\ProductDefinition;

class ProductBundleDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'product_bundle';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ProductBundleEntity::class;
    }

    public function getCollectionClass(): string
    {
        return ProductBundleCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            (new StringField('name', 'name')),
            (new StringField('description', 'description')),
            (new BoolField('active', 'active')),
            (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new Required()),
            new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class, 'id', false),
            new OneToManyAssociationField(
                'assignedProducts',
                ProductBundleAssignedProductsDefinition::class,
                'bundle_id'
            )
        ]);
    }
}
