# HW Kadence Starter

A production-grade **Kadence child theme starter** by [Highwater](https://wearehighwater.com/).
It is a clean, secure, documented base for building **new client sites** on top of the
free **Kadence** parent theme. This is a reusable starter, not a copy of any existing site.

> **Design goal:** be the opposite of our old in-house themes. No placeholder junk metadata,
> no text-domain mismatches, no 600-line `functions.php`, no dead "Options Framework", no
> disabling Gutenberg, no fragile include loaders, and **no generic slug that collides with a
> wordpress.org theme and gets auto-overwritten.**

---

## 1. What this is + prerequisite

- A **child theme of Kadence** (`Template: kadence` in `style.css`). The **Kadence parent**
  supplies every base template (index, single, archive, search, 404, comments, header/footer)
  and its own `theme.json`. This child only layers house style, brand tokens, patterns, block
  styles, and a few helpers.
- **Targets:** PHP **8.1+**, WordPress **6.5+**. The block editor is **used** (never disabled).

**Prerequisite — do this first:** install and **activate the free Kadence theme** from
wordpress.org (Appearance → Themes → Add New → search "Kadence"). A child theme cannot work
without its parent installed.

---

## 2. Spin up a new client site

1. **Copy** this folder (`hw-kadence-starter`).
2. **Rename** it to a **UNIQUE, non-generic slug** — e.g. `hw-acme`, `hw-riverside-dental`.
   Never a bare dictionary word (that is how the old "bliss" theme collided with a wp.org
   theme and got auto-overwritten during updates).
3. **Edit `style.css`** header:
   - `Theme Name:` → e.g. `HW Acme`
   - `Text Domain:` → **must match the new slug**, e.g. `hw-acme`
   - Bump `Version:` if you like.
   - Leave `Template: kadence` unchanged.
4. **Rename the text domain in code.** The starter ships with the `hw-starter` domain and
   `hw_starter_` prefixes. For a real client build, do a careful find/replace of:
   - text domain string `'hw-starter'` → `'hw-acme'`
   - function/constant prefix `hw_starter_` / `HW_STARTER_` → `hw_acme_` / `HW_ACME_`
   - rename `languages/hw-starter.pot` → `languages/hw-acme.pot`
   Then regenerate the POT (see §6). *(Prefixing is optional if you keep it as a shared base,
   but matching the text domain to the slug in `style.css` is required.)*
5. **Set the brand** — no code needed for the basics:
   - Edit the **PLACEHOLDER** palette in `theme.json` (`brand-primary`, `brand-secondary`,
     `brand-accent`, neutrals) to the client's hex values.
   - Set fonts/type in `theme.json` and/or the **Kadence Global Palette** and **Kadence
     typography** controls.
   - Upload the logo and tune layout in the **Site Editor** (Appearance → Editor) /
     **Customizer**.
   - Optionally set brand phone/email via the theme mods read by
     `hw_starter_get_contact()`.
6. **Add the `screenshot.png`** (1200×900) — see `screenshot-README.txt`.
7. **Activate** the renamed child theme.

---

## 3. DO / DON'T (learned the hard way)

**DON'T**
- ❌ Name a theme with a **generic word** that exists on wordpress.org — a slug collision can
  auto-overwrite the theme on update. Always prefix `hw-<client>`.
- ❌ Edit the **Kadence parent** theme. It gets updated and your changes vanish. Child only.
- ❌ **Disable the block editor / Gutenberg.** This starter assumes and embraces it.
- ❌ Dump logic into `functions.php`. Keep it a thin loader; real code goes in `inc/`.
- ❌ Reintroduce a blind `glob()` include loader — it hides load order and swallows missing
  files. List includes explicitly.
- ❌ Echo untrusted data raw. Everything gets escaped/sanitized/i18n'd.
- ❌ Ship an "Options Framework" or other abandoned libraries. Use `theme.json` + core APIs.

**DO**
- ✅ Keep the includes loader **explicit** (`require_once` + `get_theme_file_path()`).
- ✅ **Escape at output** (`esc_html` / `esc_attr` / `esc_url` / `wp_kses_post`) and
  **sanitize input**.
- ✅ Wrap every user-facing string in i18n functions with the theme's text domain.
- ✅ Prefix all functions/hooks/globals (`hw_starter_` → `hw_<client>_`).
- ✅ **Test with `WP_DEBUG` on** before handing off — the loader warns about missing includes
  only in debug mode.

---

## 4. How the folder is organized

```
hw-kadence-starter/
  style.css            Child-theme header only (no CSS). "How to use" comment block.
  functions.php        Lean loader: version constant + explicit require_once list.
  theme.json           Brand tokens (palette/type/spacing) layered on Kadence. v3.
  THEME-JSON-GUIDE.md  Plain-English manual for theme.json (JSON can't hold comments).
  inc/
    setup.php          Textdomain, editor style, custom image size.
    enqueue.php        Parent-then-child assets, filemtime cache-busting.
    patterns.php       Registers the "Highwater" pattern category.
    block-styles.php   Registers custom block style variations.
    helpers.php        Escaped, i18n helper functions (icons, brand contact).
  patterns/            Block patterns (auto-registered via file headers).
    hero.php  cta-banner.php  feature-grid.php  testimonial.php
  templates/           Empty by design — templates inherited from Kadence (see its README).
  assets/
    css/child.css      House styles using theme.json custom properties. Mobile-first.
    js/child.js        Minimal, dependency-free enhancement stub.
  languages/           hw-starter.pot translation seed.
  screenshot-README.txt  How/why to add the real screenshot.png.
  README.md  CHANGELOG.md  .gitignore  .editorconfig
```

**Where to add things**
- New **pattern** → add a file in `patterns/` with the `Title/Slug/Categories: hw` header. It
  auto-registers; no PHP wiring needed.
- New **style rule** → `assets/css/child.css` (use the `--wp--preset--*` tokens).
- New **block style** → register in `inc/block-styles.php`, style it in `child.css`.
- New **helper** → `inc/helpers.php` (prefixed, escaped, i18n).
- New **include file** → create it in `inc/`, then add it to the explicit list in
  `functions.php`.

---

## 4b. Where things live / where to change what

The fastest lookup: find your task, edit that file. (Full section-by-section
help for `theme.json` is in **`THEME-JSON-GUIDE.md`**.)

| I want to…​ | Edit this | Notes |
|-------------|-----------|-------|
| Change brand **colors** | `theme.json` → `settings.color.palette` | Swap the 3 PLACEHOLDER brand hexes; also set the Kadence Global Palette. See `THEME-JSON-GUIDE.md`. |
| Change **fonts** | Kadence Customizer / Site Editor (families); `theme.json` typography (sizes) | Font *families* are easiest in Kadence; font *sizes* live in `theme.json`. |
| Change **spacing / type scale** | `theme.json` → `settings.spacing` / `settings.typography` | Named presets used across patterns and CSS. |
| Add **house CSS** | `assets/css/child.css` (bottom "▼ ADD CLIENT HOUSE STYLES" marker) | Reference tokens, don't hardcode hex. |
| Add **JavaScript** | `assets/js/child.js` (inside the IIFE) | Dependency-free, deferred; no jQuery. |
| Add / edit a **pattern** | `patterns/*.php` | Auto-registers via the file header; no wiring needed. Placeholder copy is editable in the Site Editor after inserting. |
| Add a **block style** (e.g. new button look) | `inc/block-styles.php` **+** `assets/css/child.css` | Register it, then style its `.is-style-<name>` class. |
| Add a **helper function** | `inc/helpers.php` | Prefix `hw_starter_`, escape output, add an `@example`. |
| Add a **custom image size** / theme support | `inc/setup.php` | Also list new image sizes in the size chooser filter there. |
| Add / change **asset enqueues** | `inc/enqueue.php` | Reuse `hw_starter_asset_version()` for cache-busting. |
| Add a whole **new include file** | create in `inc/`, then list it in `functions.php` | The `$hw_starter_includes` array is the only wiring point. |
| Change the **pattern category** | `inc/patterns.php` | Keep the `hw` slug in sync with pattern headers. |
| Override a **template** | `templates/` (see its README) | Prefer patterns / Site Editor first; override only when necessary. |

---

## 5. Definition of done (per client site)

- [ ] Kadence parent installed and active.
- [ ] Folder renamed to a unique `hw-<client>` slug; **no** wp.org collision.
- [ ] `style.css` `Theme Name` + `Text Domain` updated (and prefixes if renaming).
- [ ] Brand palette placeholders replaced in `theme.json`; fonts/logo set.
- [ ] Patterns reviewed; placeholder copy replaced with real content.
- [ ] `screenshot.png` (1200×900) added; `screenshot-README.txt` removed.
- [ ] Site loads with **`WP_DEBUG` on** and **zero** notices/warnings.
- [ ] Front end renders (real HTTP 200 with content — not a WSOD served at 200).
- [ ] Keyboard focus visible; color contrast checked; skip-link works (from Kadence).
- [ ] POT regenerated if strings changed.
- [ ] `CHANGELOG.md` updated for the client build.

---

## 6. Translations

Regenerate the catalog after any string change (requires WP-CLI):

```
wp i18n make-pot . languages/hw-starter.pot --domain=hw-starter
```

Rename the `.pot` and `--domain` to match the client slug when you rebrand.

---

*Author: Highwater · https://wearehighwater.com/ · License: GPL-2.0-or-later*
