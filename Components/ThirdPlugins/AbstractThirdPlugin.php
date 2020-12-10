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

abstract class AbstractThirdPlugin
{
    /**
     * @var Connection
     */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return string
     */
    public abstract function getTechnicalPLuginName();

    /**
     * @param string $pluginName
     *
     * @return bool
     */
    public abstract function supports($pluginName);

    public abstract function update(UpdateConfig $config, TaxMapping $taxMapping);

    /**
     * @return array
     */
    protected function getCustomerGroupIds(array $customerGroupKeys)
    {
        return $this->connection->createQueryBuilder()
            ->select(['id'])
            ->from('s_core_customergroups')
            ->where('groupkey IN (:groupKeys)')
            ->setParameter('groupKeys', $customerGroupKeys, Connection::PARAM_STR_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);
    }
}
