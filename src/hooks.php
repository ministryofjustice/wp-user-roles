<?php

namespace MOJDigital\UserRoles;

use \WP_User;

/**
 * Class Hooks
 *
 * Some required functionality for the user roles can only be achieved through the use of WordPress's hook API.
 * All the required filters and actions for the new user roles are defined and registered using this class.
 *
 * @package MOJDigital\UserRoles
 */
class Hooks {

    /**
     * Filter editable_roles
     * Remove 'Administrator' from the list of roles if the current user is not an admin.
     *
     * @param array $roles
     *
     * @return array
     */
    public static function filterEditableRoles( $roles ) {
        if ( isset( $roles['administrator'] ) && ! current_user_can( 'administrator' ) ) {
            unset( $roles['administrator'] );
        }
        uasort( $roles, function ( $a, $b ) {
            return ( count( $a['capabilities'] ) - count( $b['capabilities'] ) ) * - 1;
        } );

        return $roles;
    }


    /**
     * Determine if the current user is allowed to edit/delete/manage the specified user.
     * Non-administrators cannot edit administrators.
     *
     * @param int $actorId The user performing the action (i.e. the user performing the edit/deletion)
     * @param int $subjectId The user being acted upon (i.e. the user being edited/deleted)
     *
     * @return bool
     */
    public static function disallowNonAdminsToEditAdmins( $actorId, $subjectId ) {
        $actor   = new WP_User( $actorId );
        $subject = new WP_User( $subjectId );

        return (
            ! $actor->has_cap( 'administrator' ) &&
            $subject->has_cap( 'administrator' )
        );
    }

    /**
     * Filter PreventModificationOfAdminUser
     * Map meta capabilities to capabilities
     * If someone is trying to edit or delete an admin and that user isn't an admin, don't allow it.
     *
     * @param $caps
     * @param $cap
     * @param $user_id
     * @param $args
     *
     * @return array
     */
    public static function filterPreventModificationOfAdminUser( $caps, $cap, $user_id, $args ) {
        $mapCaps = [
            'edit_user',
            'remove_user',
            'promote_user',
            'delete_user',
            'delete_users',
        ];
        if (
            in_array( $cap, $mapCaps ) &&
            isset( $args[0] ) &&
            self::disallowNonAdminsToEditAdmins( $user_id, $args[0] )
        ) {
            $caps = [ 'do_not_allow' ];
        }

        return $caps;
    }

    /**
     * Prevent non-administrator users from accessing the `Appearance` > `Themes` sub-menu
     */
    public static function actionRestrictAppearanceThemesMenu() {
        if ( ! current_user_can( 'administrator' ) ) {
            remove_submenu_page( 'themes.php', 'themes.php' );
        }
    }

    /**
     * Register actions and filters for the new roles.
     */
    public static function apply() {
        add_filter( 'editable_roles', __CLASS__ . '::filterEditableRoles', 10, 1 );
        add_filter( 'map_meta_cap',   __CLASS__ . '::filterPreventModificationOfAdminUser', 10, 4 );
        add_action( 'admin_menu',     __CLASS__ . '::actionRestrictAppearanceThemesMenu', 999 );
    }
}
