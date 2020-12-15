<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagTax\Components;

use Doctrine\DBAL\Connection;
use Enlight_Event_EventManager;
use SwagTax\Components\ThirdPlugins\ThirdPluginUpdater;
use SwagTax\Structs\TaxMapping;
use SwagTax\Structs\UpdateConfig;

class TaxUpdater
{
    const PRICE_COLUMN = 'price';
    const PSEUDOPRICE_COLUMN = 'pseudoprice';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @var ShopConfigUpdater
     */
    private $shopConfigUpdater;

    /**
     * @var ThirdPluginUpdater
     */
    private $pluginUpdater;

    public function __construct(
        Connection $connection,
        Enlight_Event_EventManager $eventManager,
        ShopConfigUpdater $shopConfigUpdater,
        ThirdPluginUpdater $pluginUpdater
    ) {
        $this->connection = $connection;
        $this->eventManager = $eventManager;
        $this->shopConfigUpdater = $shopConfigUpdater;
        $this->pluginUpdater = $pluginUpdater;
    }

    /**
     * @param bool $cronJobMode
     *
     * @return bool
     */
    public function update($cronJobMode = false)
    {
        $config = $this->getConfig($cronJobMode);

        if ($config === null) {
            return false;
        }

        foreach ($config->getTaxMapping() as $oldTaxId => $newTaxId) {
            $taxMapping = $this->getTaxMapping($oldTaxId, $newTaxId);

            $this->copyTaxRules($config, $taxMapping);

            $this->recalculateAndUpdate($config, $taxMapping);

            $this->shopConfigUpdater->update($config, $taxMapping);

            $this->pluginUpdater->update($config, $taxMapping);

            $this->eventManager->notify('Swag_Tax_Updated_TaxRate', [
                'config' => $config,
                'newTaxId' => $taxMapping->getNewTaxRateId(),
                'newTaxRate' => $taxMapping->getNewTaxRate(),
                'taxMapping' => $taxMapping,
            ]);
        }

        $this->connection->executeUpdate('UPDATE swag_tax_config SET active = 0');

        return true;
    }

    /**
     * @param int $taxRateId
     *
     * @return float|null
     */
    private function getTaxRateById($taxRateId)
    {
        $result = $this->connection->createQueryBuilder()
            ->select('tax')
            ->from('s_core_tax')
            ->where('id = :taxRateId')
            ->setParameter('taxRateId', $taxRateId)
            ->execute()
            ->fetchColumn();

        if ($result === false) {
            return null;
        }

        return (float) $result;
    }

    /**
     * @param bool $cronJobMode
     *
     * @return UpdateConfig
     */
    private function getConfig($cronJobMode)
    {
        $config = $this->connection->fetchAssoc('SELECT * FROM swag_tax_config WHERE active = 1 LIMIT 1');
        if ($config === false) {
            return null;
        }

        if (empty($config['scheduled_date']) || $config['scheduled_date'] === '0000-00-00 00:00:00') {
            $config['scheduled_date'] = null;

            if ($cronJobMode) {
                return null;
            }
        }

        if ($cronJobMode && time() < strtotime($config['scheduled_date'])) {
            return null;
        }

        return new UpdateConfig($config);
    }

    private function copyTaxRules(UpdateConfig $config, TaxMapping $taxMapping)
    {
        if ($config->getCopyTaxRules() === false) {
            return;
        }

        $data = $this->connection->fetchAll('SELECT * FROM s_core_tax_rules WHERE groupID = ?', [$taxMapping->getOldTaxRateId()]);

        foreach ($data as $row) {
            unset($row['id']);
            $row['groupID'] = $taxMapping->getNewTaxRateId();

            $this->connection->insert('s_core_tax_rules', $row);
        }
    }

    /**
     * @param string $column
     */
    private function recalculatePrices(TaxMapping $taxMapping, UpdateConfig $config, $column)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->update('s_articles_prices', 'prices')
            ->where('prices.pricegroup IN (:groups)')
            ->andWhere('(SELECT taxID FROM s_articles WHERE id = prices.articleID) = :newTaxID');

        $queryBuilder->set(
            $column,
            sprintf(
                '%s/%s*%s',
                $column,
                1 + ($taxMapping->getNewTaxRate() / 100),
                1 + ($taxMapping->getOldTaxRate() / 100)
            )
        );

        $queryBuilder->setParameter(':groups', $config->getCustomerGroupMapping(), Connection::PARAM_STR_ARRAY);
        $queryBuilder->setParameter(':newTaxID', $taxMapping->getNewTaxRateId());

        $queryBuilder->execute();
    }

    private function recalculateShippingCosts(TaxMapping $taxMapping)
    {
        $affectedQueryBuilder = $this->connection->createQueryBuilder();
        $affectedQueryBuilder->select('id')
            ->from('s_premium_dispatch', 'dispatch')
            ->where('tax_calculation = :oldTaxId')
            ->setParameter(':oldTaxId', $taxMapping->getOldTaxRateId());

        $affectedDispatchIds = $affectedQueryBuilder->execute()->fetchAll(\PDO::FETCH_COLUMN);

        $recalculatePricesQueryBuilder = $this->connection->createQueryBuilder();
        $recalculatePricesQueryBuilder->update('s_premium_shippingcosts', 'shippingCosts')
            ->set('value', sprintf('value / %s*%s', 1 + ($taxMapping->getOldTaxRate() / 100), 1 + ($taxMapping->getNewTaxRate() / 100)))
            ->where('shippingCosts.dispatchID IN (:dispatchIds)');

        $recalculatePricesQueryBuilder->setParameter(':dispatchIds', $affectedDispatchIds, Connection::PARAM_INT_ARRAY);

        $recalculatePricesQueryBuilder->execute();
    }

    private function updateTaxIds(TaxMapping $taxMapping)
    {
        $this->connection->executeQuery('UPDATE `s_articles` SET `taxID` = ? WHERE taxID=?', [
            $taxMapping->getNewTaxRateId(),
            $taxMapping->getOldTaxRateId(),
        ]);

        $this->connection->executeQuery('UPDATE `s_emarketing_vouchers` SET `taxconfig` = ? WHERE taxconfig = ?', [
            $taxMapping->getNewTaxRateId(),
            $taxMapping->getOldTaxRateId(),
        ]);

        $this->connection->executeQuery('UPDATE `s_premium_dispatch` SET `tax_calculation` = ? WHERE tax_calculation = ?', [
            $taxMapping->getNewTaxRateId(),
            $taxMapping->getOldTaxRateId(),
        ]);
    }

    private function recalculateAndUpdate(UpdateConfig $config, TaxMapping $taxMapping)
    {
        if ($config->getRecalculatePrices() === false) {
            $this->recalculateShippingCosts($taxMapping);
        }

        $this->updateTaxIds($taxMapping);

        if ($config->getRecalculatePrices()) {
            $this->recalculatePrices($taxMapping, $config, self::PRICE_COLUMN);
        }

        if ($config->getRecalculatePseudoPrices()) {
            $this->recalculatePrices($taxMapping, $config, self::PSEUDOPRICE_COLUMN);
        }
    }

    /**
     * @param int $oldTaxRateId
     * @param int $newTaxRateId
     *
     * @return TaxMapping
     */
    private function getTaxMapping($oldTaxRateId, $newTaxRateId)
    {
        $oldTaxRate = $this->getTaxRateById((float) $oldTaxRateId);
        $newTaxRate = $this->getTaxRateById((float) $newTaxRateId);

        return new TaxMapping($oldTaxRateId, $oldTaxRate, $newTaxRateId, $newTaxRate);
    }
}
