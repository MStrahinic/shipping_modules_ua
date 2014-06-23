<?php
/* -----------------------------------------------------------------------------------------
   $Id: mistekspres.php 899 2010/05/29 13:24:46 oleg_vamsoft $   

   VaM Shop - open source ecommerce solution
   http://vamshop.ru
   http://vamshop.com

   Copyright (c) 2007 VaM Shop
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(mistekspres.php,v 1.39 2003/02/05); www.oscommerce.com 
   (c) 2003	 nextcommerce (mistekspres.php,v 1.7 2003/08/24); www.nextcommerce.org
   (c) 2004	 xt:Commerce (mistekspres.php,v 1.7 2003/08/24); xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

defined('root') ? null : define('root', '/var/www/admin/www/patioflower.ua/' );
include_once(root."includes/modules/CalculatePriceDeliveryCdek.php");

  class mistekspres {
    var $code, $title, $description, $icon, $enabled;


    function mistekspres() {
      global $order;	
	
      $this->code = 'mistekspres';
      $this->title = MODULE_SHIPPING_MISTEKSPRES_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_MISTEKSPRES_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_SHIPPING_MISTEKSPRES_SORT_ORDER;
      $this->icon = DIR_WS_ICONS . 'shipping_mistekspres.png';
      $this->tax_class = MODULE_SHIPPING_MISTEKSPRES_TAX_CLASS;
      $this->enabled = ((MODULE_SHIPPING_MISTEKSPRES_STATUS == 'True') ? true : false);

       if ( ($this->enabled == true) && ((int)MODULE_SHIPPING_MISTEKSPRES_ZONE > 0) ) {
        $check_flag = false;
        $check_query = vam_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_TABLE_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
        while ($check = vam_db_fetch_array($check_query)) {
          if ($check['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check['zone_id'] == $order->delivery['zone_id']) {
            $check_flag = true;
            break;
          }
        }

   if ($check_flag == false) {
          $this->enabled = false;
        }
    }
    	
		$check_query = vam_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key='STORE_ZONE'");
        $check = vam_db_fetch_array($check_query);
		$own_zone_id = $check['configuration_value'];	
	
	//переключатель Доставка по своему городу
	if (($this->enabled == true) && (MODULE_SHIPPING_MISTEKSPRES_OWN_CITY_DELIVERY == 'False')){		         		        
		if (strtolower(MODULE_SHIPPING_MISTEKSPRES_FROM_CITY) == strtolower($order->delivery['city'])){				
			$this->enabled = false;
		}
	}
			
	//Переключатель Доставка по своему региону
	if (($this->enabled == true) && (MODULE_SHIPPING_MISTEKSPRES_OWN_REGION_DELIVERY == 'False'))	{		         		
		if ($own_zone_id == $order->delivery['zone_id']){				
			$this->enabled = false;
		}
	}
	
	//отключение доставки для отдельных городов
	if (($this->enabled == true) && (MODULE_SHIPPING_MISTEKSPRES_DISABLE_CITIES !== '')){		         		        
		$disabled_cities = explode(',',MODULE_SHIPPING_MISTEKSPRES_DISABLE_CITIES);
		foreach ($disabled_cities as $cityvalue){			
			if (strtolower($cityvalue) == strtolower($order->delivery['city'])){				
				$this->enabled = false;
			}
		}
	}
	
  }
// class methods
function quote($method = '') {
		global $order, $cart, $shipping_weight, $own_city_id;	
		
		$calc = new CalculatePriceDeliveryCdek();
		
		try {
			
	  	if ($this->tax_class > 0) {
        	$this->quotes['tax'] = vam_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
      	}
	  
			
		//устанавливаем город-отправитель
		$check_query = vam_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key='MODULE_SHIPPING_MISTEKSPRES_FROM_CITY'");
		$check = vam_db_fetch_array($check_query);
		$own_city_name = $check['configuration_value'];
		//echo 'Grad:'.$own_city_name.'<br>';
		
		//устанавливаем город-получатель
		$city_shipping_to_name = $order->delivery['city'];
		//echo 'Grad primaoca:'.$city_shipping_to_name.'<br>';
		
		/*добавляем места в отправление
		if ($shipping_weight == 0){
				$shipping_weight = MODULE_SHIPPING_MISTEKSPRES_DEFAULT_SHIPPING_WEIGHT;
				//echo 'Tezina:'.$shipping_weight.'<br>';
		}
		
		$calc->addGoodsItemBySize($shipping_weight, '40', '50', '60');
		//$calc->addGoodsItemByVolume('0.1', '0.1');*/
		
		$post = $calc->createConnectionString($own_city_name,$city_shipping_to_name);
		$xml = $calc->getDataFromServer($post);
		//$name = $calc->getNameMistEkspres($xml);
		$cost5 = $calc->getCostMistEkspres($xml);
		define("MODULE_SHIPPING_MISTEKSPRES_COST",$cost5);
	
	} catch (Exception $e) {
    	echo 'Ошибка: ' . $e->getMessage() . "<br />";
	}
	  $this->quotes = array('id' => $this->code,
                            'module' => MODULE_SHIPPING_MISTEKSPRES_TEXT_TITLE,
                            'methods' => array(array('id' => $this->code,
                                                     'title' => MODULE_SHIPPING_MISTEKSPRES_TEXT_NOTE,
                                                     'cost' => MODULE_SHIPPING_MISTEKSPRES_COST)));

      if (vam_not_null($this->icon)) $this->quotes['icon'] = vam_image($this->icon, $this->title);
	  
      return $this->quotes;
	}

   function check() {
      if (!isset($this->_check)) {
        $check_query = vam_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_MISTEKSPRES_STATUS'");
        $this->_check = vam_db_num_rows($check_query);
      }
      return $this->_check;
    }

      function install() {
      vam_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SHIPPING_MISTEKSPRES_STATUS', 'True', '7', '0', 'vam_cfg_select_option(array(\'True\', \'False\'), ', now())");
      vam_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_MISTEKSPRES_ALLOWED', '', '7', '0', now())");
      vam_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_MISTEKSPRES_FROM_CITY', 'Киев', '7', '0', now())");
      vam_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_MISTEKSPRES_DISABLE_CITIES', '', '7', '0', now())");
      vam_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SHIPPING_MISTEKSPRES_OWN_CITY_DELIVERY', 'True', '7', '0', 'vam_cfg_select_option(array(\'True\', \'False\'), ', now())");
      vam_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SHIPPING_MISTEKSPRES_OWN_REGION_DELIVERY', 'True', '7', '0', 'vam_cfg_select_option(array(\'True\', \'False\'), ', now())");
      vam_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_MISTEKSPRES_DEFAULT_SHIPPING_WEIGHT', '0.5', '7', '0', now())");
      vam_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_MISTEKSPRES_NATURE', '3', '6', '0', now())");
      vam_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SHIPPING_MISTEKSPRES_DEBUG', 'False', '7', '0', 'vam_cfg_select_option(array(\'True\', \'False\'), ', now())");
      vam_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_SHIPPING_MISTEKSPRES_TAX_CLASS', '0', '7', '0', 'vam_get_tax_class_title', 'vam_cfg_pull_down_tax_classes(', now())");
      vam_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_SHIPPING_MISTEKSPRES_ZONE', '0', '7', '0', 'vam_get_zone_class_title', 'vam_cfg_pull_down_zone_classes(', now())");
      vam_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_MISTEKSPRES_SORT_ORDER', '0', '7', '0', now())");
    }

    function remove() {
      vam_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

   function keys() {
      return array('MODULE_SHIPPING_MISTEKSPRES_STATUS', 'MODULE_SHIPPING_MISTEKSPRES_FROM_CITY', 'MODULE_SHIPPING_MISTEKSPRES_DISABLE_CITIES', 'MODULE_SHIPPING_MISTEKSPRES_OWN_CITY_DELIVERY', 'MODULE_SHIPPING_MISTEKSPRES_OWN_REGION_DELIVERY', 'MODULE_SHIPPING_MISTEKSPRES_DEFAULT_SHIPPING_WEIGHT', 'MODULE_SHIPPING_MISTEKSPRES_NATURE', 'MODULE_SHIPPING_MISTEKSPRES_DEBUG', 'MODULE_SHIPPING_MISTEKSPRES_ALLOWED', 'MODULE_SHIPPING_MISTEKSPRES_TAX_CLASS', 'MODULE_SHIPPING_MISTEKSPRES_ZONE', 'MODULE_SHIPPING_MISTEKSPRES_SORT_ORDER');
    }
  }
?>
