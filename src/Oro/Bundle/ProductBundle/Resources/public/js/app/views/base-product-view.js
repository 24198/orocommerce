define(function(require) {
    'use strict';

    var BaseProductView;
    var BaseView = require('oroui/js/app/views/base/view');
    var ElementsHelper = require('orofrontend/js/app/elements-helper');
    var QuantityHelper = require('orofrontend/js/app/quantity-helper');
    var BaseModel = require('oroui/js/app/models/base/model');
    var mediator = require('oroui/js/mediator');
    var routing = require('routing');
    var $ = require('jquery');
    var _ = require('underscore');

    BaseProductView = BaseView.extend(_.extend({}, ElementsHelper, {
        elements: {
            quantity: '[data-name="field__quantity"]',
            unit: '[data-name="field__unit"]',
            lineItem: '[data-role="line-item-form-container"]',
            lineItemFields: ['lineItem', ':input[data-name]']
        },

        elementsEvents: {
            'quantity': ['input', 'onQuantityChange'],
            'quantity onFocus': ['focus', 'onFocus'],
            'quantity onBlur': ['blur', 'onBlur'],
            'unit': ['change', 'onUnitChange']
        },

        modelElements: {
            quantity: 'quantity',
            unit: 'unit'
        },

        modelAttr: {
            id: 0,
            quantity: 0,
            unit: '',
            line_item_form_enable: true,
            precision: {
                'item': 5,
                'set': 3
            }
        },

        modelEvents: {
            'id': ['change', 'onProductChanged'],
            'line_item_form_enable': ['change', 'onLineItemFormEnableChanged'],
            'unit_label': ['change', 'changeUnitLabel']
        },

        originalProductId: null,

        initialize: function(options) {
            BaseProductView.__super__.initialize.apply(this, arguments);

            this.rowId = this.$el.parent().data('row-id');
            this.initModel(options);
            this.initializeElements(options);

            this.originalProductId = this.model.get('parentProduct');

            this.initializeSubviews({
                productModel: this.model
            });
        },

        initModel: function(options) {
            this.modelAttr = $.extend(true, {}, this.modelAttr, options.modelAttr || {});
            if (options.productModel) {
                this.model = options.productModel;
            }
            if (!this.model) {
                this.model = (_.isObject(this.collection) && this.collection.get(this.rowId)) ?
                                this.collection.get(this.rowId) : new BaseModel();
            }

            _.each(this.modelAttr, function(value, attribute) {
                if (!this.model.has(attribute)) {
                    this.model.set(attribute, value);
                }
            }, this);
        },

        onProductChanged: function() {
            var modelProductId = this.model.get('id');
            this.model.set('line_item_form_enable', Boolean(modelProductId));

            var productId = modelProductId || this.originalProductId;
            mediator.trigger('layout-subtree:update:product', {
                layoutSubtreeUrl: routing.generate('oro_product_frontend_product_view', {
                    id: productId,
                    parentProductId: this.model.get('parentProduct'),
                    ignoreProductVariant: true
                }),
                layoutSubtreeCallback: _.bind(this.afterProductChanged, this)
            });
        },

        onQuantityChange: function(e) {
            this.forbidQuantityField(e);
            this.setModelValueFromElement(e, 'quantity', 'quantity');
        },

        onUnitChange: function(e) {
            var $quantityField = this.getElement('quantity');
            QuantityHelper.predefinedValueByPrecision($quantityField.get(0), this._getUnitPrecision(e.target.value));
        },

        changeUnitLabel: function() {
            var $unit = this.getElement('unit');
            var unitLabel = this.model.get('unit_label');

            $unit.find('option').each(function() {
                var $option = $(this);
                if (!$option.data('originalText')) {
                    $option.data('originalText', this.text);
                }

                if (unitLabel && this.selected) {
                    this.text = unitLabel;
                } else {
                    this.text = $option.data('originalText');
                }
            });
            $unit.inputWidget('refresh');
        },

        afterProductChanged: function() {
            this.undelegateElementsEvents();
            this.clearElementsCache();
            this.setModelValueFromElements();
            this.delegateElementsEvents();

            this.onLineItemFormEnableChanged();
        },

        onLineItemFormEnableChanged: function() {
            if (this.model.get('line_item_form_enable')) {
                this.enableLineItemForm();
            } else {
                this.disableLineItemForm();
            }
        },

        enableLineItemForm: function() {
            this.getElement('lineItemFields').prop('disabled', false).inputWidget('refresh');
            this.getElement('lineItem').removeClass('disabled');
        },

        disableLineItemForm: function() {
            this.getElement('lineItemFields').prop('disabled', true).inputWidget('refresh');
            this.getElement('lineItem').addClass('disabled');
        },

        onFocus: function(e) {
            e.target.setAttribute('type', 'text');
        },

        onBlur: function(e) {
            e.target.setAttribute('type', 'number');
        },

        forbidQuantityField: function(event) {
            var start = event.target.selectionStart;

            QuantityHelper.trim(event.target);

            if (event.target.value === this.model.get('quantity')) {
                event.target.selectionStart = start - 1;
                event.target.selectionEnd = start - 1;
                return;
            }

            QuantityHelper.predefinedValueByPrecision(event.target, this._getUnitPrecision());
            event.target.selectionStart = start;
            event.target.selectionEnd = start;
        },

        _getUnitPrecision: function(unit) {
            return this.model.get('product_units')[unit || this.model.get('unit')];
        },

        dispose: function() {
            delete this.modelAttr;
            delete this.rowId;
            this.disposeElements();
            BaseProductView.__super__.dispose.apply(this, arguments);
        }
    }));

    return BaseProductView;
});
