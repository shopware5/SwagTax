<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagTax\Components\ThirdPlugins;

use SwagTax\Structs\TaxMapping;
use SwagTax\Structs\UpdateConfig;

class ThirdPluginUpdater
{
    /**
     * @var ThirdPluginHandlerFactory
     */
    private $thirdPluginHandlerFactory;

    public function __construct(ThirdPluginHandlerFactory $thirdPluginHandlerFactory)
    {
        $this->thirdPluginHandlerFactory = $thirdPluginHandlerFactory;
    }

    public function update(UpdateConfig $config, TaxMapping $taxMapping)
    {
        /** @var AbstractThirdPlugin $pluginHandler */
        foreach ($this->thirdPluginHandlerFactory->getThirdPluginHandler() as $pluginHandler) {
            $pluginHandler->update($config, $taxMapping);
        }
    }
}
