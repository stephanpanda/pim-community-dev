define(
    [
        'jquery',
        'underscore',
        'backbone',
        'oro/delete-confirmation',
        'oro/translator',
        'oro/navigation',
        'pimee/productasset/uploader'
    ],
    function ($, _, Backbone, DeleteConfirmation, __, Navigation, Uploader) {
        'use strict';

        return Backbone.View.extend({
            el: '#pimee_product_asset-tabs-variations',
            uploader: undefined,
            events: {
                'click .delete': 'confirmDelete',
                'click .reset-variations': 'confirmResetVariations'
            },
            initialize: function() {
                this.uploader = new Uploader();
            },
            confirmDelete: function (event) {
                event.preventDefault();
                var button = event.currentTarget;
                var targetUrl = $(button).data('href');
                // TODO add a translation key
                var confirmation = this.getConfirmDialog('Are you sure you want to delete this item ?', targetUrl);
                confirmation.open();
            },
            confirmResetVariations: function (event) {
                event.preventDefault();
                var button = event.currentTarget;
                var targetUrl = $(button).data('href');
                var confirmation = this.getConfirmDialog(
                    // TODO add a translation key
                    'Are you sure you want to reset all variations ?',
                    targetUrl,
                    'reset.variations'
                );
                confirmation.open();
            },
            getConfirmDialog: function (message, targetUrl, title) {
                var options = {
                    content: __(message)
                };
                if (title) {
                    options.title = __(title);
                }
                var confirmModal = new DeleteConfirmation(options);
                confirmModal.on('ok', function () {
                    Navigation.getInstance().setLocation(targetUrl);
                });
                return confirmModal;
            }
        });
    }
);
