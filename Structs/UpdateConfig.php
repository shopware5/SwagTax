<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagTax\Structs;

class UpdateConfig
{
    /**
     * @var bool
     */
    protected $recalculatePrices;

    /**
     * @var bool
     */
    protected $recalculatePseudoPrices;

    /**
     * @var bool
     */
    protected $adjustVoucherTax;

    /**
     * @var bool
     */
    protected $adjustDiscountTax;

    /**
     * @var array;
     */
    protected $taxMapping;

    /**
     * @var bool
     */
    protected $copyTaxRules;

    /**
     * @var array
     */
    protected $customerGroupMapping;

    public function __construct(array $config)
    {
        $this->recalculatePrices = (bool) $config['recalculate_prices'];
        $this->recalculatePseudoPrices = (bool) $config['recalculate_pseudoprices'];
        $this->adjustVoucherTax = (bool) $config['adjust_voucher_tax'];
        $this->adjustDiscountTax = (bool) $config['adjust_discount_tax'];
        $this->taxMapping = \json_decode($config['tax_mapping'], true);
        $this->copyTaxRules = (bool) $config['copy_tax_rules'];
        $this->customerGroupMapping = \json_decode($config['customer_group_mapping'], true);
    }

    /**
     * @return bool
     */
    public function getRecalculatePrices()
    {
        return $this->recalculatePrices;
    }

    /**
     * @return bool
     */
    public function getRecalculatePseudoPrices()
    {
        return $this->recalculatePseudoPrices;
    }

    /**
     * @return bool
     */
    public function getAdjustVoucherTax()
    {
        return $this->adjustVoucherTax;
    }

    /**
     * @return bool
     */
    public function getAdjustDiscountTax()
    {
        return $this->adjustDiscountTax;
    }

    /**
     * @return array
     */
    public function getTaxMapping()
    {
        return $this->taxMapping;
    }

    /**
     * @return bool
     */
    public function getCopyTaxRules()
    {
        return $this->copyTaxRules;
    }

    /**
     * @return array
     */
    public function getCustomerGroupMapping()
    {
        return $this->customerGroupMapping;
    }
}

