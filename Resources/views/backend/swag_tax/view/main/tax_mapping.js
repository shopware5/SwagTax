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

    createTaxWindow: null,

    initComponent: function() {
        this.searchStore = Ext.create('Shopware.apps.Base.store.Tax');
        this.store = Ext.create('Ext.data.Store', {
            model: 'Shopware.apps.SwagTax.model.Mapping'
        });

        this.callParent(arguments);
    },

    setValue: function(value) {
        var me = this,
            sourceIndex,
            targetIndex;

        me.store.removeAll();

        if (!value) {
            return;
        }

        me.searchStore.load(function() {
            Ext.Object.each(value, function(sourceId, targetId) {
                sourceIndex = me.searchStore.findExact('id', ~~(sourceId));
                targetIndex = me.searchStore.findExact('id', ~~(targetId));

                if (sourceIndex === -1 || targetIndex === -1) {
                    return;
                }

                me.store.add(me.createRecord(
                    me.searchStore.getAt(sourceIndex),
                    me.searchStore.getAt(targetIndex)
                ));
            });
        });
    },

    getValue: function() {
        var recordData = {},
            store = this.store;

        store.each(function(item) {
            recordData[item.get('id')] = item.get('targetId')
        });

        return recordData;
    },

    getComboConfig: function() {
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
            tbar: this.createToolBar(),
            store: this.store,
            border: false,
            editor: rowEditingPlugin,
            plugins: [ rowEditingPlugin ],
            flex: 1,
            hideHeaders: this.hideHeaders
        });
    },

    createToolBar: function() {
        return Ext.create('Ext.toolbar.Toolbar', {
            items: [ '->', this.createAddNewTaxButton() ],
            dock: 'top',
            ui: 'shopware-ui',
            cls: 'shopware-toolbar'
        });
    },

    createAddNewTaxButton: function() {
        return Ext.create('Ext.button.Button', {
            name: 'createTaxButton',
            text: '{s name="tax_mapping/createTax"}{/s}',
            iconCls: 'sprite-plus-circle-frame',
            handler: Ext.bind(this.onClickAddTax, this)
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
            dataIndex: 'targetId',
            header: '{s name="tax_mapping/column/tax"}{/s}',
            editor: this.createTaxRateEditor(),
            renderer: Ext.bind(this.targetTaxColumnRenderer, this),
            flex: 1
        });

        columns.push(this.createActionColumn());

        return columns;
    },

    createTaxRateEditor: function() {
        this.editor = {
            xtype: 'combo',
            forceSelection: true,
            editable: false,
            store: this.searchStore,
            displayField: 'name',
            valueField: 'id',
            listeners: {
                change: Ext.bind(this.onChangeTaxGridEditor, this),
            }
        }

        return this.editor;
    },

    createRecord: function(sourceRecord, targetRecord) {
        var currentRecord = Ext.create('Shopware.apps.SwagTax.model.Mapping');

        if (sourceRecord) {
            currentRecord.set('id', sourceRecord.get('id'));
            currentRecord.set('name', sourceRecord.get('name'));
            currentRecord.set('tax', sourceRecord.get('tax'));
        }

        if (targetRecord) {
            currentRecord.set('targetId', targetRecord.get('id'));
            currentRecord.set('targetName', targetRecord.get('name'));
            currentRecord.set('targetTax', targetRecord.get('tax'));
        }

        return currentRecord;
    },

    addItem: function(record) {
        var me = this,
            exist = false,
            newRecord = this.createRecord(record);

        this.store.each(function(item) {
            if (item.get('id') === record.get('id')) {
                exist = true;
            }
        });

        if (!exist) {
            this.store.add(newRecord);
        }

        me.fixLayout();

        this.fireEvent('change', this, this.getValue());

        return !exist;
    },

    targetTaxColumnRenderer: function(value, clsObject, record) {
        if (record.get('targetId') === 0) {
            return '';
        }

        return 'ID: ' + record.get('targetId') + ' Name: ' + record.get('targetName')
    },

    createTaxRateWindowCallback: function(newRecordId) {
        var me = this;

        me.searchField.combo.getStore().load();
        me.searchStore.load({
            callback: function() {
                me.onChangeTaxGridEditor(me, newRecordId);
            },
        });
    },

    onClickAddTax: function() {
        this.createTaxWindow = Ext.create('Shopware.apps.SwagTax.view.tax.TaxWindow', {
            taxSelectSearchField: this.searchField,
            callBack: Ext.bind(this.createTaxRateWindowCallback, this),
            scope: this
        }).show();
    },

    onChangeTaxGridEditor: function(editor, newValue) {
        var targetIndex = this.searchStore.findExact('id', ~~(newValue)),
            targetRecord = this.searchStore.getAt(targetIndex),
            selectedRecord = this.grid.getSelectionModel().getSelection()[0];

        if (!targetRecord || !selectedRecord) {
            return;
        }

        selectedRecord.set('targetId', targetRecord.get('id'));
        selectedRecord.set('targetName', targetRecord.get('name'));
        selectedRecord.set('targetTax', targetRecord.get('tax'));
    },
});
//{/block}
