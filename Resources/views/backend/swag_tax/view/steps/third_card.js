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
//{block name="backend/swag_tax/view/steps/third_card"}
Ext.define('Shopware.apps.SwagTax.view.steps.ThirdCard', {
    extend: 'Ext.container.Container',
    swId: 'card-2',
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
                        html: '{s name="customer_group_mapping/text"}{/s}',
                        height: 85
                    },
                ],
            },
            Ext.create('Shopware.apps.SwagTax.view.main.CustomerGroupMapping')
        ];

        this.callParent(arguments);
    }
});
//{/block}
