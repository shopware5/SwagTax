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
//{block name="backend/swag_tax/view/main/tax_mapping"}
Ext.define('Shopware.apps.SwagTax.view.main.TaxMapping', {
    extend: 'Shopware.form.field.Grid',
    name: 'taxMapping',
    hideHeaders: false,
    flex: 2,

    allowSorting: false,

    initComponent: function () {
        this.searchStore = Ext.create('Shopware.apps.Base.store.Tax');
        this.store = new Ext.data.SimpleStore({
            fields: ['id', 'name', 'tax']
        });

        this.callParent(arguments);
    },

    setValue: function(value) {
        var me = this,
            currentRecord,
            currentRecordIndex;

        me.store.removeAll();

        if (!value) {
            return;
        }

        me.searchStore.load(function () {
            Ext.Object.each(value, function (key, value) {
                currentRecordIndex = me.searchStore.findExact('id', ~~(key));

                if (currentRecordIndex === -1) {
                    return;
                }

                currentRecord = me.searchStore.getAt(currentRecordIndex);
                currentRecord.set('tax', value);
                me.store.add(currentRecord);
            });
        });
    },

    getValue: function () {
        var me = this,
            recordData = {},
            store = me.store;

        store.each(function(item) {
            recordData[item.get('id')] = item.get('tax')
        });

        return recordData;
    },

    getComboConfig: function () {
        var config = this.callParent(arguments);

        config.fieldLabel = '{s name="tax_mapping/fieldLabel"}{/s}';
        config.margin = '15 0 5 0';
        config.labelStyle = 'margin-top: 0;';

        return config;
    },

    createGrid: function() {
        var rowEditingPlugin = Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToEdit: 1
        });

        return Ext.create('Ext.grid.Panel', {
            columns: this.createColumns(),
            store: this.store,
            border: false,
            editor: rowEditingPlugin,
            plugins: [ rowEditingPlugin ],
            flex: 1,
            hideHeaders: this.hideHeaders
        });
    },

    createColumns: function() {
        var columns = [];

        if (this.allowSorting) {
            columns.push(this.createSortingColumn());
        }

        columns.push({
            dataIndex: 'id',
            header: '{s name="tax_mapping/column/id"}{/s}',
        });

        columns.push({
            dataIndex: 'name',
            header: '{s name="tax_mapping/column/name"}{/s}',
            flex: 1
        });

        columns.push({
            dataIndex: 'tax',
            header: '{s name="tax_mapping/column/tax"}{/s}',
            renderer: this.taxRenderer,
            editor: {
                xtype: 'numberfield',
                minValue: 0
            },
            flex: 1
        });

        columns.push(this.createActionColumn());

        return columns;
    },

    taxRenderer: function (value) {
        if (!value) {
            value = 0;
        }

        return value + '%';
    }
});
//{/block}
