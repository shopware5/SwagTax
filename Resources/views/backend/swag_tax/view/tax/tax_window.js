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
Ext.define('Shopware.apps.SwagTax.view.tax.TaxWindow', {
    extend: 'Ext.window.Window',
    title: '{s name="tax_mapping/createTax/title"}{/s}',
    layout: 'fit',
    width: 520,
    taxSelectSearchField: null,
    callBack: null,
    scope: null,

    initComponent: function() {
        if (this.taxSelectSearchField === null) {
            throw 'The property taxSelectSearchField is not set.'
        }

        this.items = this.createItems();
        this.dockedItems = [ this.createToolBar() ];

        this.callParent(arguments);
    },

    createItems: function() {
        return [
            Ext.create('Ext.container.Container', {
                padding: '20px 20px 20px 20px',
                layout: 'anchor',
                items: [
                    this.createTaxNameField(),
                    this.createTaxRateField()
                ]
            })
        ];
    },

    createTaxNameField: function() {
        this.taxNameField = Ext.create('Ext.form.field.Text', {
            name: 'name',
            fieldLabel: '{s name="tax_mapping/createTax/name"}{/s}',
            labelWidth: 155,
            allowBlank: false,
            required: true,
            anchor: '100%',
        });

        return this.taxNameField;
    },

    createTaxRateField: function() {
        this.taxRateField = Ext.create('Ext.form.field.Number', {
            name: 'taxRate',
            fieldLabel: '{s name="tax_mapping/createTax/rate"}{/s}',
            labelWidth: 155,
            allowDecimals: true,
            minValue: 0,
            maxValue: 100,
            allowBlank: false,

            anchor: '100%',
        });

        return this.taxRateField;
    },

    createToolBar: function() {
        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            items: [
                this.createCancelButton(),
                '->',
                this.createSaveTaxButton()
            ]
        });
    },

    createCancelButton: function() {
        return Ext.create('Ext.button.Button', {
            name: 'createTaxButton',
            text: '{s name="tax_mapping/cancel"}{/s}',
            cls: 'secondary',
            handler: Ext.bind(this.onCancel, this)
        });
    },

    createSaveTaxButton: function() {
        return Ext.create('Ext.button.Button', {
            name: 'createTaxButton',
            text: '{s name="tax_mapping/saveTax"}{/s}',
            cls: 'primary',
            handler: Ext.bind(this.onSaveTaxRate, this)
        });
    },

    onCancel: function() {
        this.destroy();
    },

    onSaveTaxRate: function() {
        var me = this;

        if (me.isValid() === false) {
            return;
        }

        Ext.Ajax.request({
            url: '{url controller="SwagTax" action="saveTaxRate"}',
            params: {
                name: me.taxNameField.getValue(),
                taxRate: me.taxRateField.getValue()
            },
            failure: Ext.bind(me.onFailure, me),
            success: Ext.bind(me.onSaveSuccessful, me)
        });
    },

    onFailure: function(response) {
        this.createGrowlMessage(
            '{s name="tax_mapping/saveErrorTitle"}{/s}',
            '{s name="tax_mapping/saveError"}{/s}' + '<br>' + response.responseText
        );
    },

    onSaveSuccessful: function(response) {
        var me = this,
            responseObject = Ext.JSON.decode(response.responseText);

        if (responseObject.success !== true) {
            me.onFailure(response);
            return;
        }

        me.createGrowlMessage(
            '{s name="wizard/growl/schedule/title"}{/s}',
            '{s name="tax_mapping/saveTax/success"}{/s}'
        );

        me.callBack.call(me.scope, responseObject.id);

        me.onCancel();
    },

    createGrowlMessage: function(title, message) {
        Shopware.Notification.createGrowlMessage(title, message, '', 'growl', false);
    },

    isValid: function() {
        var nameFieldIsValid = this.taxNameField.validate(),
            taxRateFieldIsValid = this.taxRateField.validate();

        return nameFieldIsValid && taxRateFieldIsValid;
    },
});
//{/block}
