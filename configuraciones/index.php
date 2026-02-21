<?php
/**
 * Stub DirectoryIndex for the /configuraciones route.
 *
 * On LiteSpeed / cPanel shared hosting the web server evaluates
 * Options -Indexes for real directories BEFORE mod_rewrite rules are
 * processed.  If a 'configuraciones/' directory exists at the app root
 * (created during deployment or by a previous developer) LiteSpeed returns
 * HTTP 403 immediately and PHP never runs.
 *
 * This file acts as the DirectoryIndex so the server hands the request to
 * PHP instead of generating a 403.  It then routes the request through the
 * main application router exactly as if the user had hit /configuraciones.
 *
 * All sub-paths (/configuraciones/auditoria, /configuraciones/backups, …)
 * are NOT directories, so they continue to be handled by the root
 * .htaccess RewriteRule and never reach this file.
 */

// Fix SCRIPT_NAME so Config::getBaseUrl() computes BASE_URL relative to
// the application root, not this 'configuraciones' subdirectory.
// Use a suffix-anchored replacement so only the trailing occurrence is removed.
$_SERVER['SCRIPT_NAME'] = preg_replace(
    '#/configuraciones/index\.php$#',
    '/index.php',
    $_SERVER['SCRIPT_NAME'] ?? '/index.php'
);

// Tell the router which route to dispatch.
$_GET['url'] = 'configuraciones';

// Bootstrap the application.
$bootstrap = dirname(__DIR__) . '/index.php';
if (!file_exists($bootstrap)) {
    http_response_code(500);
    exit('Application bootstrap not found: ' . htmlspecialchars($bootstrap));
}
require_once $bootstrap;
