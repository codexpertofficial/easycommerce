#!/usr/bin/env bash
#
# Install the WordPress PHPUnit test library so `vendor/bin/phpunit` can run.
#
#   bin/install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version]
#
# Example, matching a Local by Flywheel site:
#
#   bin/install-wp-tests.sh easy_tests root root \
#     'localhost:/home/you/.config/Local/run/XXXX/mysql/mysqld.sock' 7.0.2
#
# WARNING: the WordPress test suite DROPS AND RECREATES every table in the
# database you name here on every run. Never point it at the database your
# site uses.

set -euo pipefail

DB_NAME="${1:-}"
DB_USER="${2:-}"
DB_PASS="${3:-}"
DB_HOST="${4:-localhost}"
WP_VERSION="${5:-latest}"

if [ -z "$DB_NAME" ] || [ -z "$DB_USER" ]; then
	echo "Usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version]" >&2
	exit 1
fi

PLUGIN_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )/.." && pwd )"
WP_TESTS_DIR="${WP_TESTS_DIR:-/tmp/wordpress-tests-lib}"
POLYFILLS_DIR="${POLYFILLS_DIR:-/tmp/phpunit-polyfills}"
POLYFILLS_VERSION="1.1.5"

# The site root is four levels up from wp-content/plugins/<plugin>.
WP_ROOT="$( cd "$PLUGIN_DIR/../../.." && pwd )"

if [ "$WP_VERSION" = "latest" ]; then
	WP_VERSION="$( php -r "echo trim( file_get_contents('$WP_ROOT/wp-includes/version.php') ? preg_replace('/.*\\\$wp_version = \'([^\']+)\'.*/s', '\$1', file_get_contents('$WP_ROOT/wp-includes/version.php') ) : 'latest' );" )"
fi

echo "Plugin:      $PLUGIN_DIR"
echo "WP root:     $WP_ROOT"
echo "WP version:  $WP_VERSION"
echo "Test DB:     $DB_NAME (will be wiped on every phpunit run)"

# ── test library ──────────────────────────────────────────────────────────────
if [ ! -f "$WP_TESTS_DIR/includes/functions.php" ]; then
	echo "Downloading the test library..."
	TMP="$( mktemp -d )"
	curl -sL "https://github.com/WordPress/wordpress-develop/archive/refs/tags/${WP_VERSION}.tar.gz" -o "$TMP/wp.tar.gz"
	mkdir -p "$TMP/x"
	tar -xzf "$TMP/wp.tar.gz" -C "$TMP/x" --strip-components=1 \
		"wordpress-develop-${WP_VERSION}/tests/phpunit/includes" \
		"wordpress-develop-${WP_VERSION}/tests/phpunit/data"
	mkdir -p "$WP_TESTS_DIR"
	cp -r "$TMP/x/tests/phpunit/includes" "$WP_TESTS_DIR/"
	cp -r "$TMP/x/tests/phpunit/data" "$WP_TESTS_DIR/"
	rm -rf "$TMP"
else
	echo "Test library already present at $WP_TESTS_DIR"
fi

# ── polyfills, kept outside the repo so composer.json stays untouched ─────────
if [ ! -f "$POLYFILLS_DIR/phpunitpolyfills-autoload.php" ]; then
	echo "Downloading PHPUnit Polyfills ${POLYFILLS_VERSION}..."
	TMP="$( mktemp -d )"
	curl -sL "https://github.com/Yoast/PHPUnit-Polyfills/archive/refs/tags/${POLYFILLS_VERSION}.tar.gz" -o "$TMP/pf.tar.gz"
	mkdir -p "$POLYFILLS_DIR"
	tar -xzf "$TMP/pf.tar.gz" -C "$POLYFILLS_DIR" --strip-components=1
	rm -rf "$TMP"
else
	echo "Polyfills already present at $POLYFILLS_DIR"
fi

# ── config ────────────────────────────────────────────────────────────────────
cat > "$WP_TESTS_DIR/wp-tests-config.php" <<PHP
<?php
define( 'ABSPATH', '$WP_ROOT/' );

define( 'DB_NAME', '$DB_NAME' );
define( 'DB_USER', '$DB_USER' );
define( 'DB_PASSWORD', '$DB_PASS' );
define( 'DB_HOST', '$DB_HOST' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

// DatabaseTest asserts this prefix. Do not change it.
\$table_prefix = 'ec_test_';

define( 'WP_TESTS_DOMAIN', 'easycoms.com' );
define( 'WP_TESTS_EMAIL', 'admin@easycoms.com' );
define( 'WP_TESTS_TITLE', 'EasyCommerce Tests' );
define( 'WP_PHP_BINARY', 'php' );
define( 'WP_DEBUG', true );
define( 'WPLANG', '' );
define( 'WP_TESTS_CONFIG_FILE_PATH', __FILE__ );
define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', '$POLYFILLS_DIR' );
PHP

# ── autoloader must include the EasyCommerce\Tests namespace ──────────────────
if ! grep -q 'EasyCommerce\\\\Tests' "$PLUGIN_DIR/vendor/composer/autoload_psr4.php" 2>/dev/null; then
	echo "Dumping the autoloader with dev namespaces..."
	( cd "$PLUGIN_DIR" && composer dump-autoload )
fi

echo
echo "Done. Run:  vendor/bin/phpunit"
echo "            vendor/bin/phpunit tests/php/src/Models/CheckoutShippingTaxTest.php"
