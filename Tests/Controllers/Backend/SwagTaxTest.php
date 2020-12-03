<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagTax\Test\Controller\Backend;

use PHPUnit\Framework\TestCase;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

require_once __DIR__ . '/../../../Controllers/Backend/SwagTax.php';

class SwagTaxTest extends TestCase
{
    use DatabaseTransactionBehaviour;

    public function test_saveTaxRateAction_withNoParamsShouldBe_false()
    {
        $controller = $this->getController();

        $controller->saveTaxRateAction();

        $result = $controller->View()->getAssign('success');

        static::assertFalse($result);
    }

    public function test_saveTaxRateAction_withOnlyTaxRateShouldBe_false()
    {
        $controller = $this->getController();

        $request = $controller->Request();
        $request->setParam('taxRate', '12');

        $controller->saveTaxRateAction();

        $result = $controller->View()->getAssign('success');

        static::assertFalse($result);
    }

    public function test_saveTaxRateAction_withOnlyNameShouldBe_false()
    {
        $controller = $this->getController();

        $request = $controller->Request();
        $request->setParam('name', 'FooBar');

        $controller->saveTaxRateAction();

        $result = $controller->View()->getAssign('success');

        static::assertFalse($result);
    }

    public function test_saveTaxRateAction_withNameAndTaxRateShouldBe_true()
    {
        $controller = $this->getController();

        $request = $controller->Request();
        $request->setParam('taxRate', '33');
        $request->setParam('name', '33 %');

        $controller->saveTaxRateAction();

        $result = $controller->View()->getAssign();

        $sql = 'SELECT * FROM s_core_tax WHERE id = ?';
        $savedTaxResult = Shopware()->Container()->get('dbal_connection')
            ->fetchAssoc($sql, [$result['id']]);

        static::assertTrue($result['success']);
        static::assertNotEmpty($result['id']);

        static::assertNotEmpty($savedTaxResult['id']);
        static::assertSame('33.00', $savedTaxResult['tax']);
        static::assertSame('33 %', $savedTaxResult['description']);
    }

    private function getController()
    {
        $controller = new \Shopware_Controllers_Backend_SwagTax();
        $controller->setContainer(Shopware()->Container());
        $controller->setRequest(new \Enlight_Controller_Request_RequestHttp());
        $controller->setResponse(new \Enlight_Controller_Response_ResponseHttp());
        $controller->setView(new \Enlight_View_Default(new \Enlight_Template_Manager()));

        return $controller;
    }
}
