# Toubilib - API de Gestion de Rendez-vous Médicaux

API RESTful développée avec PHP/Slim pour la gestion de rendez-vous médicaux entre patients et praticiens.

## Lien vers le dépôt git

https://github.com/Raouf-blip/Toubilib

## Installation et Lancement

### Prérequis

- Docker et Docker Compose
- Git

### Installation

```bash
git clone https://github.com/Raouf-blip/Toubilib
cd Toubilib
# Copier le fichier de configuration
cp app/config/.env.dist app/config/.env
# Lancer les services
docker-compose up -d
```

### Vérification

```bash
curl http://localhost:6080/
```

L'API répondra avec la liste de tous les endpoints disponibles.

## Comptes de Test

**Patients (role=1):**

- Email: `Denis.Teixeira@hotmail.fr` / Mot de passe: `test`

**Praticiens (role=10):**

- Email: `dith.Didier@club-internet.fr` / Mot de passe: `test`

### Exemple d'utilisation

```bash
# 1. Se connecter et récupérer le token
TOKEN=$(curl -X POST http://localhost:6080/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"Denis.Teixeira@hotmail.fr","mdp":"test"}' \
  -s | jq -r '.data.token')

# 2. Lister les praticiens
curl -X GET http://localhost:6080/praticiens

# 3. Rechercher des praticiens par spécialité et ville
curl -X GET "http://localhost:6080/praticiens/search?specialite=radiologie&ville=Paris"

# 4. Obtenir les détails d'un praticien
curl -X GET http://localhost:6080/praticiens/{id}

# 5. Créer un RDV
curl -X POST http://localhost:6080/rdvs \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "patientId":"{patientId}",
    "praticienId":"{praticienId}",
    "dateHeureDebut":"2026-01-20 10:00:00",
    "duree":30,
    "motifVisite":"radiologie"
  }'
```

## Structure des Réponses JSON

Toutes les réponses de l'API suivent une structure standardisée pour garantir la cohérence et faciliter l'intégration.

### Réponses de Succès

Toutes les réponses de succès suivent le pattern suivant :

```json
{
  "status": "success",
  "data": {
    // Données spécifiques à l'endpoint
  },
  "_links": {
    // Liens HATEOAS
  }
}
```

**Exemples :**

- **GET /praticiens/{id}** :

```json
{
  "status": "success",
  "data": {
    "id": "uuid",
    "nom": "Dupont",
    "prenom": "Jean",
    "specialite": "radiologie",
    // ... autres champs
  },
  "_links": { ... }
}
```

- **GET /praticiens** :

```json
{
  "status": "success",
  "data": {
    "praticiens": [
      {
        "nom": "Dupont",
        "prenom": "Jean",
        // ...
      }
    ]
  },
  "_links": { ... }
}
```

- **POST /auth/login** :

```json
{
  "status": "success",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "id": "uuid",
      "email": "user@example.com",
      "role": "1 - Patient"
    },
    "expires_in": 3600
  },
  "_links": { ... }
}
```

### Réponses d'Erreur

Toutes les erreurs suivent le pattern suivant :

```json
{
  "status": "error",
  "message": "Description de l'erreur"
}
```

Pour les erreurs de validation (400), un tableau `errors` peut être inclus :

```json
{
  "status": "error",
  "errors": [
    "Le champ 'email' est requis",
    "L'email doit être au format valide"
  ]
}
```

**Codes de statut HTTP :**

- `200 OK` : Succès (GET, PATCH)
- `201 Created` : Ressource créée avec succès (POST)
- `204 No Content` : Succès sans contenu (DELETE)
- `400 Bad Request` : Erreur de validation ou requête invalide
- `401 Unauthorized` : Authentification requise ou token invalide
- `403 Forbidden` : Accès non autorisé
- `404 Not Found` : Ressource non trouvée
- `409 Conflict` : Conflit (ex: créneau déjà occupé)
- `500 Internal Server Error` : Erreur serveur

### Liens HATEOAS

Toutes les réponses incluent des liens HATEOAS dans le champ `_links` pour faciliter la navigation dans l'API :

```json
{
  "_links": {
    "self": {
      "href": "http://localhost:6080/praticiens/{id}",
      "method": "GET"
    },
    "agenda": {
      "href": "http://localhost:6080/praticiens/{id}/agenda",
      "method": "GET",
      "description": "Consulter l'agenda du praticien"
    }
  }
}
```

## Fonctionnalités Implémentées

| #  | Fonctionnalité                  | Endpoint                                                                                                                                                | Statut |
| -- | -------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------- | ------ |
| 1  | Lister les praticiens            | `GET /praticiens`                                                                                                                                     | OK     |
| 2  | Détail praticien                | `GET /praticiens/{id}`                                                                                                                                | OK     |
| 3  | Créneaux occupés               | `GET /praticiens/{id}/rdvs/occupes?dateDebut=...&dateFin=...`                                                                                         | OK     |
| 4  | Consulter RDV                    | `GET /rdvs/{id}`                                                                                                                                      | OK     |
| 5  | Réserver RDV                    | `POST /rdvs`                                                                                                                                          | OK     |
| 6  | Annuler RDV                      | `DELETE /rdvs/{id}/annuler`                                                                                                                                   | OK     |
| 7  | Agenda praticien                 | `GET /praticiens/{id}/agenda?dateDebut=...&dateFin=...`                                                                                               | OK     |
| 8  | Authentification                 | `POST /auth/login`                                                                                                                                    | OK     |
| 9  | Recherche praticiens             | `GET /praticiens/search?specialite=...&ville=...`                                                                                                     | OK     |
| 10 | Marquer RDV honoré/non honoré  | `PATCH /rdvs/{id}/honorer` / `PATCH /rdvs/{id}/non-honorer`                                                                                         | OK     |
| 11 | Historique consultations patient | `GET /patients/{id}/consultations`                                                                                                                    | OK     |
| 12 | Inscription patient              | `POST /auth/register`                                                                                                                                 | OK     |
| 13 | Gestion indisponibilités        | `POST /praticiens/{id}/indisponibilites<br>``GET /praticiens/{id}/indisponibilites<br>``DELETE /praticiens/{id}/indisponibilites/{indisponibiliteId}` | OK     |

## Architecture

- **Architecture hexagonale** : Séparation Domain, Application, Infrastructure
- **4 bases PostgreSQL** distinctes (auth, patients, praticiens, rdv)
- **Authentification JWT** avec middlewares d'autorisation par rôle et ressource
- **API RESTful** conforme aux standards REST avec liens HATEOAS
- **Validation des données** via middlewares dédiés
- **Docker** avec docker-compose pour le développement

### Structure du projet

```
app/
├── src/
│   ├── api/              # Couche API (Actions, Middlewares, Routes)
│   ├── application_core/ # Couche Application (Use Cases, DTOs, Services)
│   └── infrastructure/   # Couche Infrastructure (Repositories, DB)
├── config/               # Configuration (DI, Routes, Services)
└── public/              # Point d'entrée de l'application
```

### Accès aux bases de données

Un service Adminer est disponible sur `http://localhost:8080` pour gérer les bases de données.

## Tableau de Bord

### Réalisations par Membre du Groupe

| Membre                | Contributions Principales                                                                                                 |
| --------------------- | ------------------------------------------------------------------------------------------------------------------------- |
| **Noah**        | Architecture hexagonale, Authentification JWT, Middlewares                                                                |
| **Noah, Arman** | API RESTful, Validation des données, HATEOAS                                                                             |
| **Noah**        | Bases de données, Docker                                                                                                 |
| **Léo**        | Home, Authentification                                                                                                    |
| **Raouf**       | Lister les praticiens, Détail praticien, Créneaux occupés, Consulter RDV, Réserver RDV, Annuler RDV, Agenda praticien |
| **Arman**       | Détail praticien, Status, HATEOAS                                                                                        |

## Notes importantes

- Tous les endpoints nécessitant une authentification requièrent un header : `Authorization: Bearer {token}`
- Les tokens JWT expirent après 1 heure
