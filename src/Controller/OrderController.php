<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Adyen\Controller;

use OxidSolutionCatalysts\Adyen\Core\AdyenSession;
use OxidSolutionCatalysts\Adyen\Exception\Redirect;
use OxidSolutionCatalysts\Adyen\Model\Order;

class OrderController extends OrderController_parent
{
    /**
     * @inheritDoc
     *
     * @param integer $success status code
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @return  string  $sNextStep  partial parameter url for next step
     */
    protected function _getNextStep($success) // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        $redirectLink = AdyenSession::getRedirctLink();
        if (
            (Order::ORDER_STATE_ADYENPAYMENTNEEDSREDICRET == $success) &&
            $redirectLink
        ) {
            AdyenSession::deleteRedirctLink();
            throw new Redirect($redirectLink);
        }
        return parent::_getNextStep($success);
    }
}