<?php
  // Create menu
  $title = __('Configure', 'openstreetmaps');
  osm_menu($title);


  // GET & UPDATE PARAMETERS
  // $variable = mb_param_update( 'param_name', 'form_name', 'input_type', 'plugin_var_name' );
  // input_type: check or value
  
  if(Params::getParam('cleanCache') == 1) {
    ModelOSM::newInstance()->cleanCacheHistory();
    osc_add_flash_ok_message(__('Cache has been successfully cleaned', 'openstreetmaps'), 'admin');
    header('Location:' . osc_admin_base_url(true) . '?page=plugins&action=renderplugin&file=openstreetmaps/admin/cache.php');
    exit;
  }

  $limit = 5000;
  $logs = ModelOSM::newInstance()->getCacheHistory($limit);
?>


<div class="mb-body">

  <!-- CONFIGURE SECTION -->
  <div class="mb-box">
    <div class="mb-head"><i class="fa fa-database"></i> <?php _e('Cache Logs', 'openstreetmaps'); ?></div>

    <div class="mb-inside mb-minify">
      <div class="mb-notes">
        <div class="mb-line"><?php echo sprintf(__('In order to align with OpenStreetMaps usage policy, each query must be cached. You may find last %d stored coordinates queries.', 'openstreetmaps'), $limit); ?></div>
        <div class="mb-line"><?php _e('Each time it is required to get new coordinates, plugin first check into cache if requested coordinates already does not exists.', 'openstreetmaps'); ?></div>
      </div>
      
      <div class="mb-row">
        <a href="<?php echo osc_admin_base_url(true); ?>?page=plugins&action=renderplugin&file=openstreetmaps/admin/cache.php&cleanCache=1" class="mb-button-green mb-clean-cache"><?php _e('Clean cache history', 'openstreetmaps'); ?></a>
        <p></p>
      </div>
      
      <div class="mb-table">
        <div class="mb-table-head">
          <div class="mb-col-12 mb-align-left"><span><?php _e('Query', 'openstreetmaps');?></span></div>
          <div class="mb-col-8"><span><?php _e('Coordinate (lat, lng)', 'openstreetmaps');?></span></div>
          <div class="mb-col-4"><span><?php _e('Create date', 'openstreetmaps');?></span></div>
        </div>

        <?php if(count($logs) <= 0) { ?>
          <div class="mb-table-row mb-row-empty">
            <i class="fa fa-warning"></i><span><?php _e('No coordinate logs has been found', 'openstreetmaps'); ?></span>
          </div>
        <?php } else { ?>
          <?php foreach($logs as $l) { ?>
            <div class="mb-table-row">
              <div class="mb-col-12 mb-align-left"><?php echo urldecode($l['s_query']); ?></div>
              <div class="mb-col-8"><a target="_blank" href="https://www.openstreetmap.org/#map=16/<?php echo $l['d_coord_lat']; ?>/<?php echo $l['d_coord_long']; ?>"><?php echo $l['d_coord_lat']; ?>, <?php echo $l['d_coord_long']; ?></a></div>
              <div class="mb-col-4" style="color:#999;"><?php echo $l['dt_date']; ?></div>
            </div>
          <?php } ?>
        <?php } ?>
      </div>
    </div>
  </div>
</div>


<?php echo osm_footer(); ?>