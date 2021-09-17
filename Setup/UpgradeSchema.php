<?php

namespace ImageKit\ImageKitMagento\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.0', '<')) {
            $this->createLibraryMapTable($setup);
        }

        $setup->endSetup();
    }

    private function createLibraryMapTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()->newTable($setup->getTable('imagekit_library_map'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'image_path',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Image Path'
            )
            ->addColumn(
                'ik_path',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'ImageKit Path'
            )
            ->addIndex(
                $setup->getIdxName('imagekit_library_map', ['image_path'], AdapterInterface::INDEX_TYPE_UNIQUE),
                ['image_path'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            );

        $setup->getConnection()->createTable($table);
    }
}
