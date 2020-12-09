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
//{block name="backend/swag_tax/view/steps/second_card"}
Ext.define('Shopware.apps.SwagTax.view.steps.SecondCard', {
    extend: 'Ext.container.Container',
    swId: 'card-1',
    layout: {
        type: 'vbox',
        align : 'stretch',
        pack  : 'start',
    },

    initComponent: function () {
        this.items = [
            {
                xtype: 'fieldset',
                title: '{s name="wizard/information_panel/title"}{/s}',
                items: [
                    {
                        xtype: 'container',
                        html: '{s name="tax_mapping/text"}{/s}',
                        height: 85
                    },
                ],
            },

            Ext.create('Shopware.apps.SwagTax.view.main.TaxMapping'),

            {
                xtype: 'checkbox',
                fieldLabel: '{s name="second_card/copyTaxRules"}{/s}',
                supportText: '{s name="second_card/copyTaxRules/support"}{/s}',
                name: 'copyTaxRules',
                labelWidth: 155,
                inputValue: true,
                uncheckedValue: false,
                margin: '20 0 0 0',
                labelStyle: 'margin-top: 0;',
            },

        ];

        this.callParent(arguments);
    },

    renderTaxRate: function (value) {
        return value + '%';
    }
});
//{/block}
