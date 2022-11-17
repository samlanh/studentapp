<?php

class Utility_AboutusController extends Zend_Controller_Action
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
		$arrFilter['actionName']="contactus";
		$rsContactus = $dbAPi->getDataByAPI($arrFilter);
		$rsContactus = json_decode($rsContactus, true);
		if($rsContactus['code']=="SUCCESS"){
			$this->view->aboutUs  =$rsContactus['result']['about'];    
			//$this->view->contact  =$rsContactus['result']['contact'];  
		}
		
		//$dbGb = new Application_Model_DbTable_DbGlobal();
		//$arrFilter = array();
		//$rs = $dbGb->getContactAndAbout($arrFilter);
		//$this->view->aboutUs  =$rs['aboutUS'];    
		
		
    }

   

}







