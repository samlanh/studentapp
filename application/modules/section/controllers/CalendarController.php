<?php

class Section_CalendarController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/home';
    public function init()
    {    	
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	
	}

    public function indexAction()
    {
//     	$this->_helper->layout()->disableLayout();
		
    }
    function getholidayAction(){
    	$db = new Section_Model_DbTable_DbCalendar();
    	if($this->getRequest()->isPost()){
    		$_data = array();
    		$param = $this->getRequest()->getParams();
    		$_data['getholiday']	= empty($param['getholiday'])?'':$param['getholiday'];
    		$_data['mothHoliday']	= empty($param['mothHoliday'])?'':$param['mothHoliday'];
    		$_data['currentLang']	= empty($param['currentLang'])?'':$param['currentLang'];
    		
    		$record = $db->getHoliday($_data);
    		print_r(Zend_Json::encode($record));
    		exit();
    	}
    }
}







