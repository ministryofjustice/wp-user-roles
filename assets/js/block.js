/**
 * Accesses the Gutenburb blocks API to render a notice
 *
 * namespace = moj/wp-user-roles/notices
 */

import {addAction, doAction} from '@wordpress/hooks';

const myFunction = ( arg1, arg2 ) => {
    console.log( arg1, arg2 ); // Should output 'Hello' 'Hola'
};

addAction( 'action_name', 'function_name', myFunction );

doAction( 'action_name', 'Hello', 'Hola' );


/*
function actionHookTest()
{
  console.log('WE HAVE ACTION');
}

( function( wp ) {

  console.log('The object WP is equal to: ', wp);

  wp.hooks.addAction('transition_post_status', 'moj/wp-user-roles/notices', 'actionHookTest', 99);

  if (wp.data) {
    wp.data.dispatch('core/notices').createNotice(
      'error', // Can be one of: success, info, warning, error.
      'There are undesirable results to and has been stopped to protect the website.', // Text string to display.
      {
        isDismissible: true, // Whether the user can dismiss the notice.
        // Any actions the user can perform.
        actions: []
      }
    );
  }
} )( window.wp );
*/


