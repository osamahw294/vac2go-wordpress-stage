<?php
/* Plugin Name: HW RGForms Compat
 * Restores the RGForms alias removed in Gravity Forms 3.0 so legacy GF add-ons keep working. 2026-08-03.
 */
add_action('plugins_loaded', function () {
    if (class_exists('GFForms') && !class_exists('RGForms')) { class_alias('GFForms', 'RGForms'); }
}, 1);
