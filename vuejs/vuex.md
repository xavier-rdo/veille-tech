# Vuex

> Vuex is a state management pattern + library for Vue.js applications.

![Logo Vuex.js](img/vuex.png)

* [Références](#references)
* [State](#state)
* [Getters](#getters)
* [Mutators](#mutators)
* [Actions](#actions)
* [Modules](#modules)
* [Exemples](#exemples)

## Références :

* [Documentation officielle](https://vuex.vuejs.org/en/)

## State

Vuex gère un arbre d'état unique qui fait office de source de vérité (the _single source of truth_).

Exemple de store:

```js
const store = new Vuex.Store({
  state: {
    todos: [
      { id: 1, text: '...', done: true },
      { id: 2, text: '...', done: false },
    ]
  },
  getters: {
    doneTodos: state => {
      return state.todos.filter(todo => todo.done)
    }
  },
  mutators: {},
  actions: {},
});
```

## Getters

C'est l'équivalent de la propriété `computed` pour le store.

## Mutators

La seule manière de mettre à jour l'état du store Vuex est de committer des mutations. C'est le rôle des mutateurs.

## Actions

Similaires aux _mutators_ à la différence que :

* au lieu de muter directement l'état, les actions committent des mutations
* les actions peuvent contenir des actions asynchrones (ce que ne permettent pas les _mutators_)

Il est recommandé de toujours passer par des actions dans les composants Vue pour modifier l'état du store (et non pas invoquer directement les _mutators_). Cf. diagramme ci-dessus.

```Composant > Action > Mutation```

En résumé, les composants _dispatchent_ des actions, les actions _committent_ des mutations (`dispatch` et `commit`).

## Modules

Objectif des modules : diviser le store en modules pour ne pas se retrouver avec un store _obèse_.

Cf. https://vuex.vuejs.org/en/modules.html

## Exemples

* [Exemples livrés avec Vuex](https://github.com/vuejs/vuex/tree/dev/examples)
* [Vue Hackernews 2.0 sur Github](https://github.com/vuejs/vue-hackernews-2.0)

