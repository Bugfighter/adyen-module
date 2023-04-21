<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Adyen\Service;

use Exception;
use Adyen\AdyenException;
use OxidSolutionCatalysts\Adyen\Model\AdyenAPICaptures;

/**
 * @extendable-class
 */
class AdyenAPIResponseCaptures extends AdyenAPIResponse
{
    /**
     * @param AdyenAPICaptures $captureParams
     * @throws AdyenException
     * @return mixed
     */
    public function setCapture(AdyenAPICaptures $captureParams)
    {
        $result = false;
        try {
            $service = $this->createCheckout();
            $params = $captureParams->getAdyenCapturesParams();
            $result = $service->captures($params);
            if (!$result) {
                throw $this->getPaymentsNotFoundException();
            }
        } catch (AdyenException | Exception $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
        }
        return $result;
    }
}
