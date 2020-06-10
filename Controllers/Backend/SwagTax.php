<?php

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
            'tax_mapping' => $params['taxMapping'],
            'customer_group_mapping' => $params['customerGroupMapping'],
            'scheduled_date' => $this->Request()->getParam('scheduledDate')
        ]);
    }

    public function loadConfigAction()
    {
        $sql = <<<SQL
            SELECT
               `active`,
               `recalculate_prices` as recalculatePrices,
               `tax_mapping` as taxMapping,
               `customer_group_mapping` as customerGroupMapping,
               `scheduled_date` as scheduledDate
            FROM %s
SQL;

        $this->View()->assign([
            'data' => $this->container->get('dbal_connection')->fetchAssoc(sprintf($sql, self::TABLE_NAME))
        ]);
    }

    public function executeAction()
    {
        $this->container->get(TaxUpdater::class)->update();
    }

    private function clearTable()
    {
        $this->container->get('dbal_connection')->executeQuery(sprintf('TRUNCATE TABLE %s', self::TABLE_NAME));
    }
}
