/*jslint nomen:true*/
/*global define*/
define([
    'underscore',
    'oroui/js/messenger',
    'orotranslation/js/translator',
    'oro/datagrid/action/mass-action',
    'oroui/js/mediator'
], function (_, messenger, __, MassAction, mediator) {
    'use strict';

    var AddProductsAction;

    /**
     * Add products to shopping list
     *
     * @export  oro/datagrid/action/add-products-mass-action
     * @class   oro.datagrid.action.MarkAction
     * @extends oro.datagrid.action.MassAction
     */
    AddProductsAction = MassAction.extend({
        initialize: function (options) {
            AddProductsAction.__super__.initialize.apply(this, arguments);
            mediator.on('frontend:shoppinglist:products-add', this._beforeProductsAdd, this);
            mediator.on('frontend:shoppinglist:add-widget-requested', this._checkSelectionState, this);
        },
        /**
         * @param {object} eventArgs
         */
        _beforeProductsAdd: function (eventArgs) {
            this.route_parameters['shoppingList'] = eventArgs.id;
            this.run(true);
        },
        _checkSelectionState: function () {
            var selectionState = this.datagrid.getSelectionState(),
                models = selectionState.selectedModels,
                length = 0,
                reason;

            for (var key in models) {
                if (models.hasOwnProperty(key)) {
                    length++
                }
            }
            if (length < 1) {
                reason = AddProductsAction.__super__.defaultMessages.empty_selection;
            }

            mediator.trigger('frontend:shoppinglist:add-widget-requested-response', {cnt: length, reason: reason})
        },
        /**
         * Overridden in order to set shoppingList route param
         *
         * @param {boolean} isCustom
         */
        run: function (isCustom) {
            if (!isCustom) {
                this.route_parameters['shoppingList'] = 'current';
            }
            AddProductsAction.__super__.run.apply(this);
        }
    });

    return AddProductsAction;
});
