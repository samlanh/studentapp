<?php

class Utility_ContactusController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/home';
    public function init()
    {    	
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	
	}

    public function indexAction()
    {
		
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$arrFilter = array();
		$rs = $dbGb->getContactAndAbout($arrFilter);
		$this->view->contact  =$rs['contacting'];    
    }

   

}







