<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagTax\Components;

use Doctrine\DBAL\Connection;
use Shopware_Components_Config as ShopConfig;
use SwagTax\Exceptions\ConfigElementNotFoundException;

class ShopConfigUpdater
{
    const CONFIG_NAME_VOUCHER_TAX = 'vouchertax';
    const CONFIG_NAME_DISCOUNT_TAX = 'discounttax';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ShopConfig
     */
    private $shopConfig;

    public function __construct(Connection $connection, ShopConfig $shopConfig)
    {
        $this->connection = $connection;
        $this->shopConfig = $shopConfig;
    }

    /**
     * @param float $oldTaxRate
     * @param float $newTaxRate
     */
    public function update(array $config, $oldTaxRate, $newTaxRate)
    {
        if ($config['adjust_voucher_tax']) {
            $currentVoucherTaxRate = (float) $this->shopConfig->get(self::CONFIG_NAME_VOUCHER_TAX, 0.0);
            if ($oldTaxRate === $currentVoucherTaxRate) {
                $this->updataShopConfig(self::CONFIG_NAME_VOUCHER_TAX, $newTaxRate, $config['shops']);
            }
        }

        if ($config['adjust_discount_tax']) {
            $currentDiscountTaxRate = (float) $this->shopConfig->get(self::CONFIG_NAME_DISCOUNT_TAX, 0.0);

            if ($oldTaxRate === $currentDiscountTaxRate) {
                $this->updataShopConfig(self::CONFIG_NAME_DISCOUNT_TAX, $newTaxRate, $config['shops']);
            }
        }
    }

    /**
     * @param string $configName
     * @param float  $newValue
     */
    private function updataShopConfig($configName, $newValue, array $shops)
    {
        $newValue = \serialize((string) $newValue);

        foreach ($shops as $shopId) {
            $configValueId = $this->getConfigValueId($configName, $shopId);

            if ($configValueId === null) {
                $this->createConfigValue(
                    $this->getConfigElementId($configName),
                    $shopId,
                    $newValue
                );
                continue;
            }

            $this->updateConfigValue($configValueId, $newValue);
        }
    }

    /**
     * @param string $configName
     * @param int    $shopId
     *
     * @return array
     */
    private function getConfigValueId($configName, $shopId)
    {
        $configValueId = $this->connection->createQueryBuilder()
            ->select('c_values.id')
            ->from('s_core_config_values', 'c_values')
            ->join('c_values', 's_core_config_elements', 'elements', 'elements.id = c_values.element_id')
            ->where('c_values.shop_id = :shopId')
            ->andWhere('elements.name LIKE :configName')
            ->setParameter('configName', $configName)
            ->setParameter('shopId', $shopId)
            ->execute()
            ->fetch(\PDO::FETCH_COLUMN);

        if ($configValueId === false) {
            return null;
        }

        return (int) $configValueId;
    }

    /**
     * @param int   $elementId
     * @param int   $shopId
     * @param float $value
     */
    private function createConfigValue($elementId, $shopId, $value)
    {
        $this->connection->createQueryBuilder()
            ->insert('s_core_config_values')
            ->setValue('element_id', ':elementId')
            ->setValue('shop_id', ':shopId')
            ->setValue('value', ':value')
            ->setParameter('elementId', $elementId)
            ->setParameter('shopId', $shopId)
            ->setParameter('value', $value)
            ->execute();
    }

    /**
     * @param int    $valueId
     * @param string $newValue
     */
    private function updateConfigValue($valueId, $newValue)
    {
        $this->connection->createQueryBuilder()
            ->update('s_core_config_values')
            ->set('value', ':newValue')
            ->where('id = :elementId')
            ->setParameter('newValue', $newValue)
            ->setParameter('elementId', $valueId)
            ->execute();
    }

    /**
     * @param string $elementName
     *
     * @return int
     */
    private function getConfigElementId($elementName)
    {
        return (int) $this->connection->createQueryBuilder()
            ->select('id')
            ->from('s_core_config_elements')
            ->where('name = :elementName')
            ->setParameter('elementName', $elementName)
            ->execute()
            ->fetch(\PDO::FETCH_COLUMN);
    }
}
