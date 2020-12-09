/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

//{namespace name=backend/swag_tax/main}
//{block name="backend/swag_tax/view/steps/first_card"}
Ext.define('Shopware.apps.SwagTax.view.steps.FirstCard', {
    extend: 'Ext.container.Container',
    swId: 'card-0',

    initComponent: function() {
        this.items = [
            {
                xtype: 'fieldset',
                title: '{s name="wizard/information_panel/title"}{/s}',
                items: [
                    {
                        xtype: 'container',
                        html: '{s name="first_card/text"}{/s}',
                    }
                ],
                style: {
                    marginBottom: '15px'
                }
            },
            {
                xtype: 'fieldset',
                title: '{s name="wizard/information_panel/settings"}{/s}',
                layout: 'anchor',
                items: [
                    {
                        xtype: 'checkbox',
                        fieldLabel: '{s name="first_card/recalculatePrices"}{/s}',
                        supportText: '{s name="first_card/recalculatePrices/support"}{/s}',
                        name: 'recalculatePrices',
                        labelWidth: 155,
                        inputValue: true,
                        uncheckedValue: false,
                        labelStyle: 'margin-top: 0;'
                    },
                    {
                        xtype: 'checkbox',
                        fieldLabel: '{s name="first_card/recalculatePseudoPrices"}{/s}',
                        supportText: '{s name="first_card/recalculatePseudoPrices/support"}{/s}',
                        name: 'recalculatePseudoPrices',
                        labelWidth: 155,
                        inputValue: true,
                        uncheckedValue: false,
                        labelStyle: 'margin-top: 0;'
                    },
                    {
                        xtype: 'checkbox',
                        fieldLabel: '{s name="first_card/adjustVoucherTax"}{/s}',
                        supportText: '{s name="first_card/adjustVoucherTax/support"}{/s}',
                        name: 'adjustVoucherTax',
                        labelWidth: 155,
                        inputValue: true,
                        uncheckedValue: false,
                        labelStyle: 'margin-top: 0;',
                    },
                    {
                        xtype: 'checkbox',
                        fieldLabel: '{s name="first_card/adjustDiscountTax"}{/s}',
                        supportText: '{s name="first_card/adjustDiscountTax/support"}{/s}',
                        name: 'adjustDiscountTax',
                        labelWidth: 155,
                        inputValue: true,
                        uncheckedValue: false,
                        labelStyle: 'margin-top: 0;',
                    }
                ]
            }
        ];

        this.callParent(arguments);
    },
});
//{/block}
