# Notifier – Intégration & fonctionnement

Ce bundle fournit une **infrastructure générique de notifications** permettant :

* de déclarer des notifications via un **Attribute PHP**
* de **scanner automatiquement** toutes les notifications disponibles
* de **déclencher une notification manuellement** (ex : interface d’admin / tests)
* de **résoudre automatiquement les arguments** attendus par chaque notification

👉 Le bundle fournit l’infrastructure.
👉 **L’application fournit les notifications concrètes et les données.**

---

## 1. Principe général

Une notification est :

* une **méthode publique**
* décorée avec l’attribut `#[Notifier]`
* définie dans un service Symfony

Exemple :

```php
#[Notifier(
    name: 'notifier.event.reminder_manager.name',
    description: 'notifier.event.reminder_manager.description',
    : ['event', 'log']
)]
public function notifyEventReminderManager(Event $event, $log): void
{
    // logique d'envoi
}
```

Chaque notification peut avoir **des paramètres différents**.
Le bundle s’appuie sur des **ArgumentResolvers** pour fournir automatiquement des valeurs de test.

---

## 2. Ce que fournit le bundle

Le bundle expose :

* `Notifier` (Attribute)
* `NotifierScanner`
* `NotifierExecutor`
* `NotifierArgumentResolverInterface`

Ces services sont **déjà configurés dans le bundle**.

⚠️ Le bundle **ne contient aucune notification métier**.

---

## 3. Configuration côté application (obligatoire)

### 3.1 Déclarer les services de notification

Dans l’application, les services contenant des notifications doivent être **tagués** :

```yaml
# config/services.yaml
services:
  App\Service\Notifier\:
    resource: '../src/Service/Notifier'
    tags: ['app.notifier']
    autowire: true
    autoconfigure: true
```

Chaque classe de ce dossier peut contenir plusieurs méthodes `#[Notifier]`.

---

### 3.2 Déclarer les ArgumentResolvers

Chaque paramètre attendu par une notification doit être résolu via un resolver.

```yaml
services:
  App\Service\Notifier\ArgumentResolver\:
    resource: '../src/Service/Notifier/ArgumentResolver'
    tags: ['app.notifier_argument_resolver']
    autowire: true
    autoconfigure: true
```

---

## 4. Créer un ArgumentResolver

Chaque resolver :

* implémente `NotifierArgumentResolverInterface`
* indique s’il supporte un argument
* retourne une valeur (souvent aléatoire) depuis la base de données

### Exemple : UserAccessRightResolver

```php
class UserAccessRightResolver implements NotifierArgumentResolverInterface
{
    public function supports(\ReflectionParameter $parameter): bool
    {
        return $parameter->getType()?->getName() === UserAccessRight::class;
    }

    public function resolve(): mixed
    {
        return $this->repository->findOneBy([], ['id' => 'ASC']);
    }
}
```

👉 Le resolver est automatiquement utilisé dès qu’un paramètre correspond.

---

## 5. Scanner les notifications

Le service `NotifierScanner` permet de récupérer la liste complète des notifications disponibles.

```php
$scanner->getNotifiers();
```

Retourne par exemple :

```php
[
  'class' => 'App\\Service\\Notifier\\EventNotifier',
  'method' => 'notifyEventReminderManager',
  'name' => 'notifier.event.reminder_manager.name',
  'description' => 'notifier.event.reminder_manager.description',
]
```

Utile pour :

* une interface d’administration
* un écran de test
* une commande CLI

---

## 6. Exécuter une notification de test

Le service `NotifierExecutor` :

* instancie le bon notifier
* résout les arguments
* appelle la méthode

```php
$executor->execute(
    'App\\Service\\Notifier\\EventNotifier',
    'notifyEventReminderManager'
);
```

Les paramètres sont automatiquement injectés.

---

## 7. Cycle complet

1. L’application déclare un notifier (`#[Notifier]`)
2. Le scanner le détecte
3. L’UI affiche un bouton "Tester"
4. L’executor est appelé
5. Les resolvers fournissent les données
6. La notification est envoyée

---

## 8. Bonnes pratiques

* Une notification = **une responsabilité claire**
* Préférer des resolvers simples et déterministes
* Éviter la logique métier dans les resolvers
* Les notifiers doivent rester **stateless**

---

## 9. Résumé

| Élément           | Bundle | Application |
| ----------------- | ------ | ----------- |
| Infrastructure    | ✅      | ❌           |
| Notifiers         | ❌      | ✅           |
| ArgumentResolvers | ❌      | ✅           |
| UI / CLI          | ❌      | ✅           |

---

Pour toute question ou extension (CLI, UI, permissions, filtres…), le système est conçu pour être extensible.
