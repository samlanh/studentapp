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
		$arrFilter = array();
		$limitRecord = $dbGb->limitListView();
		$limitRecord = empty($limitRecord)?1:$limitRecord;
		
		//$allRow = $dbGb->getCountAllNews();
		
		$allRow = count($dbGb->getNewsEvents($arrFilter));
		$this->view->allRow = $allRow;
		
		$arrFilter['limitRecord']=$limitRecord;
		$rs = $dbGb->getNewsEvents($arrFilter);
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
   
	function morerecordAction(){
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
			
			$record = $db->moreNewsEvents($_data);
			print_r(Zend_Json::encode($record));exit();	
			
    	}
		
	}
}







