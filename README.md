# Ritovex for Statamic

Ritovex is an AI automation and SaaS platform website starter kit by [Rad Themes](https://statamic.com/creators/rad-themes). It uses native Statamic collections, Antlers, and Tailwind CSS 4. All imagery is bundled locally. No Webflow runtime or paid addon is required.

Build a modern SaaS product site with editable platform features, integration ecosystem, engineering blog, pricing plans, and demo request forms.

## Included

- Six main pages: home, features, integrations, pricing, blog, and contact.
- Four feature modules, four integrations, and four blog articles, each with a detail page.
- Native navigation, brand/contact globals, managed images, SEO fields, mobile navigation, and a custom 404 page.
- Demo request form with server validation, CSRF protection, honeypot, and submissions stored in the Control Panel.

## Requirements

- Statamic 6.34 or later in the 6.x series.
- PHP 8.3 or newer within 8.x and Composer 2.
- Node.js 22.12 or newer and npm.

## Installation

```sh
composer config repositories.ritovex path /absolute/path/to/ritovex
php please starter-kit:install rad-themes/ritovex --local --without-user --clear-site
php please make:user
php please stache:refresh
npm ci
npm run build
php artisan serve
```

## Checks

```sh
php artisan test --compact tests/Feature/RitovexKitTest.php
npm run build
```
