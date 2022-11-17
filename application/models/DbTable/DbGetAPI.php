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
	if(!empty($arrFilter['studentId'])){
		$actionName = $actionName."&studentId=".$arrFilter['studentId'];
	}
	if(!empty($arrFilter['searchBox'])){
		$actionName = $actionName."&searchBox=".$arrFilter['searchBox'];
	}
	if(!empty($arrFilter['academicYear'])){
		$actionName = $actionName."&academicYear=".$arrFilter['academicYear'];
	}
	if(!empty($arrFilter['exam_type'])){
		$actionName = $actionName."&exam_type=".$arrFilter['exam_type'];
	}
	if(!empty($arrFilter['examType'])){
		$actionName = $actionName."&examType=".$arrFilter['examType'];
	}
	if(!empty($arrFilter['month'])){
		$actionName = $actionName."&month=".$arrFilter['month'];
	}
	if(!empty($arrFilter['forSemester'])){
		$actionName = $actionName."&forSemester=".$arrFilter['forSemester'];
	}
	if(!empty($arrFilter['LimitStart'])){
		$actionName = $actionName."&LimitStart=".$arrFilter['LimitStart'];
	}
	if(!empty($arrFilter['limitRecord'])){
		$actionName = $actionName."&limitRecord=".$arrFilter['limitRecord'];
	}
	if(!empty($arrFilter['evaluationId'])){
		$actionName = $actionName."&evaluationId=".$arrFilter['evaluationId'];
	}
	if(!empty($arrFilter['id'])){
		$actionName = $actionName."&id=".$arrFilter['id'];
	}
	if(!empty($arrFilter['group'])){
		$actionName = $actionName."&group=".$arrFilter['group'];
	}
	if(!empty($arrFilter['day'])){
		$actionName = $actionName."&day=".$arrFilter['day'];
	}
	if(!empty($arrFilter['degree'])){
		$actionName = $actionName."&degree=".$arrFilter['degree'];
	}
	if(!empty($arrFilter['subjectId'])){
		$actionName = $actionName."&subjectId=".$arrFilter['subjectId'];
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