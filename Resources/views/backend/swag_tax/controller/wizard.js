

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
        { ref: 'prevBtn', selector: 'swag-tax-wizard [swId="card-prev"]' },
        { ref: 'nextBtn', selector: 'swag-tax-wizard [swId="card-next"]' },
        { ref: 'saveBtn', selector: 'swag-tax-wizard [swId="card-save"]' },
        { ref: 'scheduledDate', selector: 'swag-tax-wizard [swId="scheduled-date"]' },
        { ref: 'customerGroupMapping', selector: 'swag-tax-wizard [name="customerGroupMapping"]' },
    ],

    init: function () {
        this.control({
            'swag-tax-wizard': {
                previous: this.onClickPrevious,
                next: this.onClickNext,
                changeTax: this.enableAddTaxButton,
                deleteTax: this.deleteTaxFromMapping,
                save: this.onSave
            },
            'swag-tax-fourth-card': {
                saveDate: this.onSaveDate,
                execute: this.execute
            },
            'swag-tax-customer-group-mapping': {
                itemAdded: this.checkSaveButton,
                itemRemoved: this.checkSaveButton
            }
        });

        this.callParent(arguments);
    },

    onClickPrevious: function () {
        this.step(-1);
    },

    onClickNext: function () {
        this.step(1);
    },

    onSave: function () {
        var me = this,
            values = this.getWizard().getValues();

        this.save(values, function () {
            me.step(1);
        });
    },

    onSaveDate: function () {
        var values = this.getWizard().getValues();

        this.save(values, function () {
            Shopware.Notification.createGrowlMessage(
                '{s name="wizard/growl/schedule/title"}{/s}',
                '{s name="wizard/growl/schedule/message"}{/s}'
            );
        });
    },

    checkSaveButton: function () {
        this.getSaveBtn().setDisabled(this.getCustomerGroupMapping().getValue() <= 0);
    },

    save: function (values, callback) {
        Ext.Ajax.request({
            url: '{url action=save}',
            params: {
                recalculatePrices: ~~(values.recalculatePrices),
                taxMapping: Ext.JSON.encode(values.taxMapping),
                customerGroupMapping: Ext.JSON.encode(values.customerGroupMapping),
                scheduledDate: values.scheduledDate,
            },
            success: callback
        });
    },

    execute: function () {
        Ext.Ajax.request({
            url: '{url action=execute}',
            success: function () {
                Shopware.Notification.createGrowlMessage(
                    '{s name="wizard/growl/execute/title"}{/s}',
                    '{s name="wizard/growl/execute/message"}{/s}'
                );
                Ext.Ajax.request({
                    url: '{url controller=Cache action=clearCache}',
                    params: {
                        'cache[config]': 'on',
                        'cache[http]': 'on'
                    },
                    success: function () {
                        Shopware.Notification.createGrowlMessage(
                            '{s name="wizard/growl/cache/title"}{/s}',
                            '{s name="wizard/growl/cache/message"}{/s}'
                        );
                    }
                })
            }
        });
    },

    /**
     * @param { number } step
     */
    step: function (step) {
        var wizard = this.getWizard(),
            layout = wizard.getLayout(),
            index = layout.getActiveItem().swId.split('card-')[1],
            next = parseInt(index, 10) + step;

        if (next === wizard.items.length - 1) {
            this.getSaveBtn().hide();
        } else if (next === wizard.items.length - 2) {
            if (this.getCustomerGroupMapping().getValue().length <= 0) {
                this.getSaveBtn().disable();
            }

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
