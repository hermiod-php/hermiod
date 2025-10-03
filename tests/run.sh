#!/bin/sh
# Usage: ./run-tests.sh <command> [test-filter]
# Commands: tests | coverage | ci | analyse | unit | unit-coverage |
#           integration | system | php-version
# Example: ./run-tests.sh unit MyTest::testSomething

set -eu

cmd="${1:-}"
test_filter="${2:-}"

php_version() {
    PHP_VERSION=$(php -r 'echo PHP_VERSION;')
    export PHP_VERSION
}

analyse() {
    vendor/bin/phpstan analyse --level max ./src
}

tests_unit() {
    php_version
    vendor/bin/phpunit -c tests/phpunit.xml \
      --testsuite unit \
      ${test_filter:+--filter "$test_filter"} \
      --log-junit "./test-reports/$PHP_VERSION/results/unit/junit.xml"
}

tests_unit_coverage() {
    php_version
    XDEBUG_MODE=coverage \
    vendor/bin/phpunit -c tests/phpunit.xml \
      --testsuite unit \
      ${test_filter:+--filter "$test_filter"} \
      --path-coverage \
      --coverage-html "./test-reports/$PHP_VERSION/coverage/html"
}

tests_integration() {
    php_version
    vendor/bin/phpunit -c tests/phpunit.xml --testsuite integration \
        --log-junit "./test-reports/$PHP_VERSION/results/integration/junit.xml"
}

tests_system() {
    php_version
    vendor/bin/phpunit -c tests/phpunit.xml \
      --testsuite system \
      --log-junit "./test-reports/$PHP_VERSION/results/system/junit.xml"
}

tests() {
    composer install
    analyse
    tests_unit
    tests_integration
    tests_system
}

tests_coverage() {
    php_version
    composer install
    analyse
    XDEBUG_MODE=coverage \
    vendor/bin/phpunit -c tests/phpunit.xml \
      --testsuite unit \
      ${test_filter:+--filter "$test_filter"} \
      --path-coverage \
      --coverage-clover "./test-reports/$PHP_VERSION/coverage/xml/clover.xml"
    tests_integration
}

tests_ci() {
    php_version
    composer install
    composer outdated --minor-only --direct --strict
    composer audit --no-dev
    analyse
    XDEBUG_MODE=coverage \
    vendor/bin/phpunit -c tests/phpunit.xml \
      --testsuite unit \
      ${test_filter:+--filter "$test_filter"} \
      --path-coverage \
      --coverage-clover "./test-reports/$PHP_VERSION/coverage/xml/clover.xml" \
      --log-junit "./test-reports/$PHP_VERSION/results/unit/junit.xml"
    vendor/bin/coverage-check "./test-reports/$PHP_VERSION/coverage/xml/clover.xml" 100
    tests_integration
    tests_system
}

case "$cmd" in
    analyse)        analyse ;;
    unit)           tests_unit ;;
    unit-coverage)  tests_unit_coverage ;;
    integration)    tests_integration ;;
    system)         tests_system ;;
    coverage)       tests_coverage ;;
    ci)             tests_ci ;;
    *)              tests ;;
esac
