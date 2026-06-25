<?php
/**
 * AuraCRM - Global Router
 * Routes authenticated requests to the Dashboard and guest requests to Login.
 */

require_once __DIR__ . '/config.php';

// If execution reaches here, the user has a valid active session.
header("Location: dashboard.php");
exit;
