<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagTax\Tests\Controller\Backend;

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

    public function test_checkCronDate_shouldReturnEmptyDate()
    {
        $controller = $this->getController();
        $emptyDate = '0000-00-00 00:00:00';

        $reflectionMethod = (new \ReflectionClass(\Shopware_Controllers_Backend_SwagTax::class))->getMethod('checkCronDate');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invoke($controller, null);
        static::assertSame($emptyDate, $result);

        $result = $reflectionMethod->invoke($controller, '');
        static::assertSame($emptyDate, $result);

        $result = $reflectionMethod->invoke($controller, '0');
        static::assertSame($emptyDate, $result);

        $result = $reflectionMethod->invoke($controller, '0000-00-00 00:00:00');
        static::assertSame($emptyDate, $result);

        $result = $reflectionMethod->invoke($controller, '2020-01-01 00:00:00');
        static::assertSame($emptyDate, $result);

        $result = $reflectionMethod->invoke($controller, '2020-01-01 00:00:00');
        static::assertSame($emptyDate, $result);

        $now = date('Y-m-d H:i:s');
        $past = date('Y-m-d H:i:s', strtotime('-1 hour', strtotime($now)));
        $result = $reflectionMethod->invoke($controller, $past);
        static::assertSame($emptyDate, $result);
    }

    public function test_checkCronDate_shouldReturnGivenDate()
    {
        $controller = $this->getController();

        $reflectionMethod = (new \ReflectionClass(\Shopware_Controllers_Backend_SwagTax::class))->getMethod('checkCronDate');
        $reflectionMethod->setAccessible(true);

        $future = '2222-02-02 20:20:20';
        $result = $reflectionMethod->invoke($controller, $future);
        static::assertSame($future, $result);

        $now = date('Y-m-d H:i:s');
        $future = date('Y-m-d H:i:s', strtotime('+1 hour', strtotime($now)));
        $result = $reflectionMethod->invoke($controller, $future);
        static::assertSame($future, $result);
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
