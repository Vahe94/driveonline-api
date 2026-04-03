# GitHub Auto-Deploy Setup

This repository and `driveonline-next` share one production stack. A push to `main` in either repository deploys both repositories together on the server.

## GitHub Secrets To Add

Add the same secrets to both repositories:

- `DEPLOY_HOST`
- `DEPLOY_PORT`
- `DEPLOY_USER`
- `DEPLOY_SSH_KEY`
- `DEPLOY_HOST_FINGERPRINT`
- `REPO_READ_SSH_KEY`
- `FRONTEND_ENV_PRODUCTION`
- `BACKEND_ENV_PRODUCTION`

`FRONTEND_ENV_PRODUCTION` should contain the full frontend `.env.production` file.

`BACKEND_ENV_PRODUCTION` should contain the full backend `.env.production` file.

## Server Requirements

The server must already have:

- Docker and the Compose plugin.
- Nginx and certificates configured.
- `/opt/driveonline` available for the app repositories.
- A deploy user that can run Docker.
- The public key matching `DEPLOY_SSH_KEY` in `authorized_keys`.

## GitHub Key Layout

Two key pairs are recommended:

1. `DEPLOY_SSH_KEY`
   GitHub Actions uses this to SSH into the server.
2. `REPO_READ_SSH_KEY`
   The remote deploy script uses this temporary key to pull `driveonline-next` and `driveonline-api` from GitHub.

## Environment Recommendation

Create a `production` environment in GitHub for both repositories and store all deployment secrets there instead of repository-level secrets.
