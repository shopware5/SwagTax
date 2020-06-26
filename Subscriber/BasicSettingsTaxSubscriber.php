<?php

namespace SwagTax\Subscriber;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;

class BasicSettingsTaxSubscriber implements SubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PreDispatch_Backend_Config' => 'onPreDispatch'
        ];
    }

    public function onPreDispatch(\Enlight_Controller_ActionEventArgs $args)
    {
        $repoClass = $args->getRequest()->get('_repositoryClass');
        $entityId = $args->getRequest()->get('id');

        /** @var Connection $connection */
        $connection = $args->getSubject()->get('dbal_connection');

        if (strtolower($args->getRequest()->getActionName()) !== 'deletevalues') {
            return;
        }

        if ($repoClass !== 'tax') {
            return;
        }

        $snippet = $args->getSubject()->get('snippets')->getNamespace('backend/swag_tax/main');

        $productUsed = $connection->fetchColumn('SELECT 1 FROM s_articles WHERE taxID = ?', [$entityId]);
        $dispatchUsed = $connection->fetchColumn('SELECT 1 FROM s_premium_dispatch WHERE tax_calculation = ?', [$entityId]);
        $voucherUsed = $connection->fetchColumn('SELECT 1 FROM s_emarketing_vouchers WHERE taxconfig = ?', [$entityId]);
        $snippetProduct = $snippet->get('still_used/product');
        $snippetDispatch = $snippet->get('still_used/dispatch');
        $snippetVoucher = $snippet->get('still_used/voucher');

        if ($productUsed !== false) {
            throw new \RuntimeException($snippetProduct);
        }

        if ($dispatchUsed !== false) {
            throw new \RuntimeException($snippetDispatch);
        }

        if ($voucherUsed !== false) {
            throw new \RuntimeException($snippetVoucher);
        }
    }
}
