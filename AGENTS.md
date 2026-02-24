# The NCCAgent.com Website

The nccagent.com website is built with WordPress and the roots/bedrock framework.

## Relevant Files

- `/config/` We set global WordPress PHP Constants here. Find environment specific settings inside `/config/environments/`.
- `composer.json` manages our WordPress Core and plugin files.
- We store documentation in `/bin/docs/`.
- The main custom functionality for this site is implemented via the custom WordPress plugin found in `web/app/plugins/localdev-nccagent-extras/`

## Local Tooling

- `wp-cli` is available; run from `web/`. Verified on February 24, 2026. Use `wp --info` to confirm.

## Upcoming Features

- Integrate CSG Actuarial mobile app authentication found in `web/app/plugins/localdev-nccagent-extras/lib/fns/csg.php` with CSG's [Client User Authorization](https://csgactuarialauthorizationcode.docs.apiary.io/#introduction/authorization-code).
