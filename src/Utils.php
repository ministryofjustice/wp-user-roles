<?php
namespace MOJDigital\UserRoles;

use \WP_Roles;

class Utils {
    /**
     * Return the existing global $wp_roles object if it exists.
     * If not, create it.
     *
     * @return WP_Roles object
     */
    public static function getWpRolesObject() {
        global $wp_roles;
        if ( ! isset( $wp_roles ) ) {
            $wp_roles = new WP_Roles();
        }
        return $wp_roles;
    }
    /**
    * Check if role exists
    *
    * @param $role
    * @return bool
    */
    public static function roleExists( $role ) {
        $obj = self::getWpRolesObject()->get_role( $role );
        return !is_null( $obj );
    }
    /**
     * Rename a user role
     *
     * @param $role
     * @param $name
     */
    public static function renameRole( $role, $name ) {
        self::getWpRolesObject()->roles[$role]['name'] = $name;
        self::getWpRolesObject()->role_names[$role] = $name;
    }
    /**
     * Get the name of a role
     *
     * @param $role
     * @return bool
     */
    public static function roleName( $role ) {
        $names = self::getWpRolesObject()->get_names();
        if ( isset( $names[$role])  ) {
            return $names[$role];
        } else {
            return false;
        }
    }
}

