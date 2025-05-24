<?php
/*
  Plugin Name: OpenStreetMaps Plugin
  Plugin URI: https://osclasspoint.com/osclass-plugins/extra-fields-and-other/openstreetmaps-osclass-plugin-i99
  Description: OpenStreetMaps plugin add map features (select location from map, show items on map, search in radius, ...) to your classifieds
  Version: 1.7.7
  Author: MB Themes
  Author URI: https://osclasspoint.com
  Author Email: info@osclasspoint.com
  Short Name: openstreetmaps
  Plugin update URI: openstreetmaps
  Support URI: https://forums.osclasspoint.com/openstreetmaps-osclass-plugin/
  Product Key: mNqtMxg1zBJRwIDKdLuM
*/

require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'model/ModelOSM.php';
require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'functions.php';


  
// INSTALL FUNCTION - DEFINE VARIABLES
function osm_call_after_install() {
  osc_set_preference('token', '', 'plugin-osm', 'STRING');
  // osc_set_preference('hook', 1, 'plugin-osm', 'INTEGER');

  osc_set_preference('hook_item_enabled', 1, 'plugin-osm', 'BOOLEAN');
  osc_set_preference('hook_item', 'location', 'plugin-osm', 'STRING');
  osc_set_preference('hook_home_enabled', 1, 'plugin-osm', 'BOOLEAN');
  osc_set_preference('hook_home', 'home_premium', 'plugin-osm', 'STRING');
  osc_set_preference('hook_search_enabled', 1, 'plugin-osm', 'BOOLEAN');
  osc_set_preference('hook_search', 'search_items_top', 'plugin-osm', 'STRING');
  osc_set_preference('hook_publish_enabled', 1, 'plugin-osm', 'BOOLEAN');
  osc_set_preference('hook_publish', 'item_publish_images', 'plugin-osm', 'STRING');    //item_form_location
  // osc_set_preference('hook_edit_enabled', 1, 'plugin-osm', 'BOOLEAN');
  // osc_set_preference('hook_edit', 'item_edit_location', 'plugin-osm', 'STRING');
  osc_set_preference('hook_public_profile_enabled', 1, 'plugin-osm', 'BOOLEAN');
  osc_set_preference('hook_public_profile', 'user_public_profile_items_top', 'plugin-osm', 'STRING');
  
  osc_set_preference('coordinate_fields', 1, 'plugin-osm', 'INTEGER');
  osc_set_preference('height_item', 240, 'plugin-osm', 'INTEGER');
  osc_set_preference('height_search', 360, 'plugin-osm', 'INTEGER');
  osc_set_preference('height_home', 480, 'plugin-osm', 'INTEGER');
  osc_set_preference('height_publish', 320, 'plugin-osm', 'INTEGER');
  osc_set_preference('height_user', 320, 'plugin-osm', 'INTEGER');
  osc_set_preference('fullscreen_item', 0, 'plugin-osm', 'INTEGER');
  osc_set_preference('fullscreen_search', 0, 'plugin-osm', 'INTEGER');
  osc_set_preference('fullscreen_home', 0, 'plugin-osm', 'INTEGER');
  osc_set_preference('fullscreen_publish', 0, 'plugin-osm', 'INTEGER');
  osc_set_preference('zoom', 13, 'plugin-osm', 'INTEGER');

  osc_set_preference('rel_enable', 1, 'plugin-osm', 'INTEGER');
  osc_set_preference('rel_max', 30, 'plugin-osm', 'INTEGER');
  osc_set_preference('rel_cat', 1, 'plugin-osm', 'INTEGER');
  osc_set_preference('rel_loc', 1, 'plugin-osm', 'INTEGER');

  osc_set_preference('measure', 'km', 'plugin-osm', 'STRING');
  osc_set_preference('hook_radius', 1, 'plugin-osm', 'INTEGER');
  osc_set_preference('step', 500, 'plugin-osm', 'INTEGER');
  osc_set_preference('publish_map_search_version', 2, 'plugin-osm', 'INTEGER');

  osc_set_preference('random', 20, 'plugin-osm', 'INTEGER');
  osc_set_preference('default_query', '{COUNTRY} {REGION} {ZIP} {CITY} {ADDRESS}', 'plugin-osm', 'STRING');

  osc_set_preference('item_draw_circle', 1, 'plugin-osm', 'BOOLEAN');
  osc_set_preference('item_draw_circle_radius', 500, 'plugin-osm', 'INTEGER');   // meters
  osc_set_preference('item_draw_circle_color', '#0000e8', 'plugin-osm', 'STRING');   // meters

  osc_set_preference('item_map_load_on_click', 0, 'plugin-osm', 'BOOLEAN');

  ModelOSM::newInstance()->install();
}


function osm_call_after_uninstall() {
  ModelOSM::newInstance()->uninstall();
}



// ADMIN MENU
function osm_menu($title = NULL) {
  echo '<link href="' . osc_base_url() . 'oc-content/plugins/openstreetmaps/css/admin.css?v=' . date('YmdHis') . '" rel="stylesheet" type="text/css" />';
  echo '<link href="' . osc_base_url() . 'oc-content/plugins/openstreetmaps/css/bootstrap-switch.css" rel="stylesheet" type="text/css" />';
  echo '<link href="' . osc_base_url() . 'oc-content/plugins/openstreetmaps/css/tipped.css" rel="stylesheet" type="text/css" />';
  echo '<link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />';
  echo '<script src="' . osc_base_url() . 'oc-content/plugins/openstreetmaps/js/admin.js?v=' . date('YmdHis') . '"></script>';
  echo '<script src="' . osc_base_url() . 'oc-content/plugins/openstreetmaps/js/tipped.js"></script>';
  echo '<script src="' . osc_base_url() . 'oc-content/plugins/openstreetmaps/js/bootstrap-switch.js"></script>';



  if( $title == '') { $title = __('Configure', 'openstreetmaps'); }

  $text  = '<div class="mb-head">';
  $text .= '<div class="mb-head-left">';
  $text .= '<h1>' . $title . '</h1>';
  $text .= '<h2>OpenStreetMaps Plugin</h2>';
  $text .= '</div>';
  $text .= '<div class="mb-head-right">';
  $text .= '<ul class="mb-menu">';
  $text .= '<li><a href="' . osc_admin_base_url(true) . '?page=plugins&action=renderplugin&file=openstreetmaps/admin/configure.php"><i class="fa fa-wrench"></i><span>' . __('Configure', 'openstreetmaps') . '</span></a></li>';
  $text .= '<li><a href="' . osc_admin_base_url(true) . '?page=plugins&action=renderplugin&file=openstreetmaps/admin/cache.php"><i class="fa fa-database"></i><span>' . __('Cache Log', 'openstreetmaps') . '</span></a></li>';
  $text .= '</ul>';
  $text .= '</div>';
  $text .= '</div>';

  echo $text;
}



// ADMIN FOOTER
function osm_footer() {
  $pluginInfo = osc_plugin_get_info('openstreetmaps/index.php');
  $text  = '<div class="mb-footer">';
  $text .= '<a target="_blank" class="mb-developer" href="https://osclasspoint.com"><img src="https://osclasspoint.com/favicon.ico" alt="OsclassPoint Market" /> OsclassPoint Market</a>';
  $text .= '<a target="_blank" href="' . $pluginInfo['support_uri'] . '"><i class="fa fa-bug"></i> ' . __('Report Bug', 'openstreetmaps') . '</a>';
  $text .= '<a target="_blank" href="https://forums.osclasspoint.com/"><i class="fa fa-handshake-o"></i> ' . __('Support Forums', 'openstreetmaps') . '</a>';
  $text .= '<a target="_blank" class="mb-last" href="mailto:info@osclasspoint.com"><i class="fa fa-envelope"></i> ' . __('Contact Us', 'openstreetmaps') . '</a>';
  $text .= '<span class="mb-version">v' . $pluginInfo['version'] . '</span>';
  $text .= '</div>';

  return $text;
}



// ADD MENU LINK TO PLUGIN LIST
function osm_admin_menu() {
echo '<h3><a href="#">OpenStreetMaps Plugin</a></h3>
<ul> 
  <li><a style="color:#2eacce;" href="' . osc_admin_render_plugin_url(osc_plugin_path(dirname(__FILE__)) . '/admin/configure.php') . '">&raquo; ' . __('Configure', 'openstreetmaps') . '</a></li>
</ul>';
}


// ADD MENU TO PLUGINS MENU LIST
osc_add_hook('admin_menu','osm_admin_menu', 1);



// DISPLAY CONFIGURE LINK IN LIST OF PLUGINS
function osm_conf() {
  osc_admin_render_plugin( osc_plugin_path( dirname(__FILE__) ) . '/admin/configure.php' );
}

osc_add_hook( osc_plugin_path( __FILE__ ) . '_configure', 'osm_conf' );	


// CALL WHEN PLUGIN IS ACTIVATED - INSTALLED
osc_register_plugin(osc_plugin_path(__FILE__), 'osm_call_after_install');

// SHOW UNINSTALL LINK
osc_add_hook(osc_plugin_path(__FILE__) . '_uninstall', 'osm_call_after_uninstall');

?>