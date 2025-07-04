<?php
class ModelDEL extends DAO {
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


public function getTable_delta() {
  return DB_TABLE_PREFIX.'t_item_delta';
}

public function getTable_city() {
  return DB_TABLE_PREFIX.'t_city';
}

public function getTable_city_stats() {
  return DB_TABLE_PREFIX.'t_city_stats';
}

public function getTable_region() {
  return DB_TABLE_PREFIX.'t_region';
}


public function getTable_category() {
  return DB_TABLE_PREFIX.'t_category';
}

public function getTable_category_description() {
  return DB_TABLE_PREFIX.'t_category_description';
}


public function import($file) {
  $path = osc_base_path() . $file;
  $sql = file_get_contents($path);

  if(!$this->dao->importSQL($sql) ){
    throw new Exception("Error importSQL::ModelDEL<br>" . $file . "<br>" . $this->dao->getErrorLevel() . " - " . $this->dao->getErrorDesc() );
  }
}


public function install() {
  $this->import('oc-content/themes/delta/model/struct.sql');
}


// UPDATE EXPIRATION DATE
public function itemExpire($item_id, $yes = 1) {
  if($yes == 1) {
    $date = date('Y-m-d H:i:s');
  } else {
    $date = date('Y-m-d H:i:s', strtotime("+30 days"));
  }

  $this->dao->update($this->getTable_item(), array('dt_expiration' => $date), array('pk_i_id' => $item_id));
}


// GET ITEM EXTRA
public function getExtra($item_id) {
  $this->dao->select('a.*');
  //$this->dao->select('a.*, al.*');
  $this->dao->from($this->getTable_delta() . ' as a');
  //$this->dao->join($this->getTable_delta_locale() . ' as al', '(al.fk_i_item_id = a.fk_i_item_id AND al.fk_c_locale_code = "' . osc_current_user_locale() . '")', 'LEFT OUTER');

  $this->dao->where('a.fk_i_item_id', $item_id);


  $result = $this->dao->get();
  
  if($result) {
    $data = $result->row();
    return $data;
  }

  return false;
}



// UPDATE ITEM EXTRA
public function updateExtra($params) {
  return $this->dao->replace($this->getTable_delta(), $params);
}


// GET CITIES
public function getCities($country_code, $limit = 200, $not_empty = 1) {
  if(osc_get_current_user_locations_native()) {
    $this->dao->select('c.*, r.s_name as s_region_name, s.i_num_items');
  } else {
    $this->dao->select('c.*, r.s_name as s_region_name, r.s_name_native as s_region_name_native, s.i_num_items');
  }
  $this->dao->from($this->getTable_city() . ' as c, ' . $this->getTable_city_stats() . ' as s, ' . $this->getTable_region() . ' as r');
  $this->dao->where('c.pk_i_id = s.fk_i_city_id');
  $this->dao->where('c.fk_i_region_id = r.pk_i_id');

  if($not_empty == 1) {
    $this->dao->where('s.i_num_items > 0');
  }

  $this->dao->where('c.fk_c_country_code', strtolower($country_code));
  $this->dao->orderBy('c.s_name', 'ASC');
  $this->dao->limit($limit);


  $result = $this->dao->get();
  
  if($result) {
    $data = $result->result();
    return $data;
  }

  return array();

}


// SEARCH FOR CATEGORIES
public function findCategories($pattern, $limit = 6) {
  $this->dao->select('c.pk_i_id, c.fk_i_parent_id, d.s_name, p.s_name as s_name_parent');
  $this->dao->from($this->getTable_category() . ' as c');
  $this->dao->join($this->getTable_category_description() . ' as d', '(c.pk_i_id = d.fk_i_category_id AND d.fk_c_locale_code = "' . osc_current_user_locale() . '")', 'INNER');
  $this->dao->join($this->getTable_category_description() . ' as p', '(c.fk_i_parent_id = p.fk_i_category_id AND p.fk_c_locale_code = "' . osc_current_user_locale() . '")', 'LEFT OUTER');

  $this->dao->where('c.b_enabled', 1);
  
  if($pattern != '') {
    $this->dao->where(sprintf('d.s_name like "%%%s%%"', osc_esc_html($pattern)));
  }

  $this->dao->limit($limit);

  $result = $this->dao->get();
  
  if($result) {
    $data = $result->result();
    return $data;
  }

  return array();
}

}
?>