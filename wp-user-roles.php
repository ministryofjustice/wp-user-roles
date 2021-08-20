<?php
/**
 * Plugin name: MOJ Digital WP User Roles
 * Version: 1.0.0
 * Description: A WordPress plugin to create user roles typically required by a MOJ Digital WordPress site.
 *
 * This renames the 'Administrator' role to 'Digital Webmaster',
 * and creates a new role 'Web Administrator' which extends Editor role adding support for:
 *   – managing navigation menus
 *   – managing users
 */

namespace MoJDigital\UserRoles;

// KEEP SYNC'D WITH THE VERSION ABOVE
define('MOJ_USER_ROLES_VERSION', '1.0.0');
// Turn debug mode on or off
define('MOJ_USER_ROLES_DEBUG', false);
// get the directory
define('MOJ_USER_ROLES_DIR', __DIR__);

include 'src/Utils.php';
include 'src/SiteManager.php';
include 'src/hooks.php';
include 'src/settings.php';

// Instantiate new roles
SiteManager::createRole();

// Legacy Rename - to remove once it has been renamed back.
$administratorName = 'Administrator';
if (Utils::roleName('administrator') !== $administratorName) {
    Utils::renameRole('administrator', $administratorName);
}

// used for testing output - keep on, update constant above
Utils::clear_debug();
// Apply filters and actions
Hooks::apply();
