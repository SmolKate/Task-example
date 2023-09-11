import { configureStore } from '@reduxjs/toolkit';
import { combineReducers } from '@reduxjs/toolkit';
import commentsReducer from './comments_reducer';
import thunk from 'redux-thunk';

let rootReducer = combineReducers({
  comments: commentsReducer,
});

let store = configureStore({
  reducer: rootReducer,
  middleware: [thunk],
});

export default store;
