<?php
/**
 * Plugin name: MOJ Digital Custom User Roles
 * Version: 0.1.2
 * Description: A WordPress plugin to create user roles typically required by a MOJ Digital WordPress site.
 *
 * This renames the 'Administrator' role to 'Webmaster',
 * and creates a new role 'Web Administrator' which extends Editor role adding support for:
 *   – managing navigation menus
 *   – managing users
 */

namespace MOJDigital\UserRoles;

include 'src/Utils.php';
include 'src/DigitalWebmaster.php';
include 'src/WebAdministrator.php';
include 'src/hooks.php';

// Instantiate new roles
WebAdministrator::createRole();
DigitalWebmaster::createRole();

// Apply filters and actions
Hooks::apply();
