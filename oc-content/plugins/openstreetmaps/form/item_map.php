<?php
  $height = (osm_param('height_item') <= 0 ? 240 : osm_param('height_item'));
  $zoom = (osm_param('zoom') <= 0 ? 13 : osm_param('zoom'));
  
  $item = osc_item();
  $img = ItemResource::newInstance()->getResource($item['pk_i_id']);

  if(isset($img['pk_i_id']) && $img['pk_i_id'] > 0) {
    $img_link = osc_base_url() . $img['s_path'] . $img['pk_i_id'] . '_thumbnail.' . $img['s_extension'];
  } else {
    $img_link = false;
  }

  
  $lat = 0;
  $lng = 0;
  
  $query = osm_query_string2($item);
  $location = implode(', ', array_filter(array(@$item['s_country'], @$item['s_region'], @$item['s_city'], @$item['s_address'])));

  if(@$item['d_coord_lat'] <> '' && @$item['d_coord_long'] <> '') {
    $lat = $item['d_coord_lat'];
    $lng = $item['d_coord_long'];
    
  } else if($query <> '') {
    $cords = osm_cords_insert($item);

    $lat = $cords['lat'];
    $lng = $cords['lng'];
  }
  
  $circle_color = (osm_param('item_draw_circle_color') <> '' ? osm_param('item_draw_circle_color') : '#0000e8');
  $circle_radius = intval(osm_param('item_draw_circle_radius') > 0 ? osm_param('item_draw_circle_radius') : 500);
?>

<?php if($lat <> 0 && $lng <> 0) { ?>
  <link rel="stylesheet" href="<?php echo osc_base_url(); ?>oc-content/plugins/openstreetmaps/css/user.css?v=<?php echo date('YmdHis'); ?>" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.4.0/leaflet.css" integrity="sha256-YR4HrDE479EpYZgeTkQfgVJq08+277UXxMLbi/YP69o=" crossorigin="anonymous" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.4.0/leaflet.js" integrity="sha256-6BZRSENq3kxI4YYBDqJ23xg0r1GwTHEpvp3okdaIqBw=" crossorigin="anonymous"></script>

  <?php if(osm_param('fullscreen_item') == 1) { ?>
    <script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/Leaflet.fullscreen.min.js'></script>
    <link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css' rel='stylesheet' />
  <?php } ?>

  <div id="itemMap" style="width: 100%; height:<?php echo $height; ?>px;" data-theme="<?php echo osc_esc_html(osc_current_web_theme()); ?>">
    <?php if(osm_param('item_map_load_on_click') == 1) { ?>
      <div class="osm-open-item-map osm-map-button">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M192 0C85.903 0 0 86.014 0 192c0 71.117 23.991 93.341 151.271 297.424 18.785 30.119 62.694 30.083 81.457 0C360.075 285.234 384 263.103 384 192 384 85.903 297.986 0 192 0zm0 464C64.576 259.686 48 246.788 48 192c0-79.529 64.471-144 144-144s144 64.471 144 144c0 54.553-15.166 65.425-144 272zm-80-272c0-44.183 35.817-80 80-80s80 35.817 80 80-35.817 80-80 80-80-35.817-80-80z"/></svg>
        
        <div class="osm-detail">
          <strong><?php _e('Click to show map', 'openstreetmaps'); ?></strong>

          <span><?php echo implode(', ', array_filter(array(osc_item_city(), osc_item_region()))); ?></span>
          <span><?php echo implode(', ', array_filter(array(osc_item_zip(), osc_item_city_area(), osc_item_address()))); ?></span>
          <span><?php echo implode(', ', array_filter(array(osc_item_latitude(), osc_item_longitude()))); ?></span>
          <span><?php echo osc_item_country(); ?></span>

        </div>
        
        <div class="osm-map-img">
          <div class="osm-map-img-elem"></div>
          <div class="osm-map-bg-elem"></div>
          <div class="osm-map-bg2-elem"></div>
        </div>
      </div>
    <?php } ?>
  </div>

  <script type="text/javascript">
    // Generate item map on click or on load
    function osmItemMapGenerate() {
      // Define main pin
      var mainIcon = L.icon({ iconUrl: '<?php echo osc_base_url(); ?>oc-content/plugins/openstreetmaps/img/icon-main.png', iconSize: [25, 41], iconAnchor: [13,41], popupAnchor: [0, -45], shadowUrl: '<?php echo osc_base_url(); ?>oc-content/plugins/openstreetmaps/img/icon-shadow.png', shadowSize: [41,41], shadowAnchor: [13,41]});

      
      // Create map
      var osmMap = L.map('itemMap', {
        <?php if(osm_param('fullscreen_item') == 1) { ?>
          fullscreenControl: {pseudoFullscreen:true},
        <?php } ?>
        maxZoom: 18
      }).setView([<?php echo $lat; ?>, <?php echo $lng; ?>], <?php echo $zoom; ?>);


      <?php if(osm_param('item_draw_circle') == 1) { ?>
        // Add radius circle around item pin
        var osmItemCircle = L.circle([<?php echo $lat; ?>, <?php echo $lng; ?>], <?php echo $circle_radius; ?>, {
          weight: 1,
          color: '<?php echo $circle_color; ?>',
          opacity: 0.7,
          fillColor: '<?php echo $circle_color; ?>',
          fillOpacity: 0.2,
          interactive: false
        }).addTo(osmMap);
      <?php } ?>


      // Item main card
      var osmItemCard = '<a href="<?php echo osc_item_url(); ?>"><?php if($img_link) { ?><img src="<?php echo $img_link; ?>" /><?php } ?><strong><?php echo osc_esc_js(osc_item_title()); ?></strong><span><?php echo osc_esc_js(osc_item_formatted_price()); ?></span></a>';
      L.marker([<?php echo $lat; ?>, <?php echo $lng; ?>], {
        icon: mainIcon, 
        zIndexOffset: 5
      }).addTo(osmMap).bindPopup(osmItemCard, {
        minWidth: 150,
        maxWidth: 150
      });
      

      // Add layer to map from mapbox
      L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=<?php echo osm_param('token'); ?>', {
        maxZoom: 18, 
        id: 'mapbox/streets-v12', 
        attribution: '&copy; <a href="https://www.mapbox.com/about/maps/">Mapbox</a> &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
      }).addTo(osmMap);

      <?php 
        $lats = array($lat);
        $lngs = array($lng);

        if(osm_param('rel_enable') == 1) {
          $limit = osm_param('rel_max');

          $mSearch = new Search();

          if(osm_param('rel_cat') == 1) {
            $mSearch->addCategory(osc_item_category_id());
          }

          $item_country = osc_item_country_code() <> '' ? osc_item_country_code() : osc_item_country();
          $item_region = osc_item_region_id() <> '' ? osc_item_region_id() : osc_item_region();
          $item_city = osc_item_city_id() <> '' ? osc_item_city_id() : osc_item_city();

          if($item_country <> '') {
            $mSearch->addCountry($item_country);
          }

          if($item_region <> '' && osm_param('rel_loc') >= 2) {
            $mSearch->addRegion($item_region);
          }

          if($item_city <> '' && osm_param('rel_loc') >= 3) {
            $mSearch->addCity($item_city);
          }


          $mSearch->limit(1, $limit);
          $mSearch->addItemConditions(sprintf("%st_item.pk_i_id <> %d", DB_TABLE_PREFIX, osc_item_id()));
          $mSearch->addItemConditions(sprintf("%st_item_location.d_coord_lat <> 0 AND %st_item_location.d_coord_long <> 0", DB_TABLE_PREFIX, DB_TABLE_PREFIX));

          $aItems = $mSearch->doSearch(); 


          GLOBAL $osm_items;
          $osm_items = View::newInstance()->_get('items');
          View::newInstance()->_exportVariableToView('items', $aItems); 

          while(osc_has_items()) {
            if(osc_item_latitude() <> 0 && osc_item_longitude() <> 0) {
              $lats[] = osc_item_latitude();
              $lngs[] = osc_item_longitude();
            }

            $item = osc_item();
            $img = ItemResource::newInstance()->getResource($item['pk_i_id']);

            if(isset($img['pk_i_id']) && $img['pk_i_id'] > 0) {
              $img_link = osc_base_url() . $img['s_path'] . $img['pk_i_id'] . '_thumbnail.' . $img['s_extension'];
            } else {
              $img_link = false;
            }
            ?>
              // Generate related items pins
              var osmItemCard = '<a href="<?php echo osc_item_url(); ?>"><?php if($img_link) { ?><img src="<?php echo $img_link; ?>" /><?php } ?><strong><?php echo osc_esc_js(osc_item_title()); ?></strong><span><?php echo osc_esc_js(osc_item_formatted_price()); ?></span></a>';
              L.marker([<?php echo osc_item_latitude(); ?>, <?php echo osc_item_longitude(); ?>]).addTo(osmMap).bindPopup(osmItemCard, {minWidth: 150,maxWidth: 150});
            <?php
          }


          GLOBAL $stored_items;
          View::newInstance()->_exportVariableToView('items', $osm_items);
        } 
      ?>

      <?php if(osm_param('rel_enable') == 1 && count($lats) > 1) { ?>
        // Auto-focus and zoom map. Avoid zooming too much.
        osmMap.fitBounds([[<?php echo min($lats); ?>, <?php echo min($lngs); ?>],[<?php echo max($lats); ?>, <?php echo max($lngs); ?>]], {
          maxZoom: 15
        });
      <?php } ?>
    }
    
    <?php if(osm_param('item_map_load_on_click') != 1) { ?>
      osmItemMapGenerate();
      
    <?php } else { ?>
      $(document).ready(function() {
        $('body').on('click', '.osm-open-item-map', function(e) {
          e.preventDefault();
          $(this).hide(0);
          osmItemMapGenerate();
        });        
      });    
    <?php } ?>
  </script>
<?php } ?>
