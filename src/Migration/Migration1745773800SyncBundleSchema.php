<?php

declare(strict_types=1);

namespace ProductBundle\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Table;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1745773800SyncBundleSchema extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1745773800;
    }

    public function update(Connection $connection): void
    {
        $schemaManager = $connection->createSchemaManager();

        if ($schemaManager->tablesExist(['product_bundle'])) {
            $productBundle = $schemaManager->introspectTable('product_bundle');

            $this->addVarcharColumn($connection, $productBundle, 'name');
            $this->addVarcharColumn($connection, $productBundle, 'description');
            $this->addBoolColumn($connection, $productBundle, 'active', '1');
        }

        if ($schemaManager->tablesExist(['product_bundle_assigned_products'])) {
            $assignedProducts = $schemaManager->introspectTable('product_bundle_assigned_products');

            $this->addVarcharColumn($connection, $assignedProducts, 'name');
            $this->addVarcharColumn($connection, $assignedProducts, 'description');
            $this->addBoolColumn($connection, $assignedProducts, 'active', '1');
        }

        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `product_bundle_translation` (
                `id` BINARY(16) NOT NULL,
                `name` VARCHAR(255) NULL,
                `description` VARCHAR(255) NULL,
                `active` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }

    private function addVarcharColumn(Connection $connection, Table $table, string $column): void
    {
        if ($table->hasColumn($column)) {
            return;
        }

        $connection->executeStatement(
            sprintf(
                'ALTER TABLE `%s` ADD COLUMN `%s` VARCHAR(255) NULL',
                $table->getName(),
                $column
            )
        );
    }

    private function addBoolColumn(Connection $connection, Table $table, string $column, string $default): void
    {
        if ($table->hasColumn($column)) {
            return;
        }

        $connection->executeStatement(
            sprintf(
                'ALTER TABLE `%s` ADD COLUMN `%s` TINYINT(1) NOT NULL DEFAULT %s',
                $table->getName(),
                $column,
                $default
            )
        );
    }
}
