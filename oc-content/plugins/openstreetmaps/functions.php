<?php


function osm_find_closest_city() {
  if(Params::getParam('osmFindClosestCity') == 1) {
    if(Params::getParam('lat') <> '' && Params::getParam('lon') <> '') {
      $data = ModelOSM::newInstance()->findClosestCity(Params::getParam('lat'), Params::getParam('lon'));
      
      if($data !== false) {
        $data['status'] = 'OK';
        $data['s_region_final'] = osc_location_native_name_selector($data, 's_region');
        $data['s_city_final'] = osc_location_native_name_selector($data, 's_city');
        $data['s_display_name'] = $data['s_city_final'];
        
        echo json_encode($data);
      } else {
        echo json_encode(array('status' => 'ERROR'));   // not found
      }
      
      exit;
    }
  }
}

osc_add_hook('init', 'osm_find_closest_city', 7);

// SLIGHTLY SHIFT COORDINATE RANDOMLY
function osm_shift($cord) {
  if($cord == 0) {
    return 0;
  }

  $rand = osm_param('random')*100;

  $mod = rand(-$rand, $rand)/1000000;
  return $cord + $mod;
}


// GET ADDRESS TEXT
function osm_item_address($item) {
  $data = array_filter(array($item['s_address'], $item['s_zip'], $item['s_city'], $item['s_city_area'], $item['s_region'], $item['s_country']));
  return implode(', ', $data);
}


// RETRIEVE COORDINATES
function osm_retrieve_cord($item) {
  if($item === '' || $item === null) {
    $item = array();
  }
  
  $query = osm_query_string2($item);   // using {COUNTRY} {REGION} {ZIP} {CITY} {ADDRESS}, or different if changed default query by user
  $cord_cache = ModelOSM::newInstance()->getCache($query);

  if($cord_cache) {
    $lat = $cord_cache['d_coord_lat'];
    $lng = $cord_cache['d_coord_long'];

  } else {
    // Use full address except region
    $response = osc_file_get_contents(osm_response_link($query));
    $json_response = json_decode($response);
    
    if(isset($json_response[0]->lat) && $json_response[0]->lat <> '') {
      $lat = $json_response[0]->lat;
      $lng = $json_response[0]->lon;
      $cord_cache = ModelOSM::newInstance()->updateCache($query, $lat, $lng);

    } else {

      // Try to remove address & zip from search
      if(@$item['s_zip'] == '' && @$item['s_address'] == '') {
        $query = osm_query_string2($item, '{COUNTRY} {CITY}');  // because we already tried {COUNTRY} {REGION} {CITY} as address & zip are empty
      } else {
        $query = osm_query_string2($item, '{COUNTRY} {REGION} {CITY}');
      }
      
      $cord_cache = ModelOSM::newInstance()->getCache($query);

      if($cord_cache) {
        $lat = $cord_cache['d_coord_lat'];
        $lng = $cord_cache['d_coord_long'];
      
      } else {

        $response = osc_file_get_contents(osm_response_link($query));
        $json_response = json_decode($response);
        
        if(isset($json_response[0]->lat) && $json_response[0]->lat <> '') {
          $lat = $json_response[0]->lat;
          $lng = $json_response[0]->lon;
          $cord_cache = ModelOSM::newInstance()->updateCache($query, $lat, $lng);

        } else {

          // Try to remove address, zip & city from search
          $query = osm_query_string2($item, '{COUNTRY} {REGION}');

          $cord_cache = ModelOSM::newInstance()->getCache($query);

          if($cord_cache) {
            $lat = $cord_cache['d_coord_lat'];
            $lng = $cord_cache['d_coord_long'];
 
          } else {

            $response = osc_file_get_contents(osm_response_link($query));
            $json_response = json_decode($response);
            
            if(isset($json_response[0]->lat) && $json_response[0]->lat <> '') {
              $lat = $json_response[0]->lat;
              $lng = $json_response[0]->lon;
              $cord_cache = ModelOSM::newInstance()->updateCache($query, $lat, $lng);

            } else {
              $lat = 0;
              $lng = 0;

            }
          }
        }
      }
    }
  }

  return array('lat' => $lat, 'lng' => $lng);
}



// RETRIEVE COORDINATES FOR CITY
function osm_retrieve_cord_city($item) {
  $query = osm_query_string2($item, '{COUNTRY} {REGION} {CITY}');

  if($item['s_city'] == '') {
    return array('lat' => '', 'lng' => '');
  }

  $lat = '';
  $lng = '';
  $cord_cache = ModelOSM::newInstance()->getCache($query);

  if($cord_cache) {
    $lat = $cord_cache['d_coord_lat'];
    $lng = $cord_cache['d_coord_long'];

  } else {
    $response = osc_file_get_contents(osm_response_link($query));
    $json_response = json_decode($response);
    
    if(isset($json_response[0]->lat) && $json_response[0]->lat <> '') {
      $lat = $json_response[0]->lat;
      $lng = $json_response[0]->lon;
      $cord_cache = ModelOSM::newInstance()->updateCache($query, $lat, $lng);
    }
  }

  return array('lat' => $lat, 'lng' => $lng);
}


// PREPARE URL STRING TO SEARCH COORDINATES
function osm_query_string2($item, $pattern = '') {
  if($pattern == '') {
    $pattern = trim(osm_param('default_query'));
    
    if($pattern == '') {
      $pattern = '{COUNTRY} {REGION} {ZIP} {CITY} {ADDRESS}'; 
    }
  }
  
  if($item === null || $item === '') {
    $item = array();
  }
  
  $pattern = strtoupper($pattern);
  $keywords = array('{COUNTRY_CODE}', '{COUNTRY}', '{REGION}', '{CITY}', '{CITY_AREA}', '{ZIP}', '{ADDRESS}');

  $country_code = (@$item['fk_c_country_code'] <> '' ? $item['fk_c_country_code'] : '');
  $country = (@$item['s_country'] <> '' ? $item['s_country'] : '');
  $region = (@$item['s_region'] <> '' ? $item['s_region'] : '');
  $city = (@$item['s_city'] <> '' ? $item['s_city'] : '');
  $city_area = (@$item['s_city_area'] <> '' ? $item['s_city_area'] : '');
  $zip = (@$item['s_zip'] <> '' ? $item['s_zip'] : '');
  $address = (@$item['s_address'] <> '' ? $item['s_address'] : '');
  
  $data = array($country_code, $country, $region, $city, $city_area, $zip, $address);
  
  $query = str_replace($keywords, $data, $pattern);   // replace keywords with content from item
  $query = preg_replace('/{(.*?)}/', '', $query);     // remove possibly invalid keywords, like {REGOIN}
  $query = trim(preg_replace('!\s+!', ' ', $query));  // remove move than one white space
  $query = urlencode($query);                         // encode query for URL

  return $query;        
}


// RADIUS SEARCH FUNCTIONALITY
function osm_search_conditions($params) {
  $dist = (float)Params::getParam('osmRadius');

  $city = Params::getParam('city') <> '' ? Params::getParam('city') : Params::getParam('sCity');
  $region = Params::getParam('region') <> '' ? Params::getParam('region') : Params::getParam('sRegion');
  $country = Params::getParam('country') <> '' ? Params::getParam('country') : Params::getParam('sCountry');


  if(is_numeric($region)) {
    $region_row = Region::newInstance()->findByPrimaryKey($region);
    $region = isset($region_row['s_name']) ? $region_row['s_name'] : $region;
  }

  if(is_numeric($city)) {
    $city_row = City::newInstance()->findByPrimaryKey($city);
    $city = isset($city_row['s_name']) ? $city_row['s_name'] : $city;
  }

  $data = array();

  $data[] = $country;
  $data[] = $region;
  $data[] = $city;

  $query = urlencode(implode(' ', array_filter(array_map('trim', $data))));

  if($query <> '' && $dist > 0) {
    $cord_cache = ModelOSM::newInstance()->getCache($query);

    if($cord_cache) {
      $lat = $cord_cache['d_coord_lat'];
      $lng = $cord_cache['d_coord_long'];

    } else {
      $response = osc_file_get_contents(osm_response_link($query));
      $json_response = json_decode($response);

      if(isset($json_response[0]->lat) && $json_response[0]->lat <> '') {
        $lat = $json_response[0]->lat;
        $lng = $json_response[0]->lon;
        $cord_cache = ModelOSM::newInstance()->updateCache($query, $lat, $lng);
      }
    }

    if($lat <> 0 && $lng <> 0) {
      if($dist > 0) {
        $rad = $dist;

        $list = ModelOSM::newInstance()->getLocations($lat, $lng, $rad);

        if(count($list) > 0) {
          foreach($list as $l) {
            if($country <> '') {
              if($l['fk_c_country_code'] <> '') {
                Search::newInstance()->addCountry($l['fk_c_country_code']);
              } else if($l['s_country'] <> '') {
                Search::newInstance()->addCountry($l['s_country']);
              }
            }

            if($region <> '') {
              if($l['fk_i_region_id'] <> '') {
                Search::newInstance()->addRegion($l['fk_i_region_id']);
              } else if($l['s_region'] <> '') {
                Search::newInstance()->addRegion($l['s_region']);
              }
            }

            if($city <> '') {
              if($l['fk_i_city_id'] <> '') {
                Search::newInstance()->addCity($l['fk_i_city_id']);
              } else if($l['s_city'] <> '') {
                Search::newInstance()->addCity($l['s_city']);
              }
            }
          }
        }
      }
    }
  }
}

osc_add_hook('search_conditions', 'osm_search_conditions', 1);


// RADIUS SELECT BOX
function osm_radius_select() {
  $current = Params::getParam('osmRadius');
  $available = array(1,5,10,50,100,500,2000);

  $html = '';

  $html .= '<fieldset><div class="row osm-row">';
  $html .= '<label for="osmRadius">' . __('Radius', 'openstreetmaps') . '</label>';

  $html .= '<select id="osmRadius" name="osmRadius">';
  $html .= '<option value=""' . ($current <= 0 ? 'selected="selected"' : '') . '>' . __('Select radius', 'openstreetmaps') . '</option>';

  foreach($available as $a) {
    $html .= '<option value="' . $a . '"' . ($a == $current ? 'selected="selected"' : '') . '>' . $a . ' ' . osm_param('measure') . '</option>';
  }

  $html .= '</select>';
  $html .= '</div></fieldset>';

  return $html;
}


// RADIUS SELECT BOX HOOK
function osm_radius_select_hook() {
  if(osm_param('hook_radius') == 1) {
    echo osm_radius_select();
  }
}

osc_add_hook('search_form', 'osm_radius_select_hook');



// INSERT MAP INTO ITEM PAGE HOOK
function osm_map_hook() {
  if(osm_param('hook') == 1) {
    osm_item_map();
  }
}


// INSERT MAP INTO PUBLIC PROFILE PAGE HOOK
function osm_map_hook_public_profile() {
  //if(osm_param('hook') == 1) {
  if(1==1) {
    osm_user_map();
  }
}

osc_add_hook('user_public_profile_location', 'osm_map_hook_public_profile');



// CREATE ITEM MAP
function osm_item_map() {
  include 'form/item_map.php';
}

osc_add_hook('map_item', 'osm_item_map');

// Osclass 8.2 hook
if(osm_param('hook_item_enabled') == 1 && osm_param('hook_item') != '') {
  osc_add_hook(osm_param('hook_item'), 'osm_item_map');
}


// CREATE USER MAP
function osm_user_map() {
  include 'form/user_map.php';
}

osc_add_hook('map_user', 'osm_user_map');

// Osclass 8.2 hook
if(osm_param('hook_public_profile_enabled') == 1 && osm_param('hook_public_profile') != '') {
  osc_add_hook(osm_param('hook_public_profile'), 'osm_user_map');
}


// CREATE SEARCH MAP
function osm_search_map() {
  include 'form/search_map.php';
} 

osc_add_hook('map_search', 'osm_search_map');

// Osclass 8.2 hook
if(osm_param('hook_search_enabled') == 1 && osm_param('hook_search') != '') {
  osc_add_hook(osm_param('hook_search'), 'osm_search_map');
}


// CREATE HOME MAP
function osm_home_map() {
  include 'form/home_map.php';
}  

osc_add_hook('map_home', 'osm_home_map');

// Osclass 8.2 hook
if(osm_param('hook_home_enabled') == 1 && osm_param('hook_home') != '') {
  osc_add_hook(osm_param('hook_home'), 'osm_home_map');
}


// CREATE PUBLISH MAP
function osm_publish_map() {
  include 'form/publish_map.php';
}

// Osclass 8.2 hook
if(osm_param('hook_publish_enabled') == 1 && osm_param('hook_publish') != '') {
  osc_add_hook(osm_param('hook_publish'), 'osm_publish_map');
}

// if(osm_param('hook_edit_enabled') == 1 && osm_param('hook_edit') != '') {
  // osc_add_hook(osm_param('hook_edit'), 'osm_publish_map');
// }



// RESPONSE LINK
function osm_response_link($query = '') {
  return 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' . $query;
}



// FIND ITEM COORDINATES ON POST/EDIT
// Do not update coordinates in item table if not found
function osm_cords_insert($item, $is_user = false) {
  // This section is disabled for 2 reasons:
  // - if location has not been change, it will be retrieved from cache
  // - if location has been changed, we must update coordinates
  // if($item['d_coord_lat'] <> 0 && $item['d_coord_long'] <> 0) {
    // return false;
  // }


  // If coordinates were populated from map to input fields
  $p_lat = (float)(Params::getParam('d_coord_lat') != '' ? Params::getParam('d_coord_lat') : Params::getParam('latitude'));
  $p_lng = (float)(Params::getParam('d_coord_lat') != '' ? Params::getParam('d_coord_lat') : Params::getParam('longitude'));
  
  if($p_lat != 0 && $p_lng != 0) {
    if($item['d_coord_lat'] != $p_lat && $item['d_coord_long'] != $p_lng) {
      ItemLocation::newInstance()->update(
        array(
          'd_coord_lat' => $p_lat,
          'd_coord_long' => $p_lng
        ),
        array(
          'fk_i_item_id' => $item_id
        )
      );
    }
    
    return array('lat' => $p_lat, 'lng' => $p_lng);
  }
  

  $item_id = $item['pk_i_id'];
  $city_id = $item['fk_i_city_id'];
  
  if($is_user === true) {
    $item = User::newInstance()->findByPrimaryKey($item_id);
  } else {
    $item = Item::newInstance()->findByPrimaryKey($item_id);
  }
  
  $cords = osm_retrieve_cord($item);
  $cords_item_city = osm_retrieve_cord_city($item);

  $lat = $cords['lat'];
  $lng = $cords['lng'];
  
  $lat_city = $cords_item_city['lat'];
  $lng_city = $cords_item_city['lng'];

  if($is_user === false) {
    $check_cords = ModelOSM::newInstance()->checkCords($lat, $lng);

    if($check_cords) {
      $lat = osm_shift($lat);
      $lng = osm_shift($lng);
    }
  }
  
  
  if($lat != '' && $lng != '') {

    // Update item data
    if($is_user === false) {
      ItemLocation::newInstance()->update(
        array(
          'd_coord_lat' => $lat,
          'd_coord_long' => $lng
        ),
        array(
          'fk_i_item_id' => $item_id
        )
      );
      
    // Update user data
    } else {
      User::newInstance()->update(
        array(
          'd_coord_lat' => $lat,
          'd_coord_long' => $lng
        ),
        array(
          'pk_i_id' => $item_id
        )
      );
    }
  }
  
  
  // Update city coordinates in city table, in case we found city coords and one in city table are empty
  // Update osclass 410
  if($lat_city <> '' && $lng_city <> '' && $city_id > 0) {
    $city_table_fields = ModelOSM::newInstance()->getCityTableColumns();
    
    if(in_array('d_coord_lat', $city_table_fields) && in_array('d_coord_long', $city_table_fields)) {
      $city_row = City::newInstance()->findByPrimaryKey($city_id);
      
      if(isset($city_row['pk_i_id']) && (float)$city_row['d_coord_lat'] == 0 && (float)$city_row['d_coord_long'] == 0) { 
        City::newInstance()->dao->update(
          DB_TABLE_PREFIX . 't_city',
          array(
            'd_coord_lat' => $lat_city,
            'd_coord_long' => $lng_city
          ),
          array(
            'pk_i_id' => $city_id
          )
        );
      }
    }
  }

  return array('lat' => $lat, 'lng' => $lng);
}

osc_add_hook('posted_item', 'osm_cords_insert');
osc_add_hook('edited_item', 'osm_cords_insert');


// CORE FUNCTIONS
function osm_param($name) {
  return osc_get_preference($name, 'plugin-osm');
}


if(!function_exists('mb_param_update')) {
  function mb_param_update( $param_name, $update_param_name, $type = NULL, $plugin_var_name = NULL ) {
  
    $val = '';
    if( $type == 'check') {

      // Checkbox input
      if( Params::getParam( $param_name ) == 'on' ) {
        $val = 1;
      } else {
        if( Params::getParam( $update_param_name ) == 'done' ) {
          $val = 0;
        } else {
          $val = ( osc_get_preference( $param_name, $plugin_var_name ) != '' ) ? osc_get_preference( $param_name, $plugin_var_name ) : '';
        }
      }
    } else {

      // Other inputs (text, password, ...)
      if( Params::getParam( $update_param_name ) == 'done' && Params::existParam($param_name)) {
        $val = Params::getParam( $param_name );
      } else {
        $val = ( osc_get_preference( $param_name, $plugin_var_name) != '' ) ? osc_get_preference( $param_name, $plugin_var_name ) : '';
      }
    }


    // If save button was pressed, update param
    if( Params::getParam( $update_param_name ) == 'done' ) {

      if(osc_get_preference( $param_name, $plugin_var_name ) == '') {
        osc_set_preference( $param_name, $val, $plugin_var_name, 'STRING');  
      } else {
        $dao_preference = new Preference();
        $dao_preference->update( array( "s_value" => $val ), array( "s_section" => $plugin_var_name, "s_name" => $param_name ));
        osc_reset_preferences();
        unset($dao_preference);
      }
    }

    return $val;
  }
}


// CHECK IF RUNNING ON DEMO
function osm_is_demo() {
  if(osc_logged_admin_username() == 'admin') {
    return false;
  } else if(isset($_SERVER['HTTP_HOST']) && (strpos($_SERVER['HTTP_HOST'],'mb-themes') !== false || strpos($_SERVER['HTTP_HOST'],'abprofitrade') !== false)) {
    return true;
  } else {
    return false;
  }
}


if(!function_exists('message_ok')) {
  function message_ok( $text ) {
    $final  = '<div class="flashmessage flashmessage-ok flashmessage-inline">';
    $final .= $text;
    $final .= '</div>';
    echo $final;
  }
}


if(!function_exists('message_error')) {
  function message_error( $text ) {
    $final  = '<div class="flashmessage flashmessage-error flashmessage-inline">';
    $final .= $text;
    $final .= '</div>';
    echo $final;
  }
}



// COOKIES WORK
if(!function_exists('mb_set_cookie')) {
  function mb_set_cookie($name, $val) {
    Cookie::newInstance()->set_expires( 86400 * 30 );
    Cookie::newInstance()->push($name, $val);
    Cookie::newInstance()->set();
  }
}


if(!function_exists('mb_get_cookie')) {
  function mb_get_cookie($name) {
    return Cookie::newInstance()->get_value($name);
  }
}

if(!function_exists('mb_drop_cookie')) {
  function mb_drop_cookie($name) {
    Cookie::newInstance()->pop($name);
  }
}


if(!function_exists('mb_generate_rand_string')) {
  function mb_generate_rand_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

    return $randomString;
  }
}

if(!function_exists('osc_get_current_user_locations_native')) {
  function osc_get_current_user_locations_native() {
    return false;
  }
}

if(!function_exists('osc_location_native_name_selector')) {
  function osc_location_native_name_selector($array, $column = 's_name') {
    return @$array[$column];
  }
}


?>