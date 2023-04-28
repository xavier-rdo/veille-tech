# Hooks

Références : 

- [documentation React](https://react.dev/reference/react)

> Les hooks permettent d'utiliser différentes fonctionnalités de React dans nos composants

## Les principaux hooks : 

- `useState` : gérer les variables internes d'un composant (formulaire par ex)
- `useReducer` : gérer et mettre à jour les variables internes à l'aide de fonctions `reducer`
- `useContext` : accéder et s'abonner au `context` (alternative aux props de React qui facilite la transmission d'informations d'un composant à ses enfants)
- `useEffect` : synchroniser un composant avec un système externe (calls API par ex)
- `useLayoutEffect` : semblabe à `useEffect` mais il se déclenche avant l'affichage côté navigateur (privilégier `useEffect` pour des questions de performances)
- `useMemo` : mettre en cache le résultat d'un calcul pour le réutiliser entre plusieurs ré-affichages d'un composant
- `useRef` : permet de faire référence à une valeur qui n'est pas utile au rendu (rendering)
- `useCallback` : mettre en cache une définition de fonction entre les rendus (re-rendering)
- et également `useDebugValue`, `useId`, `useImperativeHandler`, `useInterserctionEffect`, `useTransition`

## State hooks

Enregistrer dans un composant les informations liées aux inputs utilisateurs. Utiles dans les composants formulaires pour gérer l'état du formulaire ou par exemple dans une galerie d'images pour conserver l'ID de l'image couramment sélectionnée.

- `useState` permet de déclarer une variable d'état que l'on peut directement mettre à jour
- `useReducer` permet de declarer une variable d'état dont la logique de mise à jour réside dans une fonction reducer

### `useState`

```javascript
import { useState } from 'react';

function MyComponent() {
  const [age, setAge] = useState(28);
  const [name, setName] = useState('Taylor');
  const [todos, setTodos] = useState(() => createTodos());
```

:warning: ce hook est toujours appelé au début du composant ; il ne doit pas être utilisé dans des boucles ou dans des conditions.

Pour utiliser les fonctions `set`, on peut :

- soit passer le nouvel état directement : `setAge(12)`
- soit lui passer une fonction qui calcule le nouvel état à partir de l'état précédent : `setAge((a) => a+1))`

Lorsqu'une fonction `set` est appelée, React enregistre le nouvel état, met à jour le rendu du composant avec le nouvel état et met à jour l'UI.

:warning: le changement d'état ne s'effectue qu'au prochain rendu, pas dans le code en cours d'exécution :

```
function handleClick() {
  setName('Robin');
  console.log(name); // Still "Taylor"!
}
```

Pour mettre à jour des tableaux (array) et des objets, il ne faut pas les éditer directement mais les remplacer par un nouveau tableau ou un nouvel objet, pour que le composant se rafraîchisse :

```javascript
// ✅ Replace state with a new object
setForm({
  ...form,
  firstName: 'Taylor'
});
```

:bulb: Mettre à jour des tableaux : https://react.dev/learn/updating-arrays-in-state

:bulb: Pour initialiser un état avec une fonction, plutôt passer la fonction en paramètre que le résultat de la fonction, pour éviter de rappeler la fonction à chaque rendu :

```javascript
🚩 const [todos, setTodos] = useState(createInitialTodos());
✅ const [todos, setTodos] = useState(() => createInitialTodos());
✅ const [todos, setTodos] = useState(createInitialTodos);
```

:bulb: Dans une liste, on peut restaurer un état initial avec une clé différente. Par exemple, dans le code qui suit, on restaure l'état initial du formulaire lorsque l'utilisateur clique sur le bouton "Reset" :

```javascript
import { useState } from 'react';

export default function App() {
  const [version, setVersion] = useState(0);

  function handleReset() {
    setVersion(version + 1);
  }

  return (
    <>
      <button onClick={handleReset}>Reset</button>
      <Form key={version} />
    </>
  );
}
```

:bulb: Erreur `Too many re-renders`

```javascript
// 🚩 Wrong: calls the handler during render
return <button onClick={handleClick()}>Click me</button>

// ✅ Correct: passes down the event handler
return <button onClick={handleClick}>Click me</button>

// ✅ Correct: passes down an inline function
return <button onClick={(e) => handleClick(e)}>Click me</button>
```

:bulb: En mode strict et en dév, React peut appeler deux fois des setters ou des fonctions d'initialisation d'état (pour s'assurer que les fonctions sont pures)

### `useReducer`

Ce hook permet de gérer l'état d'un composant à l'aide d'une fonction `reducer` : `const [state, dispatch] = useReducer(reducer, initialArg, init?)`

```javascript
import { useReducer } from 'react';

function reducer(state, action) {
  if (action.type === 'incremented_age') {
    return {
      age: state.age + 1
    };
  }
  throw Error('Unknown action.');
}

export default function Counter() {
  const [state, dispatch] = useReducer(reducer, { age: 42 });

  return (
    <>
      <button onClick={() => {
        dispatch({ type: 'incremented_age' })
      }}>
        Increment age
      </button>
      <p>Hello! You are {state.age}.</p>
    </>
  );
}

```

:warning: comme `useState`, le hook `useReducer` ne peut être appelé qu'au début du composant et ne doit pas être appelé à l'intérieur d'une boucle ou d'une condition.

Globalement, ce hook possède les mêmes limites et le même fonctionnement que le hook `useState` (même manière de modifier les objets et les tableaux, appelé deux fois en mode strict, etc.). La différence, c'est que la logique de mise à jour de l'état est déportée dans une fonction hors du composant plutôt que dans des event handlers.

:bulb: [En savoir plus sur les reducers ...](https://react.dev/learn/extracting-state-logic-into-a-reducer)

### `useContext`

Hook React qui permet de lire un contexte et y souscrire : `const value = useContext(SomeContext)`. Le composant est actualisé chaque fois que le contexte est modifié.

Souvent, on souhaite modifer le contexte ; pour cela, on le combine avec l'état (`state`) => on déclare une variable d'état dans le composant parent, et on le propage en tant que valeur de contexte (`context value`) au provider :

```javascript
function MyPage() {
  const [theme, setTheme] = useState('dark');
  return (
    <ThemeContext.Provider value={theme}>
      <Form />
      <Button onClick={() => {
        setTheme('light');
      }}>
        Switch to light theme
      </Button>
    </ThemeContext.Provider>
  );
}
```

```javascript

import { useCallback, useMemo } from 'react';

function MyApp() {
  const [currentUser, setCurrentUser] = useState(null);

  const login = useCallback((response) => {
    storeCredentials(response.credentials);
    setCurrentUser(response.user);
  }, []);

  const contextValue = useMemo(() => ({
    currentUser,
    login
  }), [currentUser, login]);

  return (
    <AuthContext.Provider value={contextValue}>
      <Page />
    </AuthContext.Provider>
  );
}

```

## `useEffect`

Reference : https://react.dev/reference/react/useEffect

### Usage : `useEffect(setup, dependencies?)`

```javascript
import { useEffect } from 'react';
import { createConnection } from './chat.js';

function ChatRoom({ roomId }) {
  const [serverUrl, setServerUrl] = useState('https://localhost:1234');

  useEffect(() => {
    const connection = createConnection(serverUrl, roomId);
    connection.connect();
    return () => {
      connection.disconnect();
    };
  }, [serverUrl, roomId]);
  // ...
}
```

Paramètres :

- `setUp` : la fonction qui comporte la logique de l'effet ; elle peut retourner une fonction de cleanup (facultatif). Lorsque un composant est ajouté pour la première fois au DOM, React exécute la fonction de setup ; ensuite, à chaque rendu, si les dépendances (second paramètre) changent, React exécutera la fonction de clean up avec les anciennes valeurs puis la fonction de setup avec les nouvelles valeurs. Lorsque le composant est retiré du DOM, React exécute la fonction de cleanup une dernière fois.

- `dependencies` (facultatif) : la liste des valeurs réactives référencées dans le code de `setup` ; ces valeurs peuvent être des props, des valeurs d'état et toutes les variables et fonctions déclarées directement dans le corps du composant React. React vérifiera chaque dépendance avec sa valeur précédente (comparaison `Object.is`) : si cet argument est omis, l'effet sera exécuté à chaque fois que le composant est rendu (re-render).

Limites, conseils :

- ne pas l'appeler à l'intérieur d'une boucle ou d'une condition
- si votre composant ne nécessite pas de synchronisation avec un quelconque système externe, c'est probablement que `useEffect` n'est pas utile dans ce cas
- si les dépendances sont des objets ou des fonctions, il y a un risque que le hook se lance plus souvent que nécessaire => ne pas passer en dépendance les fonctions et objets qui ne sont pas utiles ; sinon, déplacer hors du hook les mises à jour d'état et la logique non-reactive
- si le hook n'est pas déclenché par une interaction (clic par ex), React laissera le navigateur actualiser le rendu avant l'exécution du hook. Si le hook a un impact visuel (tooltip par ex) et que le délai est conséquent (scintillement/flicker), utiliser plutôt `useLayoutEffect`
- même si le hook est déclenché par une interaction, il est possible que le navigateur réactualise le rendu avant que les changements d'état à l'intérieur du hook soient effectifs : c'est généralement le comportement attendu, mais si vous souhaitez bloquer le rafraichissement du rendu, il faut utiliser `useLayoutEffect`
- ce hook ne s'exécute que côté client et pas durant un rendu servi côté serveur
- passer un tableau vide `[]` en dépendance si le hook n'a pas de dépendance pour éviter que le hook ne soit exécuté à chaque ré-affichage

### Exemples

Composants qui doivent rester connectés au réseau, à une API navigateur, une librairie tierce, alors qu'ils sont déjà affichés. Ces systèmes ne sont pas du ressort de React (c'est pourquoi ils sont dits "externes"). Exemples :

- un timer géré par les fonctions `setInterval` et `clearInterval`
- une souscription d'événements (`window.addEventListener` et `window.removeListener`)
- une librairie tierce d'animation avec une API comme `animation.start()` et `animation.reset()`

- connexion à un serveur de chat
- écoute d'événements navigateur globaux
- déclenchement d'une animation
- contrôle d'une modale de dialogue
- surveiller la visibilité d'un élément

=> cf. la documentation React pour avoir des exemples d'implémentation

React appelle la fonction setup et la fonction de cleanup à chaque fois que nécessaire et cela peut donc se produire plusieurs fois :

1. le code de `setUp` s'exécute lorsque le composant est ajouté à la page (mounted)
2. Après chaque rendu lorsque les dépendances ont changé (cleanup puis setup)
3. Le code de cleanup s'exécute une dernière fois au démontage

:bulb: Si vous écrivez souvent des hooks effect, c'est peut-être le signe qu'il faut extraire certains hooks personnalisés si des comportements sont communs à plusieurs composants. Cf. https://react.dev/learn/escape-hatches

### Récupérer des données avec les hooks Effects

```javascript

import { useState, useEffect } from 'react';
import { fetchBio } from './api.js';

export default function Page() {
  const [person, setPerson] = useState('Alice');
  const [bio, setBio] = useState(null);

  useEffect(() => {
    let ignore = false;
    setBio(null);
    fetchBio(person).then(result => {
      if (!ignore) {
        setBio(result);
      }
    });
    return () => {
      ignore = true;
    };
  }, [person]);

  // ...
```

:bulb: La variable `ignore` sert à s'assurer que le code ne souffre pas des "race conditions" (les réponses réseau peuvent arriver dans un ordre différent de celui des requêtes). 

:bulb: l'exemple ci-dessus peut être réécrit en utilisant la syntaxe `async / await`, mais il faut toujours fournir la fonction de cleanup : 

```diff

import { useState, useEffect } from 'react';
import { fetchBio } from './api.js';

export default function Page() {
  const [person, setPerson] = useState('Alice');
  const [bio, setBio] = useState(null);
  useEffect(() => {
    async function startFetching() {
      setBio(null);
      const result = await fetchBio(person);
      if (!ignore) {
        setBio(result);
      }
    }

    let ignore = false;
    startFetching();
    return () => {
      ignore = true;
    }
  }, [person]);

  return (
    <>
      <select value={person} onChange={e => {
        setPerson(e.target.value);
      }}>
        <option value="Alice">Alice</option>
        <option value="Bob">Bob</option>
        <option value="Taylor">Taylor</option>
      </select>
      <hr />
      <p><i>{bio ?? 'Loading...'}</i></p>
    </>
  );
}

```

:warning: On ne peut pas choisir les dépendances passées à un hook Effect : toutes les valeurs réactives utilisées dans le code du hook doivent être déclarées en dépendance. La liste des dépendances est déterminée par le code environnant : 

```diff
function ChatRoom({ roomId }) { // This is a reactive value
  const [serverUrl, setServerUrl] = useState('https://localhost:1234'); // serverUrl is a reactive value too

  useEffect(() => {
    const connection = createConnection(serverUrl, roomId); // This Effect reads these reactive values
    connection.connect();
    return () => connection.disconnect();
  }, [serverUrl, roomId]); // ✅ So you must specify them as dependencies of your Effect
  // ...
}
```

## `useLayoutEffect`

:warning: ce hook peut avoir un gros impact sur les performances => préférez `useEffect` lorsque c'est possible.

Hook similaire à `useEffect` mais déclenché avant que le navigateur ne rafraichisse la vue

Usage : `useLayoutEffect(setup, dependencies?)`

La fonction `setUp` est exécutée par React avant que le composant ne soit ajouté au DOM.

Mêmes limites que `useEffect`. En particulier, si les dépendances comportent des fonctions ou des objets définis à l'intérieur du composant, il y a un risque d'exécuter le hook plus souvent que nécessaire. Pour y remédier, retirer des dépendances toute fonction et tout objet non nécessaires, et également déplacer les mises à jours d'état et la logique non-réactive en dehors du hook.

Le code du hook et toutes les mises à jour d'état planifiés par le hook bloquent le rafraichissement de la vue par le navigateur (ce qui peut rendre l'application très lente si on abuse de ce hook). Lorsque c'est possible, privilégier le hook `useEffect`.

### Usage

Mesurer le layout avant que le navigateur rafraîchisse l'écran : la plupart des composants n'ont pas besoin de connaître leur taille et leur position pour être rendus (ils retournent juste du JSX). Puis le navigateur calcule leur disposition (taille et position) et rafraichit la page.

Mais parfois cela ne suffit pas. Exemple : un tooltip qui doit tenir compte de la place disponible en haut et en bas pour s'afficher correctement. Pour cela, il faut connaître sa hauteur ...

Il faut donc :

- rendre le tooltip n'importe où (même à une position incorrecte)
- mesurer la hauteur et décider s'il se positionnera en haut ou en bas
- rendre à nouveau le tooltip au bon endroit

Tout cela, avant que le navigateur ne rafraichisse la vue (pour ne pas que l'utilisateur voie le tooltip bouger)

```javascript
function Tooltip() {
  const ref = useRef(null);
  const [tooltipHeight, setTooltipHeight] = useState(0); // You don't know real height yet

  useLayoutEffect(() => {
    const { height } = ref.current.getBoundingClientRect();
    setTooltipHeight(height); // Re-render now that you know the real height
  }, []);

  // ...use tooltipHeight in the rendering logic below...
}
```

:bulb: utiliser `useLayoutEffect` lorsqu'on souhaite bloquer le rafraichissement de la vue par le navigateur, sinon utiliser `useEffect`

## `useMemo`

Hook utilisé pour mettre en cache des résultats de calculs entre plusieurs rendus.

Usage : `const cachedValue = useMemo(calculateValue, dependencies)`

```javascript

import { useMemo } from 'react';

function TodoList({ todos, tab }) {
  const visibleTodos = useMemo(
    () => filterTodos(todos, tab),
    [todos, tab]
  );
  // ...
}
```

:bulb: par défaut, quand le rendu d'un composant est actualisé, React ré-actualise le rendu de tous ses enfants de manière récursive, d'où l'intérêt de mettre en cache le résultat de calculs coûteux.

## `useCallback`

Rappel : ce hook permet de mettre en cache une définition de fonction entre plusieurs rendus.

Usage : `const cachedFn = useCallback(fn, dependencies)`

### Utilité

Voici un exemple où un composant `ProductPage` passe en tant que propriété (`prop`) une fonction `handleSubmit` à son composant enfant `ShippingForm` :

```javascript
function ProductPage({ productId, referrer, theme }) {
  // ...
  return (
    <div className={theme}>
      <ShippingForm onSubmit={handleSubmit} />
    </div>
  );
```

On remarque lorsque l'on change la propriété `theme`, que l'application se fige (freeze) pendant un moment mais fonctionne correctement dès que l'on retire le composant `<ShippingForm>` : c'est un signe que le composant `ShippingForm` doit être optimisé.

Par défaut, quand un composant est rendu, React rends aussi tous ses enfants de manière récursive. Ca ne pose pas de souci si les composants ne prévoient pas de calculs lourds, mais si l'on constate un ralentissement du rendu, cela vaut le coup de demander au composant `ShippingForm` de ne pas rafraichir son rendu lorsque ses propriétés restent inchangées, grace au hook `memo` :

```javascript
import { memo } from 'react';

const ShippingForm = memo(function ShippingForm({ onSubmit }) {
  // ...
});
```

Grâce à ce changement, `ShippingForm` ne modifiera pas son rendu si toutes ses propriétés restent inchangées depuis le dernier rendu. Et c'est là que c'est important de mettre en cache une fonction ! Admettons que l'on ait défini la fonction `handleSubmit` sans le hook `useCallback` :

```javascript
function ProductPage({ productId, referrer, theme }) {
  // Every time the theme changes, this will be a different function...
  function handleSubmit(orderDetails) {
    post('/product/' + productId + '/buy', {
      referrer,
      orderDetails,
    });
  }

  return (
    <div className={theme}>
      {/* ... so ShippingForm's props will never be the same, and it will re-render every time */}
      <ShippingForm onSubmit={handleSubmit} />
    </div>
  );
}
```

En Javascript, `function() {}` ou `() => {}` crée toujours une nouvelle fonction (donc différente), ce qui n'est pas un problème la plupart du temps ; mais dans notre cas, cela signifie que les propriétés de `ShippingForm` ne seront jamais les mêmes et notre optimisation ci-dessus avec le hook `memo` ne fonctionnera pas. Ici, il faudra utiliser le hook `useCallback` :

```javascript
function ProductPage({ productId, referrer, theme }) {
  // Tell React to cache your function between re-renders...
  const handleSubmit = useCallback((orderDetails) => {
    post('/product/' + productId + '/buy', {
      referrer,
      orderDetails,
    });
  }, [productId, referrer]); // ...so as long as these dependencies don't change...

  return (
    <div className={theme}>
      {/* ...ShippingForm will receive the same props and can skip re-rendering */}
      <ShippingForm onSubmit={handleSubmit} />
    </div>
  );
}
```

On s'assure ainsi qu'entre plusieurs rendus, c'est toujours la même fonction qui est passé en propriété (en tout cas, tant que les dépendances ne changent pas). Mais il n'est pas nécessaire de le faire si cela ne se justifie pas. Dans notre exemple, c'est utile parce que l'on passe cette fonction à un composant encapsulé dans un `memo` et cela permet donc de ne pas déclencher un nouveau rendu injustifié. Noter que le hook `useCallback` peut être utile dans d'autres situations (voir ci-dessous).

En résumé : on n'utilise le hook `useCallback` que pour des questions d'optimisation. Si votre code ne fonctionne pas sans ce hook, c'est qu'il y a un autre souci ...

### Mise à jour du state à partir d'un memoized callback

Parfois, on souhaite mettre à jour un état par rapport au dernier état obtenu d'un memoized callback. On a par exemple une fonction `handleAddTodo` qui calcule le prochain état des todos à partir de son état actuel :

```
function TodoList() {
  const [todos, setTodos] = useState([]);

  const handleAddTodo = useCallback((text) => {
    const newTodo = { id: nextId++, text };
    setTodos([...todos, newTodo]);
  }, [todos]);
  // ...
```

En général, on souhaite limiter au maximum le nombre de dépendances des fonctions mises en memo (memoized). Quand on lit un état pour en déduire le prochain, mieux vaut passer par une fonction de mise à jour (updater function) :

```javascript
function TodoList() {
  const [todos, setTodos] = useState([]);

  const handleAddTodo = useCallback((text) => {
    const newTodo = { id: nextId++, text };
    setTodos(todos => [...todos, newTodo]);
  }, []); // ✅ No need for the todos dependency
  // ...
```

:bulb: En savoir plus sur les fonctions de mise à jour (`updater functions`) : https://react.dev/reference/react/useState#updating-state-based-on-the-previous-state

### Empêcher le déclenchement trop fréquent d'un Effect

Parfois on souhaite appeler une fonction depuis un Effect :

```javascript
function ChatRoom({ roomId }) {
  const [message, setMessage] = useState('');

  function createOptions() {
    return {
      serverUrl: 'https://localhost:1234',
      roomId: roomId
    };
  }

  useEffect(() => {
    const options = createOptions();
    const connection = createConnection();
    connection.connect();
    // ...
```

Il y a un souci ici : chaque valeur réactive doit être déclarée en tant que dépendance de l'effet (Effect). Mais si l'on déclare `createOptions` comme dépendance, votre Effect se reconnectera au chat room en permanence.

```javascript
useEffect(() => {
    const options = createOptions();
    const connection = createConnection();
    connection.connect();
    return () => connection.disconnect();
  }, [createOptions]); // 🔴 Problem: This dependency changes on every render
  // ...
```

Solution : encapsuler la fonction que l'on doit appeler depuis un Effect dans un hook `useCallback` :

```javascript
function ChatRoom({ roomId }) {
  const [message, setMessage] = useState('');

  const createOptions = useCallback(() => {
    return {
      serverUrl: 'https://localhost:1234',
      roomId: roomId
    };
  }, [roomId]); // ✅ Only changes when roomId changes

  useEffect(() => {
    const options = createOptions();
    const connection = createConnection();
    connection.connect();
    return () => connection.disconnect();
  }, [createOptions]); // ✅ Only changes when createOptions changes
  // ...
```

Cela garantit que la fonction `createOptions` est toujours la même entre plusieurs rendus, aussi longtemps que `roomId` reste inchangé. Mais le mieux est encore de supprimer la dépendance à une fonction, en déplaçant la fonction dans l'effet (Effect) :

```javascript
function ChatRoom({ roomId }) {
  const [message, setMessage] = useState('');

  useEffect(() => {
    function createOptions() { // ✅ No need for useCallback or function dependencies!
      return {
        serverUrl: 'https://localhost:1234',
        roomId: roomId
      };
    }

    const options = createOptions();
    const connection = createConnection();
    connection.connect();
    return () => connection.disconnect();
  }, [roomId]); // ✅ Only changes when roomId changes
  // ...
```

:bulb: En savoir plus sur la suppression des dépendances des hooks `useEffect` : https://react.dev/learn/removing-effect-dependencies#move-dynamic-objects-and-functions-inside-your-effect

### Optimisation d'un hook custom

:bulb: [Reusing Logic with Custom Hooks](https://react.dev/learn/reusing-logic-with-custom-hooks)

Si vous écrivez vos propres hooks, il est recommandé d'encapsuler toutes les fonctions qu'il retourne dans un hook `useCallback`

```javascript
function useRouter() {
  const { dispatch } = useContext(RouterStateContext);

  const navigate = useCallback((url) => {
    dispatch({ type: 'navigate', url });
  }, [dispatch]);

  const goBack = useCallback(() => {
    dispatch({ type: 'back' });
  }, [dispatch]);

  return {
    navigate,
    goBack,
  };
}
```

Cela permet aux utilisateurs de votre hook d'optimiser leur propre code si besoin.

---

Date de création : 28 avril 2023<br/>
Dernière édition : 28 avril 2023<br/>
Version de référence : React 18.2
