/*jslint nomen: true*/
/*global define*/
define(function(require) {
    'use strict';

    var AddProductsButtonComponent;
    var $ = require('jquery');
    var _ = require('underscore');
    var routing = require('routing');
    var mediator = require('oroui/js/mediator');
    var messenger = require('oroui/js/messenger');
    var ShoppingListWidgetComponent = require('orob2bshoppinglist/js/app/components/shopping-list-widget-component');
    var BaseComponent = require('oroui/js/app/components/base/component');
    var options = {
        successMessage: 'orob2b.shoppinglist.menu.add_products.success.message',
        errorMessage: 'orob2b.shoppinglist.menu.add_products.error.message',
        redirect: 'orob2b_product_frontend_product_index',
        intention: {
            create_new: 'new'
        }
    };
    AddProductsButtonComponent = BaseComponent.extend({
        initialize: function(additionalOptions) {
            _.extend(options, additionalOptions || {});
            mediator.on('frontend:shoppinglist:add-widget-requested-response', this.showForm, this);
            options._sourceElement.find('.grid-control').click($.proxy(this.onClick, null));
        },
        onClick: function(e) {
            e.preventDefault();

            if ($(e.currentTarget).data('intention') === options.intention.create_new) {
                mediator.trigger('frontend:shoppinglist:add-widget-requested');
            } else {
                mediator.trigger('frontend:shoppinglist:products-add', {shoppingListId: $(this).data('id')});
            }
        },
        showForm: function(selections) {
            if (!selections.cnt) {
                messenger.notificationFlashMessage('warning', selections.reason);
                return;
            }
            var dialog = ShoppingListWidgetComponent.createDialog();
            dialog.render();
            dialog.on('formSave', _.bind(function(response) {
                mediator.trigger('frontend:shoppinglist:products-add', {shoppingListId: response});
                $('.btn[data-intention="current"]').data('id', response);
            }, this));
        },
        dispose: function() {
            if (this.disposed) {
                return;
            }

            ShoppingListWidgetComponent.dispose();
            options._sourceElement.find('.grid-control').off();
            mediator.off('frontend:shoppinglist:add-widget-requested-response', this.showForm, this);
            AddProductsButtonComponent.__super__.dispose.call(this);
        }
    });

    return AddProductsButtonComponent;
});

