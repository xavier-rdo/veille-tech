# Webpack

![Logo Webpack](img/webpack-logo.png)

> _Bundle your assets_. Module bundler for modern JS applications.

Site officiel : [https://webpack.js.org](https://webpack.js.org)

* [Concepts](#concepts)
* [Installation](#install)
* [Débuter](#start)
* [Outils de développement](#dev-tools)

##<a name="concepts"></a> Les quatre concepts clés de Webpack

* __entry__ : le point d'entrée (Ex: _index.js_, _app.js_) à partir duquel Webpack construit le graphe des dépendances
* __output__ : fichier(s) en sortie où Webpack écrira le code empaqueté (_bundled_). Ex: _dist/bundle.js_
* __loaders__ : transforment les fichiers d'entrée en modules
* __plugins__ : offrent des fonctionnalités supplémentaires, comme par exemple la minification, l'_uglyfication_, etc.

##<a name="install"></a> Installation

Webpack nécessite une version récente de Node.js.

A la racine du projet : 

```shell
    npm init -y
    npm install webpack --save-dev
```

Le binaire de Webpack est alors disponible en ligne de commande : `./node_modules/.bin/webpack`

`./node_modules/.bin/webpack --help` pour lister les commandes CLI disponibles

__Nota__ : privilégier une installation locale pour gérer les différences de versions entre projets ; l'installation globale est déconseillée.

##<a name="start"></a> Débuter

Etat donné un fichier _app/index.js_ qui utilise par exemple _lodash_ comme dépendance (`import _ from 'lodash'`), lancer la commande suivante : 

```shell
    ./node_modules/.bin/webpack app/index.js dist/bundle.js
```

### Webpack et les modules ES0215

Par défaut, Webpack ne modifie pas le code, hormis les instructions `import`/`export`. Un transpileur, tel que __Babel__ est requis si le code JS à empaqueter utilise des fonctionnalités d'ES6.

### Fichier de config : `webpack.config.js`

```javascript
    var path = require('path');

    module.exports = {
      entry: './app/index.js',
      output: {
        filename: 'bundle.js',
        path: path.resolve(__dirname, 'dist')
      }
    };
```

`./node_modules/.bin/webpack --config webpack.config.js` (pas nécessaire car Webpack prend `webpack.config.js` comme fichier de configuration par défaut).

### Utiliser Webpack avec npm

Ajouter cette entrée au fichier `package.json` : 

```json
    {
      ...
      "scripts": {
        "build": "webpack"
      },
      ...
    }
```

Désormais, la commande `npm run build` peut être plus simplement utilisée pour exécuter Webpack.

##<a name="dev-tools"></a> Outils de développement

Référence : [https://webpack.js.org/guides/development/](https://webpack.js.org/guides/development/)

* [webpack-dev-server/](https://webpack.js.org/configuration/dev-server/)
* _Watch mode_ [https://webpack.js.org/guides/development/#webpack-watch-mode](https://webpack.js.org/guides/development/#webpack-watch-mode)
* [webpack-dev-middleware](https://webpack.js.org/guides/development/#webpack-dev-middleware)
* [Chrome DevTools](https://medium.com/@rafaelideleon/webpack-your-chrome-devtools-workspaces-cb9cca8d50da#.8idaxo1gj)
