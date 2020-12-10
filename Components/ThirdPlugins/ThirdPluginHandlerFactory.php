<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagTax\Components\ThirdPlugins;

use Doctrine\DBAL\Connection;
use Doctrine\Common\Collections\ArrayCollection;
use Enlight_Event_EventManager as EventManager;

class ThirdPluginHandlerFactory
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var EventManager
     */
    private $eventManager;

    public function __construct(Connection $connection, EventManager $eventManager)
    {
        $this->connection = $connection;
        $this->eventManager = $eventManager;
    }

    /**
     * @return AbstractThirdPlugin[]
     */
    public function getThirdPluginHandler()
    {
        $handlers = $this->getAvailableHandler();

        $installedPlugins = $this->getInstalledPlugins($handlers);

        $installedHandler = [];
        foreach ($installedPlugins as $installedPlugin) {
            $handler = $this->getHandlerByPluginName($handlers, $installedPlugin);
            if ($handler === null) {
                continue;
            }

            $installedHandler[] = $handler;
        }

        return $installedHandler;
    }

    /**
     * @return array
     */
    private function getInstalledPlugins(array $handlers)
    {
        $technicalThirdPluginNames = array_map(function ($handler) {
            return $handler->getTechnicalPLuginName();
        }, $handlers);

        return $this->connection->createQueryBuilder()
            ->select(['name'])
            ->from('s_core_plugins')
            ->where('name IN (:pluginNames)')
            ->andWhere('active = 1')
            ->setParameter('pluginNames', $technicalThirdPluginNames, Connection::PARAM_STR_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * @param string $pluginName
     *
     * @return AbstractThirdPlugin|null
     */
    private function getHandlerByPluginName(array $handlers, $pluginName)
    {
        foreach ($handlers as $handler) {
            if ($handler->supports($pluginName)) {
                return $handler;
            }
        }

        return null;
    }

    /**
     * @return AbstractThirdPlugin[]
     */
    private function getAvailableHandler()
    {
        $handlers = $this->eventManager->collect('Swag_Tax_Collect_PluginUpdateHandler', new ArrayCollection([]));

        $handlers->add(new SwagCustomProductsHandler($this->connection));
        $handlers->add(new SwagBundleHandler($this->connection));

        return $handlers->toArray();
    }
}
