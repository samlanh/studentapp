<?php

class Section_CourseController extends Zend_Controller_Action
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
		
		$arrFilter['actionName']="course";
		$rsCourse = $dbAPi->getDataByAPI($arrFilter);
		$rsCourse = json_decode($rsCourse, true);
		if($rsCourse['code']=="SUCCESS"){
			$this->view->course  =$rsCourse['result'];
		}
    }
	
	
}







