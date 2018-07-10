#!/bin/bash

COMMAND=$1
shift
ARGUMENTS="${@}"

cd docker

usage() {
    echo "$0 [COMMAND] [ARGUMENTS]"
    echo "  Commands:"
}

fn_exists() {
    type $1 2>/dev/null | grep -q 'is a function'
}

up() {
    docker-compose up --build -d ${@}
    composer install
}

composer() {
    docker-compose run --no-deps --rm php composer ${@}
}

console() {
    docker-compose run --no-deps --rm php bin/console ${@}
}

codecept() {
    docker-compose run --no-deps --rm php vendor/bin/codecept ${@}
}

tests-build() {
    echo "Clearing cache..."
    console cache:clear --env=test
    console doctrine:cache:clear-metadata --env=test
    console doctrine:cache:clear-query --env=test
    console doctrine:cache:clear-result --env=test
    echo "done."

    echo "Clearing database..."
    console doctrine:schema:drop --force --full-database --env=test
    echo "done."

    echo "Creating database structure..."
    console doctrine:migrations:migrate -q --env=test
    echo "done."

    console cache:warmup

    codecept build
}

tests-run() {
    if [ "$1" == "--build" ]; then
        shift
        tests-build
    fi
    docker-compose run --no-deps --rm php vendor/bin/codecept run ${@}
}

fn_exists $COMMAND
if [ $? -eq 0 ]; then
    $COMMAND $ARGUMENTS
else
    usage
fi
