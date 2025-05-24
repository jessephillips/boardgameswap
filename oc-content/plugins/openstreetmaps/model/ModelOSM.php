<?php
class ModelOSM extends DAO {
private static $instance;

public static function newInstance() {
  if( !self::$instance instanceof self ) {
    self::$instance = new self;
  }
  return self::$instance;
}

function __construct() {
  parent::__construct();
}


public function getTable_item() {
  return DB_TABLE_PREFIX.'t_item';
}

public function getTable_item_location() {
  return DB_TABLE_PREFIX.'t_item_location';
}

public function getTable_user() {
  return DB_TABLE_PREFIX.'t_user';
}

public function getTable_category() {
  return DB_TABLE_PREFIX.'t_category';
}

public function getTable_cache() {
  return DB_TABLE_PREFIX.'t_osm_cache';
}

public function getTable_city() {
  return DB_TABLE_PREFIX.'t_city';
}

public function getTable_region() {
  return DB_TABLE_PREFIX.'t_region';
}


public function import($file) {
  $path = osc_plugin_resource($file);
  $sql = file_get_contents($path);

  if(!$this->dao->importSQL($sql) ){
    throw new Exception("Error importSQL::ModelOSM<br>" . $file . "<br>" . $this->dao->getErrorLevel() . " - " . $this->dao->getErrorDesc() );
  }
}


public function install($version = '') {
  if($version == '') {
    $this->import('openstreetmaps/model/struct.sql');

    osc_set_preference('version', 100, 'plugin-osm', 'INTEGER');
  }
}


public function uninstall() {
  // DELETE ALL TABLES
  //$this->dao->query(sprintf('DROP TABLE %s', $this->getTable_attribute()));


  // DELETE ALL PREFERENCES
  $db_prefix = DB_TABLE_PREFIX;
  $query = "DELETE FROM {$db_prefix}t_preference WHERE s_section = 'plugin-osm'";
  $this->dao->query($query);
}


public function getLocations($lat, $lng, $rad) {
  $this->dao->select('distinct fk_i_city_id, s_city, fk_i_region_id, s_region, fk_c_country_code, s_country');
  $this->dao->from($this->getTable_item_location());

  $measurement = 6371;  // 3959 for miles 

  //$this->dao->where(sprintf('( POWER(%st_item_location.d_coord_lat - %f, 2) + POWER(%st_item_location.d_coord_long - %f, 2) <= POWER(%f, 2) )', DB_TABLE_PREFIX, (float)$lat, DB_TABLE_PREFIX, (float)$lng, (float)$rad));
  $this->dao->where(sprintf('(%d * acos(cos(radians(%f)) * cos(radians(%st_item_location.d_coord_lat)) * cos(radians(%st_item_location.d_coord_long) - radians(%f)) + sin(radians(%f)) * sin(radians(%st_item_location.d_coord_lat)))) <= %f', (int)$measurement, (float)$lat, DB_TABLE_PREFIX, DB_TABLE_PREFIX, (float)$lng, (float)$lat, DB_TABLE_PREFIX, (float)$rad));


  $result = $this->dao->get();
  if(!$result) { 
    return array(); 
  }

  $prepare = $result->result();
  return $prepare;
}


public function getCityTableColumns() {
  $result = $this->dao->query('SHOW COLUMNS FROM ' . DB_TABLE_PREFIX . 't_city');

  if(!$result) { 
    return array(); 
  }

  $output = array();
  $prepare = $result->result();
   
  foreach($prepare as $p) {
    $output[] = $p['Field'];
  }
  
  return $output;
}

public function getMissing($limit) {
  $this->dao->select();
  $this->dao->from($this->getTable_item_location());

  $this->dao->where('d_coord_lat is null and d_coord_long is null');
  $this->dao->limit($limit);

  $result = $this->dao->get();
  if(!$result) { 
    return array(); 
  }

  $prepare = $result->result();
  return $prepare;
}


public function getCache($query) {
  $this->dao->select();
  $this->dao->from($this->getTable_cache());

  $this->dao->where('s_query', trim(strtolower($query)));
  $this->dao->limit(1);

  $result = $this->dao->get();

  if(!$result) { 
    return false; 
  }

  $prepare = $result->row();

  if(!isset($prepare['d_coord_lat']) || !isset($prepare['d_coord_long']) || ($prepare['d_coord_lat'] == 0 && $prepare['d_coord_long'] == 0)) {
    return false;
  } 

  return $prepare;
}


public function getCacheHistory($limit = 500) {
  $this->dao->select();
  $this->dao->from($this->getTable_cache());

  $this->dao->orderby('pk_i_id DESC');
  $this->dao->limit($limit);

  $result = $this->dao->get();

  if(!$result) { 
    return array(); 
  }

  $prepare = $result->result();
  return $prepare;
}


public function updateCache($query, $lat, $lng) {
  if($lat == 0 && $lng == 0) {
    return false;
  }
 
  $check = $this->getCache($query);

  if(!$check) {
    $values = array(
      's_query' => trim(strtolower($query)),
      'd_coord_lat' => $lat,
      'd_coord_long' => $lng
    );

    $this->dao->insert($this->getTable_cache(), $values);
  }
}



public function getMissingCount($failed = false) {
  $this->dao->select('count(*) as i_count');
  $this->dao->from($this->getTable_item_location());

  if(!$failed) {
    $this->dao->where('d_coord_lat is null AND d_coord_long is null');
  } else {
    $this->dao->where('d_coord_lat is not null AND d_coord_long is not null AND d_coord_lat = 0 AND d_coord_long = 0');
  }

  $result = $this->dao->get();
  if(!$result) { 
    return 0; 
  }

  $prepare = $result->row();
  return $prepare['i_count'];
}


public function clearCords() {
  $value = array(
    'd_coord_lat' => null,
    'd_coord_long' => null
  );

  $this->dao->update($this->getTable_item_location(), $value, array());
}


public function checkCords($lat, $lng) {
  if($lat == 0 && $lng == 0) {
    return false;
  }

  $this->dao->select();
  $this->dao->from($this->getTable_item_location());

  $this->dao->where('d_coord_lat', round($lat, 6));
  $this->dao->where('d_coord_long', round($lng, 6));

  $result = $this->dao->get();

  if($result) { 
    $row = $result->row();

    if(@$row['fk_i_item_id'] > 0) {
      return true;   // there exist item with same coordinates
    }
  }

  return false;
}


public function cleanCacheHistory() {
  return $this->dao->query(sprintf('DELETE FROM %s', $this->getTable_cache()));
}


// GET CLOSEST CITY
public function findClosestCity($lat, $lon, $radius = 50) {
  //$distance_select_col = sprintf('SQRT(POWER(c.d_coord_lat - %f, 2) + POWER(c.d_coord_long - %f, 2)) as d_distance', (float)$lat, (float)$lon);

  $measurement = 6371;  // 3959 for miles 
  $distance_select_col = sprintf('(%d * acos(cos(radians(%f)) * cos(radians(c.d_coord_lat)) * cos(radians(c.d_coord_long) - radians(%f)) + sin(radians(%f)) * sin(radians(c.d_coord_lat))))', (int)$measurement, (float)$lat, (float)$lon, (float)$lat);
  
  if(function_exists('osc_get_current_user_locations_native') && osc_get_current_user_locations_native() == 1) {
    $this->dao->select('c.pk_i_id as fk_i_city_id, c.fk_i_region_id, c.fk_c_country_code, c.s_name as s_city, c.s_name_native as s_city_native, r.s_name as s_region, r.s_name_native as s_region_native, c.d_coord_lat, c.d_coord_long, ' . $distance_select_col . ' as d_distance');
  } else {
    $this->dao->select('c.pk_i_id as fk_i_city_id, c.fk_i_region_id, c.fk_c_country_code, c.s_name as s_city, "" as s_city_native, r.s_name as s_region, "" as s_region_native, c.d_coord_lat, c.d_coord_long, ' . $distance_select_col . ' as d_distance');
  }

  $this->dao->from($this->getTable_city() . ' as c');
  $this->dao->join($this->getTable_region() . ' as r', 'c.fk_i_region_id = r.pk_i_id', 'INNER');

  $this->dao->where('c.d_coord_lat is not null');
  $this->dao->where('c.d_coord_long is not null');
  $this->dao->where('coalesce(' . $distance_select_col . ', 9999) <= ' . $radius);
  //$this->dao->where('d_distance < 50');
  $this->dao->orderby('d_distance', 'ASC');
  $this->dao->limit(1);

  $result = $this->dao->get();
  
  if($result) {
    $data = $result->row();
    return $data;
  }

  return false;
}


}
?>