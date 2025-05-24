<?php
  osc_reset_latest_items();

  $height = (osm_param('height_home') <= 0 ? 480 : osm_param('height_home'));
  $zoom = (osm_param('zoom') <= 0 ? 13 : osm_param('zoom'));

  $lats = array();
  $lngs = array();

  while(osc_has_latest_items()) {
    if(osc_item_latitude() <> 0 && osc_item_longitude() <> 0) {
      $lats[] = osc_item_latitude();
      $lngs[] = osc_item_longitude();
    }
  }

  osc_reset_latest_items();

?>

<?php if(osc_count_latest_items() > 0 && !empty($lats)) { ?>
  <link rel="stylesheet" href="<?php echo osc_base_url(); ?>oc-content/plugins/openstreetmaps/css/user.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.4.0/leaflet.css" integrity="sha256-YR4HrDE479EpYZgeTkQfgVJq08+277UXxMLbi/YP69o=" crossorigin="anonymous" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.4.0/leaflet.js" integrity="sha256-6BZRSENq3kxI4YYBDqJ23xg0r1GwTHEpvp3okdaIqBw=" crossorigin="anonymous"></script>

  <?php if(osm_param('fullscreen_home') == 1) { ?>
    <script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/Leaflet.fullscreen.min.js'></script>
    <link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css' rel='stylesheet' />
  <?php } ?>

  <div id="searchMap" style="width: 100%; height:<?php echo $height; ?>px;margin:0 0 25px 0;" data-theme="<?php echo osc_esc_html(osc_current_web_theme()); ?>"></div>

  <script>
    var osmMap = L.map('searchMap'<?php if(osm_param('fullscreen_home') == 1) { ?>, {fullscreenControl:{pseudoFullscreen:true}}<?php } ?>).setView([40.6971,-74.2598], <?php echo $zoom; ?>);
    L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=<?php echo osm_param('token'); ?>', {maxZoom: 18, id: 'mapbox/streets-v12', attribution: '&copy; <a href="https://www.mapbox.com/about/maps/">Mapbox</a> &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'}).addTo(osmMap);
    osmMap.fitBounds([[<?php echo min($lats); ?>, <?php echo min($lngs); ?>],[<?php echo max($lats); ?>, <?php echo max($lngs); ?>]]);

    <?php 
      while(osc_has_latest_items()) {
        $item = osc_item();
        $img = ItemResource::newInstance()->getResource($item['pk_i_id']);

        if(isset($img['pk_i_id']) && $img['pk_i_id'] > 0) {
          $img_link = osc_base_url() . $img['s_path'] . $img['pk_i_id'] . '_thumbnail.' . $img['s_extension'];
        } else {
          $img_link = false;
        }

        if(osc_item_latitude() <> 0 && osc_item_longitude() <> 0) {
          ?>
            var osmItemCard = '<a href="<?php echo osc_item_url(); ?>"><?php if($img_link) { ?><img src="<?php echo $img_link; ?>" /><?php } ?><strong><?php echo osc_esc_js(osc_item_title()); ?></strong><span><?php echo osc_esc_js(osc_item_formatted_price()); ?></span></a>';
            L.marker([<?php echo osc_item_latitude(); ?>, <?php echo osc_item_longitude(); ?>]).addTo(osmMap).bindPopup(osmItemCard, {minWidth: 150,maxWidth: 150});
          <?php
        }
      }
    ?>
  </script>

  <?php osc_reset_latest_items(); ?>
<?php } ?>
  