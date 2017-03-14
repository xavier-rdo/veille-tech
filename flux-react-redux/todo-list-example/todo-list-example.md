# Exemple de Todo list pas à pas avec Redux & Create React App

Références :

* [https://github.com/reactjs/redux/tree/master/examples/todos](https://github.com/reactjs/redux/tree/master/examples/todos)
* [https://github.com/reactjs/redux/blob/8bea64d51c41768f1490282ca60ca9061b1adebe/docs/basics/ExampleTodoList.md](https://github.com/reactjs/redux/blob/8bea64d51c41768f1490282ca60ca9061b1adebe/docs/basics/ExampleTodoList.md)

Sommaire :

* [Génération du projet avec Create React App](#scaffold)
* [Installation de Redux](#install-redux)
* [Les actions](#actions)
* [Les reducers](#reducers)
* [Le store](#store)
* [Premier aperçu de l'app](#preview)
* [Intégration de React](#react)

## <a name="scaffold"></a> Génération du projet avec [Create React App](https://github.com/facebookincubator/create-react-app)

* Installer __Create React App__ globalement : `npm install -g create-react-app`

* Vérifier l'installation : `create-react-app --version`

* Générer la structure du projet : `create-react-app <my-app-name>` (génère la structure du projet et installe les dépendances __react__, __react_scripts__ (dev) & __react-dom__)

* Voir la liste des dépendances installées : `cat package.json`

* Démarrer le serveur : `cd <my-app-name> && npm start`

* Lancer les tests : `npm test` ou bien `yarn test`

* Noter que l'installeur a posé un fichier [README.md](README.md) à la racine du projet

* Structure du projet après installation :

```
my-app/
  README.md
  node_modules/
  package.json
  public/
    index.html
    favicon.ico
  src/
    App.css
    App.js
    App.test.js
    index.css
    index.js
    logo.svg
```

## <a name="install-redux"></a>Installation de Redux

* Installer `redux` et `react-redux` :

```shell
	yarn add redux react-redux
```

* Créer les quatre dossiers suivants : `actions`, `components`, `containers`, `reducers` dans le répertoire `src` :

```shell
	mkdir src/actions src/components src/containers src/reducers
```

## <a name="actions"></a>Les actions

Référence : [http://redux.js.org/docs/basics/Actions.html](http://redux.js.org/docs/basics/Actions.html)

`actions/index.js` :

```
	/* Actions as constants */
	export const ADD_TODO = 'ADD_TODO'
	export const TOGGLE_TODO = 'TOGGLE_TODO'
	export const SET_VISIBILITY_FILTER = 'SET_VISIBILITY_FILTER'
	
	/* Visibility constants */
	export const VisibilityFilters = {
	  SHOW_ALL: 'SHOW_ALL',
	  SHOW_COMPLETED: 'SHOW_COMPLETED',
	  SHOW_ACTIVE: 'SHOW_ACTIVE'
	}
	
	/* Action creators */
	let nextTodoId = 0
	export function addTodo(text) {
	  return {
		  type: ADD_TODO,
		  id: nextTodoId++,
		  text
	  }
	}
	
	export function toggleTodo(index) {
	  return { type: TOGGLE_TODO, index }
	}
	
	export function setVisibilityFilter(filter) {
	  return { type: SET_VISIBILITY_FILTER, filter }
	}
```

## <a name="reducers"></a> Les reducers

Référence : [http://redux.js.org/docs/basics/Reducers.html](http://redux.js.org/docs/basics/Reducers.html)

Les actions décrivent des événements mais ignorent comment ils se répercutent sur l'état de l'application. Modifier l'état de l'application est de la responsabilité des `reducers`.

Un `reducer` :

* prend en argument 
    * un état
    * une action
* retourne une copie de l'état modifiée en fonction de l'action passée en argument

`(previousState, action) => newState`

`reducers/index.js` :

```
	import { combineReducers } from 'redux'
	import { ADD_TODO, TOGGLE_TODO, SET_VISIBILITY_FILTER, VisibilityFilters } from './../actions'
	const { SHOW_ALL } = VisibilityFilters

	/* Define one reducer responsible for handling visibility filter state */
	function visibilityFilter(state = SHOW_ALL, action) {
	  switch (action.type) {
	    case SET_VISIBILITY_FILTER:
	      return action.filter
	    default:
	      return state
	  }
	}

	/* Define another reducer to handle the todo list */
	function todos(state = [], action) {
	  switch (action.type) {
	    case ADD_TODO:
	      return [
	        ...state,
	        {
	          text: action.text,
	          id; action.id,
	          completed: false
	        }
	      ]
	    case TOGGLE_TODO:
	      return state.map((todo, index) => {
	        if (index === action.index) {
	          return Object.assign({}, todo, {
	            completed: !todo.completed
	          })
	        }
	        return todo
	      })
	    default:
	      return state
	  }
	}

	/* Combine our two reducers to obtain one main reducer that we will pass to the Redux store */
	const todoApp = combineReducers({
	  visibilityFilter,
	  todos
	})

	export default todoApp
```

On définit ici deux `reducers` (`todos` et `visibilityFilter`) chargés chacun de ne gérer qu'une partie de l'état global. On expose enfin un `reducer` unique composé de ces deux `reducers` à l'aide de la fonction `combineReducers` de Redux. Cette division des responsabilités s'appelle la _composition par reducer_ (_reducer composition_) dans le jargon de Redux.

Noter ici l'usage de l'opérateur de décomposition (`...state`, [`spread operator`](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Operators/Spread_operator)) et celui de la méthode [`Object.assign`](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Object/assign), deux nouveautés JS issues d'ES6.

## <a name="store"></a> Le Store

Référence : [http://redux.js.org/docs/basics/Store.html](http://redux.js.org/docs/basics/Store.html)

* Les `actions` décrivent "ce qui s'est passé"
* Les `reducers` mettent à jour l'état de l'application en fonction de ces actions
* Le `store` (__unique__), lui, permet de faire le lien entre les actions et les reducers

### Responsabilités du Store :

* il contient l'état de l'application
* il permet d'accéder à cet état avec la méthode `getState()`
* il permet de mettre à jour l'état avec la méthode `dispatch(action)`
* il enregistre les listeners via `subscribe(listener)`
* il gère la désinscription des listeners via la fonction retournée par `subscribe(listener)`

`index.js` :

```
	// Conserver éventuellement le code initial généré par Create React App
	// ...

	import { createStore } from 'redux'
	import todoApp from './reducers'

	let store = createStore(todoApp)
```

La méthode `createStore` accepte en second argument un état initial.  C'est notamment utile pour hydrater l'état à partir de l'état de l'application Redux côté serveur :

```
    let store = createStore(todoApp, window.STATE_FROM_SERVER)
```

## <a name="preview"></a> Premier aperçu

Ajouter les lignes suivantes au fichier `index.js`:

```

import { addTodo, toggleTodo, setVisibilityFilter, VisibilityFilters } from './actions'

// Log the initial state
console.log(store.getState())

// Every time the state changes, log it
// Note that subscribe() returns a function for unregistering the listener
let unsubscribe = store.subscribe(() =>
  console.log(store.getState())
)

// Dispatch some actions
store.dispatch(addTodo('Learn about actions'))
store.dispatch(addTodo('Learn about reducers'))
store.dispatch(addTodo('Learn about store'))
store.dispatch(toggleTodo(0))
store.dispatch(toggleTodo(1))
store.dispatch(setVisibilityFilter(VisibilityFilters.SHOW_COMPLETED))

// Stop listening to state updates
unsubscribe()
```

Et observer les logs dans la console du navigateur :

![Console Chrome - Etat du store](img/chrome-console.png)

> Rappel: `npm build && npm start` pour builder les assets et démarrer le serveur de dév.

## <a name="react"></a> Intégration de React

[`react-redux`](https://github.com/reactjs/react-redux) est la bibliothèque de liaison entre React & Redux. Elle n'est pas incluse par défaut dans Redux.

```shell
    yarn ad react-redux
```

Dans les applications React, on distingue généralement les composants de présentation et les composants conteneurs (_container_) : 

Référence: [http://redux.js.org/docs/basics/UsageWithReact.html](http://redux.js.org/docs/basics/UsageWithReact.html)

<table>
    <thead>
        <tr>
            <th></th>
            <th scope="col" style="text-align:left">Presentational Components</th>
            <th scope="col" style="text-align:left">Container Components</th>
        </tr>
    </thead>
    <tbody>
        <tr>
          <th scope="row" style="text-align:right">Purpose</th>
          <td>How things look (markup, styles)</td>
          <td>How things work (data fetching, state updates)</td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right">Aware of Redux</th>
          <td>No</th>
          <td>Yes</th>
        </tr>
        <tr>
          <th scope="row" style="text-align:right">To read data</th>
          <td>Read data from props</td>
          <td>Subscribe to Redux state</td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right">To change data</th>
          <td>Invoke callbacks from props</td>
          <td>Dispatch Redux actions</td>
        </tr>
        <tr>
          <th scope="row" style="text-align:right">Are written</th>
          <td>By hand</td>
          <td>Usually generated by React Redux</td>
        </tr>
    </tbody>
</table>

### Composants de présentation :

* [App.js](src/components/App.js)
* [Footer.js](src/components/Footer.js)
* [Link.js](src/components/Links.js)
* [Todo.js](src/components/Todo.js)
* [TodoList.js](src/components/TodoList.js)

### Composants conteneurs :

Dans les composants conteneurs :

* la fonction `mapStateToProps` permet de passer l'état du store en tant que propriétés du composant
* la fonction `mapDispatchToProps` permet quant à elle de dispatcher les actions

Ces deux méthodes sont ensuite passées en arguments de la méthode `connect` qui permet de passer ces deux fonctions au composant concerné.

`containers/FilterLinks` :

```javascript
	import { connect } from 'react-redux'
	import { setVisibilityFilter } from '../actions'
	import Link from '../components/Link'

	const mapStateToProps = (state, ownProps) => {
	  return {
	    active: ownProps.filter === state.visibilityFilter
	  }
	}

	const mapDispatchToProps = (dispatch, ownProps) => {
	  return {
	    onClick: () => {
	      dispatch(setVisibilityFilter(ownProps.filter))
	    }
	  }
	}

	const FilterLink = connect(
	  mapStateToProps,
	  mapDispatchToProps
	)(Link)

	export default FilterLink
```

`containers/VisibleTodoList`:

```javascript
import { connect } from 'react-redux'
import { toggleTodo } from '../actions'
import TodoList from '../components/TodoList'

const getVisibleTodos = (todos, filter) => {
  switch (filter) {
    case 'SHOW_ALL':
      return todos
    case 'SHOW_COMPLETED':
      return todos.filter(t => t.completed)
    case 'SHOW_ACTIVE':
      return todos.filter(t => !t.completed)
  }
}

const mapStateToProps = (state) => {
  return {
    todos: getVisibleTodos(state.todos, state.visibilityFilter)
  }
}

const mapDispatchToProps = (dispatch) => {
  return {
    onTodoClick: (id) => {
      dispatch(toggleTodo(id))
    }
  }
}

const VisibleTodoList = connect(
  mapStateToProps,
  mapDispatchToProps
)(TodoList)

export default VisibleTodoList
```

`containers/AddTodo.js` :

```javascript
import React from 'react'
import { connect } from 'react-redux'
import { addTodo } from '../actions'

let AddTodo = ({ dispatch }) => {
  let input

  return (
    <div>
      <form onSubmit={e => {
        e.preventDefault()
        if (!input.value.trim()) {
          return
        }
        dispatch(addTodo(input.value))
        input.value = ''
      }}>
        <input ref={node => {
          input = node
        }} />
        <button type="submit">
          Add Todo
        </button>
      </form>
    </div>
  )
}
AddTodo = connect()(AddTodo)

export default AddTodo
```

### Injection du Store (`index.js`)

`index.js` :

```javascript
import React from 'react'
import { render } from 'react-dom'
import { Provider } from 'react-redux'
import { createStore } from 'redux'
import todoApp from './reducers'
import App from './components/App'

let store = createStore(todoApp)

render(
  <Provider store={store}>
    <App />
  </Provider>,
  document.getElementById('root')
)
```
