<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ModuleTemplate\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleSettingBridgeInterface;
use OxidEsales\ModuleTemplate\Core\Module;

class ModuleSettings
{
    public const GREETING_MODE = 'oemoduletemplate_GreetingMode';

    public const GREETING_MODE_GENERIC = 'generic';

    public const GREETING_MODE_PERSONAL = 'personal';

    public const GREETING_MODE_VALUES = [
        self::GREETING_MODE_GENERIC,
        self::GREETING_MODE_PERSONAL,
    ];

    /** @var ModuleSettingBridgeInterface */
    private $moduleSettingBridge;

    public function __construct(
        ModuleSettingBridgeInterface $moduleSettingBridge
    ) {
        $this->moduleSettingBridge = $moduleSettingBridge;
    }

    public function getGreetingMode(): string
    {
        $value = (string) $this->getSettingValue(self::GREETING_MODE);

        return (!empty($value) && in_array($value, self::GREETING_MODE_VALUES)) ? $value : self::GREETING_MODE_GENERIC;
    }

    public function save(string $name, mixed $value): void
    {
        $this->moduleSettingBridge->save($name, $value, Module::MODULE_ID);
    }

    /**
     * @return mixed
     */
    private function getSettingValue(string $key)
    {
        return $this->moduleSettingBridge->get($key, Module::MODULE_ID);
    }
}
