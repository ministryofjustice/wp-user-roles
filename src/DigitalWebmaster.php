<?php

namespace MOJDigital\UserRoles;

class DigitalWebmaster
{
    /**
     * Rename administrator role if required
     */
    public static function renameAdministratorRole()
    {
        $administratorName = 'Digital Webmaster';
        if (Utils::roleName('administrator') !== $administratorName) {
            Utils::renameRole('administrator', $administratorName);
        }
    }

    /**
     * Create the new DigitalWebmaster role
     *
     * In practice this just renames the default administrator role.
     */
    public static function createRole()
    {
        add_action('init', __NAMESPACE__ . '\DigitalWebmaster::renameAdministratorRole');
    }
}
