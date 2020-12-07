<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagTax\Components\TaxUpdater;

class Shopware_Controllers_Backend_SwagTax extends Shopware_Controllers_Backend_ExtJs
{
    const TABLE_NAME = 'swag_tax_config';

    public function saveAction()
    {
        $params = $this->Request()->getParams();

        $this->clearTable();

        $this->container->get('dbal_connection')->insert(self::TABLE_NAME, [
            'active' => 1,
            'recalculate_prices' => (bool) $params['recalculatePrices'],
            'recalculate_pseudoprices' => (bool) $params['recalculatePseudoPrices'],
            'adjust_voucher_tax' => (bool) $params['adjustVoucherTax'],
            'adjust_discount_tax' => (bool) $params['adjustDiscountTax'],
            'shops' => $params['shops'],
            'tax_mapping' => $params['taxMapping'],
            'customer_group_mapping' => $params['customerGroupMapping'],
            'scheduled_date' => $this->Request()->getParam('scheduledDate'),
        ]);
    }

    public function loadConfigAction()
    {
        $sql = <<<SQL
            SELECT
               `active`,
               `recalculate_prices` as recalculatePrices,
               `recalculate_pseudoprices` as recalculatePseudoPrices,
               `adjust_voucher_tax` as adjustVoucherTax,
               `adjust_discount_tax` as adjustDiscountTax,
               `shops`,
               `tax_mapping` as taxMapping,
               `customer_group_mapping` as customerGroupMapping,
               `scheduled_date` as scheduledDate
            FROM %s
SQL;

        $this->View()->assign([
            'data' => $this->container->get('dbal_connection')->fetchAssoc(sprintf($sql, self::TABLE_NAME)),
        ]);
    }

    public function saveTaxRateAction()
    {
        $params = $this->Request()->getParams();
        $connection = $this->container->get('dbal_connection');

        $taxParams = $this->getTaxParams($params);
        if ($taxParams === null) {
            $this->view->assign([
                'success' => false,
            ]);

            return;
        }

        $success = (bool) $connection->insert(
            's_core_tax',
            [
                'tax' => $taxParams['taxRate'],
                'description' => $taxParams['name'],
            ]
        );

        $this->view->assign([
            'success' => $success,
            'id' => $connection->lastInsertId(),
        ]);
    }

    public function executeAction()
    {
        $this->container->get(TaxUpdater::class)->update();
    }

    /**
     * @return array|null
     */
    private function getTaxParams(array $params)
    {
        $taxDescription = \trim($params['name'] ?? '');
        $taxRate = (float) \trim($params['taxRate'] ?? '');

        if ($taxDescription === '' || $taxRate === 0.0) {
            return null;
        }

        return [
            'name' => $taxDescription,
            'taxRate' => $taxRate,
        ];
    }

    private function clearTable()
    {
        $this->container->get('dbal_connection')->executeQuery(sprintf('TRUNCATE TABLE %s', self::TABLE_NAME));
    }
}
