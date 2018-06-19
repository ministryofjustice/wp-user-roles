<?php

namespace MOJDigital\UserRoles;

class WebAdministrator {
    /**
     * Add new role Web Administrator.
     * Inherit capabilities from Editor role.
     */
    private static function addNewAdministratorRole() {
        $editor = Utils::getWpRolesObject()->get_role( 'editor' );
        // Add a new role with editor caps
        $web_administrator = Utils::getWpRolesObject()->add_role( 'web-administrator', 'Web Administrator', $editor->capabilities );
        // Additional capabilities which this role should have
        $additionalCapabilities = [
            'list_users',
            'create_users',
            'edit_users',
            'delete_users',
            'edit_theme_options',
        ];
        foreach ( $additionalCapabilities as $cap ) {
            $web_administrator->add_cap( $cap );
        }
    }

    /**
     * Create the role web-administrator if it does not exist
     * and stop this user from editing the theme.
     */
    public static function createRole() {
        // Add web-administrator role if required
        if ( ! Utils::roleExists( 'web-administrator' ) ) {
            self::addNewAdministratorRole();
        }
    }
}
