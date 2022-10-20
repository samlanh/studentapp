<?php

class Section_ScheduleController extends Zend_Controller_Action
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

		
		$dbSch = new Section_Model_DbTable_DbSchedule();
		$arrFilter = array();
		$this->view->row  =$dbSch->getStudentSchedule($arrFilter);
    }

   

}







