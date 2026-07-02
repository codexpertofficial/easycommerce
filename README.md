# EasyCommerce

AI-powered WordPress ecommerce plugin — an online store builder with a built-in AI shopping
agent, content writer, and image generator, plus products, orders, carts, coupons, variations,
shipping, and tax.

- **Requires WordPress:** 6.0+
- **Tested up to:** 7.0
- **Requires PHP:** 7.4+
- **Stable tag:** 1.41
- **License:** GPLv2 or later — https://www.gnu.org/licenses/gpl-2.0.html
- **Website:** https://easycommerce.dev

This repository is the human-readable source for the EasyCommerce plugin. The distributed plugin
(WordPress.org / installable zip) is a compiled build of this source; see the build steps below to
produce it yourself.

## Requirements

- PHP **7.4+**
- WordPress **6.0+**
- [Composer](https://getcomposer.org/) (PHP dependencies)
- [Node.js](https://nodejs.org/) **20+** and npm (JavaScript/React build)

## Build from source

```bash
# 1. Clone
git clone https://github.com/codexpertofficial/easycommerce.git
cd easycommerce

# 2. Install PHP dependencies
composer install

# 3. Install JS dependencies and build the admin SPAs, blocks, and assets
npm install
npm run build
```

For a production build, install PHP deps without dev packages and minify assets:

```bash
composer install --no-dev --optimize-autoloader
npm run build
npm run minify:js
npm run minify:css
```

### Common commands

| Command | What it does |
|---------|--------------|
| `npm run build` | Compile all SPAs and blocks to `/build/` |
| `npm run watch` | Webpack watch mode for development |
| `composer install` | Install PHP dependencies |
| `vendor/bin/phpunit` | Run the PHP test suite |
| `vendor/bin/phpcs app/` | Lint PHP against `phpcs.xml` |
| `composer run makepot` | Generate the translation `.pot` file |

## Installation (built plugin)

1. Build the plugin (above), or download a release build.
2. Copy the `easycommerce` directory into `wp-content/plugins/` (or upload the zip via
   **Plugins → Add New → Upload Plugin**).
3. Activate **EasyCommerce** from the WordPress Plugins screen and follow the setup wizard.

## Overview

- **PHP backend** in `app/` (namespace `EasyCommerce\`) — REST API, models, controllers, payment
  gateways, and background jobs.
- **Admin** is a React SPA (`spa/`, Tailwind CSS) compiled by Webpack.
- **Storefront** is PHP templates (`views/`) with vanilla/jQuery in `assets/`.
- Orders, carts, coupons, variations, shipping, and tax use dedicated `wp_ec_*` tables; products are
  a WordPress `product` custom post type, customers are WP users, and reviews are WP comments.

See `readme.txt` for the full feature list and end-user documentation.

## Contributing

Issues and pull requests are managed in the primary development repository. This mirror tracks
released source.

## License

EasyCommerce is free software, released under the GNU General Public License v2.0 or later. See the
[license](https://www.gnu.org/licenses/gpl-2.0.html) for details.
