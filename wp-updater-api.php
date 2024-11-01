<?php
/*
Plugin Name: WP Updater API
Plugin URI: https://github.com/sideshowcoder/wp-updater-api
Description: Plugin to expose an API to remotely check WP sites if updates are available
Author: Philipp Fehre
Version: 1.1
Author URI: http://sideshowcoder.com/
License: MIT
 */

// Menu
add_action('admin_menu', 'register_wp_updater_api_key_page');
function register_wp_updater_api_key_page(){
  add_menu_page(
    'Updater API Key', 'Updater API Key',
    'manage_options', 'updater_api_key',
    'wp_updater_api_key_page', plugins_url('wp-updater-api/images/menu_icon.png')
  );
}

function _wp_updater_api_generate_key($length=32, $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')
{
  $key = '';
  $count = strlen($charset);
  while ($length--) {
    $key .= $charset[mt_rand(0, $count-1)];
  }
  return $key;
}

function wp_updater_api_key_page(){
  $api_key = get_option('wp_updater_api_key');
  $regenerate = (isset($_POST['regenerate-key']) && $_POST['regenerate-key'] == 'Y');
  if(!$api_key || $regenerate) {
    $api_key = _wp_updater_api_generate_key();
    update_option('wp_updater_api_key', $api_key);
  }
// display and regenerate key
?>
  <div class='tool-box'>
  <h1>Updater API Key</h1>'
  <p><?php echo $api_key ?></p>
  <form name="wp-updater-api-key-form" method="post" action="" id="wp-updater-api-key-form">
  <form name="wp-updater-api-key-form" method="post" action="" id="wp-updater-api-key-form">
    <input type="hidden" name="regenerate-key" value="Y">
  <p class="submit">
    <input type="submit" class="button" value="Regenerate API Key" />
  </p>
  </form>
  </div>
<?php

}

// API
add_filter('xmlrpc_methods', 'wp_updater_api_methods');
function wp_updater_api_methods($methods) {
  $methods['getCoreVersion'] = 'wp_updater_get_core_version';
  $methods['getCoreUpdatesAvailable'] = 'wp_updater_get_core_updates_available';
  $methods['getPluginUpdatesAvailable'] = 'wp_updater_get_plugin_updates_available';
  return $methods;
}

function _check_credentials($args) {
  $api_key = $args;
  $stored_key = get_option('wp_updater_api_key');

  // Let's run a check to see if credentials are okay
  if ( $api_key != $stored_key  ) {
    return "Invalid API Key";
  } else {
    return true;
  }
}

function wp_updater_get_core_version($args) {
  if( ($check = _check_credentials($args)) !== true ) return $check;
  return array('version' => get_bloginfo('version'));
}

function wp_updater_get_core_updates_available($args) {
  if( ($check = _check_credentials($args)) !== true ) return $check;

  do_action("wp_version_check"); // make WP check for updates
  $update_core = get_site_transient("update_core");
  $updates = array();
  if('upgrade' == $update_core->updates[0]->response) {
    $new_core_ver = $update_core->updates[0]->current;
    $old_core_ver = get_bloginfo('version');
    $updates = array( 'installed' => $old_core_ver, 'current' => $new_core_ver );
  }

  return $updates;
}

function wp_updater_get_plugin_updates_available($args) {
  if( ($check = _check_credentials($args)) !== true ) return $check;

  do_action("wp_update_plugins"); // make WP check plugins for updates
  $update_plugins = get_site_transient('update_plugins');
  $updates = array();
  if(!empty($update_plugins->response)) {
    $plugins_need_update = $update_plugins->response;
    if(count($plugins_need_update) >= 1) {
      require_once(ABSPATH . 'wp-admin/includes/plugin-install.php'); // Required for plugin API
      foreach($plugins_need_update as $key => $data) {
        $plugin_info = get_plugin_data(WP_PLUGIN_DIR . "/" . $key);
        $updates[] = array(
          'plugin' => $plugin_info['Name'],
          'installed' => $plugin_info['Version'],
          'current' => $data->new_version
        );
      }
    }
  }
  return $updates;
}

