<?php
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagTax\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\Components\CacheManager;
use SwagTax\Components\TaxUpdater;

class CronJobSubscriber implements SubscriberInterface
{
    /**
     * @var TaxUpdater
     */
    private $taxUpdater;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    public function __construct(TaxUpdater $taxUpdater, CacheManager $cacheManager)
    {
        $this->taxUpdater = $taxUpdater;
        $this->cacheManager = $cacheManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Shopware_CronJob_SwagTax' => 'onCronjobRun'
        ];
    }

    public function onCronjobRun()
    {
        $didRun = $this->taxUpdater->update(true);

        if ($didRun) {
            $this->cacheManager->clearConfigCache();
            $this->cacheManager->clearHttpCache();
            $this->cacheManager->clearTemplateCache();
            return 'Updated prices';
        }

        return 'Nothing todo';
    }
}
