[{if $oViewConf|method_exists:'checkAdyenHealth' && $oViewConf->checkAdyenHealth()}]
    [{* We include it as template, so that it can be modified in custom themes *}]
    [{include file="modules/osc/adyen/payment/adyen_payment_psp.tpl"}]
[{/if}]
[{$smarty.block.parent}]