[{if $oViewConf|method_exists:'checkAdyenHealth' && $oViewConf->checkAdyenHealth() && !$oViewConf->isInAdyenAuthorization()}]
    [{* We include it as template, so that it can be modified in custom themes *}]
    [{include file="modules/osc/adyen/payment/adyen_assets.tpl"}]
[{/if}]
[{$smarty.block.parent}]