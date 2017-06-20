# Les événements de Doctrine

Référence : [http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/events.html](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/events.html)

* [Généralités](#general)
* [Lifecycle events](#lifecycle-events)
* [Lifecycle callbacks](#lifecylce-callbacks)
* [Réagir à des _lifecycle events_](#listen-subscribe)
* [Implémenter des _event listeners_](#listener-impl)
* [Les _Entity listeners_](#entity-listeners)
* [L'événement `Load ClassMetadata`](#load-class-metadata)

## <a name="general"></a> Généralités

Le système d'événement de Doctrine s'articule autour de la classe `EventManager`, qui possède les méthodes `addEventListener`, `removeEventListener` et `dispatchEvent`.

Doctrine expose également un "Event Subscriber" (`Doctrine\Common\EventSubscriber`) qui peut être étendu.

## <a name="lifecycle-events"></a> Lifecycle events

Evénements levés par l'EntityManager et le UnitOfWork de Doctrine : 

* preRemove
* postRemove
* prePersist
* postPersist
* preUpdate
* postUpdate
* postLoad
* loadClassMetadata (levé lorsque les méta-données de mapping d'une entité ont été chargées depuis un fichier de mapping, annotations/yml/xml)
* onClassMetadataNotFound
* preFlush (levé au tout début d'un flush)
* onFlush (levé après que tous les changements d'une entité ont été calculés)
* postFlush
* onClear (lorsque la méthode `EntityManager#clear()` est invoquée)

> :warning: Lorsque l'on exécute la méthode `Doctrine\ORM\AbstractQuery#iterate()`, les événements `postLoad` sont exécutés aussitôt les objets hydratés et il est donc possible que les associations ne soient pas encore initialisées. Il n'est pas recommandé d'utiliser conjointement la méthode `#iterate()` et les _handlers_ d'événement `postLoad`.

> :warning: l'événement `postRemove` et tout événement déclenché après une suppression d'entité peuvent recevoir un _proxy_ non initialisable si une entité est configurée avec des suppressions en cascade. Dans ce cas, il vaut mieux charger soi-même le _proxy_ dans le pré-événement correspondant.

> :warning: Tous les événements de cycle de vie déclenchés au `flush()` de l'EntityManager ont des contraintes spécifiques et notamment certaines opérations sont fortement déconseillées (voir plus bas).

## <a name="lifecylce-callbacks"></a> Lifecycle callbacks

Les _lifecycle callbacks_ sont définis sur les entités. Ils permettent de déclencher des _callbacks_ lors du cycle de vie des entités. Exemples de _callbacks_ avec une config. par annotation : @PrePersist, @PostPersist, @PostLoad, @PreUpdate. Pour que ces _callbacks_ soient activées, l'entité doit être marquée avec l'annotation `@HasLifecycleCallbacks`.

Exemple :

```php
    <?php

    /** @Entity @HasLifecycleCallbacks */
    class User
    {
        /** @PrePersist */
        public function doOtherStuffOnPrePersist()
        {
            $this->value = 'changed from prePersist callback!';
        }
    }

```

Depuis la version 2.4 de Doctrine, l'événement levé est passé au _lifecycle callback_, ce qui permet d'accéder à l'EntityManager et à l'UnitOfWork.

```php
    <?php

    class User
    {
        public function preUpdate(PreUpdateEventArgs $event)
        {
            if ($event->hasChangedField('username')) {
                // Do something when the username is changed.
            }
        }
    }
```

## <a name="listen-subscribe"></a> Réagir à des _lifecycle events_ (_Listen_ and _subscribe_)

Les _lifecycle event listeners_ sont plus puissants que les _lifecycle fallbacks_ définis sur les entités. En effet, ces _listeners_ se situent à un niveau supérieur à celui de l'entité et permettent d'implémenter des comportements réutilisables pour plusieurs entités.

Exemple de _listener_ :

```php

    <?php
    use Doctrine\ORM\Events;
    use Doctrine\Common\EventSubscriber;
    use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

    class MyEventSubscriber implements EventSubscriber
    {
        public function getSubscribedEvents()
        {
            return array(
                Events::postUpdate,
            );
        }

        public function postUpdate(LifecycleEventArgs $args)
        {
            $entity = $args->getObject();
            $entityManager = $args->getObjectManager();

            // perhaps you only want to act on some "Product" entity
            if ($entity instanceof Product) {
                // do something with the Product
            }
        }
```

> Les lifecycle events sont déclenchés pour toutes les entités. Les _listeners_ et les _subscribers_ doivent donc s'assurer que l'entité est d'un type qu'ils gèrent.

## <a name="listener-impl"></a> Implémenter des _event listeners_

Cette section présente les actions autorisées et celles interdites en fonction des événements de l'UnitOfWork. L'EntityManager est disponible dans chacun de ces événements, mais cela ne signifie pas pour autant qu'il n'existe aucune restriction sur les actions possibles.

* [L'événement `prePersist`](#pre-persist)
* [L'événement `preRemove`](#pre-remove)
* [L'événement `preFlush`](#pre-flush)
* [L'événement `onFlush`](#on-flush)
* [L'événement `postFlush`](#post-flush)
* [L'événement `preUpdate`](#pre-update)
* [Les événements `postUpdate`, `postRemove` et `postPersist`](#post-events)
* [L'événement `postLoad`](#post-load)

### <a name="pre-persist"></a> L'événement `prePersist`

L'événement `prePersist` est levé de deux manières : 

* lors de l'invocation de la méthode `EntityManager#persist()`
* au `flush` lorsqu'une entité _flushée_ possède des associations persistées en cascade (_cascade persist_)

L'événement possède un argument `LifecycleEventArgs` qui permet d'accéder à l'entité et à l'`EntityManager`.

**Restrictions** :

- Si vous utilisez une séquence pour générer l'ID, celui-ci n'est pas encore disponible
- Doctrine ne reconnaît pas les changements faits sur les relations (modifications de collections telles que les ajouts, les suppressions, les remplacements)

### <a name="pre-remove"></a> L'événement `preRemove`

Levé lorsque la méthode `EntityManager#remove()` est invoquée pour une entité. levé également en cascade lorsque les associations sont marquées en tant que `cascade delete`.

Pas de restriction particulière, hormis lorsque la méthode `remove` est appelée durant une opération de `flush`.

### <a name="pre-flush"></a> L'événement `preFlush`

Evénement levé dès que la méthode `EntityManager#flush()` est exécutée. Pas de restriction.

### <a name="on-flush"></a> L'événement `onFlush`

Evénement levé lors de la méthode `EntityManager#flush()`, après que toutes les entités managées et leurs associations ont été calculées. L'événement a donc accès à toutes les entités planifiées pour une insertion, une mise à jour ou une suppression, ainsi qu'à toutes les collections planifiées pour une mise à jour ou une suppression. Pour gérer cet événement, il faut connaître l'API de l'UnitOfWork, qui donne accès aux changements énumérés ci-dessus :

```php
    <?php
    public function onFlush(OnFlushEventArgs $args) {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) { }
        foreach ($uow->getScheduledEntityUpdates() as $entity) { }
        foreach ($uow->getScheduledEntityDeletions() as $entity) { }
        foreach ($uow->getScheduledCollectionDeletions() as $col) { }
        foreach ($uow->getScheduledCollectionUpdates() as $col) { }
    }
```

**Restrictions** :

* si l'on crée et persiste une nouvelle entité durant l'événement `onFlush`, appeler la méthode `EntityManager#persist()` ne suffit pas ; il faut également appeler la méthode `$unitOfWork->computeChangeSet($classMetadata, $entity)`
* modifier des champs natifs de l'entité ou ses associations nécessite de déclencher explicitement un re-calcul des modifications de l'entité concernée : `$unitOfWork->recomputeSingleEntityChangeSet($classMetadata, $entity)`

### <a name="post-flush"></a> L'événement `postFlush`

Evénement appelé à la fin de `EntityManager#flush()`.

**Restriction** : Appeler `EntityManager#flush` dans un `postFlush _listener_` est fortement déconseillé.

### <a name="pre-update"></a> L'événement `preUpdate`

`PreUpdate` est l'événement le plus restrictif car il est appelé juste avant l'instruction d'`update` d'une entité dans la méthode `EntityManager#flush()`. Noter que cet événement n'est pas levé pour les entités dont les modifications sont vides (_computed changeset_).

**Les modifications apportées aux associations sont prohibées** (Doctrine ne peut garantir l'intégrité référentielle à ce stade du _flush_). 

Malgré ses restrictions, cet événement possède une fonctionnalité très puissante. En effet, il possède un argument `PreUpdateEventArgs` qui permet d'accéder à tous les changements de l'entité (_computed changeset_).

Méthodes de l'objet `PreUpdateEventArgs`: `getEntity()`, `getEntityChangeSet()`, `hasChangedField($fieldName)`, `getOldValue($fieldName)`, `getNewValue($fieldName)` et `setNewValue($fieldName, $value)`.

Exemple :

```php
    <?php
    class NeverAliceOnlyBobListener
    {
        public function preUpdate(PreUpdateEventArgs $eventArgs)
        {
            if ($eventArgs->getEntity() instanceof User) {
                if ($eventArgs->hasChangedField('name') && $eventArgs->getNewValue('name') == 'Alice') {
                    $eventArgs->setNewValue('name', 'Bob');
                }
            }
        }
    }
```

Cet événement peut aussi être utilisé pour implémenter de la logique de validation des changements (c'est par ailleurs plus efficace que dans un _lifecycle callback_ contenant des validations coûteuses) :

```php
    <?php
    class ValidCreditCardListener
    {
        public function preUpdate(PreUpdateEventArgs $eventArgs)
        {
            if ($eventArgs->getEntity() instanceof Account) {
                if ($eventArgs->hasChangedField('creditCard')) {
                    $this->validateCreditCard($eventArgs->getNewValue('creditCard'));
                }
            }
        }

        private function validateCreditCard($no)
        {
            // throw an exception to interrupt flush event. Transaction will be rolled back.
        }
    }
```

**Restrictions de l'événement `preUpdate` :**

* les changements sur les associations de l'entité ne sont plus reconnus par les opérations de _flush_
* les changeents sur les champs de l'entité non plus ; pour modifier des champs natifs de l'entité, passer par le _computed changeset_, *ie* utiliser la méthode `$args->setNewValue($field, $value);` (cf. exemple de Bob & Alice ci-dessous)
* bannir tous les appels à `EntityManager#persist()` ou `EntitiyManager#remove()`, même en passant par l'API de l'UnitOfWork.

### <a name="post-events"></a> Les événements `postUpdate`, `postRemove` et `postPersist`

Ces trois événements sont déclenchés dans la méthode `EntityManager#flush()`. Les changements effectués ici ne sont pas liés à la persistence en base, mais ces événements peuvent être utilisés pour modifier des éléments non persistés, tels que des champs non mappés, des logs ou même des classes associées pas directement mappées à Doctrine.

### <a name="post-load"></a> L'événement `postLoad`

Cet événenement est appelé après qu'une entité a été instanciée par l'EntityManager.

## <a name="entity-listeners"></a> Les _Entity listeners_ (Doctrine version >= 2.4)

Un _entity listener_ est un _listener_ sur le cycle de vie d'une entité donnée.

Le mapping d'un _entity listener_ peut s'appliquer à une classe d'entité ou à une _mapped superclass_. Pour définir un _entity listener_, il faut mapper la classe d'entité au mapping correspondant. Exemple : 

```php
    <?php
    /** @Entity @EntityListeners({"UserListener"}) */
    class User
    {
        // ....
    }
```

Un _entity listener_ peut être n'importe quelle classe. Par défaut, elle ne devrait pas avoir d'argument dans son constructeur.

Contrairement aux _event listeners_, les _entity listeners_ ne s'appliquent qu'à une entité spécifique.

Une méthode d'_event listener_ reçoit deux arguments : l'entité et le _lifecycle event_ (`preUpdate(User $user, PreUpdateEventArgs $args)`).

La méthode de callback peut être définie par convention ou en spécifiant un mapping de méthode (si aucun mapping n'existe, le parseur cherchera une méthode correspondant à la convention, la méthode `preUpdate()` par exemple) ; si un mapping existe, le parseur ne cherchera pas de méthode basée sur la convention.

Exemple de _Listener_ qui utilise un mapping (basé sur les annotations) et non les conventions :

```php
    <?php
    class UserListener
    {
        /** @PrePersist */
        public function prePersistHandler(User $user, LifecycleEventArgs $event) { // ... }

        /** @PostPersist */
        public function postPersistHandler(User $user, LifecycleEventArgs $event) { // ... }

```

> :warning: L'ordre d'exécution de méthodes multiples pour un même événement n'est pas garanti.

Doctrine s'appuie sur un _listener resolver_ pour obtenir une instance de _listener_. Un _resolver_ permet d'obtenir une instance de _listener_ ; il est possible d'implémenter son propre _resolver_ en étendant la classe `Doctrine\ORM\Mapping\DefaultEntityListenerResolver` ou en implémentant `Doctrine\ORM\Mapping\EntityListenerResolver`.

Exemple :

```php
    <?php
    // User.php

    /** @Entity @EntityListeners({"UserListener"}) */
    class User
    {
        // ....
    }

    // UserListener.php
    class UserListener
    {
        public function __construct(MyService $service)
        {
            $this->service = $service;
        }

        public function preUpdate(User $user, PreUpdateEventArgs $event)
        {
            $this->service->doSomething($user);
        }
    }

    // register a entity listener.
    $listener = $container->get('user_listener');
    $em->getConfiguration()->getEntityListenerResolver()->register($listener);

```

## <a name="load-class-metadata"></a> L'événement `Load ClassMetadata`

Evénement levé lorsque le schéma de mapping d'une entité est lu. Il est alors possible de se brancher (_hook_) sur le process pour manipuler l'instance : 

```php
    <?php
    $test = new TestEvent();
    $metadataFactory = $em->getMetadataFactory();
    $evm = $em->getEventManager();
    $evm->addEventListener(Events::loadClassMetadata, $test);

    class TestEvent
    {
        public function loadClassMetadata(\Doctrine\ORM\Event\LoadClassMetadataEventArgs $eventArgs)
        {
            $classMetadata = $eventArgs->getClassMetadata();
            $fieldMapping = array(
                'fieldName' => 'about',
                'type' => 'string',
                'length' => 255
            );
            $classMetadata->mapField($fieldMapping);
        }
    }
```
