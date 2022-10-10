<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Adyen\Tests\Integration\Model;

use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidSolutionCatalysts\Adyen\Model\Order;
use OxidSolutionCatalysts\Adyen\Core\Module;

class OrderTest extends UnitTestCase
{
    private const PAYMENT_DESC_ADYEN = 'Adyen';
    private const PAYMENT_DESC_DUMMY = 'TestDummy';

    public function setup(): void
    {
        parent::setUp();
        foreach ($this->providerTestOrderData() as $dataSet) {
            [$orderId, $orderData, ] = $dataSet;
            $order = oxNew(Order::class);
            $order->setId($orderId);
            $order->assign($orderData);
            $order->save();
        }
        foreach ($this->providerTestPaymentData() as $dataSet) {
            [$paymentId, $paymentData] = $dataSet;
            $payment = oxNew(Payment::class);
            $payment->setId($paymentId);
            $payment->assign($paymentData);
            $payment->save();
        }
    }

    public function tearDown(): void
    {
        parent::tearDown();
        foreach ($this->providerTestOrderData() as $dataSet) {
            [$orderId, , ] = $dataSet;
            $order = oxNew(Order::class);
            $order->load($orderId);
            $order->delete();
        }
        foreach ($this->providerTestPaymentData() as $dataSet) {
            [$paymentId, ] = $dataSet;
            $payment = oxNew(Payment::class);
            $payment->load($paymentId);
            $payment->delete();
        }
    }

    /**
     * @dataProvider providerTestOrderData
     */
    public function testIsAdyenOrder($orderId, $orderData, $paymentName): void
    {
        $order = oxNew(Order::class);
        $order->load($orderId);
        $isAdyenOrder = (
            $orderData['oxorder__oxpaymenttype'] === Module::STANDARD_PAYMENT_ID &&
            $orderData['oxorder__adyenpspreference'] !== ''
        );
        $this->assertSame($isAdyenOrder, $order->isAdyenOrder());
    }

    /**
     * @dataProvider providerTestOrderData
     */
    public function testGetAdyenPaymentName($orderId, $orderData, $paymentName): void
    {
        $order = oxNew(Order::class);
        $order->load($orderId);

        $this->assertSame($paymentName, $order->getAdyenPaymentName());
    }

    public function providerTestOrderData(): array
    {
        return [
            [
                '123',
                [
                    'oxorder__oxpaymenttype' => Module::STANDARD_PAYMENT_ID,
                    'oxorder__adyenpspreference' => 'test',
                ],
                self::PAYMENT_DESC_ADYEN

            ],
            [
                '456',
                [
                    'oxorder__oxpaymenttype' => 'dummy'
                ],
                self::PAYMENT_DESC_DUMMY
            ]
        ];
    }

    public function providerTestPaymentData(): array
    {
        return [
            [
                Module::STANDARD_PAYMENT_ID,
                [
                    'oxpayments__oxdesc' => self::PAYMENT_DESC_ADYEN
                ],

            ],
            [
                'dummy',
                [
                    'oxpayments__oxdesc' => self::PAYMENT_DESC_DUMMY
                ]
            ]
        ];
    }
}
