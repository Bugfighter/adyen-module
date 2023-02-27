<?php

namespace OxidSolutionCatalysts\Adyen\Service;

use OxidEsales\EshopCommunity\Application\Controller\FrontendController;
use OxidEsales\EshopCommunity\Internal\Framework\Templating\TemplateEngineInterface;
use OxidSolutionCatalysts\Adyen\Controller\OrderController;
use OxidSolutionCatalysts\Adyen\Controller\PaymentController;
use OxidSolutionCatalysts\Adyen\Core\ViewConfig;
use OxidSolutionCatalysts\Adyen\Model\Payment;
use Psr\Log\LoggerInterface;

class JSAPITemplateConfiguration
{
    private TemplateEngineInterface $templateEngine;
    private LoggerInterface $logger;
    private JSAPIConfigurationService $configurationService;

    public function __construct(
        TemplateEngineInterface $templateEngine,
        JSAPIConfigurationService $configurationService,
        LoggerInterface $logger
    ) {
        $this->templateEngine = $templateEngine;
        $this->logger = $logger;
        $this->configurationService = $configurationService;
    }

    public function getConfiguration(
        ViewConfig $viewConfig,
        FrontendController $controller,
        ?Payment $payment
    ): string {
        return $this->templateEngine->render(
            'modules/osc/adyen/payment/adyen_assets_configuration.tpl',
            $this->getViewData($viewConfig, $controller, $payment)
        );
    }

    private function getViewData(
        ViewConfig $viewConfig,
        FrontendController $controller,
        ?Payment $payment
    ): array {
        return [
            'configFields' => $this->getConfigFieldsJsonFormatted(
                $viewConfig,
                $controller,
                $payment
            ),
            'isLog' => $viewConfig->isAdyenLoggingActive(),
            'isPaymentPage' => $controller instanceof PaymentController,
            'isOrderPage' => $controller instanceof OrderController,
            'paymentConfigNeedsCard' => $this->paymentMethodsConfigurationNeedsCardField(
                $controller,
                $viewConfig,
                $payment
            ),
        ];
    }

    private function getConfigFieldsJsonFormatted(
        ViewConfig $viewConfig,
        FrontendController $controller,
        ?Payment $payment
    ): string {
        $configFieldsArray = $this->configurationService->getConfigFieldsAsArray($viewConfig, $controller, $payment);

        $configFieldsJson = json_encode($configFieldsArray);
        if (false === $configFieldsJson) {
            $this->logger->error(
                sprintf(
                    '%s::getDefaultConfigFieldsJsonFormatted error during json_encode `%s`',
                    self::class,
                    var_export($configFieldsArray, true)
                )
            );

            return '';
        }

        // replace leading and ending curly bracket, because, we need to join
        // js function in the resulting js object in adyen_assets_configuration.tpl
        $configFieldsJsonResult = preg_replace(
            [
                '/^{/',
                '/}$/',
                '/"([^"]+)":/'
            ],
            [
                '',
                '',
                '$1:'
            ],
            $configFieldsJson
        );

        if (is_null($configFieldsJsonResult)) {
            $this->logger->error(
                sprintf(
                    '%s::getDefaultConfigFieldsJsonFormatted error during preg_replace `%s`',
                    self::class,
                    $configFieldsJson
                )
            );

            return '';
        }

        return $configFieldsJsonResult;
    }

    private function paymentMethodsConfigurationNeedsCardField(
        FrontendController $controller,
        ViewConfig $viewConfig,
        ?Payment $payment
    ): bool {
        return $controller instanceof PaymentController
            && $payment instanceof Payment
            && $payment->showInPaymentCtrl()
            && $payment->getId() === $viewConfig->getAdyenPaymentCreditCardId();
    }
}
