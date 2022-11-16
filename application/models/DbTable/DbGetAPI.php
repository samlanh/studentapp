<?php

class Application_Model_DbTable_DbGetAPI extends Zend_Db_Table_Abstract
{
    // set name value
	public function setName($name){
		$this->_name=$name;
	}
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	function getDataByAPI($actionName=''){
    
	  $dbGb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbGb->currentlang();
		$systemLink = $dbGb->systemLink();
		
	$actionName=empty($actionName)?'introductionhome':$actionName."&currentLang=".$currentLang;
	$url=$systemLink."/api/index?url=".$actionName;//test
      
      
       

     $headers = array(
    'Accept: application/json',
    'Content-Type: application/json',

    );
      //open curl
      $curl = curl_init();
	 

      curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_TIMEOUT => 300000,
          CURLOPT_POST => false,
          CURLOPT_HTTPHEADER => $headers));
      $respone = curl_exec($curl);
      $err = curl_error($curl);//you can echo curl error
      curl_close($curl);//you need to close curl connection
      return $respone;
    }
}
?>