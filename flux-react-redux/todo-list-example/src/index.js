import React from 'react';
import { render } from 'react-dom'
import { Provider } from 'react-redux'
import { createStore } from 'redux'
import todoApp from './reducers'
import App from './components/App';
import './index.css';
import logo from './logo.svg';

let store = createStore(todoApp)

render(
  <Provider store={store}>
        <div className="App">
            <div className="App-header">
                <img src={logo} className="App-logo" alt="logo" />
                <h2>Welcome to React</h2>
            </div>
            <App />
        </div>
  </Provider>,
  document.getElementById('root')
)
