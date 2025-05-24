<?php
  $height = (osm_param('height_publish') <= 0 ? 360 : osm_param('height_publish'));
  $zoom = (osm_param('zoom') <= 0 ? 13 : osm_param('zoom'));

  $has_cords = false;
  $cords = osm_retrieve_cord(osc_item() ? osc_item() : osc_user());

  if(@$cords['lat'] <> 0 && @$cords['lng'] <> 0) {
    $has_cords = true;
  }

  if(osc_item_latitude() <> 0 && osc_item_longitude() <> 0) {
    $has_cords = true;
    $cords['lat'] = osc_item_latitude();
    $cords['lng'] = osc_item_longitude();
  }  
?>

<link rel="stylesheet" href="<?php echo osc_base_url(); ?>oc-content/plugins/openstreetmaps/css/user.css?v=<?php echo date('YmdHis'); ?>" crossorigin="anonymous" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.4.0/leaflet.css" integrity="sha256-YR4HrDE479EpYZgeTkQfgVJq08+277UXxMLbi/YP69o=" crossorigin="anonymous" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.4.0/leaflet.js" integrity="sha256-6BZRSENq3kxI4YYBDqJ23xg0r1GwTHEpvp3okdaIqBw=" crossorigin="anonymous"></script>

<?php if(osm_param('fullscreen_publish') == 1) { ?>
  <script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/Leaflet.fullscreen.min.js'></script>
  <link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css' rel='stylesheet' />
<?php } ?>

<?php if(osm_param('coordinate_fields') == 1) { ?>
  <input type="hidden" name="d_coord_lat" value="<?php echo osc_item_latitude(); ?>" />
  <input type="hidden" name="d_coord_long" value="<?php echo osc_item_longitude(); ?>" />
<?php } ?>

<div id="publishMap" style="width: 100%; height:<?php echo $height; ?>px;margin:0 0 25px 0;z-index:1;" data-theme="<?php echo osc_esc_html(osc_current_web_theme()); ?>"></div>

<script>
  var lat = <?php echo ($has_cords ? $cords['lat'] : 40.6971); ?>;
  var lng = <?php echo ($has_cords ? $cords['lng'] : -74.2598); ?>;

  var myMarker = L.marker([lat, lng], {draggable: true}).on('dragend', function(e) {
    var lat = e.target._latlng.lat;         //String(myMarker.getLatLng().lat);
    var lng = e.target._latlng.lng;

    osmGetAddress(myMarker, lat, lng);

  }).on('click', function(e) {
    return false;
  });


  // DEFINE MAP
  var osmMap = L.map('publishMap'<?php if(osm_param('fullscreen_publish') == 1) { ?>, {fullscreenControl:{pseudoFullscreen:true}}<?php } ?>).setView([lat, lng], <?php echo $zoom; ?>).on('click', function(e) {
    var lat = e.latlng.lat;
    var lng = e.latlng.lng;
    myMarker.setLatLng(new L.LatLng(lat, lng)).addTo(osmMap);

    osmGetAddress(myMarker, lat, lng);
  });

  L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=<?php echo osm_param('token'); ?>', {maxZoom: 18, id: 'mapbox/streets-v12', attribution: '&copy; <a href="https://www.mapbox.com/about/maps/">Mapbox</a> &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'}).addTo(osmMap);

  <?php if($has_cords) { ?>
    // ADD MARKER IF USER HAS LOCATION FILLED
    myMarker.addTo(osmMap).bindPopup('<div class="osm-address"><?php echo osc_esc_js(osm_item_address(osc_item() ? osc_item() : osc_user())); ?></div>').openPopup();
  <?php } ?>


  // DEFAULT MARKER ADDED IF ITEM OR USER HAS NO COORDINATES
  <?php if(!$has_cords) { ?>
    myMarker.setLatLng(new L.LatLng(lat, lng)).addTo(osmMap);
    //osmGetAddress(myMarker, lat, lng);
  <?php } ?>


  <?php if(!$has_cords) { ?>
  // GEO LOCATOR - SET VIEW TO CURRENT USER ADDRESS
  navigator.geolocation.getCurrentPosition(function(location) {
    var lat = location.coords.latitude;
    var lng = location.coords.longitude;

    osmMap.setView([lat, lng], <?php echo $zoom; ?>);
  });
  <?php } ?>



// GET ADDRESS FROM LATITUDE AND LONGITUDE
function osmGetAddress(marker, lat, lon) {
  if(lat != 0 && lon != 0) {
    <?php if(osm_param('publish_map_search_version') < 2) { ?>
      $.ajax({
        type: 'GET',
        url: location.protocol + '//nominatim.openstreetmap.org/reverse.php?format=json&accept-language=<?php echo osc_current_user_locale(); ?>&lat='+lat+'&lon=' + lon,
        success: function(data) {
          osmMarkerAddressBind(marker, data);
        }
      });
    <?php } else if(osm_param('publish_map_search_version') == 2) { ?>
      $.ajax({
        type: 'GET',
        dataType: "json",
        url: '<?php echo osc_base_url(true); ?>?osmFindClosestCity=1&lat='+lat+'&lon=' + lon,
        success: function(data) {
          osmMarkerAddressBind(marker, data, lat, lon);
        }
      });

      // FILL ADDRESS, ZIP
      $.ajax({
        type: 'GET',
        dataType: "json",
        url: location.protocol + '//nominatim.openstreetmap.org/reverse.php?format=json&accept-language=<?php echo osc_current_user_locale(); ?>&lat='+lat+'&lon=' + lon,
        success: function(response) {
          $('input[name="cityArea"]').val(osmDef(response.address.city_district));
          $('input[name="zip"]').val(osmGetZip(response));
          $('input[name="address"]').val((osmDef(response.address.road) + ' ' + osmDef(response.address.house_number)).trim());
          $('input[name="d_coord_lat"]').val(lat);
          $('input[name="d_coord_long"]').val(lon);
          marker.bindPopup('<div class="osm-address">' + osmDef(response.display_name) + '</div>').openPopup();
        }
      });
    <?php } ?>
  }

  return false;
}


// FILL ADDRESS TO ALL POSSIBLE PLACES
function osmMarkerAddressBind(marker, data, lat = '', lon = '') {
  <?php if(osm_param('publish_map_search_version') < 2) { ?>
  
    if(data !== false) {
      $('input[name="regionId"], input[name="cityId"]').val('');
      
      $('input[name="d_coord_lat"]').val(lat != '' ? lat : data.lat);
      $('input[name="d_coord_long"]').val(lon != '' ? lon : data.lon);
      $('input[name="sCountry"], input[name="country"]').val(osmDef(data.address.country));
      $('input[name="countryId"]').val(osmDef(data.address.country_code));
      $('input[name="sRegion"], input[name="region"]').val(osmDef(data.address.state)); 
      $('input[name="sCity"], input[name="city"]').val(osmGetCity(data)); 
      $('input[name="cityArea"]').val(osmDef(data.address.city_district));
      $('input[name="zip"]').val(osmGetZip(data));
      $('input[name="address"]').val((osmDef(data.address.road) + ' ' + osmDef(data.address.house_number)).trim());
      $('input[name="term"]').val((osmGetCity(data)).trim() + ', ' + osmDef(data.address.state) + ', ' + osmDef(data.address.country_code).toUpperCase());


      // SET LOCATION VALUES TO CASCADING DROPDOWNS
      if($('select[name="countryId"]').length) { 
        $('input[name="sCountry"], input[name="country"], input[name="countryId"]').val('').attr('disabled', true);
        $('select[name="countryId"] option[value=' + (osmDef(data.address.country_code).toUpperCase()) + ']').attr('selected','selected').change();
        $('select[name="countryId"]').parent().find('span:first-child').text($('select[name="countryId"]').find('option:selected').text());
      }

      setTimeout(function(){ 
        if($('select[name="regionId"]').length) {
          $('input[name="sRegion"], input[name="region"], input[name="regionId"]').val('').attr('disabled', true);
          $('select[name="regionId"] option:contains(' + osmDef(data.address.state) + ')').attr('selected','selected').change();
          $('select[name="regionId"]').parent().find('span:first-child').text($('select[name="regionId"]').find('option:selected').text());
        }
      }, 500);

      setTimeout(function(){ 
        if($('select[name="cityId"]').length) {
          $('input[name="sCity"], input[name="city"], input[name="cityId"]').val('').attr('disabled', true);
          $('select[name="cityId"] option:contains(' + osmGetCity(data) + ')').attr('selected','selected').change();
          $('select[name="cityId"]').parent().find('span:first-child').text($('select[name="cityId"]').find('option:selected').text());
        }
      }, 1200);
      
      marker.bindPopup('<div class="osm-address">' + osmDef(data.display_name) + '</div>').openPopup();
      $('input.term, input.term2').val(data.display_name);
    }
    
  <?php } else if(osm_param('publish_map_search_version') == 2) { ?>
    if(data.status == 'OK') {
      $('input[name="d_coord_lat"]').val(lat != '' ? lat : data.d_coord_lat);
      $('input[name="d_coord_long"]').val(lon != '' ? lon : data.d_coord_long);
    
      $('select[name="countryId"], input[name="sCountry"], input[name="country"], input[name="countryId"]').val(data.fk_c_country_code);
      $('select[name="regionId"], input[name="sRegion"], input[name="region"], input[name="regionId"]').val(data.fk_i_region_id);
      $('select[name="cityId"], input[name="sCity"], input[name="city"], input[name="cityId"]').val(data.fk_i_city_id);

      // SET LOCATION VALUES TO CASCADING DROPDOWNS
      if($('select[name="countryId"]').length) { 
        $('input[name="sCountry"], input[name="country"], input[name="countryId"]').val('').attr('disabled', true);
        $('select[name="countryId"]').val(data.fk_c_country_code).change();
        $('select[name="countryId"]').parent().find('span:first-child').text($('select[name="countryId"]').find('option:selected').text());
      }

      setTimeout(function(){ 
        if($('select[name="regionId"]').length) {
          $('input[name="sRegion"], input[name="region"], input[name="regionId"]').val('').attr('disabled', true);
          $('select[name="regionId"]').val(data.fk_i_region_id).change();
          $('select[name="regionId"]').parent().find('span:first-child').text($('select[name="regionId"]').find('option:selected').text());
        }
      }, 500);

      setTimeout(function(){ 
        if($('select[name="cityId"]').length) {
          $('input[name="sCity"], input[name="city"], input[name="cityId"]').val('').attr('disabled', true);
          $('select[name="cityId"]').val(data.fk_i_city_id).change();
          $('select[name="cityId"]').parent().find('span:first-child').text($('select[name="cityId"]').find('option:selected').text());
        }
      }, 1000);
      
      //marker.bindPopup('<div class="osm-address">' + data.s_display_name + '</div>').openPopup();
      $('input.term, input.term2').val(data.s_display_name);
    } else {
      console.log(data); 
    }
    
  <?php } ?>
}

function osmDef(data) {
  if(data !== undefined) {
    return data;
  }

  return '';
}

function osmGetCity(data) {
  if(osmDef(data.address.city) != '') {
    return osmDef(data.address.city);
  } else if(osmDef(data.address.town) != '' && osmDef(data.address.town) != osmDef(data.address.city_district)) {
    return osmDef(data.address.town);
  } else if(osmDef(data.address.hamlet) != '') {
    return osmDef(data.address.hamlet);
  } else if(osmDef(data.address.county) != '') {
    return osmDef(data.address.county);
  } else if(osmDef(data.address.village) != '') {
    return osmDef(data.address.village);
  } else if(osmDef(data.address.city_district) != '') {
    return osmDef(data.address.city_district);
  } else if(osmDef(data.address.town) != '') {
    return osmDef(data.address.town);
  }

  return '';
}

function osmGetZip(data) {
  if(osmDef(data.address.post_code) != '') {
    return osmDef(data.address.post_code).replace(/\s/g,'');
  } else if(osmDef(data.address.postcode) != '') {
    return osmDef(data.address.postcode).replace(/\s/g,'');
  }

  return '';
}
</script>
