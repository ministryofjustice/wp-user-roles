<?php
/**
 * @internal never define functions inside callbacks.
 * these functions could be run multiple times; this would result in a fatal error.
 */

use MOJDigital\UserRoles\Utils;

/**
 * custom option and settings
 */
function moj_user_roles_settings_init()
{
    // register a new setting for "moj_user_roles_options" page
    register_setting('moj_user_roles_options', 'moj_user_roles_data');

    // register a new section in the "moj_user_roles" page
    add_settings_section(
        'moj_user_roles_section_heading',
        __('Settings and Information', 'moj_user_roles'),
        'moj_user_roles_section_header_cb',
        'moj_user_roles'
    );

    add_settings_section(
        'moj_user_roles_section_history',
        __('Flush User Permissions - History', 'moj_user_roles'),
        'moj_user_roles_section_version_history_cb',
        'moj_user_roles'
    );

}

/**
 * register our moj_user_roles_settings_init to the admin_init action hook
 */
add_action('admin_init', 'moj_user_roles_settings_init', 11);

/**
 * custom option and settings:
 * callback functions
 */

// developers section cb

// section callbacks can accept an $args parameter, which is an array.
// $args have the following keys defined: title, id, callback.
// the values are defined at the add_settings_section() function.
function moj_user_roles_section_header_cb($args)
{
    ?>
    <p id="<?php echo esc_attr($args['id']); ?>"><?php esc_html_e('Plugin settings can be found here.',
            'moj_user_roles'); ?></p>
    <?php
}

function moj_user_roles_section_version_history_cb($args)
{
    // get the value of the setting we've registered with register_setting()
    $options = get_option('moj_user_roles_data', []);
    $debugs = get_option('moj_user_roles_data_debug', []);
    // output the data
    ?>
    <p id="<?php echo esc_attr($args['id']); ?>"><?php esc_html_e('This section will show you the history of changes and the last time user roles were regenerated.',
            'moj_user_roles'); ?></p>
    <table class="moj-table">
        <thead>
        <tr>
            <th>Date</th>
            <th>Version</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $options = array_reverse($options);
        foreach ($options as $option) {
            ?>
            <tr class="roles-update-data">
                <td class="date"><?php echo date('d / m / y -- G:i', $option['datetime']) ?></td>
                <td class="date"><?php echo $option['version'] ?></td>
            </tr>
            <?php
        }

        ?>
        </tbody>
    </table>

    <?php if (!empty($debugs)) { ?>
    <p>----------------------------------------</p>
    <h2>Debug Data</h2>
    <table class="moj-table">
        <thead>
        <tr>
            <th>Stage</th>
            <th>Result</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($debugs as $debug) {
            ?>
            <tr class="roles-update-data">
                <td class="date"><?php echo $debug['stage'] ?></td>
                <td class="date"><?php echo $debug['result'] ?></td>
            </tr>
            <?php
        }

        ?>
        </tbody>
    </table>
    <?php
    }
}

/**
 * top level menu
 */
function moj_user_roles_options_page()
{
    // add top level menu page
    add_options_page(
        'MOJ User Roles',
        'MOJ User Roles',
        'manage_options',
        'moj_user_roles',
        'moj_user_roles_options_page_html'
    );
}

/**
 * register our moj_user_roles_options_page to the admin_menu action hook
 */
add_action('admin_menu', 'moj_user_roles_options_page');

/**
 * top level menu:
 * callback functions
 */
function moj_user_roles_options_page_html()
{
    // check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }


    if (isset($_GET['admin-script'])) {
        if($_GET['admin-script'] == 'remove-web-admin'){
            Utils::removeRole('web-administrator');
        }
    }
    // add error/update messages

    // check if the user have submitted the settings
    // wordpress will add the "settings-updated" $_GET parameter to the url
    if (isset($_GET['settings-updated'])) {
        // add settings saved message with the class of "updated"
        add_settings_error('moj_user_roles_messages', 'moj_user_roles_message',
            __('User profiles have been flushed', 'moj_user_roles'), 'updated');
    }

    // show error/update messages
    settings_errors('moj_user_roles_messages');
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            // output security fields for the registered setting "moj_user_roles"
            settings_fields('moj_user_roles');
            // output setting sections and their fields
            // (sections are registered for "moj_user_roles", each field is registered to a specific section)
            do_settings_sections('moj_user_roles');
            // output save settings button
            // submit_button('Save Settings');
            ?>
        </form>
    </div>
    <?php
}


