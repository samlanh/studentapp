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
	function getDataByAPI($arrFilter=array()){
    
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbGb->currentlang();
		$systemLink = $dbGb->systemLink();
	
		$headers = array(
		'Accept: application/json',
		'Content-Type: application/json',

		);
	
	$actionName = $arrFilter['actionName'];
	$actionName=empty($arrFilter['actionName'])?'introductionhome':$arrFilter['actionName']."&currentLang=".$currentLang;
	
	if(!empty($arrFilter['isForHome'])){
		$actionName = $actionName."&isForHome=".$arrFilter['isForHome'];
	}
	if(!empty($arrFilter['stu_id'])){
		$actionName = $actionName."&stu_id=".$arrFilter['stu_id'];
	}
	$url=$systemLink."/api/index?url=".$actionName;
	
	if(!empty($arrFilter['methodPost'])){
		$headers = array('Content-Type: application/x-www-form-urlencoded');
		
		$studentCode = empty($arrFilter['account'])?"":$arrFilter['account'];
		$password = empty($arrFilter['password'])?"":$arrFilter['password'];
		$fields =('studentCode='.$studentCode.'&password='.$password);
		
		 $curl = curl_init();
		  curl_setopt_array($curl, array(
			  CURLOPT_URL => $url,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_TIMEOUT => 300000,
			  CURLOPT_POST => true,
			  CURLOPT_POSTFIELDS =>$fields,
			  CURLOPT_HTTPHEADER => $headers));
		  $respone = curl_exec($curl);
		  $err = curl_error($curl);//you can echo curl error
		  curl_close($curl);//you need to close curl connection
		  return $respone;
	}else{
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
}
?>