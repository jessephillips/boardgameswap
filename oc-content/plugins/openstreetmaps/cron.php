<?php
  define('ABS_PATH', dirname(dirname(dirname(dirname(__FILE__)))) . '/');
  require_once ABS_PATH . 'oc-load.php';

  // ALLOWED PARAMETERS: limit (def 500)

  $limit = (Params::getParam('limit') > 0 ? Params::getParam('limit') : 500);
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

    echo sprintf(__('%d coordinates successfully found, %d coordinates not found.', 'openstreetmaps'), $success, $fail);

  } else {
    _e('There are no coordinates to be filled.', 'openstreetmaps');
  }
?>