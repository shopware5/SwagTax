<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagTax\Structs;

class TaxMapping
{
    /**
     * @var int
     */
    protected $oldTaxRateId;

    /**
     * @var float
     */
    protected $oldTaxRate;

    /**
     * @var int
     */
    protected $newTaxRateId;

    /**
     * @var float
     */
    protected $newTaxRate;

    public function __construct($oldTaxRateId, $oldTaxRate, $newTaxRateId, $newTaxRate)
    {
        $this->oldTaxRateId = $oldTaxRateId;
        $this->oldTaxRate = $oldTaxRate;
        $this->newTaxRateId = $newTaxRateId;
        $this->newTaxRate = $newTaxRate;
    }

    /**
     * @return int
     */
    public function getOldTaxRateId()
    {
        return $this->oldTaxRateId;
    }

    /**
     * @return float
     */
    public function getOldTaxRate()
    {
        return $this->oldTaxRate;
    }

    /**
     * @return int
     */
    public function getNewTaxRateId()
    {
        return $this->newTaxRateId;
    }

    /**
     * @return float
     */
    public function getNewTaxRate()
    {
        return $this->newTaxRate;
    }
}
