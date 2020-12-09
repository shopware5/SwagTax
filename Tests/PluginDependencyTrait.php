<?php
/**
 * Shopware Premium Plugins
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this plugin can be used under
 * a proprietary license as set forth in our Terms and Conditions,
 * section 2.1.2.2 (Conditions of Usage).
 *
 * The text of our proprietary license additionally can be found at and
 * in the LICENSE file you have received along with this plugin.
 *
 * This plugin is distributed in the hope that it will be useful,
 * with LIMITED WARRANTY AND LIABILITY as set forth in our
 * Terms and Conditions, sections 9 (Warranty) and 10 (Liability).
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the plugin does not imply a trademark license.
 * Therefore any rights, title and interest in our trademarks
 * remain entirely with us.
 */

namespace SwagTax\Tests;

trait PluginDependencyTrait
{
    /**
     * If plugin is not installed mark test as skipped
     *
     * @param string $technicalPluginName
     * @param bool   $skippTest
     *
     * @return bool
     */
    public function isPluginInstalled($technicalPluginName, $skippTest = true)
    {
        $result = !empty(Shopware()->Container()->get('dbal_connection')->fetchColumn(
            'SELECT id FROM s_core_plugins WHERE name LIKE :pluginName and active = 1',
            ['pluginName' => $technicalPluginName]
        ));

        if (!$result && $skippTest) {
            $this->markTestSkipped(sprintf('%s is not installed', $technicalPluginName));
        }

        return $result;
    }
}
