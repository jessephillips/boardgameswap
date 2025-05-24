<?php
  // Create menu
  $title = __('Configure', 'openstreetmaps');
  osm_menu($title);


  // GET & UPDATE PARAMETERS
  // $variable = mb_param_update( 'param_name', 'form_name', 'input_type', 'plugin_var_name' );
  // input_type: check or value

  $token = mb_param_update('token', 'plugin_action', 'value', 'plugin-osm');
  // $hook = mb_param_update('hook', 'plugin_action', 'check', 'plugin-osm');
  $coordinate_fields = mb_param_update('coordinate_fields', 'plugin_action', 'check', 'plugin-osm');
  $height_item = mb_param_update('height_item', 'plugin_action', 'value', 'plugin-osm');
  $height_search = mb_param_update('height_search', 'plugin_action', 'value', 'plugin-osm');
  $height_home = mb_param_update('height_home', 'plugin_action', 'value', 'plugin-osm');
  $height_publish = mb_param_update('height_publish', 'plugin_action', 'value', 'plugin-osm');
  $publish_map_search_version = mb_param_update('publish_map_search_version', 'plugin_action', 'value', 'plugin-osm');

  
  $hook_item_enabled = mb_param_update('hook_item_enabled', 'plugin_action', 'check', 'plugin-osm');
  $hook_item = mb_param_update('hook_item', 'plugin_action', 'value', 'plugin-osm');
  $hook_home_enabled = mb_param_update('hook_home_enabled', 'plugin_action', 'check', 'plugin-osm');
  $hook_home = mb_param_update('hook_home', 'plugin_action', 'value', 'plugin-osm');
  $hook_search_enabled = mb_param_update('hook_search_enabled', 'plugin_action', 'check', 'plugin-osm');
  $hook_search = mb_param_update('hook_search', 'plugin_action', 'value', 'plugin-osm');
  $hook_publish_enabled = mb_param_update('hook_publish_enabled', 'plugin_action', 'check', 'plugin-osm');
  $hook_publish = mb_param_update('hook_publish', 'plugin_action', 'value', 'plugin-osm');
  $hook_public_profile_enabled = mb_param_update('hook_public_profile_enabled', 'plugin_action', 'check', 'plugin-osm');
  $hook_public_profile = mb_param_update('hook_public_profile', 'plugin_action', 'value', 'plugin-osm');
  
  $zoom = mb_param_update('zoom', 'plugin_action', 'value', 'plugin-osm');
  $random = mb_param_update('random', 'plugin_action', 'value', 'plugin-osm');

  $rel_enable = mb_param_update('rel_enable', 'plugin_action', 'check', 'plugin-osm');
  $rel_max = mb_param_update('rel_max', 'plugin_action', 'value', 'plugin-osm');
  $rel_cat = mb_param_update('rel_cat', 'plugin_action', 'check', 'plugin-osm');
  $rel_loc = mb_param_update('rel_loc', 'plugin_action', 'value', 'plugin-osm');

  $hook_radius = mb_param_update('hook_radius', 'plugin_action', 'check', 'plugin-osm');
  $measure = mb_param_update('measure', 'plugin_action', 'value', 'plugin-osm');
  $step = mb_param_update('step', 'plugin_action2', 'value', 'plugin-osm');

  $fullscreen_home = mb_param_update('fullscreen_home', 'plugin_action', 'check', 'plugin-osm');
  $fullscreen_search = mb_param_update('fullscreen_search', 'plugin_action', 'check', 'plugin-osm');
  $fullscreen_item = mb_param_update('fullscreen_item', 'plugin_action', 'check', 'plugin-osm');
  $fullscreen_publish = mb_param_update('fullscreen_publish', 'plugin_action', 'check', 'plugin-osm');

  $item_map_load_on_click = mb_param_update('item_map_load_on_click', 'plugin_action', 'check', 'plugin-osm');
  
  $item_draw_circle = mb_param_update('item_draw_circle', 'plugin_action', 'check', 'plugin-osm');
  $item_draw_circle_radius = mb_param_update('item_draw_circle_radius', 'plugin_action', 'value', 'plugin-osm');
  $item_draw_circle_color = mb_param_update('item_draw_circle_color', 'plugin_action', 'value', 'plugin-osm');


  $default_query = mb_param_update('default_query', 'plugin_action', 'value', 'plugin-osm');
  $default_query = ($default_query <> '' ? $default_query : '{COUNTRY} {REGION} {ZIP} {CITY} {ADDRESS}');

  if(Params::getParam('plugin_action') == 'done' || Params::getParam('plugin_action2') == 'done') {
    message_ok( __('Settings were successfully saved', 'openstreetmaps') );
  }


  // Fill missing coordinates
  if(Params::getParam('what') == 'fill') {
    $items = ModelOSM::newInstance()->getMissing($step);

    $success = 0;
    $fail = 0;

    if(count($items) > 0) {
      foreach($items as $item) {
        $item_id = $item['fk_i_item_id'];
        $cords = osm_cords_insert(array('pk_i_id' => $item_id));

        $lat = $cords['lat'];
        $lng = $cords['lng'];

        if($lat <> 0) {
          $success++;
        } else {
          $fail++;
        }

        sleep(1); // to not overload OSM api, max. cords is 1 per second
      }

      message_ok(sprintf(__('%d coordinates successfully found, %d coordinates not found.', 'openstreetmaps'), $success, $fail));

    } else {
      message_ok( __('There are no coordinates to be filled.', 'openstreetmaps') );
    }
  }


  // Clear all coordinates
  if(Params::getParam('what') == 'clear') {
    ModelOSM::newInstance()->clearCords();
    message_ok( __('Coordinates removed.', 'openstreetmaps') );
  }


  $missing = ModelOSM::newInstance()->getMissingCount();
  $missing_fail = ModelOSM::newInstance()->getMissingCount(true);
?>


<div class="mb-body">

  <!-- CONFIGURE SECTION -->
  <div class="mb-box">
    <div class="mb-head"><i class="fa fa-wrench"></i> <?php _e('Configure', 'openstreetmaps'); ?></div>

    <div class="mb-inside mb-minify">
      <form name="promo_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
        <?php if(!osm_is_demo()) { ?>
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>configure.php" />
        <input type="hidden" name="plugin_action" value="done" />
        <?php } ?>

        <div class="mb-row">
          <label for="token" class="h0"><span><?php _e('Access Token', 'openstreetmaps'); ?></span></label> 
          <input name="token" size="100" type="text" value="<?php echo (osm_is_demo() ? '' : $token); ?>" />

          <div class="mb-explain"><?php _e('Request access token (free):', 'openstreetmaps'); ?> <a href="https://account.mapbox.com/access-tokens/" target="_blank">https://account.mapbox.com/access-tokens/</a></div>
        </div>


        <div class="mb-row">
          <label for="zoom" class="h3"><span><?php _e('Default Zoom', 'openstreetmaps'); ?></span></label> 
          <input name="zoom" style="width:150px;" type="number" min="0" max="19" value="<?php echo $zoom; ?>" />

          <div class="mb-explain"><?php _e('Zoom level of map. Min: 0; Max: 19; Def: 13', 'openstreetmaps'); ?></div>
        </div>

        <div class="mb-row">
          <label for="random" class=""><span><?php _e('Coords Randomization', 'openstreetmaps'); ?></span></label> 
          <input name="random" style="width:150px;" type="number" min="0" max="100" value="<?php echo $random; ?>" />

          <div class="mb-explain"><?php _e('In case there are 2 same coordinates on one place, they will be shifted slightly from place of origin to avoid overlaping of marks. Enter 0 to disable. Min: 0; Max: 100; Def: 20', 'openstreetmaps'); ?></div>
        </div>
        
        
        <div class="mb-subtitle"><?php _e('Map Hook Settings', 'openstreetmaps'); ?></div>

        
        <div class="mb-row">
          <label for="hook_item_enabled"><span><?php _e('Hook Map on Item Page', 'openstreetmaps'); ?></span></label> 
          <input name="hook_item_enabled" type="checkbox" class="element-slide" <?php echo ($hook_item_enabled == 1 ? 'checked' : ''); ?> />

          <div class="mb-explain"><?php _e('Require Osclass 8.2 hooks.', 'openstreetmaps'); ?></div>
        </div>
        
        <div class="mb-row">
          <label for="hook_item"><span><?php _e('Item Page Hook', 'openstreetmaps'); ?></span></label> 
          <input name="hook_item" size="30" type="text" value="<?php echo $hook_item; ?>" />

          <div class="mb-explain"><?php _e('Require Osclass 8.2 hooks.', 'openstreetmaps'); ?></div>
        </div>
        
        <div class="mb-row">
          <label for="hook_home_enabled"><span><?php _e('Hook Map on Home Page', 'openstreetmaps'); ?></span></label> 
          <input name="hook_home_enabled" type="checkbox" class="element-slide" <?php echo ($hook_home_enabled == 1 ? 'checked' : ''); ?> />

          <div class="mb-explain"><?php _e('Require Osclass 8.2 hooks.', 'openstreetmaps'); ?></div>
        </div>
        
        <div class="mb-row">
          <label for="hook_home"><span><?php _e('Home Page Hook', 'openstreetmaps'); ?></span></label> 
          <input name="hook_home" size="30" type="text" value="<?php echo $hook_home; ?>" />

          <div class="mb-explain"><?php _e('Require Osclass 8.2 hooks.', 'openstreetmaps'); ?></div>
        </div>
        
  
        <div class="mb-row">
          <label for="hook_search_enabled"><span><?php _e('Hook Map on Search Page', 'openstreetmaps'); ?></span></label> 
          <input name="hook_search_enabled" type="checkbox" class="element-slide" <?php echo ($hook_search_enabled == 1 ? 'checked' : ''); ?> />

          <div class="mb-explain"><?php _e('Require Osclass 8.2 hooks.', 'openstreetmaps'); ?></div>
        </div>
        
        <div class="mb-row">
          <label for="hook_search"><span><?php _e('Search Page Hook', 'openstreetmaps'); ?></span></label> 
          <input name="hook_search" size="30" type="text" value="<?php echo $hook_search; ?>" />

          <div class="mb-explain"><?php _e('Require Osclass 8.2 hooks.', 'openstreetmaps'); ?></div>
        </div>
        
        <div class="mb-row">
          <label for="hook_publish_enabled"><span><?php _e('Hook Map on Item Publish/Edit Page', 'openstreetmaps'); ?></span></label> 
          <input name="hook_publish_enabled" type="checkbox" class="element-slide" <?php echo ($hook_publish_enabled == 1 ? 'checked' : ''); ?> />

          <div class="mb-explain"><?php _e('Require Osclass 8.2 hooks.', 'openstreetmaps'); ?></div>
        </div>
        
        <div class="mb-row">
          <label for="hook_publish"><span><?php _e('Item Publish/Edit Page Hook', 'openstreetmaps'); ?></span></label> 
          <input name="hook_publish" size="30" type="text" value="<?php echo $hook_publish; ?>" />

          <div class="mb-explain"><?php _e('Require Osclass 8.2 hooks.', 'openstreetmaps'); ?></div>
        </div>
        
        <div class="mb-row">
          <label for="hook_public_profile_enabled"><span><?php _e('Hook Map on Public Profile Page', 'openstreetmaps'); ?></span></label> 
          <input name="hook_public_profile_enabled" type="checkbox" class="element-slide" <?php echo ($hook_public_profile_enabled == 1 ? 'checked' : ''); ?> />

          <div class="mb-explain"><?php _e('Require Osclass 8.2 hooks.', 'openstreetmaps'); ?></div>
        </div>
        
        <div class="mb-row">
          <label for="hook_public_profile"><span><?php _e('Public Profile Page Hook', 'openstreetmaps'); ?></span></label> 
          <input name="hook_public_profile" size="30" type="text" value="<?php echo $hook_public_profile; ?>" />

          <div class="mb-explain"><?php _e('Require Osclass 8.2 hooks.', 'openstreetmaps'); ?></div>
        </div>



        <div class="mb-subtitle"><?php _e('Search Hook Settings', 'openstreetmaps'); ?></div>
        

        <div class="mb-row">
          <label for="hook_radius" class=""><span><?php _e('Hook Radius Select', 'openstreetmaps'); ?></span></label> 
          <input name="hook_radius" type="checkbox" class="element-slide" <?php echo ($hook_radius == 1 ? 'checked' : ''); ?> />

          <div class="mb-explain"><?php _e('When enabled, radius select box is hooked to search sidebar.', 'openstreetmaps'); ?></div>
        </div>

        <div class="mb-row">
          <label for="measure" class=""><span><?php _e('Radius Measure', 'openstreetmaps'); ?></span></label> 
          <select name="measure">
            <option value="km" <?php if($measure == 'km') { ?>selected="selected"<?php } ?>><?php _e('Kilometers (km)', 'openstreetmaps'); ?></option>
            <option value="m" <?php if($measure == 'm') { ?>selected="selected"<?php } ?>><?php _e('Miles (m)', 'openstreetmaps'); ?></option>
          </select>

          <div class="mb-explain"><?php _e('Select in what measure radius search will work (kilometers or miles).', 'openstreetmaps'); ?></div>
        </div>

        <div class="mb-row">
          <label for="default_query" class=""><span><?php _e('Default location pattern', 'openstreetmaps'); ?></span></label> 
          <input name="default_query" type="text" size=60 value="<?php echo $default_query; ?>" />

          <div class="mb-explain">
            <p><?php _e('Pattern that will be used to search coordinates on OpenStreetMaps API.', 'openstreetmaps'); ?></p>
            <p><?php _e('Available keywords: {COUNTRY}, {COUNTRY_CODE}, {REGION}, {CITY}, {CITY_AREA}, {ZIP}, {ADDRESS}. Def: {COUNTRY} {REGION} {ZIP} {CITY} {ADDRESS}', 'openstreetmaps'); ?></p>

            <?php $test_link = osm_response_link('United+States+Connecticut+Central+Village'); ?>
            <p><?php _e('Sample call:', 'openstreetmaps'); ?> <a href="<?php echo $test_link; ?>" target="_blank"><?php echo $test_link; ?></a></p>
          </div>
        </div>
        
        
        <div class="mb-subtitle"><?php _e('Item Map Specific Settings', 'openstreetmaps'); ?></div>
        

        <div class="mb-row">
          <label for="item_map_load_on_click" class=""><span><?php _e('Load Map after Click', 'openstreetmaps'); ?></span></label> 
          <input name="item_map_load_on_click" type="checkbox" class="element-slide" <?php echo ($item_map_load_on_click == 1 ? 'checked' : ''); ?> />

          <div class="mb-explain">
            <div class="mb-line"><?php _e('When enabled, map on item page is not loaded with page load, but is loaded after click on map image/button.', 'openstreetmaps'); ?></div>
            <div class="mb-line"><?php _e('This may greatly reduce your API usage.', 'openstreetmaps'); ?></div>
          </div>
        </div>
        
        
        <div class="mb-row">
          <label for="rel_enable" class=""><span><?php _e('Enable Related Items', 'openstreetmaps'); ?></span></label> 
          <input name="rel_enable" type="checkbox" class="element-slide" <?php echo ($rel_enable == 1 ? 'checked' : ''); ?> />

          <div class="mb-explain"><?php _e('When enabled, related items are shown on map at listing page.', 'openstreetmaps'); ?></div>
        </div>

        <div class="mb-row">
          <label for="rel_max" class=""><span><?php _e('Rel. Items Limit', 'openstreetmaps'); ?></span></label> 
          <input name="rel_max" style="width:150px;" type="number" min="0" max="50" value="<?php echo $rel_max; ?>" />
          <div class="mb-input-desc"><?php _e('items', 'openstreetmaps'); ?></div>

          <div class="mb-explain"><?php _e('Maxmimum items shown on map. Def: 20', 'openstreetmaps'); ?></div>
        </div>

        <div class="mb-row">
          <label for="rel_cat" class=""><span><?php _e('Rel. Items Category', 'openstreetmaps'); ?></span></label> 
          <input name="rel_cat" type="checkbox" class="element-slide" <?php echo ($rel_cat == 1 ? 'checked' : ''); ?> />

          <div class="mb-explain"><?php _e('When enabled, only items from same category are shown as related.', 'openstreetmaps'); ?></div>
        </div>

        <div class="mb-row">
          <label for="rel_loc" class=""><span><?php _e('Items Location', 'openstreetmaps'); ?></span></label> 
          <select name="rel_loc">
            <option value="1" <?php if($rel_loc == 1) { ?>selected="selected"<?php } ?>><?php _e('Same country', 'openstreetmaps'); ?></option>
            <option value="2" <?php if($rel_loc == 2) { ?>selected="selected"<?php } ?>><?php _e('Same country & region', 'openstreetmaps'); ?></option>
            <option value="3" <?php if($rel_loc == 3) { ?>selected="selected"<?php } ?>><?php _e('Same country & region & city', 'openstreetmaps'); ?></option>
          </select>

          <div class="mb-explain"><?php _e('Select how location will be used to filter out related items.', 'openstreetmaps'); ?></div>
        </div>


        <hr/> 
        
        
        <div class="mb-row">
          <label for="item_draw_circle" class=""><span><?php _e('Enable Item Map Circle', 'openstreetmaps'); ?></span></label> 
          <input name="item_draw_circle" type="checkbox" class="element-slide" <?php echo ($item_draw_circle == 1 ? 'checked' : ''); ?> />

          <div class="mb-explain"><?php _e('When enabled, circle with defined radius and color is drawn around item map pin.', 'openstreetmaps'); ?></div>
        </div>
        

        <div class="mb-row">
          <label for="item_draw_circle_radius" class=""><span><?php _e('Item Map Circle Radius', 'openstreetmaps'); ?></span></label> 
          <input name="item_draw_circle_radius" style="width:150px;" type="number" min="0" value="<?php echo $item_draw_circle_radius; ?>" />
          <div class="mb-input-desc"><?php _e('meters', 'openstreetmaps'); ?></div>

          <div class="mb-explain"><?php _e('Item map circle radius in meters. Def: 500', 'openstreetmaps'); ?></div>
        </div>
        
        
        <div class="mb-row">
          <label for="item_draw_circle_color" class=""><span><?php _e('Item Map Circle Color', 'openstreetmaps'); ?></span></label> 
          <input name="item_draw_circle_color" style="width:150px;" type="text" min="0" max="50" value="<?php echo $item_draw_circle_color; ?>" />

          <div class="mb-explain"><?php _e('Item map circle HEX color. Def: #0000e8', 'openstreetmaps'); ?></div>
        </div>
        



        <div class="mb-subtitle"><?php _e('Map size & fullscreen mode', 'openstreetmaps'); ?></div>

        <div class="mb-row">
          <label for="height_item" class="h2"><span><?php _e('Item Map Height', 'openstreetmaps'); ?></span></label> 
          <input name="height_item" style="width:150px;" type="number" value="<?php echo $height_item; ?>" />
          <div class="mb-input-desc">px</div>

          <div class="mb-explain"><?php _e('Enter numerical value only, represents pixels. Def: 240px.', 'openstreetmaps'); ?></div>
        </div>
        

        <div class="mb-row">
          <label for="fullscreen_item" class=""><span><?php _e('Fullscreen Item Map', 'openstreetmaps'); ?></span></label> 
          <input name="fullscreen_item" type="checkbox" class="element-slide" <?php echo ($fullscreen_item == 1 ? 'checked' : ''); ?> />

          <div class="mb-explain"><?php _e('When enabled, fullscreen control is added into map. Test before using in production, for some themes it may not work correctly.', 'openstreetmaps'); ?></div>
        </div>

        <div class="mb-row">
          <label for="height_search" class=""><span><?php _e('Search Map Height', 'openstreetmaps'); ?></span></label> 
          <input name="height_search" style="width:150px;" type="number" value="<?php echo $height_search; ?>" />
          <div class="mb-input-desc">px</div>

          <div class="mb-explain"><?php _e('Enter numerical value only, represents pixels. Def: 360px.', 'openstreetmaps'); ?></div>
        </div>

        <div class="mb-row">
          <label for="fullscreen_search" class=""><span><?php _e('Fullscreen Search Map', 'openstreetmaps'); ?></span></label> 
          <input name="fullscreen_search" type="checkbox" class="element-slide" <?php echo ($fullscreen_search == 1 ? 'checked' : ''); ?> />

          <div class="mb-explain"><?php _e('When enabled, fullscreen control is added into map. Test before using in production, for some themes it may not work correctly.', 'openstreetmaps'); ?></div>
        </div>

        <div class="mb-row">
          <label for="height_home" class=""><span><?php _e('Home Map Height', 'openstreetmaps'); ?></span></label> 
          <input name="height_home" style="width:150px;" type="number" value="<?php echo $height_home; ?>" />
          <div class="mb-input-desc">px</div>

          <div class="mb-explain"><?php _e('Enter numerical value only, represents pixels. Def: 480px.', 'openstreetmaps'); ?></div>
        </div>

        <div class="mb-row">
          <label for="fullscreen_home" class=""><span><?php _e('Fullscreen Home Map', 'openstreetmaps'); ?></span></label> 
          <input name="fullscreen_home" type="checkbox" class="element-slide" <?php echo ($fullscreen_home == 1 ? 'checked' : ''); ?> />

          <div class="mb-explain"><?php _e('When enabled, fullscreen control is added into map. Test before using in production, for some themes it may not work correctly.', 'openstreetmaps'); ?></div>
        </div>

        <div class="mb-row">
          <label for="height_publish" class="h2"><span><?php _e('Publish Map Height', 'openstreetmaps'); ?></span></label> 
          <input name="height_publish" style="width:150px;" type="number" value="<?php echo $height_publish; ?>" />
          <div class="mb-input-desc">px</div>

          <div class="mb-explain"><?php _e('Enter numerical value only, represents pixels. Def: 240px.', 'openstreetmaps'); ?></div>
        </div>

        <div class="mb-row">
          <label for="fullscreen_publish" class=""><span><?php _e('Fullscreen Publish Map', 'openstreetmaps'); ?></span></label> 
          <input name="fullscreen_publish" type="checkbox" class="element-slide" <?php echo ($fullscreen_publish == 1 ? 'checked' : ''); ?> />

          <div class="mb-explain"><?php _e('When enabled, fullscreen control is added into map. Test before using in production, for some themes it may not work correctly.', 'openstreetmaps'); ?></div>
        </div>


        <div class="mb-subtitle"><?php _e('Publish Map Specific Settings', 'openstreetmaps'); ?></div>

        
        <div class="mb-row">
          <label for="publish_map_search_version" class=""><span><?php _e('Publish Map Search Version', 'openstreetmaps'); ?></span></label> 
          <select name="publish_map_search_version">
            <option value="" <?php if($publish_map_search_version == '') { ?>selected="selected"<?php } ?>><?php _e('v1 - exact match for dropdowns', 'openstreetmaps'); ?></option>
            <option value="2" <?php if($publish_map_search_version == '2') { ?>selected="selected"<?php } ?>><?php _e('v2 - universal closest match', 'openstreetmaps'); ?></option>
          </select>

          <div class="mb-explain"><?php _e('Select search functionality version. V2 will work just in case you have coordinates filled in t_city table (osclass 4.4+, locations v2+).', 'openstreetmaps'); ?></div>
        </div>
        
        <div class="mb-row">
          <label for="coordinate_fields" class="h1"><span><?php _e('Coordinates Fields', 'openstreetmaps'); ?></span></label> 
          <input name="coordinate_fields" type="checkbox" class="element-slide" <?php echo ($coordinate_fields == 1 ? 'checked' : ''); ?> />

          <div class="mb-explain"><?php _e('When enabled, coordinates input fields are added to item publish/edit forms (name d_coord_lat, d_coord_long) as hidden inputs.', 'openstreetmaps'); ?></div>
        </div>



        <div class="mb-row">&nbsp;</div>

        <div class="mb-foot">
          <?php if(osm_is_demo()) { ?>
            <a class="mb-button mb-has-tooltip disabled" onclick="return false;" style="cursor:not-allowed;opacity:0.5;" title="<?php echo osc_esc_html(__('This is demo site', 'openstreetmaps')); ?>"><?php _e('Save', 'openstreetmaps');?></a>
          <?php } else { ?>
            <button type="submit" class="mb-button"><?php _e('Save', 'openstreetmaps');?></button>
          <?php } ?>
        </div>
      </form>
    </div>
  </div>


  <!-- FILL COORDINATES STEP -->
  <div class="mb-box">
    <div class="mb-head"><i class="fa fa-upload"></i> <?php _e('Upload missing coordinates', 'openstreetmaps'); ?></div>

    <div class="mb-inside mb-minify">
      <form name="promo_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
        <?php if(!osm_is_demo()) { ?>
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>configure.php" />
        <input type="hidden" name="plugin_action2" value="done" />
        <?php } ?>

        <div class="mb-notes">
          <div class="mb-line"><?php echo sprintf(__('There is %s listings with missing coordinates', 'openstreetmaps'), $missing); ?></div>

          <?php if($missing_fail > 0) { ?>
            <div class="mb-line"><?php echo sprintf(__('There is %s listings with coordinates where location could not be found', 'openstreetmaps'), $missing_fail); ?></div>
          <?php } ?>

          <div class="mb-line"><?php _e('Please click on "Fill missing coordinates" button in order to get coordinates for items those are missing them', 'openstreetmaps'); ?></div>
        </div>

        <div class="mb-row">
          <label for="step" class=""><span><?php _e('Items Processed', 'openstreetmaps'); ?></span></label> 
          <input name="step" style="width:150px;" type="number" value="<?php echo $step; ?>" />
          <div class="mb-input-desc"><?php _e('items', 'openstreetmaps'); ?></div>

          <div class="mb-explain"><?php _e('Enter numerical value of how many items will be filled in one step. Def: 500', 'openstreetmaps'); ?></div>
        </div>

        <div class="mb-row">
          <a href="<?php echo osc_admin_base_url(true); ?>?page=plugins&action=renderplugin&file=openstreetmaps/admin/configure.php&what=fill" class="mb-button-green" style="margin-left:25%;"><?php _e('Fill missing coordinates', 'openstreetmaps'); ?></a>

          <?php if(!osm_is_demo()) { ?>
          <a href="<?php echo osc_admin_base_url(true); ?>?page=plugins&action=renderplugin&file=openstreetmaps/admin/configure.php&what=clear" class="mb-button-gray" style="margin-left:5px;"><?php _e('Clear coordinates', 'openstreetmaps'); ?></a>
          <?php } ?>
        </div>


        <div class="mb-row">&nbsp;</div>

        <div class="mb-foot">
          <?php if(osm_is_demo()) { ?>
            <a class="mb-button mb-has-tooltip disabled" onclick="return false;" style="cursor:not-allowed;opacity:0.5;" title="<?php echo osc_esc_html(__('This is demo site', 'openstreetmaps')); ?>"><?php _e('Save', 'openstreetmaps');?></a>
          <?php } else { ?>
            <button type="submit" class="mb-button"><?php _e('Save', 'openstreetmaps');?></button>
          <?php } ?>
        </div>
      </form>
    </div>
  </div>


  <!-- PLUGIN INTEGRATION -->
  <div class="mb-box">
    <div class="mb-head"><i class="fa fa-wrench"></i> <?php _e('Plugin Setup', 'openstreetmaps'); ?></div>

    <div class="mb-inside">
      <div class="mb-row">
        <div class="mb-line"><strong><?php _e('Following integrations may be required for Osclass 8.1 or lower. In Osclass 8.2 were introduced new hooks (theme must integrate these) that enables seamless integration into theme.', 'openstreetmaps'); ?></strong></div>

        <div class="mb-line"><?php _e('Plugin does not require any modifications in theme files until you want to place to your theme home or search page map. Do not add more than 1 map at same page!', 'openstreetmaps'); ?></div>
        
        <div class="mb-row">
          <div class="mb-line"><?php _e('To show latest items on home page map, please add to your main.php following code:', 'openstreetmaps'); ?></div>
          <span class="mb-code">&lt;?php osc_run_hook('map_home'); ?&gt;</span>
        </div>

        <div class="mb-row">
          <div class="mb-line"><?php _e('To show search items on search page map, please add to your search.php following code:', 'openstreetmaps'); ?></div>
          <span class="mb-code">&lt;?php osc_run_hook('map_search'); ?&gt;</span>
        </div>
        
        <div class="mb-row">
          <div class="mb-line"><?php _e('To show item (and related items) on listing page map, enable "Hook Map" option or add to your item.php following code:', 'openstreetmaps'); ?></div>
          <span class="mb-code">&lt;?php osc_run_hook('map_item'); ?&gt;</span>
        </div>

        <div class="mb-row">
          <div class="mb-line"><?php _e('To show map on publish/edit page to select item location from map, add to your item-post.php / item-edit.php following code:', 'openstreetmaps'); ?></div>
          <span class="mb-code">&lt;?php if(function_exists('osm_publish_map')) { osm_publish_map(); } ?&gt;</span>
        </div>


        <div class="mb-row">
          <div class="mb-line"><?php _e('To show radius select box on search page, enable "Hook Radius Select" option or add to your search.php (form) following code:', 'openstreetmaps'); ?></div>
          <span class="mb-code">&lt;?php if(function_exists('osm_radius_select')) { echo osm_radius_select(); } ?&gt;</span>
        </div>

      </div>
    </div>
  </div>


  <!-- CRON SETUP -->
  <div class="mb-box">
    <div class="mb-head"><i class="fa fa-clock-o"></i> <?php _e('Cron Setup', 'openstreetmaps'); ?></div>

    <div class="mb-inside">
      <div class="mb-row">
        <div class="mb-line"><?php _e('If your osclass has thousands of listings, filling coordinates may be complicated. For this reason we have created cron you can setup to fill coordinates for you.', 'openstreetmaps'); ?></div>
        <div class="mb-line"><?php _e('You may get output of cron to email so you will be notified once all coordinates are filled and cron can be disabled.', 'openstreetmaps'); ?></div>
        <div class="mb-line"><?php _e('Due to usage limits of OpenStreetMaps, it is allowed to get max 1 coordinate per second and therefore script is sleeped to match this requirement. This means for 1 minute you can get about 60 coordinates.', 'openstreetmaps'); ?></div>
        <div class="mb-line"><?php _e('If your maximum php execution time is i.e. 300 seconds (5min), you can get about 290 items in that time, meaning you would setup cron to run once per 5 minute with limit of 290 listings.', 'openstreetmaps'); ?></div>
        <div class="mb-line"><?php _e('In this scenario you would setup cron with following preferences.', 'openstreetmaps'); ?></div>
        
        <div class="mb-row">
          <span class="mb-code">
            <?php echo osc_base_url(); ?>oc-content/plugins/openstreetmaps/cron.php?limit=300<br/>
            */5 * * * *
          </span>
        </div>
      </div>
    </div>
  </div>
</div>


<?php echo osm_footer(); ?>