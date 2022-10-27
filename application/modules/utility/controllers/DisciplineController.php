<?php

class Utility_DisciplineController extends Zend_Controller_Action
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
		$rs = $dbGb->getDiscipline();
		$this->view->rs  =$rs;    
    }

   

}







