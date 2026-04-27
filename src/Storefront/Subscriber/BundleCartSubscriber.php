<?php

declare(strict_types=1);

namespace ProductBundle\Storefront\Subscriber;

use Shopware\Core\Checkout\Cart\Event\BeforeLineItemAddedEvent;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BundleCartSubscriber implements EventSubscriberInterface
{
    private const CHILD_FLAG = 'productBundleChild';
    private const BUNDLE_ID = 'productBundleId';
    private const PARENT_PRODUCT_ID = 'productBundleParentProductId';
    private const PARENT_LINE_ITEM_ID = 'productBundleParentLineItemId';
    private const BUNDLE_QUANTITY = 'productBundleQuantity';

    public function __construct(
        private readonly EntityRepository $productRepository
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeLineItemAddedEvent::class => 'onBeforeLineItemAdded',
        ];
    }

    public function onBeforeLineItemAdded(BeforeLineItemAddedEvent $event): void
    {
        $addedLineItem = $event->getLineItem();
        $lineItem = $event->getCart()->get($addedLineItem->getId());

        if (!$lineItem instanceof LineItem || $lineItem->getType() !== LineItem::PRODUCT_LINE_ITEM_TYPE) {
            return;
        }

        if ($lineItem->hasPayloadValue(self::CHILD_FLAG)) {
            return;
        }

        $referencedId = $lineItem->getReferencedId();
        if (!$referencedId) {
            return;
        }

        $product = $this->loadProductWithBundles($referencedId, $event);
        if (!$product instanceof ProductEntity) {
            return;
        }

        $bundles = $product->getExtension('bundleProducts');
        if (!$bundles || $bundles->count() === 0) {
            return;
        }

        foreach ($bundles as $bundle) {
            if (!$bundle->isActive() || !$bundle->getAssignedProducts()) {
                continue;
            }

            foreach ($bundle->getAssignedProducts() as $assignedProduct) {
                $assignedProductId = $assignedProduct->getProductId();

                if (!$assignedProductId) {
                    continue;
                }

                $bundleLineItem = new LineItem(
                    $this->buildBundleLineItemId($lineItem->getId(), $bundle->getId(), $assignedProductId),
                    LineItem::PRODUCT_LINE_ITEM_TYPE,
                    $assignedProductId,
                    $assignedProduct->getQuantity() * $addedLineItem->getQuantity()
                );

                $bundleLineItem->setRemovable(false);
                $bundleLineItem->setStackable(true);
                $bundleLineItem->setPayloadValue(self::CHILD_FLAG, true);
                $bundleLineItem->setPayloadValue(self::BUNDLE_ID, $bundle->getId());
                $bundleLineItem->setPayloadValue(self::PARENT_PRODUCT_ID, $referencedId);
                $bundleLineItem->setPayloadValue(self::PARENT_LINE_ITEM_ID, $lineItem->getId());
                $bundleLineItem->setPayloadValue(self::BUNDLE_QUANTITY, $assignedProduct->getQuantity());

                $event->getCart()->add($bundleLineItem);
            }
        }
    }

    private function buildBundleLineItemId(string $parentLineItemId, string $bundleId, string $productId): string
    {
        return 'bundle-' . substr(hash('sha256', $parentLineItemId . $bundleId . $productId), 0, 32);
    }

    private function loadProductWithBundles(string $productId, BeforeLineItemAddedEvent $event): ?ProductEntity
    {
        $criteria = new Criteria([$productId]);
        $criteria->addAssociation('bundleProducts.assignedProducts.product');

        /** @var ProductEntity|null $product */
        $product = $this->productRepository
            ->search($criteria, $event->getContext())
            ->first();

        return $product;
    }
}
