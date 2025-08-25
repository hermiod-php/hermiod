#!/usr/bin/env sh

set -e -x

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

docker compose -f "$SCRIPT_DIR/docker/docker-compose.yml" run --rm php82-ci composer tests-ci
docker compose -f "$SCRIPT_DIR/docker/docker-compose.yml" run --rm php83-ci composer tests-ci
docker compose -f "$SCRIPT_DIR/docker/docker-compose.yml" run --rm php84-ci composer tests-ci