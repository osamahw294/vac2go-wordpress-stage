# theme.json — plain-English guide

`theme.json` is the **design brain** of this child theme. JSON files can't hold
comments, so this file is the manual. It explains every section, exactly **which
values to swap per client**, what each token controls, and **where each shows up**
in the editor.

> **Golden rule:** change the brand HERE (and in the Kadence Global Palette), and
> the patterns, block styles, and `child.css` all follow automatically — because
> they reference these tokens instead of hardcoding colors. Don't paste hex codes
> into CSS; edit them here.

How this file relates to Kadence: it **layers on top of** the Kadence parent's own
`theme.json`. Anything you set here wins. You don't need to redeclare things
Kadence already provides — only overrides.

Official reference: https://developer.wordpress.org/themes/global-settings-and-styles/

---

## Per-client checklist (the short version)

1. `settings.color.palette` → replace the **three PLACEHOLDER brand colors**
   (`brand-primary`, `brand-secondary`, `brand-accent`) with the client's hex codes.
2. `settings.layout` → adjust `contentSize` / `wideSize` only if the client's
   design needs a different measure.
3. `settings.typography.fontSizes` → adjust the scale only if the design calls for it.
4. Set **fonts** and **logo** via the Kadence Customizer / Site Editor (fonts are
   easier to manage in Kadence than by hand here).
5. Leave the `neutral-*` and `base-white` colors alone unless the design is dark/tinted.

---

## Section 1 — `settings` (what editors are ALLOWED to do)

### `appearanceTools: true`
Turns on the full set of design controls (border, spacing, typography, etc.) in the
editor. **Leave on.** Where it shows up: the block **Settings/Styles** sidebar gains
more controls.

### `useRootPaddingAwareAlignments: true`
Makes full-width blocks line up correctly with the page's edge padding. **Leave on.**

### `layout`
```
"contentSize": "720px"   // width of normal (non-wide) content
"wideSize": "1200px"     // width of blocks set to "Wide width"
```
- **Swap per client?** Only if the design uses a wider/narrower measure.
- **Where it shows up:** the width of your content column, and the "Wide" alignment
  option on blocks (Site Editor + front end).

---

## Section 2 — `settings.color.palette` (THE MAIN THING YOU CHANGE)

Each entry is one swatch. `slug` = the internal name used everywhere (DON'T change
slugs — patterns/CSS reference them). `color` = the hex you swap. `name` = the label
editors see in color pickers.

| slug              | Swap per client? | Controls / where it shows up |
|-------------------|------------------|------------------------------|
| `brand-primary`   | **YES** (PLACEHOLDER) | Main brand color: buttons, links, focus rings. Used by the hero primary button + outline-pill style. Appears as a swatch in every color picker. |
| `brand-secondary` | **YES** (PLACEHOLDER) | Dark brand tone: CTA banner background, link/button hover. |
| `brand-accent`    | **YES** (PLACEHOLDER) | Accent/highlight: the CTA banner's button. |
| `neutral-900`     | Usually keep     | Body text color. |
| `neutral-700`     | Usually keep     | Muted/secondary text. |
| `neutral-400`     | Usually keep     | Borders, placeholders. |
| `neutral-100`     | Usually keep     | Card/section surfaces (feature grid, testimonial bg). |
| `base-white`      | Usually keep     | Page background, button text on dark. |

**How to swap:** replace the `color` hex for the three brand entries. That's it. The
placeholder `name` strings (`"PLACEHOLDER #RRGGBB — CLIENT PRIMARY ..."`) are just
reminders — you can shorten them to a clean label like `"Primary"` once set.

**Two things to keep in sync with Kadence:**
- These tokens become CSS variables `--wp--preset--color--<slug>` used by `child.css`
  and patterns.
- Kadence also has its **Global Palette** (Customizer → Colors). For a fully
  consistent site, set the matching brand colors there too, so Kadence's own header/
  footer/buttons match. Think of theme.json as the tokens and the Kadence Global
  Palette as where Kadence-specific UI reads its colors.

`defaultPalette: false` / `defaultGradients: false` hide WordPress's stock swatches so
editors only see the curated brand set. Leave as-is unless a client needs the extras.

---

## Section 3 — `settings.typography` (the type scale)

```
"fluid": true            // font sizes scale smoothly between mobile and desktop
"fontSizes": [ ... ]     // the named size options in the editor's Typography panel
```
Each size has a `slug` (referenced by blocks), a `size` (default), a `name` (editor
label), and optional `fluid` min/max for responsive scaling.

| slug       | Rough use | Shows up as |
|------------|-----------|-------------|
| `small`    | fine print, captions | "Small" in the font-size picker |
| `medium`   | body copy | "Medium" |
| `large`    | lead paragraphs, card titles | "Large" |
| `x-large`  | section headings (h2) | "Extra Large" |
| `xx-large` | big headings | "2X Large" |
| `huge`     | hero display heading | "Huge (display)" |

- **Swap per client?** Usually leave the scale; adjust only if the design system
  specifies different steps. To change actual **fonts (families)**, use the Kadence
  Customizer / Site Editor typography controls — that's the easier, client-friendly
  place.
- **Where it shows up:** the block **Typography → Size** dropdown, and anywhere a
  pattern sets a `fontSize`.

---

## Section 4 — `settings.spacing` (the spacing scale)

```
"spacingScale": { "steps": 0 }   // 0 = we define sizes by hand below (no auto scale)
"spacingSizes": [ ... ]          // the named padding/margin/gap presets
```

| slug | size    | Editor label   |
|------|---------|----------------|
| `20` | 0.5rem  | 1 — X Small    |
| `30` | 1rem    | 2 — Small      |
| `40` | 1.5rem  | 3 — Medium     |
| `50` | 2.5rem  | 4 — Large      |
| `60` | 4rem    | 5 — X Large    |
| `70` | 6rem    | 6 — XX Large   |

- **Swap per client?** Rarely. Keep the scale consistent so spacing stays even
  across the site. Patterns use these presets (e.g. `spacing|60` section padding).
- **Where it shows up:** the block **Dimensions → Padding/Margin/Block spacing**
  controls (the slider steps), and `var(--wp--preset--spacing--40)` in CSS/patterns.

---

## Section 5 — `styles` (the DEFAULT look, applied site-wide)

This section wires tokens into actual defaults so you rarely style by hand:
- `styles.color` — default page background (`base-white`) and text (`neutral-900`).
- `styles.elements.link` — link color = `brand-primary`, hover = `brand-secondary`.
- `styles.elements.button` — button bg = `brand-primary`, hover = `brand-secondary`.
- `styles.elements.heading` — heading color/weight/line-height.

**Swap per client?** Usually no — because these already point at your brand tokens,
updating the palette (Section 2) updates these automatically. Edit here only to change
default *relationships* (e.g. make buttons use the accent color instead of primary).

---

## FAQ

**"I changed a color but the site looks the same."**
Clear any caching plugin / CDN, and check whether the element is styled by the
**Kadence Global Palette** instead (header/footer buttons often are). Set the brand
color in both places.

**"Where do fonts go?"**
Font *sizes* are here; font *families* are best set in the Kadence Customizer / Site
Editor. Kadence has first-class font management (Google/local fonts).

**"Can I add a new color token?"**
Yes — add an entry with a new unique `slug` to `settings.color.palette`. It becomes a
picker swatch and a `--wp--preset--color--<slug>` variable immediately.

**"Do I edit the Kadence parent's theme.json?"**
Never. Only this child's. Yours overrides the parent.
