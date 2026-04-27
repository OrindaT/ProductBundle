<?php

declare(strict_types=1);

namespace ProductBundle\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1738700000CreateBundleTables extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1738700000;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `product_bundle` (
                `id` BINARY(16) NOT NULL,
                `product_id` BINARY(16) NOT NULL,
                `name` VARCHAR(255) NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `fk.product_bundle.product_id`
                    FOREIGN KEY (`product_id`)
                    REFERENCES `product` (`id`)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `product_bundle_assigned_products` (
                `id` BINARY(16) NOT NULL,
                `bundle_id` BINARY(16) NOT NULL,
                `product_id` BINARY(16) NOT NULL,
                `quantity` INT(11) NOT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `fk.bundle_assigned.bundle_id`
                    FOREIGN KEY (`bundle_id`)
                    REFERENCES `product_bundle` (`id`)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void {}
}
