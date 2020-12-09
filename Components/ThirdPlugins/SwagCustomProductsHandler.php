<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagTax\Components\ThirdPlugins;

use Doctrine\DBAL\Connection;
use SwagTax\Structs\TaxMapping;
use SwagTax\Structs\UpdateConfig;

class SwagCustomProductsHandler extends AbstractThirdPlugin
{
    public function getTechnicalPLuginName()
    {
        return 'SwagCustomProducts';
    }

    public function supports($pluginName)
    {
        return $pluginName === $this->getTechnicalPLuginName();
    }

    public function update(UpdateConfig $config, TaxMapping $taxMapping)
    {
        $customerGroupIds = $this->getCustomerGroupIds($config->getCustomerGroupMapping());

        $this->updateTaxRate($taxMapping, $customerGroupIds);

        if ($config->getRecalculatePrices() === false) {
            return;
        }

        $this->recalculatePrices($taxMapping, $customerGroupIds);
    }

    private function updateTaxRate(TaxMapping $taxMapping, array $customerGroupIds)
    {
        $this->connection->createQueryBuilder()
            ->update('s_plugin_custom_products_price')
            ->set('tax_id', ':newTaxId')
            ->where('customer_group_id IN (:customerGroupIds)')
            ->andWhere('tax_id = :oldTaxRateId')
            ->setParameter('newTaxId', $taxMapping->getNewTaxRateId())
            ->setParameter('customerGroupIds', $customerGroupIds, Connection::PARAM_INT_ARRAY)
            ->setParameter('oldTaxRateId', $taxMapping->getOldTaxRateId())
            ->execute();
    }

    private function recalculatePrices(TaxMapping $taxMapping, array $customerGroupIds)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->update('s_plugin_custom_products_price', 'cp_prices')
            ->where('cp_prices.customer_group_id IN (:groups)')
            ->andWhere('cp_prices.tax_id = :newTaxID')
            ->set(
                'surcharge',
                sprintf(
                    '%s/%s*%s',
                    'surcharge',
                    1 + ($taxMapping->getNewTaxRate() / 100),
                    1 + ($taxMapping->getOldTaxRate() / 100)
                )
            )
            ->setParameter('groups', $customerGroupIds, Connection::PARAM_INT_ARRAY)
            ->setParameter('newTaxID', $taxMapping->getNewTaxRateId())
            ->execute();
    }
}
