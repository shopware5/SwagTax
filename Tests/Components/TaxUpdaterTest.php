<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagTax\Test\Components;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Compatibility\LegacyStructConverter;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use SwagTax\Components\TaxUpdater;

class TaxUpdaterTest extends TestCase
{
    use DatabaseTransactionBehaviour;

    const TABLE_NAME = 'swag_tax_config';

    /**
     * @var TaxUpdater
     */
    private $taxUpdater;

    /**
     * @var Connection
     */
    private $con;

    /**
     * @var ListProductServiceInterface
     */
    private $listProductService;

    /**
     * @var ShopContextInterface
     */
    private $context;

    /**
     * @var LegacyStructConverter
     */
    private $legacyStructConverter;

    public function __construct()
    {
        $this->taxUpdater = Shopware()->Container()->get(TaxUpdater::class);
        $this->con = Shopware()->Container()->get('dbal_connection');
        $this->listProductService = Shopware()->Container()->get('shopware_storefront.list_product_service');
        $this->context = Shopware()->Container()->get('shopware_storefront.context_service')->getContext();
        $this->legacyStructConverter = Shopware()->Container()->get('legacy_struct_converter');

        parent::__construct();
    }

    public function testEmptyConfigTable()
    {
        static::assertFalse($this->taxUpdater->update());
    }

    public function testScheduledNotMatch()
    {
        $this->con->insert(self::TABLE_NAME, [
            'active' => 1,
            'recalculate_prices' => 1,
            'tax_mapping' => json_encode([1 => 15]),
            'customer_group_mapping' => json_encode(['EK']),
            'scheduled_date' => (new \DateTime())->add(new \DateInterval('P30D'))->format('Y-m-d H:i:s'),
        ]);

        static::assertFalse($this->taxUpdater->update(true));
    }

    public function testScheduledMatch()
    {
        $this->con->insert(self::TABLE_NAME, [
            'active' => 1,
            'recalculate_prices' => 1,
            'tax_mapping' => json_encode([1 => 15]),
            'customer_group_mapping' => json_encode(['EK']),
            'scheduled_date' => (new \DateTime())->add(new \DateInterval('P1D'))->format('Y-m-d H:i:s'),
        ]);

        static::assertTrue($this->taxUpdater->update());
    }

    public function testTaxChangeOnOneTax()
    {
        $sql = "INSERT INTO `s_core_tax` (`id`, `tax`, `description`)
                VALUES (7, '15.00', '15 %');";

        $this->con->executeQuery($sql);

        $products = $this->getProductNumberWithTax();
        $numbers = [$products[0]['ordernumber'], $products[1]['ordernumber']];

        $this->con->insert(self::TABLE_NAME, [
            'active' => 1,
            'recalculate_prices' => 1,
            'tax_mapping' => json_encode([$products[0]['id'] => 7]),
            'customer_group_mapping' => json_encode(['EK']),
            'scheduled_date' => null,
        ]);

        $currentProducts = $this->legacyStructConverter->convertListProductStructList($this->listProductService->getList($numbers, $this->context));

        static::assertTrue($this->taxUpdater->update());

        // Update internal state of context
        Shopware()->Container()->get('shopware_storefront.context_service')->initializeContext();
        $this->context = Shopware()->Container()->get('shopware_storefront.context_service')->getContext();

        $updatedProducts = $this->legacyStructConverter->convertListProductStructList($this->listProductService->getList($numbers, $this->context));

        // Test changed tax product
        static::assertEquals($currentProducts[$numbers[0]]['price'], $updatedProducts[$numbers[0]]['price']);
        static::assertNotEquals($currentProducts[$numbers[0]]['tax'], $updatedProducts[$numbers[0]]['tax']);
        static::assertEquals($updatedProducts[$numbers[0]]['tax'], 15);

        // Test not changed other taxed products
        static::assertEquals($currentProducts[$numbers[1]]['price'], $updatedProducts[$numbers[1]]['price']);
        static::assertEquals($currentProducts[$numbers[1]]['tax'], $updatedProducts[$numbers[1]]['tax']);
    }

    public function test_update_expectedUpdatedPseudoPrice()
    {
        $sql = 'INSERT INTO `s_core_tax` (`id`, `tax`, `description`)
                VALUES (7, "10.00", "10 %");';
        $this->con->exec($sql);

        $sql = 'UPDATE `s_articles_prices` SET `pseudoprice` = 25.210084033613 WHERE `id` = 473;';
        $this->con->exec($sql);

        $this->con->insert(self::TABLE_NAME, [
            'active' => 1,
            'recalculate_prices' => 0,
            'recalculate_pseudoprices' => 1,
            'tax_mapping' => json_encode([1 => 7]),
            'customer_group_mapping' => json_encode(['EK']),
            'scheduled_date' => (new \DateTime())->add(new \DateInterval('P1D'))->format('Y-m-d H:i:s'),
        ]);

        static::assertTrue($this->taxUpdater->update(false));

        $resultSql = 'SELECT `pseudoprice` FROM `s_articles_prices` WHERE `id` = 473;';
        $result = (float) $this->con->fetchColumn($resultSql);

        $expectedPseudoPrice = 23.303439022667483;

        static::assertSame($expectedPseudoPrice, $result);
    }

    public function test_shouldUpdateOtherTaxRates()
    {
        $sql = 'INSERT INTO `s_core_tax` (`id`, `tax`, `description`)
                VALUES (7, "10.00", "10 %");';
        $this->con->exec($sql);

        $sql = 'UPDATE `s_articles_prices` SET `pseudoprice` = 25.210084033613 WHERE `id` = 473;';
        $this->con->exec($sql);

        $this->con->insert(self::TABLE_NAME, [
            'active' => 1,
            'recalculate_prices' => 0,
            'recalculate_pseudoprices' => 1,
            'adjust_voucher_tax' => true,
            'adjust_discount_tax' => true,
            'shops' => json_encode([1, 2]),
            'tax_mapping' => json_encode([1 => 7]),
            'customer_group_mapping' => json_encode(['EK']),
            'scheduled_date' => (new \DateTime())->add(new \DateInterval('P1D'))->format('Y-m-d H:i:s'),
        ]);

        static::assertTrue($this->taxUpdater->update(false));

        $sql = "SELECT cv.id, cv.shop_id, ce.name, cv.value FROM s_core_config_values as cv
                JOIN s_core_config_elements ce ON ce.id = cv.element_id
                WHERE ce.name IN ('vouchertax', 'discounttax')
                ORDER BY cv.shop_id";

        $expectedResult = require __DIR__ . '/_fixtures/otherTaxRatesUpdateResult.php';
        $result = $this->con->fetchAll($sql);

        foreach ($result as $index => $resultElement) {
            static::assertSame($expectedResult[$index]['shopId'], $resultElement['shopId']);
            static::assertSame($expectedResult[$index]['name'], $resultElement['name']);
            static::assertSame($expectedResult[$index]['value'], $resultElement['value']);
        }
    }

    private function getProductNumberWithTax()
    {
        return $this->con->fetchAll('SELECT ordernumber, s_core_tax.tax, s_core_tax.id
FROM s_articles_details
INNER JOIN s_articles ON(s_articles.id = s_articles_details.articleID)
INNER JOIN s_core_tax ON(s_core_tax.id = s_articles.taxID)
WHERE s_articles.active = 1 AND s_articles_details.active = 1
GROUP BY s_core_tax.tax');
    }
}
