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
//{block name="backend/swag_tax/view/main/customer_group_mapping"}
Ext.define('Shopware.apps.SwagTax.view.main.CustomerGroupMapping', {

    extend: 'Shopware.form.field.Grid',
    name: 'customerGroupMapping',
    hideHeaders: false,
    allowSorting: false,

    initComponent: function () {
        this.searchStore = Ext.create('Shopware.apps.Base.store.CustomerGroup');
        this.store = new Ext.data.SimpleStore({
            fields: ['id', 'key', 'name']
        });

        this.callParent(arguments);
    },

    getComboConfig: function () {
        var config = this.callParent(arguments);

        // TODO: Snippets
        config.fieldLabel = 'Kundengruppe auswählen';
        config.margin = '0 0 5 0';
        config.labelStyle = 'margin-top: 0;';

        return config;
    },

    getValue: function () {
        var me = this,
            recordData = [],
            store = me.store;

        store.each(function(item) {
            recordData.push(item.get('key'));
        });

        return recordData;
    },

    createColumns: function() {
        var me = this, columns = [];

        columns.push({
            dataIndex: 'id',
            hidden: true
        });

        columns.push({
            dataIndex: 'name',
            // TODO: Snippets
            header: 'Kundengruppe',
            flex: 1
        });

        columns.push({
            dataIndex: 'key',
            // TODO: Snippets
            header: 'Kundengruppen Key',
            flex: 1
        });

        columns.push(me.createActionColumn());

        return columns;
    },
});
//{/block}
