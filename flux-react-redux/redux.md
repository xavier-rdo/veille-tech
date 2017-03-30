# Redux

![Logo Redux](img/redux-logo.png)

> Predictable state container for JavaScript apps

Site officiel: [http://redux.js.org/](http://redux.js.org/)

* [Concepts clés](#concepts)
* [Les trois principes de base](#principles)
* [Exemple d'une Todo List](#example)
* [Autres exemples](#examples)
* [Ressources & tutoriels](#resources)
* [Outillage](#tools)

## <a name="concepts"></a>Concepts clés : état, actions & *reducers* (fonctions)

Source: [http://redux.js.org/docs/introduction/CoreConcepts.html](http://redux.js.org/docs/introduction/CoreConcepts.html)

### L'état

L'état de l'application est décrit dans un simple objet JS :

```
    {
      todos: [{
        text: 'Eat food',
        completed: true
      }, {
        text: 'Exercise',
        completed: false
      }],
      visibilityFilter: 'SHOW_COMPLETED'
    }
```

En d'autres termes, il s'agit du modèle (à cette différence qu'il ne possède pas de *setters*). Pour modifier l'état, il faut déclencher des `actions`.

### Les actions

Les actions sont de simples objets JS décrivant ce qui s'est passé : 

```
    { type: 'ADD_TODO', text: 'Go to swimming pool' }
    { type: 'TOGGLE_TODO', index: 1 }
    { type: 'SET_VISIBILITY_FILTER', filter: 'SHOW_ALL' }
```

Ce sont des fonctions (appelées *reducers*) qui permettront d'appliquer les actions à l'état.

### Les reducers (fonctions JS)

Exemple de deux fonctions gérant chacune une partie de l'état de l'application :

```
    function visibilityFilter(state = 'SHOW_ALL', action) {
      if (action.type === 'SET_VISIBILITY_FILTER') {
        return action.filter;
      } else {
        return state;
      }
    }

    function todos(state = [], action) {
      switch (action.type) {
      case 'ADD_TODO':
        return state.concat([{ text: action.text, completed: false }]);
      case 'TOGGLE_TODO':
        return state.map((todo, index) =>
          action.index === index ?
            { text: todo.text, completed: !todo.completed } :
            todo
       )
      default:
        return state;
      }
    }
```

Et enfin, exemple d'une fonction qui gère l'état complet de l'application en s'appuyant sur les deux *reducers* définis ci-dessus :

```
    function todoApp(state = {}, action) {
      return {
        todos: todos(state.todos, action),
        visibilityFilter: visibilityFilter(state.visibilityFilter, action)
      };
    }
```

## <a name="principles"></a>Les trois principes de base de Redux :

* *Un seul store* pour maintenir l'état de l'application (*object tree*)
* *L'état est en lecture seule*. L'émission d'actions est le seul moyen de le modifier.
* Les modifications sont faites à l'aide de fonctions JS pures (*reducers*).

>>Une fonction est 'pure' si elle n'altère pas les données (aucune mutation d'arguments), n'a aucun effet de bord, ne fait aucun appel API, n'appelle aucune fonction impure (`Date.now()` par exemple est impure), etc. Une fonction pure se contente de faire des calculs et de retourner un résultat prévisible.

## <a name="example"></a>Un exemple de TodoList

[Exemple pas à pas d'une Todo-List](todo-list-example/todo-list-example.md) avec Redux & Create React App

## <a name="examples"></a>Autres exemples :

* [http://redux.js.org/docs/introduction/Examples.html](http://redux.js.org/docs/introduction/Examples.html) : compteur, liste de Todo's, panier, arbre, asynchrone, *Real World* (exemple avancé), etc.
* [Full-Stack Redux Tutorial](https://teropa.info/blog/2015/09/10/full-stack-redux-tutorial.html) : exemple TDD, React, Redux & Immutable

## <a name="resources"></a>Ressources & tutoriels

* [http://redux.js.org/](http://redux.js.org/)

* [Getting Started with Redux](https://egghead.io/courses/getting-started-with-redux) : une trentaine de vidéos présentées par le créateur de Redux (Dan Abramov)

* [Redux sur Github](https://github.com/reactjs/redux)

* [https://github.com/happypoulp/redux-tutorial](https://github.com/happypoulp/redux-tutorial)

* [Liste de ressources & tutoriels](http://redux.js.org/docs/introduction/Ecosystem.html#tutorials-and-articles)

* [Ecosystème de Redux](http://redux.js.org/docs/introduction/Ecosystem.html)

* [Awesome Redux](https://github.com/xgrommx/awesome-redux)

* [Learn React & Redux](https://www.youtube.com/watch?v=d0oUGmSE6IY&list=PLJBrYU54JD2pTblB20OmV7GL6H5J-p2g8&index=1) : un peu moins d'une trentaine de vidéos démontrant les différents aspects de React/Redux à travers l'exemple d'une application de gestion d'utilisateurs, avec redux-minimal (*starter kit*)

* [Step by Step Guide To Building React Redux Apps](https://medium.com/@rajaraodv/step-by-step-guide-to-building-react-redux-apps-using-mocks-48ca0f47f9a) par rajaraodv

* [A Guide For Building A React Redux CRUD App](https://medium.com/@rajaraodv/a-guide-for-building-a-react-redux-crud-app-7fe0b8943d0f) par rajaraodv. Application CRUD Redux/React avec requêtes asynchrones et gestion des réponses serveur

## <a name="tools"></a>Outillage :

* [Redux sur npm](https://www.npmjs.com/package/redux)
* [react-redux](https://www.npmjs.com/package/react-redux) Adapteur officiel pour React (*React bindings*)
* [redux-devtools](https://github.com/gaearon/redux-devtools)
* [redux-devtools-extension](https://github.com/zalmoxisus/redux-devtools-extension)
* [Create React App](https://github.com/facebookincubator/create-react-app) : créer un squelette d'application React (Webpack, Babel, ESLint, react-scripts, etc.)
* [redux-minimal](https://redux-minimal.js.org/) React/Redux minimal starter kit (React, Redux, Webpack, webpack-dev-server, Babel, Bootstrap3, etc.)


