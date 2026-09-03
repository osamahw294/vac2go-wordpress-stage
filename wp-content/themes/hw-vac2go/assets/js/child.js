/**
 * HW Kadence Starter — child front-end script.
 * ==========================================================================
 * WHAT THIS FILE IS
 *   The child theme's JavaScript. Enqueued (deferred, in the footer, with auto
 *   cache-busting) by inc/enqueue.php. It is intentionally minimal.
 *
 * HOW TO ADD BEHAVIOR SAFELY
 *   - Write your code INSIDE the IIFE below (under the marked line) so nothing
 *     leaks into the global scope. 'use strict' is already on.
 *   - DEPENDENCY-FREE: use plain DOM APIs (querySelector, addEventListener).
 *     Do NOT reach for jQuery — it is not enqueued as a dependency, on purpose.
 *   - The script is DEFERRED, so the DOM is already parsed when this runs —
 *     no need to wrap things in DOMContentLoaded.
 *   - Heavy or page-specific logic? Put it in its OWN file and enqueue it
 *     conditionally in inc/enqueue.php (e.g. only on the front page) instead of
 *     bloating this always-loaded file.
 * ==========================================================================
 */
( function () {
	'use strict';

	// Scaffold: mark <html> so CSS can target JS-enabled sessions. Harmless to
	// keep; replace or extend as needed.
	document.documentElement.classList.remove( 'no-js' );
	document.documentElement.classList.add( 'hw-starter-js' );

	// ▼▼▼  ADD PROGRESSIVE-ENHANCEMENT CODE BELOW THIS LINE  ▼▼▼

}() );
