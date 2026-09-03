screenshot.png — must be added manually
=======================================

WordPress shows screenshot.png as the theme's preview tile in
Appearance → Themes. This starter ships WITHOUT one on purpose: a binary PNG
cannot be authored reliably as text, and every client build should get its own.

WHAT TO DROP HERE
  - File name: screenshot.png  (exactly; .jpg is not used by core for this)
  - Recommended size: 1200 x 900 px (4:3). Core displays it at 387x290 but a
    2x source keeps it crisp on retina screens.
  - Keep it under ~1 MB. Show the real homepage or a representative layout.

WHY IT MATTERS
  - A missing screenshot looks unfinished to clients and teammates browsing
    the themes list.
  - Per-client screenshots make it obvious which site a build belongs to when
    several HW child themes are installed.

Delete this note once the real screenshot.png is in place.
