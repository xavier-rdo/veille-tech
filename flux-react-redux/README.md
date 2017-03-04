# Flux, React & Redux

## [Flux](flux.md)

* __Flux__ est une architecture d'application destinée aux interfaces utilisateurs. 

* Elle prévoit un flot de données unidirectionnel ('*unidirectional data flow*')

* Composants d'une architecture Flux : 

    * Dispatcher
    * Store
    * Action
    * View

* C'est l'architecture sur laquelle repose React.js

* Référence : [https://github.com/facebook/flux](https://github.com/facebook/flux)


## [React.js](react.md)

Bibliothèque Javascript pour développer des interfaces utilisateurs, orientées composants.

Référence : [https://facebook.github.io/react/](https://facebook.github.io/react/)

## [Redux](redux.md)

* __Redux__ est un conteneur d'état prédictible ('*predictable state container*') pour applications Javascript. 

* Inspiré de l'architecture Flux et du langage Elm ; il se différencie néanmoins de Flux par l'absence de Dispatcher.

* Les trois piliers d'une application Redux : 

    * _store_ unique (arbre d'objets), il est l'*unique source de vérité*
    * état en lecture seule : le seul moyen de mettre à jour l'état, c'est d'émettre des *actions* (objets décrivant ce qui s'est passé)
    * Les changements s'effectuent avec des fonctions. Pour spécifier comment l'arbre d'état doit être modifié par les actions, on développe des _reducers_

* Référence : [http://redux.js.org/](http://redux.js.org/)


