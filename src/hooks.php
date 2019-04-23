<?php

namespace MOJDigital\UserRoles;

use WP_User;

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
        if (isset($roles['administrator']) && !current_user_can('administrator')) {
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
        $actor = new WP_User($actorId);
        $subject = new WP_User($subjectId);

        return (
            !$actor->has_cap('administrator') &&
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

    public static function updateRoleMaybe()
    {
        $options = get_option('moj_user_roles_data', []);

        // create a preliminary record
        $data = [
            'datetime' => time(),
            'version' => MOJ_USER_ROLES_VERSION
        ];

        if (!empty($options)) {
            $last_entry = end($options);

            Utils::debug('last_entry_' . __LINE__, $last_entry);

            // make sure we have an entry to work with...
            if (!isset($last_entry['version'])) {
                return false;
            }

            Utils::debug('last_entry_exists_' . __LINE__, true);

            // only update if we have a difference
            if ($last_entry['version'] !== MOJ_USER_ROLES_VERSION) {
                Utils::debug('difference_found_' . __LINE__,
                    ['last_version' => $last_entry['version'], 'now_version' => MOJ_USER_ROLES_VERSION]);
                if (Utils::removeRole('web-administrator') === true) {
                    array_push($options, $data);

                    if (update_option('moj_user_roles_data', $options)) {
                        Utils::debug('History Updated ' . __LINE__, 'true');
                    }

                    // recreate the role
                    WebAdministrator::createRole();
                } else {
                    Utils::debug('role_not_removed_' . __LINE__,
                        ['last_version' => $last_entry['version'], 'now_version' => MOJ_USER_ROLES_VERSION]);
                }
            }

        } else {
            // should occur once, or if the option 'moj_user_roles_data' is empty
            array_push($options, $data);
            update_option('moj_user_roles_data', $options);
            return true;
        }

        return null;
    }

    public static function loadAssets()
    {
        wp_register_style('moj_user_roles', plugins_url('src/assets/main.css', dirname(__FILE__)), false,
            MOJ_USER_ROLES_VERSION);
        wp_enqueue_style('moj_user_roles');

    }

    /**
     * Register actions and filters for the new roles.
     */
    public static function apply()
    {
        add_filter('editable_roles', __CLASS__ . '::filterEditableRoles', 10, 1);
        add_filter('map_meta_cap', __CLASS__ . '::filterPreventModificationOfAdminUser', 10, 4);
        add_action('admin_menu', __CLASS__ . '::actionRestrictAppearanceThemesMenu', 999);
        add_action('admin_init', __CLASS__ . '::updateRoleMaybe', 10);
        add_action('admin_enqueue_scripts', __CLASS__ . '::loadAssets', 10);
    }
}
