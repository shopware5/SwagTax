

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
//{block name="backend/swag_tax/controller/wizard"}
Ext.define('Shopware.apps.SwagTax.controller.Wizard', {

    extend: 'Enlight.app.Controller',

    refs: [
        { ref: 'wizard', selector: 'swag-tax-wizard' },
        { ref: 'prevBtn', selector: 'swag-tax-wizard #card-prev' },
        { ref: 'nextBtn', selector: 'swag-tax-wizard #card-next' },
        { ref: 'saveBtn', selector: 'swag-tax-wizard #card-save' },
        { ref: 'taxMappingCombo', selector: 'swag-tax-wizard #tax-selection' },
        { ref: 'addTaxButton', selector: 'swag-tax-wizard #add-tax-button' },
        { ref: 'taxMappingGrid', selector: 'swag-tax-wizard #mapping-grid' },
    ],

    init: function () {
        this.control({
            'swag-tax-wizard': {
                previous: this.onClickPrevious,
                next: this.onClickNext,
                addTax: this.onAddTaxToMapping,
                changeTax: this.enableAddTaxButton,
                deleteTax: this.deleteTaxFromMapping,
                save: this.onSave
            },
        });

        this.callParent(arguments);
    },

    onClickPrevious: function() {
        this.step(-1);
    },

    onClickNext: function() {
        this.step(1);
    },

    onSave: function() {
        var values = this.getWizard().getValues();

        delete values.scheduledDate;

        Ext.Ajax.request({
            url: '{url action=save}',
            params: {
                recalculatePrices: values.recalculatePrices,
                taxMapping: Ext.JSON.encode(values.taxMapping),
                customerGroupMapping: Ext.JSON.encode(values.customerGroupMapping),
            },
            success: function () {
                this.step(1);
            }
        });
    },

    onAddTaxToMapping: function () {
        var taxCombo = this.getTaxMappingCombo(),
            taxId = taxCombo.getValue(),
            taxStore,
            taxGrid,
            taxGridStore,
            selectedTaxRecord;

        if (taxId === null) {
            return;
        }

        taxStore = taxCombo.getStore();
        selectedTaxRecord = taxStore.getById(taxId);

        taxGrid = this.getTaxMappingGrid();
        taxGridStore = taxGrid.getStore();

        // Already in the grid
        if (taxGridStore.find('taxId', selectedTaxRecord.get('id')) !== -1) {
            return;
        }

        taxGridStore.add({
            taxId: selectedTaxRecord.get('id'),
            taxName: selectedTaxRecord.get('name'),
            mappedTaxRate: 0
        });
    },

    enableAddTaxButton: function () {
        this.getAddTaxButton().setDisabled(false);
    },

    deleteTaxFromMapping: function (rowIndex) {
        this.getTaxMappingGrid().getStore().removeAt(rowIndex);
    },

    /**
     * @param { number } step
     */
    step: function (step) {
        var wizard = this.getWizard(),
            layout = wizard.getLayout(),
            index = layout.getActiveItem().id.split('card-')[1],
            next = parseInt(index, 10) + step;

        if (next === wizard.items.length - 1) {
            this.getSaveBtn().hide();
        } else if (next === wizard.items.length - 2) {
            this.getSaveBtn().show();
            this.getNextBtn().hide();
        } else if (next === wizard.items.length - 3) {
            this.getSaveBtn().hide();
            this.getNextBtn().show();
        }

        layout.setActiveItem(next);
        this.getPrevBtn().setDisabled(next === 0);
        this.getNextBtn().setDisabled(next === (wizard.items.length - 1));
    },
});
//{/block}
