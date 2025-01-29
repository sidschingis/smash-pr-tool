# Symfony Docker

A [Docker](https://www.docker.com/)-based installer and runtime for the [Symfony](https://symfony.com) web framework,
with [FrankenPHP](https://frankenphp.dev) and [Caddy](https://caddyserver.com/) inside!

![CI](https://github.com/dunglas/symfony-docker/workflows/CI/badge.svg)

## Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose --env-file .env.local --file compose.yaml -f compose.override.yaml build --no-cache` to build fresh images
3. Run `docker compose --env-file .env.local --file compose.yaml -f compose.override.yaml up --pull always -d --wait` to start the project
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker compose down --remove-orphans` to stop the Docker containers.

### First time setup

* create a new text file named `startGGToken.txt` in `.secrets` (folder might need to be created first) containing your start.gg API token
* connect to the php docker container and initialize the database `php bin/console doctrine:migrations:migrate`
* create a `.env.local` file. See `.env.template`

## Features

* Manage Players, Seasons and Sets
* Import Sets and Players from start.gg
* Aggregate wins/losses per player per season

**Enjoy!**

## License

This project is available under the MIT License.

## Credits

### Project skeleton:
https://github.com/dunglas/symfony-docker
Created by [Kévin Dunglas](https://dunglas.dev), co-maintained by [Maxime Helias](https://twitter.com/maxhelias) and sponsored by [Les-Tilleuls.coop](https://les-tilleuls.coop).
