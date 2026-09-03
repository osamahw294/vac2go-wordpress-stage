<?php
/*
Plugin Name: HW Recovery-Mode Mail Redirect
Description: Redirects WordPress's built-in "Your Site is Experiencing a Technical Issue" fatal-error /
recovery-mode emails AWAY from the client's admin_email TO Highwater, so clients don't receive alarming
automated error emails and WE get real-time visibility into any plugin/theme fatal instead. Reversible:
delete this file. (HW 2026-09-02)
*/
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Where fatal-error / recovery-mode alerts should go instead of the client.
if ( ! defined( 'HW_RECOVERY_ALERT_TO' ) ) {
    define( 'HW_RECOVERY_ALERT_TO', 'support@wearehighwater.com' );
}

/* WordPress builds the recovery-mode email through this filter. Rewrite the recipient (and tag the
   subject with the site host so a fleet-wide inbox stays sortable). Everything else is left intact. */
add_filter( 'recovery_mode_email', function ( $email ) {
    $host = wp_parse_url( home_url(), PHP_URL_HOST );
    $email['to'] = HW_RECOVERY_ALERT_TO;
    if ( isset( $email['subject'] ) && $host ) {
        $email['subject'] = '[HW fatal: ' . $host . '] ' . $email['subject'];
    }
    return $email;
}, 999 );
