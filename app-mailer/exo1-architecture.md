# TP 2.3 - Exercice 1 : Architecture

## 1. Schéma des services

```
app.rdv (producteur)
    ↓
    │ publie événements
    ↓
RabbitMQ (courtier)
    - Exchange: toubilib.events (TOPIC)
    - Queue: email.notifications
    ↓
    │ consomme messages
    ↓
app.mailer (consommateur)
```

**Services :**
- **Producteur fonctionnel** : app.rdv détecte création/annulation RDV
- **Consommateur fonctionnel** : app.mailer envoie emails
- **Courtier technique** : RabbitMQ route les messages

## 2. Configuration RabbitMQ

**Exchange :**
- Nom : `toubilib.events`
- Type : **TOPIC** (permet routage flexible avec patterns)
- Durable : true

**Queues :**
- `email.notifications` (actuelle)
- `sms.notifications` (future)
- `push.notifications` (future)

**Routing keys :**
- `rdv.created.patient`
- `rdv.created.praticien`
- `rdv.cancelled.patient`
- `rdv.cancelled.praticien`

**Bindings :**
- Queue `email.notifications` ← binding `rdv.#` (tous événements RDV)

## 3. Producteur d'événements (app.rdv)

**Composant responsable** : `ServiceRDV` (use case)

**Mécanisme :**
- Après `creerRendezVous()` ou `annulerRendezVous()`, appel à un publisher
- Injection via une interface : `EventPublisherInterface`

**Architecture hexagonale pour flexibilité :**

```
ServiceRDV → EventPublisherInterface (port)
                      ↑
                      │ implémente
                      │
              AMQPEventPublisher (adapter)
```

**Pour changer de protocole :**
- Créer nouveau adapter (ex: `KafkaEventPublisher`)
- Changer config DI
- ServiceRDV non impacté (dépend de l'interface)

**Format message JSON :**
```json
{
  "eventType": "rdv.created.patient",
  "rdvId": "uuid",
  "recipient": {"type": "patient", "email": "...", "nom": "..."},
  "data": {"dateHeureDebut": "...", "praticien": {...}}
}
```
