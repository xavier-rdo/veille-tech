# Javascript, ECMAScript 2015, ES6

![Logo ECMAScript](img/ecmascript-logo.png)

* [Les Promesses](#promises)
* [Itérateurs & générateurs](#iterators)
* [Autres nouveautés d'ES6](#misc)
* [Ressources](#ressources)

## <a name="promises"></a>Les Promesses en JS

Réf. (spécification): https://promisesaplus.com/

Une promesse peut avoir quatre états :

* `fullfilled` (succès)
* `rejected` (échec)
* `pending` (pas encore de succès ou d'échec)
* `settled` (succès ou échec)

### Création :
```js
var promise = new Promise(function(resolve, reject) {
    // Do a thing, possibly async, then ...
    
    if (/* Everything turned out fine */) {
        resolve('It worked!');
    } else {
        reject(Error('It failed!'));
    }
});
```

### Usage :

```js
promise.then(function(result) {
    console.log(result); // 'It worked!'
}, function(err) {
    console.log(err); // Error: 'It failed!'
});
```

`then` prend deux arguments (optionnels) :

* une fonction de callback en cas de succès
* une fonction de callback en cas d'échec

Il est possible de ne passer qu'une seule fonction callback de succès ou d'échec.

Noter que l'API promesses traite tout objet possédant une méthode `then` comme une promesse (interface `thenable`, implémentée par exemple par les Deferreds de jQuery).

### Séquence d'actions asynchrones

Les promesses peuvent être utilisées pour chaîner des actions asynchrones. Lorsqu'un callback d'un `then` retourne une valeur, le `then()` suivant est appelé avec cette valeur. Mais si l'on retourne une promesse (ou `thenable`), le `then()` suivant attend le résultat et n'est appelé que lorsque la promesse a abouti à un échec ou un succès (`settled`).

```js
getJSON('story.json').then(function(story) {
  return getJSON(story.chapterUrls[0]);
}).then(function(chapter1) {
  console.log("Got chapter 1!", chapter1);
})
```

### Gestion des erreurs

`then()` prend deux arguments : l'un en cas de succès, l'autre en cas d'échec :

```js
get('story.json').then(function(response) {
  console.log("Success!", response);
}, function(error) {
  console.log("Failed!", error);
})
```

Il est possible d'utiliser une clause `catch()`:

```js
get('story.json').then(function(response) {
  console.log("Success!", response);
}).catch(function(error) {
  console.log("Failed!", error);
})
```

**Nota bene** : il y a une différence entre les deux extraits ci-dessus. Le `reject` d'une promesse conduit au prochain `then()` avec un callback d'échec. Avec `then(func1, func2)`, `func1` sera appelée ou bien `func2`, mais pas les deux. Avec `then(func1).catch(func2)`, les deux seront appelées si `func1` échoue.

Le `reject` a lieu si une promesse est explicitement rejetée, mais également implicitement si une erreur est levée dans le callback passé au constructeur de la promesse :

```js
var jsonPromise = new Promise(function(resolve, reject) {
  // JSON.parse throws an error if you feed it some
  // invalid JSON, so this implicitly rejects:
  resolve(JSON.parse("This ain't JSON"));
});

jsonPromise.then(function(data) {
  // This never happens:
  console.log("It worked!", data);
}).catch(function(err) {
  // Instead, this happens:
  console.log("It failed!", err);
})
```

En cas d'échec, tous les callbacks de succès avant le `catch()` seront ignorés :

```js
getJSON('story.json').then(function(story) {
  return getJSON(story.chapterUrls[0]);
}).then(function(chapter1) {
  addHtmlToPage(chapter1.html);
}).catch(function() {
  addTextToPage("Failed to show chapter");
}).then(function() {
  document.querySelector('.spinner').style.display = 'none';
})
```

Par exemple, si `story.chapterUrls[0]` échoue (erreur 500), la clause `catch` s'exécute directement. En revanche, le code qui suit l'erreur interceptée s'exécute.

### Promesses parallèles

```js
let myPromise1 = new Promise(function(resolve, reject) {
    setTimeout(function() {
        resolve('myPromise1 succeeded');
    }, 1000);
});

let myPromise2 = new Promise(function(resolve, reject) {
    setTimeout(function() {
        resolve('myPromise2 succeeded');
    }, 1500);
});

Promise.all([myPromise1, myPromise2])
    .then(function(data) {
        console.log(data);
    })
    .catch(function(err) {
        console.log(err);
    })
;
```

### API Promise

#### Méthodes statiques :

|Méthode|Description|
|-------|-----------|
|Promise.resolve(promise)|Retourne une promesse|
|Promise.resolve(thenable)|Retourne une nouvelle promesse à partir de `thenable`|
|Promise.resolve(obj)|Crée une promesse qui réussit en retournant l'objet `obj`|
|Promise.reject(obj)|Crée une promesse qui échoue en retournant `obj` (instance de `Error`)|
|Promise.all(array)|Crée une promesse qui réussit lorsque chaque élément réussit ou échoue si l'un des éléments échoue|
|Promise.race(array)|Crée une promesse qui réussit dès qu'un élément réussit ou échoue dès qu'un élément échoue|

#### Constructeur :

`new Promise(function(resolve, reject) {});` 

* resolve(thenable): la promesse sera `fullfilled` ou `rejected` avec le résultat de `thenable`
* resolve(obj): la promesse réussit en retournant `obj`
* reject(obj): la promesse échoue en retournant `obj` (instance de Error)

#### Méthodes d'instance :

`promise.then(onFullfilled, onRejected)` : `onFullfilled` est appelée lorsque le promesse réussit, `onRejected` lorsque la promesse échoue. Les deux callbacks ont un seul paramètre : la valeur de succès ou la cause du rejet. `then()` retourne une nouvelle promesse équivalente à la valeur retournée par `onFullfilled|onRejected` après avoir été passée à la méthode `Promise.resolve`. Si une erreur se produit dans le callback, la promesse retournée est rejetée avec cette erreur.

`promise.catch(onRejected)` = équivalent à `promise.then(undefined, onRejected)`

### Support des promesses :

Chrome 32, Opera 19, Firefox 29, Safari 8 & Microsoft Edge.

### Resources

* [Mozilla Developer Network (MDN)](https://developer.mozilla.org/fr/docs/Web/JavaScript/Reference/Objets_globaux/Promise)
* [developers.google.com](https://developers.google.com/web/fundamentals/getting-started/primers/promises)

## <a name="iterators"></a>Itérateurs & générateurs

Un générateur est un type de fonction spécial qui fonctionne comme une fabrique (*factory*) d'itérateurs. Une fonction devient un générateur lorsqu'elle contient une ou plusieurs expressions `yield` et qu'elle utilise la syntaxe `function*`.

```js
function* idMaker() {
  var index = 0;
  while(true)
      yield index++;
}

var gen = idMaker();
console.log(gen.next().value); // 0
console.log(gen.next().value); // 1
console.log(gen.next().value); // 2
// ...
```

### Resources

* [Mozilla Developer Network (MDN)](https://developer.mozilla.org/fr/docs/Web/JavaScript/Guide/iterateurs_et_generateurs)

## <a name="misc"></a>Autres nouveautés d'ES6

### `const` & `let`

`let` permet de déclarer des variables dont la portée est limitée à celle du bloc dans lequel elles sont déclarées (`var` permet quant à lui de définir une variable globale ou locale à une fonction, sans distinction des blocs).

Dans l'exemple ci-dessous, `maVariable` n'est pas définie en dehors du bloc `if`:

```js
if (true) {
  let maVariable = 'foo';
}
console.log(maVariable); // ReferenceError
```

`const` permet de créer une constante nommée accessible uniquement en lecture (*ie* l'identifiant ne peut pas être réaffecté). Ex: `const MA_CONSTANTE = 'foo';`.

### Proxy & Reflect

* [Proxy sur MDN ](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Proxy)
* [Reflect sur MDN](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Reflect)
* [Article de "Putain de Code" sur Proxy](http://putaindecode.io/fr/articles/js/es2015/proxy/)

### Divers

* Arrow functions (`=>`)
* spread operator (`...`)
* template strings (backtick)
* destructuring (`var [one, two] = ['one', 'two'];`)
* `Set` (pour stocker des valeurs uniques) et `Set.prototype.entries` (iterator)
* `Map` (dictionnaire) et `Map.prototype.entries` (iterator)
* `for of` (parcourir un objet itérable)
* `Object.assign()` (cloner un objet existant)
* mot-clé `static`: pour définir une méthode statique d'une classe

## <a name="resources"></a>Ressources

* [ES6 in Depth (ponyfoo)](https://ponyfoo.com/articles/tagged/es6-in-depth)
* [Exploring ES6](http://exploringjs.com/es6/index.html)
