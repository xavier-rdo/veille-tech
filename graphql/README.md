# GraphQL

> A query language for your API

<img src="http://graphql.org/img/logo.svg" width="50%">

__Références :__

* [Learn GraphQL](http://graphql.org/learn/)
* [The Fullstack Tutorial for GraphQL](https://www.howtographql.com/)
* [GraphiQL (An in-browser IDE for exploring GraphQL.)](https://github.com/graphql/graphiql)

__Table des matières :__

* [GraphQL](#graphql-concepts---cles)
* [GraphQL et PHP](#graphql-et-php)
* [GraphQL en Javascript](#graphql-en-javascript)
* [Ressources](#ressources)

## GraphQL - Concepts clés

### `Queries` et `Mutations`

Réf.: http://graphql.org/learn/queries/

Exemple de query avec GraphiQL :

```
query User {
  User(uuid: "83cfd26b-65bd-41c1-b267-1965e5a02b57") {
    uuid
    firstname
    lastname
    createdAt
    updatedAt
  }
}
```

Exemple de mutation avec GraphiQL :

```
mutation EditUser {
  EditUser(uuid: "21dd8fc8-856e-4acc-9aa9-f1147c7b2d68", payload: {
    firstname: "John",
    lastname: "Doe"
  }) {
    uuid
    firstname
    lastname
    createdAt
    updatedAt
  }
}
```

### `Schémas` et `Types`

Réf.: http://graphql.org/learn/schema/

## GraphQL et PHP

* [graphql-php](https://github.com/webonyx/graphql-php) : Implémentation PHP de GraphQL
* [GraphQLBundle pour Symfony](https://github.com/overblog/GraphQLBundle) : pour implémenter un serveur GraphQL en Symfony

## GraphQL en Javascript

* [Apollo](https://www.apollographql.com/)

    * [Apollo-server](https://www.apollographql.com/docs/apollo-server/) : implémenter un serveur GraphQL en Node.js
    * [Apollo client](https://www.apollographql.com/client/) : client GraphQL pour Javascript, React et les plates-formes natives

## Ressources

* [Apollo/GraphQL integration for VueJS (Tutorial)](https://github.com/akryum/vue-apollo)
* [The Fullstack Tutorial for GraphQL](https://www.howtographql.com/)
