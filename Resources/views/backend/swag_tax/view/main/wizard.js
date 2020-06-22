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
//{block name="backend/swag_tax/view/wizard"}
Ext.define('Shopware.apps.SwagTax.view.main.Wizard', {
    extend: 'Ext.form.Panel',
    alias: 'widget.swag-tax-wizard',
    cls: 'shopware-form',

    layout: 'card',
    region: 'center',
    bodyPadding: '20px',
    activeItem: 0,
    style: 'background: #fff',

    initComponent: function () {
        this.items = this.createSteps();
        this.bbar = this.createBottomBar();

        this.callParent(arguments);
    },

    createSteps: function () {
        return [
            Ext.create('Shopware.apps.SwagTax.view.steps.FirstCard'),
            Ext.create('Shopware.apps.SwagTax.view.steps.SecondCard'),
            Ext.create('Shopware.apps.SwagTax.view.steps.ThirdCard'),
            Ext.create('Shopware.apps.SwagTax.view.steps.FourthCard'),
        ];
    },

    createBottomBar: function () {
        var me = this;

        return ['->', {
            swId: 'card-prev',
            cls: 'secondary card-prev',
            text: '{s name="wizard/prevBtn"}{/s}',
            disabled: true,
            handler: function () {
                me.fireEvent('previous');
            }
        },{
            swId: 'card-next',
            cls: 'secondary',
            text: '{s name="wizard/nextBtn"}{/s}',
            handler: function () {
                me.fireEvent('next');
            }
        },{
            swId: 'card-save',
            cls: 'primary',
            hidden: true,
            text: '{s name="saveBtn"}{/s}',
            handler: function () {
                me.fireEvent('save');
            }
        }];
    },

    renderTaxRate: function (value) {
        return value + '%';
    }
});
//{/block}
