<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Adyen\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\ConfigFile;
use OxidEsales\Facts\Facts;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220908162100 extends AbstractMigration
{
    public function __construct($version)
    {
        parent::__construct($version);

        $this->platform->registerDoctrineTypeMapping('enum', 'string');
    }

    public function up(Schema $schema): void
    {
        $this->createAdyenHistoryTable($schema);
        $this->createAdyenPSPReference($schema);
    }

    public function down(Schema $schema): void
    {
    }

    /**
     * create Adyen PSP Reference in oxorder
     */
    protected function createAdyenPSPReference(Schema $schema): void
    {
        $order = $schema->getTable('oxorder');

        if (!$order->hasColumn('ADYENPSPREFERENCE')) {
            $order->addColumn(
                'ADYENPSPREFERENCE',
                Types::STRING,
                ['columnDefinition' => 'char(32) collate latin1_general_ci']
            );
        }
    }

    /**
     * create Adyen History order table
     */
    protected function createAdyenHistoryTable(Schema $schema): void
    {
        if (!$schema->hasTable('oscadyenhistory')) {
            $historyTable = $schema->createTable('oscadyenhistory');
        } else {
            $historyTable = $schema->getTable('oscadyenhistory');
        }

        if (!$historyTable->hasColumn('OXID')) {
            $historyTable->addColumn(
                'OXID',
                Types::STRING,
                ['columnDefinition' => 'char(32) collate latin1_general_ci']
            );
        }
        if (!$historyTable->hasColumn('OXSHOPID')) {
            $historyTable->addColumn(
                'OXSHOPID',
                Types::INTEGER,
                [
                    'columnDefinition' => 'int(11)',
                    'default' => 1,
                    'comment' => 'Shop ID (oxshops)'
                ]
            );
        }
        if (!$historyTable->hasColumn('OXORDERID')) {
            $historyTable->addColumn(
                'OXORDERID',
                Types::STRING,
                [
                    'columnDefinition' => 'char(32) collate latin1_general_ci',
                    'comment' => 'OXID Order id (oxorder)'
                ]
            );
        }
        if (!$historyTable->hasColumn('PSPREFERENCE')) {
            $historyTable->addColumn(
                'PSPREFERENCE',
                Types::STRING,
                [
                    'columnDefinition' => 'char(32) collate latin1_general_ci',
                    'comment' => 'Adyen Payment Service Provider-Reference'
                ]
            );
        }
        if (!$historyTable->hasColumn('PARENTPSPREFERENCE')) {
            $historyTable->addColumn(
                'PARENTPSPREFERENCE',
                Types::STRING,
                [
                    'columnDefinition' => 'char(32) collate latin1_general_ci',
                    'comment' => 'Adyen Parent or Original Payment Service Provider-Reference'
                ]
            );
        }
        if (!$historyTable->hasColumn('OXPRICE')) {
            $historyTable->addColumn(
                'OXPRICE',
                Types::STRING,
                [
                    'columnDefinition' => 'double not null',
                    'default' => 0,
                    'comment' => 'Adyen Price'
                ]
            );
        }
        if (!$historyTable->hasColumn('OXTIMESTAMP')) {
            $historyTable->addColumn(
                'OXTIMESTAMP',
                Types::DATETIME_MUTABLE,
                ['columnDefinition' => 'timestamp default current_timestamp on update current_timestamp']
            );
        }
        if (!$historyTable->hasPrimaryKey('OXID')) {
            $historyTable->setPrimaryKey(['OXID']);
        }
        if (!$historyTable->hasIndex('OXSHOPID_OXORDERID_PSPREFERENCE')) {
            $historyTable->addUniqueIndex(['OXSHOPID', 'OXORDERID', 'PSPREFERENCE'], 'OXSHOPID_OXORDERID_PSPREFERENCE');
        }
    }
}