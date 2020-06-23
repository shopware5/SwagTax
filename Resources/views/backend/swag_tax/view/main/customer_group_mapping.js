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
    flex: 2,
    alias: 'widget.swag-tax-customer-group-mapping',

    initComponent: function () {
        this.searchStore = Ext.create('Shopware.apps.Base.store.CustomerGroup');
        this.store = new Ext.data.SimpleStore({
            fields: ['id', 'key', 'name']
        });

        this.callParent(arguments);
    },

    getComboConfig: function () {
        var config = this.callParent(arguments);

        config.fieldLabel = '{s name="customer_group_mapping/fieldLabel"}{/s}';
        config.margin = '15 0 5 0';
        config.labelStyle = 'margin-top: 0;';

        return config;
    },

    setValue: function(value) {
        var me = this,
            currentRecordIndex;

        me.store.removeAll();

        if (!value) {
            return;
        }

        me.searchStore.load(function () {
            Ext.Array.each(value, function (item) {
                currentRecordIndex = me.searchStore.findExact('key', item);

                if (currentRecordIndex === -1) {
                    return;
                }

                me.store.add(me.searchStore.getAt(currentRecordIndex));
            });
        });
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
            header: '{s name="customer_group_mapping/column/name"}{/s}',
            flex: 1
        });

        columns.push({
            dataIndex: 'key',
            header: '{s name="customer_group_mapping/column/key"}{/s}',
            flex: 1
        });

        columns.push(me.createActionColumn());

        return columns;
    },

    addItem: function(record) {
        var newlyAdded = this.callParent(arguments);

        if (newlyAdded) {
            this.fireEvent('itemAdded');
        }

        return newlyAdded;
    },

    removeItem: function(record) {
        this.callParent(arguments);

        this.fireEvent('itemRemoved');
    },
});
//{/block}
