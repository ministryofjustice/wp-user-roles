# WP User Roles

A WordPress plugin to create user roles typically required 
by a MOJ Digital WordPress site.

## Roles
### 1. Web Administrator
This role is similar to the standard Editor role
except that is has the additional following capabilities

* `list_users`
* `create_users`
* `edit_users`
* `delete_users`
* `edit_theme_options`

This role is typically assigned to user accounts of stakeholders 
that are tasked with maintaining the relevant site.

This role is prevented from accessing the _Appearance_ > _Theme_ submenu.

This role is prevented from creating new `adminstrator` roles.
It is also prevented from modifying existing `adminstrator` roles.

### 2. Digital Webmaster
This role is the `administrator` role. It has simply been renamed.

## Installation
Use the standard method of installing plugins for your site.
For example go to _Plugins_ > _Add new_ > _Upload Plugin_.

Or use if using _Composer_, add `"ministryofjustice/wp-user-roles": "*"`
to the `require` section of `composer.json`.

Once the plugin folder is in place activate it by going to `wp-admin/plugins.php`
and clicking on the _Activate_ link under _MOJ Digital Custom User Roles_.
