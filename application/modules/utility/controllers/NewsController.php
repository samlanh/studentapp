<?php

class Utility_NewsController extends Zend_Controller_Action
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
		$rs = $dbGb->getNewsEvents();
		$this->view->rs  =$rs;    
    }
	
	public function detailAction()
    {
		
		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$rs = $dbGb->getNewsEventsDetail($id);
		$this->view->row  =$rs;    
    }
   

}







