<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagTax\Tests\Components;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Compatibility\LegacyStructConverter;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use SwagTax\Components\TaxUpdater;
use SwagTax\Tests\PluginDependencyTrait;

class TaxUpdaterTest extends TestCase
{
    use DatabaseTransactionBehaviour;
    use PluginDependencyTrait;

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

        $expectedPseudoPrice = 27.272727272726787;

        static::assertSame($expectedPseudoPrice, $result);
    }

    public function test_update_shouldUpdateOtherTaxRates()
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

    public function test_update_shouldUpdateThirdPlugins()
    {
        $this->isPluginInstalled('SwagBundle');
        $this->isPluginInstalled('SwagCustomProducts');

        $sql = 'INSERT INTO `s_core_tax` (`id`, `tax`, `description`)
                VALUES (7, "10.00", "10 %");';
        $this->con->exec($sql);

        $this->con->insert(self::TABLE_NAME, [
            'active' => 1,
            'recalculate_prices' => 1,
            'recalculate_pseudoprices' => 0,
            'adjust_voucher_tax' => 0,
            'adjust_discount_tax' => 0,
            'tax_mapping' => json_encode([1 => 7]),
            'customer_group_mapping' => json_encode(['EK']),
            'scheduled_date' => '0000-00-00 00:00:00',
        ]);

        $sql = file_get_contents(__DIR__ . '/_fixtures/plugin_data.sql');
        $this->con->exec($sql);

        static::assertTrue($this->taxUpdater->update(false));

        $expectedBundleResult = [
            [
                'price' => '54.53636363636324',
            ], [
                'price' => '42.981308411215',
            ],
        ];

        $expectedCustomProductsResult = require __DIR__ . '/_fixtures/expectedCustomProductPriceResult.php';

        $sql = "SELECT price FROM s_articles_bundles_prices";
        $bundleResult = $this->con->fetchAll($sql);

        $sql = "SELECT * FROM s_plugin_custom_products_price";
        $customProductsResult = $this->con->fetchAll($sql);

        foreach ($expectedBundleResult as $index => $price) {
            static::assertSame($price['price'], $bundleResult[$index]['price']);
        }

        foreach ($expectedCustomProductsResult as $index => $customProductPrice) {
            static::assertSame($customProductPrice['id'], $customProductsResult[$index]['id']);
            static::assertSame($customProductPrice['surcharge'], $customProductsResult[$index]['surcharge']);
            static::assertSame($customProductPrice['tax_id'], $customProductsResult[$index]['tax_id']);
        }
    }

    public function test_update_shouldCopyTaxRules()
    {
        $sql = "INSERT INTO `s_core_tax_rules`
            (`id`, `areaID`, `countryID`, `stateID`, `groupID`, `customer_groupID`, `tax`, `name`, `active`)
            VALUES (1, 1, 2, 3, 1, 1, '8.00', 'A', 1);";
        $this->con->exec($sql);

        $sql = 'INSERT INTO `s_core_tax` (`id`, `tax`, `description`)
                VALUES (7, "10.00", "10 %");';

        $this->con->exec($sql);

        $this->con->insert(self::TABLE_NAME, [
            'active' => 1,
            'recalculate_prices' => 0,
            'recalculate_pseudoprices' => 0,
            'adjust_voucher_tax' => 0,
            'adjust_discount_tax' => 0,
            'tax_mapping' => json_encode([1 => 7]),
            'copy_tax_rules' => 1,
            'customer_group_mapping' => json_encode(['EK']),
            'scheduled_date' => '0000-00-00 00:00:00',
        ]);

        static::assertTrue($this->taxUpdater->update(false));

        $sql = "SELECT * FROM `s_core_tax_rules` WHERE groupID = 7";
        $result = $this->con->fetchAssoc($sql);

        $expectedResult = [
            'id' => '2',
            'areaID' => '1',
            'countryID' => '2',
            'stateID' => '3',
            'groupID' => '7',
            'customer_groupID' => '1',
            'tax' => '8.00',
            'name' => 'A',
            'active' => '1',
        ];

        static::assertSame($expectedResult['areaID'], $result['areaID']);
        static::assertSame($expectedResult['countryID'], $result['countryID']);
        static::assertSame($expectedResult['stateID'], $result['stateID']);
        static::assertSame($expectedResult['groupID'], $result['groupID']);
        static::assertSame($expectedResult['customer_groupID'], $result['customer_groupID']);
        static::assertSame($expectedResult['tax'], $result['tax']);
        static::assertSame($expectedResult['name'], $result['name']);
        static::assertSame($expectedResult['active'], $result['active']);
    }

    private function getProductNumberWithTax()
    {
        return $this->con->fetchAll(
            'SELECT ordernumber, s_core_tax.tax, s_core_tax.id
            FROM s_articles_details
            INNER JOIN s_articles ON(s_articles.id = s_articles_details.articleID)
            INNER JOIN s_core_tax ON(s_core_tax.id = s_articles.taxID)
            WHERE s_articles.active = 1 AND s_articles_details.active = 1
            GROUP BY s_core_tax.tax'
        );
    }
}
