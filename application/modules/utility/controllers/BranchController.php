<?php

class Utility_BranchController extends Zend_Controller_Action
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
		$arrFilter['actionName']="branchList";
		$rsContactus = $dbAPi->getDataByAPI($arrFilter);
		$rsContactus = json_decode($rsContactus, true);
		if($rsContactus['code']=="SUCCESS"){
			$this->view->rs  =$rsContactus['result'];    
		}
		
		//$dbGb = new Application_Model_DbTable_DbGlobal();
		//$rs = $dbGb->getSchoolBranch();
		//$this->view->rs  =$rs;    
    }

}







