<?php
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagTax;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;

class SwagTax extends Plugin
{
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_SwagTax' => 'registerSwagTaxController',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_SwagTax' => 'initController'
        ];
    }

    public function install(InstallContext $context)
    {
        $this->container->get('dbal_connection')->executeQuery('CREATE TABLE `swag_tax_config` (
  `active` tinyint(1) NOT NULL,
  `recalculate_prices` tinyint(1) NOT NULL,
  `tax_mapping` longtext COLLATE utf8_unicode_ci NOT NULL,
  `customer_group_mapping` longtext COLLATE utf8_unicode_ci NOT NULL,
  `scheduled_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
');

        $this->addCron();
        $this->container->get('acl')->createResource('swagtax', ['read']);
    }

    public function uninstall(UninstallContext $context)
    {
        $this->container->get('dbal_connection')->executeUpdate('DROP TABLE IF EXISTS swag_tax_config');
        $this->removeCron();

        $this->container->get('acl')->deleteResource('swagtax');
    }

    public function addCron()
    {
        $connection = $this->container->get('dbal_connection');
        $connection->insert(
            's_crontab',
            [
                'name'             => 'Update tax rate',
                'action'           => 'Shopware_CronJob_SwagTax',
                'next'             => new \DateTime(),
                'start'            => null,
                '`interval`'       => '300',
                'active'           => 1,
                'end'              => new \DateTime(),
                'pluginID'         => null
            ],
            [
                'next' => 'datetime',
                'end'  => 'datetime',
            ]
        );
    }

    public function removeCron()
    {
        $this->container->get('dbal_connection')->executeQuery('DELETE FROM s_crontab WHERE `action` = ?', [
            'Shopware_CronJob_SwagTax'
        ]);
    }

    public function initController(\Enlight_Event_EventArgs $args)
    {
        /** @var \Shopware_Controllers_Backend_SwagTax $subject */
        $subject = $args->getSubject();
        $subject->View()->addTemplateDir($this->getPath() . '/Resources/views/');
    }

    public function registerSwagTaxController(\Enlight_Event_EventArgs $args)
    {
        return $this->getPath() . '/Controllers/Backend/SwagTax.php';
    }
}
