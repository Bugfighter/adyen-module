<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Adyen\Core;

use Exception;
use OxidEsales\DoctrineMigrationWrapper\MigrationsBuilder;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidSolutionCatalysts\Adyen\Model\Payment;
use OxidSolutionCatalysts\Adyen\Service\ModuleSettings;
use OxidSolutionCatalysts\Adyen\Service\StaticContents;
use OxidEsales\Eshop\Application\Model\Payment as EshopModelPayment;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Class defines what module does on Shop events.
 *
 * @codeCoverageIgnore
 */
final class ModuleEvents
{
    /**
     * Execute action on activate event
     *
     * @throws Exception
     */
    public static function onActivate(): void
    {
        // execute module migrations
        self::executeModuleMigrations();

        // add static contents like payment methods
        //NOTE: this assumes the module's servies.yaml is already in place at the time this method is called
        self::addStaticContents();
    }

    /**
     * Execute action on deactivate event
     *
     * @throws Exception
     */
    public static function onDeactivate(): void
    {
        $activePaymentMethods = [];
        $paymentIds = array_keys(Module::PAYMENT_DEFINTIONS);
        foreach ($paymentIds as $paymentId) {
            /** @var $paymentMethod Payment */
            $paymentMethod = oxNew(EshopModelPayment::class);
            if (
                $paymentMethod->load($paymentId)
                && $paymentMethod->getAdyenBoolData('oxactive') === true
            ) {
                $paymentMethod->assign([
                    'oxpayments__oxactive' => true
                ]);
                $paymentMethod->save();
                $activePaymentMethods[] = $paymentId;
            }
        }

        /** @var ModuleSettings $service */
        $service = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleSettings::class);
        $service->saveActivePayments($activePaymentMethods);
    }

    /**
     * Execute necessary module migrations on activate event
     */
    private static function executeModuleMigrations(): void
    {
        $migrations = (new MigrationsBuilder())->build();

        $output = new BufferedOutput();
        $migrations->setOutput($output);
        $neeedsUpdate = $migrations->execute('migrations:up-to-date', 'osc_adyen');

        if ($neeedsUpdate) {
            $migrations->execute('migrations:migrate', 'osc_adyen');
        }
    }

    /**
     * add Static Contents like payment methods
     *
     * @return void
     */
    private static function addStaticContents(): void
    {
        /** @var StaticContents $service */
        $service = ContainerFactory::getInstance()
            ->getContainer()
            ->get(StaticContents::class);

        $service->ensurePaymentMethods();
    }
}
