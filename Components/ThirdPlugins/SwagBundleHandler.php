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

class SwagBundleHandler extends AbstractThirdPlugin
{
    public function getTechnicalPLuginName()
    {
        return 'SwagBundle';
    }

    public function supports($pluginName)
    {
        return $pluginName === $this->getTechnicalPLuginName();
    }

    public function update(UpdateConfig $config, TaxMapping $taxMapping)
    {
        if ($config->getRecalculatePrices() === false) {
            return;
        }

        $this->recalculatePrices($taxMapping, $this->getCustomerGroupIds($config->getCustomerGroupMapping()));
    }

    private function recalculatePrices(TaxMapping $taxMapping, array $customerGroupIds)
    {
        $priceIds = $this->getAffectedPrices($taxMapping, $customerGroupIds);

        $this->recalculate($taxMapping, $priceIds);
    }

    private function getAffectedPrices(TaxMapping $taxMapping, array $customerGroupIds)
    {
        return $this->connection->createQueryBuilder()
            ->select(['DISTINCT bundlePrices.id'])
            ->from('s_articles_bundles', 'bundles')
            ->join('bundles', 's_articles_bundles_articles', 'bundleArticles', 'bundles.id = bundleArticles.bundle_id')
            ->join('bundles', 's_articles_bundles_prices', 'bundlePrices', 'bundles.id = bundlePrices.bundle_id')
            ->join('bundles', 's_articles', 'products', 'bundles.articleID = products.id')
            ->where('bundles.rab_type = "abs"')
            ->andWhere('bundlePrices.customer_group_id IN (:customerGroups)')
            ->andWhere('products.taxID = :newTaxRateId')
            ->setParameter('customerGroups', $customerGroupIds, Connection::PARAM_INT_ARRAY)
            ->setParameter('newTaxRateId', $taxMapping->getNewTaxRateId())
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);
    }

    private function recalculate(TaxMapping $taxMapping, array $priceIds)
    {
        $this->connection->createQueryBuilder()->update('s_articles_bundles_prices', 'prices')
            ->where('prices.id IN (:priceIds)')
            ->set(
                'prices.price',
                sprintf(
                    '%s/%s*%s',
                    'prices.price',
                    1 + ($taxMapping->getNewTaxRate() / 100),
                    1 + ($taxMapping->getOldTaxRate() / 100)
                )
            )
            ->setParameter('priceIds', $priceIds, Connection::PARAM_INT_ARRAY)
            ->execute();
    }
}
