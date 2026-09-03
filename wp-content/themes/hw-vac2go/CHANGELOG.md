# Changelog

All notable changes to this starter are documented here.
Format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [1.0.0] - 2026-08-03

### Added
- Initial release of the **HW Kadence Starter** child theme.
- Child-theme `style.css` header (`Template: kadence`) with an in-file
  "how to use" block and no CSS bloat.
- Lean `functions.php` with a `HW_STARTER_VERSION` constant and an explicit,
  readable include loader (no blind globs).
- `theme.json` (v3) with `appearanceTools`, a clearly-labeled PLACEHOLDER
  brand palette, a fluid type scale, and a named spacing scale.
- Includes:
  - `inc/setup.php` — textdomain, editor style, custom image size.
  - `inc/enqueue.php` — parent-then-child styles + deferred child JS with
    `filemtime()` cache-busting.
  - `inc/helpers.php` — escaped, i18n-ready `hw_starter_get_icon()`,
    `hw_starter_icon()`, `hw_starter_get_contact()`, `hw_starter_contact_link()`.
  - `inc/block-styles.php` — button "Outline Pill", group "Card", image
    "Rounded" variations.
  - `inc/patterns.php` — registers the "Highwater" pattern category.
- Block patterns (auto-registered via header): `hero`, `cta-banner`,
  `feature-grid`, `testimonial`.
- `assets/css/child.css` (mobile-first, token-driven) and a minimal
  `assets/js/child.js` stub.
- `languages/hw-starter.pot` seed catalog.
- Team docs: `README.md`, this changelog, `.gitignore`, `.editorconfig`,
  `templates/README.txt`, and `screenshot-README.txt`.

[1.0.0]: https://wearehighwater.com/
