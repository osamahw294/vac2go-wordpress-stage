templates/ — intentionally (almost) empty
==========================================

This child theme inherits ALL block templates from the Kadence parent:
index, single, page, archive, search, 404, header/footer template parts, etc.

Do NOT copy Kadence templates in here just to have them. Override a template
only when the client genuinely needs different structure that the Site Editor
and patterns cannot achieve. Overriding unnecessarily means you now own that
template forever and miss the parent's improvements.

If you DO add an override:
  - Use the block (HTML) template format, e.g. templates/page-landing.html
  - Use valid block grammar (<!-- wp:... --> ... <!-- /wp:... -->)
  - Prefer a custom template (selectable per page) over replacing a core one
  - Register template part areas via theme.json if you add parts/

Rule of thumb: reach for a PATTERN (patterns/) or the Site Editor first.
