'use strict';
/**
 * Source switcher extension
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
define(
    [
        'underscore',
        'pim/form',
        'pim/fetcher-registry',
        'pimee/template/product/source-switcher'
    ],
    function (
        _,
        BaseForm,
        FetcherRegistry,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'AknDropdown AknButtonList-item source-switcher',
            events: {
                'click li a': 'changeSource'
            },

            /**
             * Render the sources select
             *
             * @returns {Object}
             */
            render: function () {
                var context = {
                    sources: [],
                    currentSource: ''
                };

                this.trigger('pim_enrich:form:source_switcher:render:before', context);
                this.$el.html(this.template(context));
                this.delegateEvents();

                return this;
            },

            /**
             * Trigger the source change event
             *
             * @param {Object} event
             */
            changeSource: function (event) {
                this.trigger('pim_enrich:form:source_switcher:source_change', event.currentTarget.dataset.source);
            }
        });
    }
);
