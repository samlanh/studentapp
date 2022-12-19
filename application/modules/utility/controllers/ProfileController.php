<?php

class Utility_ProfileController extends Zend_Controller_Action
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
		$userId = $dbGb->getUserId();
		
		$dbAPi = new Application_Model_DbTable_DbGetAPI();
		$arrFilter = array();
		$arrFilter['actionName']="profile";
		$arrFilter['stu_id']=empty($userId)?0:$userId;
		$rsStu = $dbAPi->getDataByAPI($arrFilter);
		$rsStu = json_decode($rsStu, true);

		
		if($rsStu['code']=="SUCCESS"){
			if(!empty($rsStu['result'])){
				$student  =$rsStu['result'][0];
				$this->view->student = $student;
				
			}
		}
		
		
		
		
    }
	
	public function changepasswordAction(){
		if($this->getRequest()->isPost()){
    		Application_Form_FrmMessage::redirectUrl("/home?message=SUCCESS_CHANGE_PASSWORD");	
    	}
	}
	
	
}







