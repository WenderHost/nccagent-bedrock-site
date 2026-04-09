# NCCAgent.com Codebase Research (2026-02-24)

## 1. Executive Summary
This repository is a Roots Bedrock–based WordPress site for nccagent.com. Core WordPress is managed as a Composer dependency and installed into `web/wp`, while site-specific code lives in `web/app`. The site’s presentation layer is Elementor + Hello Elementor (child theme), and the bulk of custom business logic lives in a bespoke plugin, `nccagent-extras`.

The custom plugin defines data models (CPTs + taxonomies), shortcodes, REST endpoints, WP-CLI commands, and multiple integrations (ActiveCampaign, Gravity Forms, Elementor Pro, Cloudflare Turnstile, and a CSG Actuarial integration). It also provides operational tooling for importing/exporting carrier/product data and managing user onboarding and approval messaging.

## 2. Repository Layout (What Lives Where)
- `config/` — Bedrock configuration. `application.php` defines all shared constants; `config/environments/` overrides per environment.
- `.env` / `.env.example` — Environment config (DB credentials, API keys, WP_HOME/WP_SITEURL, salts, etc.).
- `web/` — Web root. Contains `wp-config.php`, Bedrock front controller, static assets, and core WordPress in `web/wp`.
- `web/app/` — WordPress content directory.
  - `mu-plugins/` — Roots/Bedrock MU plugins (autoloader, disallow indexing, plugin disabler).
  - `plugins/` — Site plugins (Composer-managed and custom).
  - `themes/` — Hello Elementor parent + child theme + Twenty Twenty-Four.
  - `uploads/` — Media uploads (not reviewed in depth).
  - `updraft/` — UpdraftPlus backups (not reviewed in depth).
- `sql/` — Local DB dump (`localhost.sql`).
- `bin/docs/` — Documentation output target (this report).
- `wp-cli.yml` / `wp-cli.local.yml` — WP-CLI config and production alias.
- `web/sync-remote-to-local.sh` — Operational script to sync production to local.

## 3. Bedrock Configuration & Environment Behavior
### 3.1 Global config (`config/application.php`)
- Loads `.env` (and `.env.local` if present) via `vlucas/phpdotenv`.
- Defines `WP_ENV` (default `production`) and derives `WP_ENVIRONMENT_TYPE`.
- Defines core URLs: `WP_HOME`, `WP_SITEURL`.
- Defines NCC-specific constants:
  - `ACTIVECAMPAIGN_API_KEY`
  - `HS_PORTAL_ID`
  - `GF_ONLINE_CONTRACTING_FORM_ID`
  - `GF_CARRIER_CHECKLIST_FIELD_ID`
  - `HS_AGENT_REGISTRATION_FORM_ID`
  - `WP_MEMORY_LIMIT`, `WP_MAX_MEMORY_LIMIT`
- Sets `DISALLOW_FILE_MODS` to `true` globally (overridden in dev).
- Sets WP_DEBUG / logging behavior via env variables.
- Sets Bedrock content dir to `web/app`.

### 3.2 Environment overrides
- `config/environments/development.php`
  - Enables debugging (`WP_DEBUG`, `SAVEQUERIES`, `SCRIPT_DEBUG`).
  - Sets `DISALLOW_INDEXING` to true (via bedrock-disallow-indexing).
  - `DISALLOW_FILE_MODS` set to `false` (allow plugin/theme changes).
  - Sets `DISABLED_PLUGINS` list for dev-only disabling.
- `config/environments/staging.php`
  - Sets `DISALLOW_INDEXING` only; other settings inherited from production.

### 3.3 WP-CLI & Ops
- `wp-cli.yml` sets WordPress path to `web/wp` and docroot to `web`.
- `wp-cli.local.yml` includes a production alias:
  - `@production` → `nccagent@159.223.140.236:/sites/nccagent.com/files/web`
- `web/sync-remote-to-local.sh` performs:
  - Remote DB export over SSH
  - Local DB backup
  - Download + import remote SQL
  - URL replacement (standard + Elementor)
  - Elementor CSS flush
  - Plugin toggling for local dev

## 4. MU-Plugins (Bedrock Tools)
- `bedrock-autoloader` — autoloads standard plugins as MU plugins.
- `bedrock-disallow-indexing` — blocks indexing when `DISALLOW_INDEXING` is true.
- `bedrock-plugin-disabler` — disables plugins based on `DISABLED_PLUGINS` config.

## 5. Themes
- `hello-elementor` + `hello-theme-child` (child theme).
  - Child theme is mostly stock: it enqueues its `style.css` after parent and defines a version constant.
  - All layout/content is likely Elementor-driven; no custom PHP template files beyond the standard child theme bootstrap.
- `twentytwentyfour` included as a fallback.

## 6. Composer-Managed Plugins & Dependencies
Composer manages all WordPress core + plugins/themes. Highlights from `composer.json`:
- Core: `roots/wordpress` (WordPress 6.x), Bedrock helpers.
- Page builder: `elementor`, `elementor-pro`, `hello-elementor`.
- Forms: `gravityforms` (premium).
- ACF Pro: `advanced-custom-fields-pro`.
- Security: `ithemes-security-pro`.
- SEO: `wordpress-seo` + premium.
- Events: `the-events-calendar` + pro.
- Backup: `updraftplus`.
- Several utilities: `redirection`, `members`, `wp-approve-user`, etc.
- Custom plugin: `wenderhost/nccagent-extras` (VCS repo).

## 7. The `nccagent-extras` Plugin (Core Custom Logic)
Location: `web/app/plugins/nccagent-extras/`

### 7.1 Bootstrapping & Constants
- Defines:
  - `NCC_CSS_DIR` = `dist` or `css` based on `.local` URL or `SCRIPT_DEBUG`.
  - `NCC_DEV_ENV` = true if site URL contains `.local`.
  - `NCC_PLUGIN_DIR`, `NCC_PLUGIN_URL`.
- Loads Composer autoloader (`vendor/autoload.php`).
- Requires a long list of feature modules from `lib/fns/`.

### 7.2 Data Model (Custom Post Types & Taxonomies)
Defined via ACF JSON (likely using ACF extended CPT + taxonomy builder):
- CPTs:
  - `carrier` (Carriers)
  - `product` (Products, hierarchical)
  - `team_member` (Team Members, hierarchical, permalink under `/about/staff`)
  - `whatsnew` (News Items, not publicly queryable)
- Taxonomies:
  - `state` (assigned to team members)
  - `staff_type` (assigned to team members)

ACF Field Groups (from `lib/acf-json/`):
- Carrier Options
- Product Options
- Team Member Options
- News Item Options
- Online Contracting Settings
- Email Settings
- NCC Settings

ACF JSON save/load path is explicitly set to plugin-managed `lib/acf-json`.

### 7.3 Template Rendering
- Uses LightnCandy/Handlebars templates:
  - `.hbs` in `lib/templates/`
  - Compiled PHP in `lib/templates/compiled/`
  - Runtime compiles if template is newer.
- HTML snippets stored in `lib/html/` and used for alerts and UI blocks.

### 7.4 Shortcodes (Front-End Features)
Key shortcodes:
- `[carrier_page]`, `[product_page]` — renders CPT pages with structured sections, CTAs, and quick links.
- `[carrier_products]` / `[acf_carrier_products]` — carrier product list/accordion.
- `[acf_product_carriers]` — show carriers for a product.
- `[carrierdocs]` — integration with carrier document library.
- `[dirlister]`, `[dirlisterbutton]` — drives VPN directory listing UI.
- `[mymarketer]`, `[marketer_contact_details]`, `[marketer_states]`, `[marketer_testimonials]` — marketer/team member features.
- `[contracting_confirmation]` — GravityForms confirmation for Online Contracting.
- `[ncclistall]` — list all carriers/products.

### 7.5 REST API Endpoints
Registered under `nccagent/v1`:
- `GET /dirlister` — returns file listing from `https://vpn.ncc-agent.com/docs/` (requires `read` capability).
- `GET /products` — public product finder data (carriers + products + URLs).
- `POST|GET /productimport` — API import/update for carrier products.
- `POST|GET /verifyAccount` — CSG Actuarial mobile app authentication endpoint (email + password).

Also adds custom REST fields for `team_member` and allows `orderby=rand` in its collection.

### 7.6 Admin & Back Office Tools
- Admin UI for carrier/product CSV import/export.
- WP-CLI commands:
  - `wp ncc carriers export` and `wp ncc carriers import`
  - `wp ncc users import`
- Custom columns for Team Member CPT in admin.
- ACF Options Pages under “NCC Settings” and subpages for Email + Online Contracting.

### 7.7 Integrations & Behavior
- ActiveCampaign:
  - Injects tracking script in footer.
  - Elementor Form hook creates WP user + posts contact to ActiveCampaign for a named form.
- Cloudflare Turnstile:
  - Integrates Turnstile into Elementor Pro forms with explicit rendering and validation.
- Gravity Forms:
  - Dynamic carrier checklist population for Online Contracting form.
  - Custom notification content for Online Contracting.
- User onboarding:
  - Hooks into `wp-approve-user` plugin to send approval emails.
  - Custom “registration received” emails after user creation.
  - Additional user profile fields (NPN, company, marketer assignment).
- SEO:
  - Custom Yoast title logic for carrier/product CPTs.
- Login/registration:
  - Redirects `/wp-login.php?action=register` to `/register`.
  - Redirects login to `/dashboard`.
  - Custom login CSS.

### 7.8 Front-End Assets
- CSS and JS bundled in `lib/dist/` and `lib/css/` with cache-busting.
- Registers external libs (DataTables, Select2) and custom modules:
  - Product Finder
  - Directory Lister
  - Accordion
  - Menu toggles

### 7.9 Notable Implementation Details
- Uses custom query vars (`carrierproduct`, `productcarrier`, `path`) with rewrite rules and redirect logic to keep carrier/product URL variants valid.
- Uses file_get_contents to fetch VPN directory listings, then parses HTML for links.
- Builds navigation “Quick Links” on carrier/product pages.

## 8. Local Dev / Operational Tooling
- `web/sync-remote-to-local.sh` is the primary workflow to refresh local content:
  - remote export + local backup + import + URL replacements + Elementor flush.
- `localdev-switcher` plugin is toggled on for local work.
- Local-only plugin `localdev-nccagent-extras` mirrors the production plugin and includes node_modules and dev tooling.

## 9. Content/SEO Artifacts
- `web/llms.txt` is generated by Yoast SEO and lists sample pages/posts, carriers/products, events, etc. It suggests the local DB has events (`ncc-events`), venues, and a blog.
- `web/robots.txt` is intentionally minimal (Yoast guidance).

## 10. Findings, Risks, and Specificities
- `productimport` REST endpoint allows `GET` and `POST` with `permission_callback` returning true. This may be intentional for internal tooling but it is open to unauthenticated requests.
- `verifyAccount` endpoint also allows unauthenticated access (expected for a login endpoint, but worth ensuring rate limits / audit logging).
- `ncc_get_state_name` contains a duplicate key: `MI` appears twice (Michigan and Missouri). Missouri should be `MO`.
- `ncc_hbs_template_exists` references `DONMAN_DIR` (not defined in this plugin), which may be a leftover constant.
- Some code paths are intentionally disabled via comments (e.g., Elementor global JS enqueue, Gravity Forms custom confirmation). This suggests legacy behavior that was intentionally turned off.

## 11. What I Did Not Review In Depth
- `web/wp/` (WordPress core) — standard, Composer-managed.
- `vendor/` — Composer vendor libraries.
- `web/app/uploads/` and `web/app/updraft/` — content/backups.
- Plugin vendor directories other than `nccagent-extras` and operational scripts.

## 12. Suggested Next Investigations (If You Want Deeper Coverage)
1. Audit `nccagent-extras` REST endpoints for authentication/authorization needs.
2. Review `localdev-nccagent-extras` vs `nccagent-extras` for divergence.
3. Catalog Elementor templates and the content model in the database (CPT relationships + ACF field usage).
4. Document the Carrier/Product import CSV format and operational workflow for admins.
