<?php

class Utility_AttendanceController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/home';
    public function init()
    {    	
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	
	}

    public function indexAction()
    {
		$dbAPi = new Application_Model_DbTable_DbGetAPI();
		$arrFilter = array();
		$arrFilter['actionName']="attendancePolicy";
		$rs = $dbAPi->getDataByAPI($arrFilter);
		$rs = json_decode($rs, true);
		if($rs['code']=="SUCCESS"){
			$this->view->rs  =$rs['result'];  
		}
		
		//$dbGb = new Application_Model_DbTable_DbGlobal();
		//$rs = $dbGb->getDiscipline();
		//$this->view->rs  =$rs;    
    }

   

}







