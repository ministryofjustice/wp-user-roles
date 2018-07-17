<?php

namespace MOJDigital\UserRoles;

use WP_User;
use WP_Privacy_Policy_Content;

/**
 * Class Hooks
 *
 * Some required functionality for the user roles can only be achieved through the use of WordPress's hook API.
 * All the required filters and actions for the new user roles are defined and registered using this class.
 *
 * @package MOJDigital\UserRoles
 */
class Hooks
{

    /**
     * Filter editable_roles
     * Remove 'Administrator' from the list of roles if the current user is not an admin.
     *
     * @param array $roles
     *
     * @return array
     */
    public static function filterEditableRoles($roles)
    {
        if (isset($roles['administrator']) && ! current_user_can('administrator')) {
            unset($roles['administrator']);
        }
        uasort($roles, function ($a, $b) {
            return (count($a['capabilities']) - count($b['capabilities'])) * -1;
        });

        return $roles;
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
    public static function filterPreventModificationOfAdminUser($caps, $cap, $user_id, $args)
    {
        $mapCaps = [
            'edit_user',
            'remove_user',
            'promote_user',
            'delete_user',
            'delete_users',
        ];
        if (in_array($cap, $mapCaps) &&
            isset($args[0]) &&
            self::disallowNonAdminsToEditAdmins($user_id, $args[0])
        ) {
            $caps = ['do_not_allow'];
        }

        return $caps;
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
    public static function disallowNonAdminsToEditAdmins($actorId, $subjectId)
    {
        $actor   = new WP_User($actorId);
        $subject = new WP_User($subjectId);

        return (
            ! $actor->has_cap('administrator') &&
            $subject->has_cap('administrator')
        );
    }

    /**
     * Prevent non-administrator users from accessing the `Appearance` > `Themes` sub-menu
     */
    public static function actionRestrictAppearanceThemesMenu()
    {
        if (!current_user_can('administrator')) {
            remove_submenu_page('themes.php', 'themes.php');
        }
    }

    /**
     * Filter map_meta_cap to add support for 'manage_privacy_options' capability
     *
     * By default, WordPress maps the cap 'manage_privacy_options' to 'manage_options'
     * However we grant 'manage_privacy_options' as a capability in its own right
     * to some user roles (i.e. Web Administrators).
     * So if the user does not have 'manage_options' capability (i.e. if they're not an admin),
     * then don't map 'manage_privacy_options' to 'manage_options'
     * In practice, this is implemented as mapping 'manage_privacy_options' to
     * itself ('manage_privacy_options')
     *
     * @param array $caps The user's actual capabilities.
     * @param string $cap Capability name.
     * @param int $user_id The user id.
     * @param array $args Adds the context to the cap. Typically the object ID.
     *
     * @return array The user's actual capabilities.
     */
    public static function managePrivacyOptionsCap($caps, $cap, $user_id, $args)
    {
        if ($cap == 'manage_privacy_options' && !user_can($user_id, 'manage_options')) {
            $caps = ['manage_privacy_options'];
        }
        return $caps;
    }

    /**
     * Give a 'Privacy Settings' admin menu item to users who have permission to
     * 'manage_privacy_options' but not to 'manage_options'.
     * Without this, the 'Privacy Settings' page becomes inaccessible due to the
     * user not having permission to access the top-level 'Settings' page.
     */
    public static function privacySettingsAdminMenu()
    {
        global $menu, $submenu;

        // Don't change anything if this is an admin user
        // Leave the Privacy submenu item under the Settings menu
        if (current_user_can('manage_options')) {
            return;
        }

        // phpcs:disable
        // Shamelessly stolen from WordPress's original implementation of the Settings menu
        // This will notify when there are suggested changes for the Privacy page
        // Source: wp-admin/menu.php
        // Link: https://github.com/WordPress/WordPress/blob/d1802a68ec7a3536f744d45afb72660e4fef0292/wp-admin/menu.php#L252-L255
        $change_notice = '';
        if ( current_user_can( 'manage_privacy_options' ) && WP_Privacy_Policy_Content::text_change_check() ) {
            $change_notice = ' <span class="update-plugins 1"><span class="plugin-count">' . number_format_i18n( 1 ) . '</span></span>';
        }
        // phpcs:enable

        // Add a new Privacy menu item directly after Settings
        // In this case, we know Settings will be hidden anyway, so this will
        // effectively sit in its place
        $menu[81] = [
            sprintf('Privacy %s', $change_notice),
            'manage_privacy_options',
            'privacy.php',
            '',
            'menu-top menu-icon-settings',
            'menu-settings',
            'dashicons-shield',
        ];

        // Move the Privacy submenu item from Settings to our new Privacy menu item
        $submenu['privacy.php'][10] = $submenu['options-general.php'][45];
        unset($submenu['options-general.php'][45]);
    }

    /**
     * Register actions and filters for the new roles.
     */
    public static function apply()
    {
        add_filter('editable_roles', __CLASS__ . '::filterEditableRoles', 10, 1);
        add_filter('map_meta_cap', __CLASS__ . '::filterPreventModificationOfAdminUser', 10, 4);
        add_action('admin_menu', __CLASS__ . '::actionRestrictAppearanceThemesMenu', 999);
        add_filter('map_meta_cap', __CLASS__ . '::managePrivacyOptionsCap', 10, 4);
        add_action('_admin_menu', __CLASS__ . '::privacySettingsAdminMenu');
    }
}
