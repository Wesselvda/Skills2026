# Local Content Service Stack

Docker Compose setup for running the **SkillShare Academy content service** locally while you build your own unified REST API (Module C).

## What is included

| Service              | Description                                                        |
| -------------------- | ------------------------------------------------------------------ |
| **content-services** | Official content API image from GHCR                               |
| **db**               | MariaDB 11 with schema and seed data from `db/content-service.sql` |
| **phpmyadmin**       | Web UI to inspect the database                                     |

API specification: [`../api/content-service-api.yaml`](../api/content-service-api.yaml)

## Prerequisites

- [Docker](https://docs.docker.com/get-docker/) with Docker Compose v2
- Ports **5000** and **8081** available on the host

## Quick start

From the **repository root**:

```bash
docker compose -f assets/content-service/content-service.yaml up -d
```

Check that containers are healthy:

```bash
docker compose -f assets/content-service/content-service.yaml ps
```

### Endpoints

| Purpose     | URL                               |
| ----------- | --------------------------------- |
| Content API | http://localhost:5000             |
| Example     | http://localhost:5000/api/courses |
| phpMyAdmin  | http://localhost:8081             |

Point your backend’s content-service client at `http://localhost:5000`.

### Default database credentials

| Setting                                   | Default                                                                 |
| ----------------------------------------- | ----------------------------------------------------------------------- |
| Host (from phpMyAdmin / other containers) | `db`                                                                    |
| Host (from your machine)                  | `localhost` (not published by default; use phpMyAdmin or `docker exec`) |
| Port                                      | `3306` (internal only)                                                  |
| Database                                  | `content-service`                                                       |
| User                                      | `root`                                                                  |
| Password                                  | `skill17`                                                               |

Override with environment variables or a `.env` file next to `content-service.yaml`:

- `MYSQL_ROOT_PASSWORD`
- `MYSQL_DATABASE`

The content service uses the same values via `DATABASE_URL`.

## Pulling the Docker image

Image:

`ghcr.io/marketable-it-skills/s17-es2025-training-hu-module_c-rest-api-content-service:latest`

## Stopping and resetting

Stop the stack:

```bash
docker compose -f assets/content-service/content-service.yaml down
```

Remove containers **and** the database volume (re-runs the SQL init script on next start):

```bash
docker compose -f assets/content-service/content-service.yaml down -v
```

## Files

```
assets/content-service/
├── README.md
├── content-service.yaml   # Compose stack
└── db/
    └── content-service.sql   # MariaDB init script (first start only)
```

## Notes

- The database is initialized **only on the first start** when the `db-data` volume is empty. To reload data, use `down -v` and start again.
