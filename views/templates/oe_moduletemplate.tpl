[{capture append="oxidBlock_content"}]
<h1 class="page-header">[{oxmultilang ident="OEMODULETEMPLATE_GREETING_UPDATE_TITLE"}]</h1>

<br>
<form action="[{$oViewConf->getSelfActionLink()}]" name="OxsampleDoSomethingAction" method="post" role="form">
    <div>
        [{$oViewConf->getHiddenSid()}]
        <input type="hidden" name="cl" value="oetmgreeting">
        <input type="hidden" name="fnc" value="updateGreeting">
        <input type="text" class="editinput" maxlength="254" value="[{$oetm_greeting}]"
        <button type="submit" id="oetmgreeting_submit" class="submitButton">[{oxmultilang ident="SUBMIT"}]</button>
    </div>
</form>
<br>
[{/capture}]


[{include file="layout/page.tpl"}]