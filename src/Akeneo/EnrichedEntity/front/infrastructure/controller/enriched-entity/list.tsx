import * as $ from 'jquery';
import * as ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import * as React from 'react';
import EnrichedEntityView from 'akeneoenrichedentity/application/component/enriched-entity/index';
import createStore from 'akeneoenrichedentity/infrastructure/store';
import enrichedEntityReducer from 'akeneoenrichedentity/application/reducer/enriched-entity/index';
import {updateResults} from 'akeneoenrichedentity/application/action/enriched-entity/search';
import { catalogLocaleChanged, catalogChannelChanged, uiLocaleChanged } from 'akeneoenrichedentity/domain/event/user';

const BaseController = require('pim/controller/base');
const mediator = require('oro/mediator');
const userContext = require('pim/user-context')

class EnrichedEntityListController extends BaseController {
  renderRoute() {
    const store = createStore(true)(enrichedEntityReducer);
    store.dispatch(catalogLocaleChanged(userContext.get('catalogLocale')));
    store.dispatch(catalogChannelChanged(userContext.get('catalogScope')));
    store.dispatch(uiLocaleChanged(userContext.get('uiLocale')));
    store.dispatch(updateResults());

    mediator.trigger('pim_menu:highlight:tab', { extension: 'pim-menu-enriched-entity' });

    ReactDOM.render(
      (<Provider store={store}>
        <EnrichedEntityView/>
      </Provider>),
      this.el
    );

    return $.Deferred().resolve();
  }
}

export = EnrichedEntityListController;