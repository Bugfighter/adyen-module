[{if $paymentmethod|method_exists:'isAdyenPayment' && $paymentmethod->isAdyenPayment()}]
    [{* Show Adyen Payments only if Adyen is healthy *}]
    [{if $paymentmethod->showInPaymentCtrl() && $oViewConf->checkAdyenHealth()}]
        [{* We include it as template, so that it can be modified in custom themes *}]
        [{include file="modules/osc/adyen/payment/adyen_payment.tpl"}]
    [{/if}]
[{else}]
    [{$smarty.block.parent}]
[{/if}]