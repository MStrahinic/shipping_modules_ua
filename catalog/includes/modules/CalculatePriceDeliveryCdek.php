<?php
/**
 * Расчёт стоимости доставки СДЭК
 * Модуль для интернет-магазинов (ИМ)
 * 
 * @version 1.0
 * @since 21.06.2012
 * @link http://www.edostavka.ru/integrator/
 * @see 3197
 * @author Tatyana Shurmeleva
 */
class CalculatePriceDeliveryCdek {
	
	private $post = "";

	public function __construct() {
	     $this->dateExecute = date('Y-m-d');
	}
	
	public function createConnectionString($own_city_name,$city_shipping_to_name){
		
		$post = "apiKey=f6s8f686fsd6f6s86s8f63213&command=search&fromAddress=".urlencode($own_city_name)."&toAddress=".urlencode($city_shipping_to_name)."&formValue=100&formWeight=1&formVolume=0.001&allDepts=1";
		
		return $post;	
	}
	
	public function getDataFromServer($post){
		$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, 'http://edostavka.com.ua/api/endpoint.php');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/x-www-form-urlencoded"));
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	
		$response = curl_exec($ch);
		curl_close($ch);
	
	   $response = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $response);
	   $response = preg_replace('/^.+\n/', '', $response);
	   $response = ltrim($response,' ');
	   $xml = simplexml_load_string($response);
	   
	   return $xml;
	}
	
	//Nova Posta
	
	public function getNameNovaPosta($xml){
		$i = 0;
		foreach ($xml as $datainfo):
			if($datainfo['name']!="" || $datainfo['cost']!=""){
				$i++;
				if($i==1){
					$name=$datainfo['name'];
				}
			}
		endforeach;
		
		return $name; 
	}
	
	public function getCostNovaPosta($xml){
		$i = 0;
		foreach ($xml as $datainfo):
			if($datainfo['name']!="" || $datainfo['cost']!=""){
				$i++;
				if($i==1){
					$cost=$datainfo['cost'];
				}
			}
		endforeach;
		
		return $cost;
	}
	
	//In Tajm
	
	public function getNameInTajm($xml){
		$i = 0;
		foreach ($xml as $datainfo):
			if($datainfo['name']!="" || $datainfo['cost']!=""){
				$i++;
				if($i==2){
					$name=$datainfo['name'];
				}
			}
		endforeach;
		
		return $name; 
	}
	
	public function getCostInTajm($xml){
		$i = 0;
		foreach ($xml as $datainfo):
			if($datainfo['name']!="" || $datainfo['cost']!=""){
				$i++;
				if($i==2){
					$cost=$datainfo['cost'];
				}
			}
		endforeach;
		
		return $cost;
	}
	
	//Avtoluks
	
	public function getNameAvtoluks($xml){
		$i = 0;
		foreach ($xml as $datainfo):
			if($datainfo['name']!="" || $datainfo['cost']!=""){
				$i++;
				if($i==3){
					$name=$datainfo['name'];
				}
			}
		endforeach;
		
		return $name; 
	}
	
	public function getCostAvtoluks($xml){
		$i = 0;
		foreach ($xml as $datainfo):
			if($datainfo['name']!="" || $datainfo['cost']!=""){
				$i++;
				if($i==3){
					$cost=$datainfo['cost'];
				}
			}
		endforeach;
		
		return $cost;
	}
	
	//Mist Ekspres
	
	public function getNameMistEkspres($xml){
		$i = 0;
		foreach ($xml as $datainfo):
			if($datainfo['name']!="" || $datainfo['cost']!=""){
				$i++;
				if($i==4){
					$name=$datainfo['name'];
				}
			}
		endforeach;
		
		return $name; 
	}
	
	public function getCostMistEkspres($xml){
		$i = 0;
		foreach ($xml as $datainfo):
			if($datainfo['name']!="" || $datainfo['cost']!=""){
				$i++;
				if($i==4){
					$cost=$datainfo['cost'];
				}
			}
		endforeach;
		
		return $cost;
	}
	
	//Deliveri
	
	public function getNameDeliveri($xml){
		$i = 0;
		foreach ($xml as $datainfo):
			if($datainfo['name']!="" || $datainfo['cost']!=""){
				$i++;
				if($i==5){
					$name=$datainfo['name'];
				}
			}
		endforeach;
		
		return $name; 
	}
	
	public function getCostDeliveri($xml){
		$i = 0;
		foreach ($xml as $datainfo):
			if($datainfo['name']!="" || $datainfo['cost']!=""){
				$i++;
				if($i==5){
					$cost=$datainfo['cost'];
				}
			}
		endforeach;
		
		return $cost;
	}
	
	//Nocnoj Ekspress
	
	
	public function getNameNocnojEkspress($xml){
		$i = 0;
		foreach ($xml as $datainfo):
			if($datainfo['name']!="" || $datainfo['cost']!=""){
				$i++;
				if($i==6){
					$name=$datainfo['name'];
				}
			}
		endforeach;
		
		return $name; 
	}
	
	public function getCostNocnojEkspress($xml){
		$i = 0;
		foreach ($xml as $datainfo):
			if($datainfo['name']!="" || $datainfo['cost']!=""){
				$i++;
				if($i==6){
					$cost=$datainfo['cost'];
				}
			}
		endforeach;
		
		return $cost;
	}
	
	//setCity of shop
	
	
}

?>