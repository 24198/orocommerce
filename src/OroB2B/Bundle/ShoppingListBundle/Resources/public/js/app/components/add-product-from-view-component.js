/*jslint nomen:true*/
/*global define*/
define(function (require) {
    'use strict';

    var AddProductFromViewComponent,
        BaseComponent = require('oroui/js/app/components/base/component'),
        DialogWidget = require('oro/dialog-widget'),
        routing = require('routing'),
        mediator = require('oroui/js/mediator'),
        Error = require('oroui/js/error'),
        $ = require('jquery'),
        _ = require('underscore');

    AddProductFromViewComponent = BaseComponent.extend({
        initialize: function (options) {
            var component = this;
            options._sourceElement.on('click', function () {
                var el = $(this),
                    form = el.closest('form'),
                    urlOptions = el.data('urloptions');

                if (!component.validateForm(form)) {
                    return;
                }

                if (el.data('intention') === 'new') {
                    component.createNewShoppingList(el.data('url'), urlOptions, form.serialize());
                } else if (el.data('intention') === 'current' && !urlOptions.id) {
                    component.createDefaultShoppingList(el.data('url'), urlOptions, form.serialize());
                } else {
                    urlOptions.shoppingListId = urlOptions.id;
                    component.addProductToShoppingList(el.data('url'), urlOptions, form.serialize());
                }
            });
        },

        validateForm: function (form) {
            var component = this,
                validator,
                valid = true;

            if (form.data('validator')) {
                validator = form.validate();
                $.each(component.formElements(form), function () {
                    valid = validator.element(this) && valid;
                });
            }

            return valid;
        },

        formElements: function (form) {
            return form.find('input, select, textarea').not(':submit, :reset, :image');
        },

        createNewShoppingList: function (url, urlOptions, formData) {
            var component = this,
                dialog = new DialogWidget({
                    'url': routing.generate('orob2b_shopping_list_frontend_create'),
                    'title': 'Create new Shopping List',
                    'regionEnabled': false,
                    'incrementalPosition': false,
                    'dialogOptions': {
                        'modal': true,
                        'resizable': false,
                        'width': '400',
                        'autoResize': true
                    }
                });
            dialog.render();
            dialog.on('formSave', _.bind(function (response) {
                urlOptions.shoppingListId = response;
                component.addProductToShoppingList(url, urlOptions, formData);
            }, this));
        },

        createDefaultShoppingList: function (url, urlOptions, formData) {
            var component = this;
            $.ajax({
                type: 'POST',
                url: routing.generate('orob2b_shopping_list_frontend_create_default'),
                data: [],
                success: function (response) {
                    urlOptions.shoppingListId = response.shoppingListId;
                    component.addProductToShoppingList(url, urlOptions, formData);
                }
            });
        },

        addProductToShoppingList: function (url, urlOptions, formData) {
            $.ajax({
                type: 'POST',
                url: routing.generate(url, urlOptions),
                data: formData,
                success: function (response) {
                    if (response && response.message) {
                        mediator.once('page:afterChange', function () {
                            mediator.execute('showFlashMessage', (response.successful ? 'success' : 'error'), response.message);
                        });
                    }
                    mediator.execute('refreshPage');
                },
                error: function (xhr) {
                    Error.handle({}, xhr, {enforce: true});
                }
            });
        }
    });

    return AddProductFromViewComponent;
});
