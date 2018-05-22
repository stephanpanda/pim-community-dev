'use strict';

/**
 * Push data to pim.ai
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'pimee/template/product/suggest-data-push',
        'routing'
    ],
    function (
        _,
        __,
        BaseForm,
        template,
        Routing
    ) {
        return BaseForm.extend({
            template: _.template(template),

            render: function () {
                this.$el.html(
                    this.template({
                        path: Routing.generate('pimee_suggest_data_push_product', {productId: this.getFormData().meta.id}),
                        label: __('pimee_suggest_data.product.edit.btn.push')
                    })
                );

                return this;
            }
        });
    }
);
