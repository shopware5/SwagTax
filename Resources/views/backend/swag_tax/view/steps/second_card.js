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
    id: 'card-1',
    layout: 'fit',

    // TODO: SNIPPETS
    initComponent: function () {
        this.items = Ext.create('Shopware.apps.SwagTax.view.main.TaxMapping');

        this.callParent(arguments);
    },

    createColumns: function () {
        var me = this;

        // TODO: SNIPPETS
        return [
            {
                header: 'id',
                dataIndex: 'taxId',
                hidden: true
            },{
                header: 'Ausgewählte Steuer',
                dataIndex: 'taxName',
                flex: 1
            }, {
                header: 'Neuer Steuersatz',
                dataIndex: 'mappedTaxRate',
                flex: 1,
                editor: {
                    xtype: 'numberfield',
                    minValue: 0
                },
                renderer: this.renderTaxRate
            },
            {
                xtype: 'actioncolumn',
                width: 80,
                items: [
                    {
                        iconCls: 'sprite-minus-circle',
                        action: 'deleteTax',
                        handler: function (view, rowIndex) {
                            me.fireEvent('deleteTax', rowIndex);
                        }
                    },
                ]
            }
        ];
    },

    renderTaxRate: function (value) {
        return value + '%';
    }
});
//{/block}
