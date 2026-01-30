# Toubilib - Backend Microservices

API RESTful pour gestion de rendez-vous médicaux. Architecture microservices avec PHP/Slim.

## Installation

### Prérequis

- Docker & Docker Compose
- Git

### Lancer le projet

```bash
git clone https://github.com/Raouf-blip/Toubilib
cd Toubilib

# Démarrer tous les services
docker-compose up -d

# Configurer RabbitMQ (une seule fois)
bash app-mailer/scripts/setup-rabbitmq.sh
```

### Vérification

```bash
# Tester l'API Gateway
curl http://localhost:6081/praticiens
```

## Services

| Service        | Port  | Description                   |
| -------------- | ----- | ----------------------------- |
| API Gateway    | 6081  | Point d'entrée unique        |
| app.praticiens | 6082  | Microservice praticiens       |
| app.rdv        | 6083  | Microservice RDV              |
| app.auth       | -     | Authentification JWT          |
| app.mailer     | -     | Envoi emails (RabbitMQ)       |
| RabbitMQ       | 15672 | Interface admin (guest/guest) |
| MailCatcher    | 1080  | Emails de test                |
| Adminer        | 8080  | Gestion bases de données     |

## Comptes de test

**Patient :**

- Email : `Denis.Teixeira@hotmail.fr`
- Mot de passe : `test`

**Praticien :**

- Email : `dith.Didier@club-internet.fr`
- Mot de passe : `test`

## Exemple d'utilisation

```bash
# 1. Login
TOKEN=$(curl -s -X POST http://localhost:6081/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"Denis.Teixeira@hotmail.fr","mdp":"test"}' \
  | jq -r '.data.token')

# 2. Créer un RDV
curl -X POST http://localhost:6081/rdvs \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "patientId":"d975aca7-50c5-3d16-b211-cf7d302cba50",
    "praticienId":"d6021537-72b7-3289-bfe7-efa9b4281b73",
    "dateHeureDebut":"2026-08-15 14:00:00",
    "duree":30,
    "motifVisite":"radiologie"
  }'

# 3. Vérifier les emails
# http://localhost:1080
```

## Architecture

### Microservices

- **Gateway** : Routage et authentification centralisée
- **app.auth** : Gestion JWT, login, register
- **app.praticiens** : CRUD praticiens, spécialités
- **app.rdv** : CRUD RDV, agenda, annulation
- **app.mailer** : Consumer RabbitMQ, envoi emails

### Communication

- **Synchrone** : HTTP/REST (Gateway → Microservices)
- **Asynchrone** : RabbitMQ (Événements RDV → Emails)

### Bases de données (PostgreSQL)

- `toubiauth` (5433) - Authentification
- `toubiprat` (5432) - Praticiens
- `toubirdv` (5434) - Rendez-vous
- `toubipatient` (5435) - Patients

## Équipe

| Membre | Contributions                              |
| ------ | ------------------------------------------ |
| Noah   | Architecture, JWT, Microservices, RabbitMQ |
| Raouf  | API praticiens, RDV, endpoints             |
| Arman  | HATEOAS, Status, endpoints                 |
| Léo   | Authentification                           |

### Réalisations TPs 2.1, 2.2, 2.3

| TP | Réalisations |
|----|--------------|
| **TP 2.1 - Microservices** | Arman (Gateway, routage), Raouf (microservices praticiens & RDV), Noah (corrections) |
| **TP 2.2 - Auth/Authz** | Léo (authentification JWT complète), Noah (middlewares, register, refresh token), Raouf (corrections) |
| **TP 2.3 - RabbitMQ/Emails** | Noah (RabbitMQ, consumer, notifications emails) |

---

**Lien GitHub :** https://github.com/Raouf-blip/Toubilib

https://github.com/lclerc561/Toubilibv2
