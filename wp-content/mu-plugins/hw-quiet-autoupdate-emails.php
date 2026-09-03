<?php
/* Plugin Name: HW Quiet Auto-Update Emails
 * Suppresses WordPress's "Some plugins were automatically updated" client emails. Highwater
 * tracks all updates centrally (the morning security digest), so clients don't need these. */
add_filter('auto_plugin_update_send_email', '__return_false');
add_filter('auto_theme_update_send_email', '__return_false');
add_filter('auto_core_update_send_email', '__return_false');
