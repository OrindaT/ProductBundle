<?php

namespace ProductBundle\Extension;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use ProductBundle\Core\Content\ProductBundle\ProductBundleDefinition;

class ProductExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField(
                'bundleProducts',
                ProductBundleDefinition::class,
                'product_id'
            )
        );
    }

    public function getDefinitionClass(): string
    {
        return ProductDefinition::class;
    }

    public function getEntityName(): string
    {
        return 'product';
    }
}
