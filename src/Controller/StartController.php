<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ModuleTemplate\Controller;

use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\ModuleTemplate\Service\GreetingMessage;
use OxidEsales\ModuleTemplate\Service\ModuleSettings;
use OxidEsales\ModuleTemplate\Traits\ServiceContainer;

final class StartController extends StartController_parent
{
    use ServiceContainer;

    /**
     * All we need here is to fetch the information we need from a service.
     * As in our example we extend a block of a template belonging ONLY
     * to the shop's StartController, we extend that Controller with a new method.
     * NOTE: only leaf classes can be extended this way. The FrontendController class which
     *      many Controllers inherit from cannot be extended this way.
     */
    public function getOetmGreeting(): string
    {
        $service = $this->getServiceFromContainer(GreetingMessage::class);

        $user   = $this->getUser() ?: null;
        $result = $service->getOetmGreeting($user);

        $result = EshopRegistry::getLang()->translateString($result);

        return is_array($result) ? (string) array_pop($result) : $result;
    }

    public function canUpdateOetmGreeting(): bool
    {
        $moduleSettings = $this->getServiceFromContainer(ModuleSettings::class);

        return $this->getUser() && $moduleSettings->isPersonalGreetingMode();
    }
}
