# GitHub Auto-Deploy Setup

This repository and `driveonline-next` share one production Docker Compose stack on the server. A push to `main` in either repository rebuilds the changed image, publishes it to GHCR, and redeploys the shared stack over SSH.

## Current Deploy Model

- GitHub Actions builds and pushes images to GHCR.
- The server does not `git pull` either repository during deploy.
- Runtime env values come from GitHub environment secrets and are written to temporary files under `/tmp` during the deploy step.
- The server must already have `/opt/driveonline/deploy/docker-compose.prod.yml`.

The production compose file lives in the frontend repository:

- [`driveonline-next/deploy/docker-compose.prod.yml`](/Users/vahe/Desktop/Projects/driveOnline/driveonline-next/deploy/docker-compose.prod.yml)

Copy it to the server once before the first image-based deploy:

```bash
mkdir -p /opt/driveonline/deploy
scp /path/to/driveonline-next/deploy/docker-compose.prod.yml root@SERVER_IP:/opt/driveonline/deploy/docker-compose.prod.yml
```

## GitHub Secrets To Add

Add the same `production` environment secrets to both repositories:

- `DEPLOY_HOST`
- `DEPLOY_PORT`
- `DEPLOY_USER`
- `DEPLOY_SSH_KEY`
- `FRONTEND_ENV_PRODUCTION`
- `BACKEND_ENV_PRODUCTION`
- `COMPOSE_ENV_PRODUCTION`
- `GHCR_PULL_USERNAME`
- `GHCR_PULL_TOKEN`

Secret meaning:

- `FRONTEND_ENV_PRODUCTION`
  The full frontend `.env.production` content.
- `BACKEND_ENV_PRODUCTION`
  The full backend production env content.
- `COMPOSE_ENV_PRODUCTION`
  Only Docker Compose interpolation values such as:

```dotenv
DB_DATABASE=driveonline
DB_USERNAME=driveonline
DB_PASSWORD=strong-password
MYSQL_ROOT_PASSWORD=very-strong-root-password
COMPOSE_PROJECT_NAME=deploy
```

- `GHCR_PULL_TOKEN`
  A classic GitHub PAT for the package owner with at least `read:packages`. If private GHCR pulls still return `403`, add `repo`.

## Server Requirements

The server must already have:

- Docker and the Compose plugin.
- Nginx and certificates configured.
- `/opt/driveonline/deploy/docker-compose.prod.yml` present.
- A deploy user that can run Docker.
- The public key matching `DEPLOY_SSH_KEY` in `authorized_keys`.

## Deploy Behavior

The backend workflow:

- builds `ghcr.io/<owner>/driveonline-api:main`
- pushes the image to GHCR
- SSHes into the server
- writes temporary frontend, backend, and compose env files
- pulls the latest images defined in the shared compose file
- recreates the containers
- runs `php artisan migrate --force`
- runs `php artisan optimize --except=views`

The backend workflow expects the frontend compose file to already be on the server. If it is missing, the workflow exits with a clear error.

## First Run Order

1. Add the shared production secrets to both repositories.
2. Copy `driveonline-next/deploy/docker-compose.prod.yml` to `/opt/driveonline/deploy/docker-compose.prod.yml`.
3. Run the frontend deploy workflow once.
4. Run the backend deploy workflow once.
5. After that, normal pushes to `main` can redeploy either image.

## Production Admin Bootstrap

Admin access is controlled by the backend `users.is_admin` boolean. After the first production boot, verify whether an admin user exists:

```bash
docker exec -it deploy-backend-1 php artisan tinker --execute="dump(App\Models\User::where('is_admin', true)->count());"
```

If the count is `0`, create or promote one:

```bash
docker exec -it deploy-backend-1 php artisan tinker --execute="App\Models\User::updateOrCreate(['email' => 'info@example.com'], ['name' => 'Admin', 'password' => bcrypt('change_me_123'), 'is_admin' => true]);"
```

Verify the result:

```bash
docker exec -it deploy-backend-1 php artisan tinker --execute="App\Models\User::where('is_admin', true)->get(['id','name','email','is_admin'])->each(fn($u) => dump($u->toArray()));"
```
