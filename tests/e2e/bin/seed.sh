#!/usr/bin/env bash
#
# Seed a store with the prerequisites the e2e suite needs (idempotent):
#   - Cash on Delivery enabled as a payment method
#   - one global flat-rate shipping method (region_code '--' matches everywhere)
#
# Products are assumed to already exist (the suite discovers one from the shop
# page). Run after WordPress + the plugin are installed:
#
#   WP_CLI="wp --path=/var/www/html" tests/e2e/bin/seed.sh
#
set -euo pipefail

WP="${WP_CLI:-wp}"
PREFIX="$( $WP db prefix )"
PLAN_NAME="E2E Global Shipping"
# Country the checkout test uses; a region row for it guarantees a qualifying
# method even when another plan already claims that country with a narrow range
# (region matching returns every plan at the most specific matched level).
COUNTRY="${EC_COUNTRY:-US}"

exists="$( $WP db query "SELECT id FROM ${PREFIX}ec_shipping_plans WHERE name='${PLAN_NAME}' LIMIT 1;" --skip-column-names 2>/dev/null || true )"

if [ -z "${exists}" ]; then
	$WP db query "INSERT INTO ${PREFIX}ec_shipping_plans (name, description, active, taxable, calculation_base) VALUES ('${PLAN_NAME}', 'Seeded for e2e checkout tests', 1, 0, 'price');"
	plan_id="$( $WP db query "SELECT id FROM ${PREFIX}ec_shipping_plans WHERE name='${PLAN_NAME}' ORDER BY id DESC LIMIT 1;" --skip-column-names )"
	# region_code '--' (empty country-state-city) is the everywhere catch-all;
	# '{COUNTRY}--' guarantees this plan is among those matched for the test
	# country even when a more-specific region shadows the catch-all.
	$WP db query "INSERT INTO ${PREFIX}ec_shipping_plan_regions (plan_id, region_code, zip_code) VALUES (${plan_id}, '--', '');"
	$WP db query "INSERT INTO ${PREFIX}ec_shipping_plan_regions (plan_id, region_code, zip_code) VALUES (${plan_id}, '${COUNTRY}--', '');"
	# calculation_base 'price': min/max bound the cart total; 0..huge always applies.
	$WP db query "INSERT INTO ${PREFIX}ec_shipping_plan_methods (plan_id, name, min_unit, min, max_unit, max, cost) VALUES (${plan_id}, 'Flat Rate', 'price', 0, 'price', 9999999.99, 0);"
	echo "Seeded global shipping plan #${plan_id} with a free Flat Rate method."
else
	echo "Global shipping plan already present (#${exists})."
fi

echo "Store seeding complete."
