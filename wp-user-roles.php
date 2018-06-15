<?php
/**
 * Plugin name: MOJ Digital Custom User Roles
 * Description: Configure MOJ Digital user roles.
 * Version: 1.0
 *
 * This renames the 'Administrator' role to 'Webmaster',
 * and creates a new role 'Web Administrator' which extends Editor role adding support for:
 *   – managing navigation menus
 *   – managing users
 */
namespace MOJDigital\UserRoles;

include 'Utils.php';
include 'DigitalWebmaster.php';
include 'WebAdministrator.php';

use \WP_User;

/**
 * Filter editable_roles
 * Remove 'Administrator' from the list of roles if the current user is not an admin.
 *
 * @param array $roles
 * @return array
 */
 function filterEditableRoles($roles) {
    if (isset($roles['administrator']) && !current_user_can('administrator')) {
        unset($roles['administrator']);
    }
    uasort($roles, function($a, $b) {
        return (count($a['capabilities']) - count($b['capabilities'])) * -1;
    });
    return $roles;
}
/**
 * Filter map_meta_cap
 * Map meta capabilities to capabilities
 * If someone is trying to edit or delete an admin and that user isn't an admin, don't allow it.
 *
 * @param $caps
 * @param $cap
 * @param $user_id
 * @param $args
 * @return array
 */
function filterMapMetaCap($caps, $cap, $user_id, $args) {
    $mapCaps = [
        'edit_user',
        'remove_user',
        'promote_user',
        'delete_user',
        'delete_users',
    ];
    if (
        in_array($cap, $mapCaps) &&
        isset($args[0]) &&
        disallowEditUser($user_id, $args[0])
    ) {
        $caps[] = 'do_not_allow';
    }
    return $caps;
}

/**
* Determine if the current user is allowed to edit/delete/manage the specified user.
* Non-administrators cannot edit administrators.
*
* @param int $actorId The user performing the action (i.e. the user performing the edit/deletion)
* @param int $subjectId The user being acted upon (i.e. the user being edited/deleted)
* @return bool
*/
function disallowEditUser($actorId, $subjectId) {
    $actor = new WP_User($actorId);
    $subject = new WP_User($subjectId);
    return (
        !$actor->has_cap('administrator') &&
        $subject->has_cap('administrator')
    );
}

// Instantiate new roles
WebAdministrator::createRole();
DigitalWebmaster::createRole();

// Register hooks and filters.
add_filter('editable_roles',  'MOJDigital\UserRoles\filterEditableRoles');
add_filter('map_meta_cap', 'MOJDigital\UserRoles\filterMapMetaCap', 10, 4);
