import thunkMiddleware from 'redux-thunk';
import {applyMiddleware, createStore, Store, combineReducers} from 'redux';
import routerMiddleware from 'akeneoenrichedentity/infrastructure/middleware/router';
import formNotifier from 'akeneoenrichedentity/infrastructure/middleware/form-notifier';
import {composeWithDevTools} from 'redux-devtools-extension';
const router = require('pim/router');

export default (debug: boolean = true) => (reducer: any): Store<any> => {
  return createStore(
    combineReducers(reducer),
    true === debug
      ? composeWithDevTools(applyMiddleware(thunkMiddleware, routerMiddleware(router), formNotifier()))
      : applyMiddleware(thunkMiddleware, routerMiddleware(router), formNotifier())
  );
};
