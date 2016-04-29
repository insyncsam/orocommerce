define(function(require) {
    'use strict';

    var PaymentTermComponent;
    var _ = require('underscore');
    var mediator = require('oroui/js/mediator');
    var BaseComponent = require('oroui/js/app/components/base/component');
    var routing = require('routing');

    PaymentTermComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: {
            paymentMethod: null,
            successUrl: ''
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);

            mediator.on('checkout:place-order:response', this.handleSubmit, this);
        },

        handleSubmit: function(eventData) {
            if (eventData.responseData.paymentMethod === this.options.paymentMethod) {
                eventData.stopped = true;

                var responseData = _.extend({successUrl: this.getSuccessUrl}, eventData.responseData);

                if (!responseData.successUrl) {
                    return;
                }

                mediator.execute('redirectTo', {url: responseData.successUrl}, {redirect: true});
            }
        },

        getSuccessUrl: function() {
            if (this.options.successUrl) {
                return routing.generate(this.options.successUrl);
            }

            return null;
        },

        dispose: function() {
        }
    });

    return PaymentTermComponent;
});
