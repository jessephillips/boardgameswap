<?php
  $height = (osm_param('height_user') <= 0 ? 360 : osm_param('height_user'));
  $zoom = (osm_param('zoom') <= 0 ? 13 : osm_param('zoom'));
  
  $lat = 0;
  $lng = 0;
  
  $user = osc_user();
  $query = osm_query_string2($user);
  $location = implode(', ', array_filter(array(@$user['s_country'], @$user['s_region'], @$user['s_city'], @$user['s_address'])));

  if(@$user['d_coord_lat'] <> '' && @$user['d_coord_long'] <> '') {
    $lat = $user['d_coord_lat'];
    $lng = $user['d_coord_long'];
    
  } else if($query <> '') {
    $cords = osm_cords_insert($user, true);

    $lat = $cords['lat'];
    $lng = $cords['lng'];
  }
?>


<?php if($lat <> 0 && $lng <> 0) { ?>
  <link rel="stylesheet" href="<?php echo osc_base_url(); ?>oc-content/plugins/openstreetmaps/css/user.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.4.0/leaflet.css" integrity="sha256-YR4HrDE479EpYZgeTkQfgVJq08+277UXxMLbi/YP69o=" crossorigin="anonymous" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.4.0/leaflet.js" integrity="sha256-6BZRSENq3kxI4YYBDqJ23xg0r1GwTHEpvp3okdaIqBw=" crossorigin="anonymous"></script>

  <?php if(osm_param('fullscreen_item') == 1) { ?>
    <script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/Leaflet.fullscreen.min.js'></script>
    <link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css' rel='stylesheet' />
  <?php } ?>

  <div id="itemMap" style="width: 100%; height:<?php echo $height; ?>px;" data-theme="<?php echo osc_esc_html(osc_current_web_theme()); ?>"></div>

  <script>
    var mainIcon = L.icon({ iconUrl: '<?php echo osc_base_url(); ?>oc-content/plugins/openstreetmaps/img/icon-main.png', iconSize: [25, 41], iconAnchor: [13,41], popupAnchor: [0, -45], shadowUrl: '<?php echo osc_base_url(); ?>oc-content/plugins/openstreetmaps/img/icon-shadow.png', shadowSize: [41,41], shadowAnchor: [13,41]});

    var osmMap = L.map('itemMap'<?php if(osm_param('fullscreen_item') == 1) { ?>, {fullscreenControl:{pseudoFullscreen:true}}<?php } ?>).setView([<?php echo $lat; ?>, <?php echo $lng; ?>], <?php echo $zoom; ?>);

    var osmItemCard = '<a href="<?php echo osc_user_public_profile_url(osc_user_id()); ?>"><strong><?php echo osc_esc_js(osc_user_name()); ?></strong><span><?php echo osc_esc_js($location); ?></span></a>';
    L.marker([<?php echo $lat; ?>, <?php echo $lng; ?>], {icon: mainIcon, zIndexOffset: 5}).addTo(osmMap).bindPopup(osmItemCard, {minWidth: 150,maxWidth: 150});
    L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=<?php echo osm_param('token'); ?>', {maxZoom: 18, id: 'mapbox/streets-v12', attribution: '&copy; <a href="https://www.mapbox.com/about/maps/">Mapbox</a> &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'}).addTo(osmMap);
  </script>
<?php } ?>
