<?php

namespace ProductBundle\Storefront\Subscriber;

use Shopware\Core\Content\Product\Events\ProductListingCriteriaEvent;
use Shopware\Core\Content\Product\Events\ProductSearchCriteriaEvent;
use Shopware\Core\Content\Product\Events\ProductSuggestCriteriaEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Storefront\Page\Product\ProductPageCriteriaEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ProductPageCriteriaEvent::class => 'onProductPageCriteria',
            ProductListingCriteriaEvent::class => 'onProductListingCriteria',
            ProductSearchCriteriaEvent::class => 'onProductSearchCriteria',
            ProductSuggestCriteriaEvent::class => 'onProductSuggestCriteria',
        ];
    }

    public function onProductPageCriteria(ProductPageCriteriaEvent $event): void
    {
        $this->addBundleAssociation($event->getCriteria());
    }

    public function onProductListingCriteria(ProductListingCriteriaEvent $event): void
    {
        $this->addBundleAssociation($event->getCriteria());
    }

    public function onProductSearchCriteria(ProductSearchCriteriaEvent $event): void
    {
        $this->addBundleAssociation($event->getCriteria());
    }

    public function onProductSuggestCriteria(ProductSuggestCriteriaEvent $event): void
    {
        $this->addBundleAssociation($event->getCriteria());
    }

    private function addBundleAssociation(Criteria $criteria): void
    {
        $criteria->addAssociation('bundleProducts.assignedProducts.product.options.group');
        $criteria->addAssociation('bundleProducts.product');
    }
}
