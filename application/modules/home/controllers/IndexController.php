<?php

class Home_IndexController extends Zend_Controller_Action
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
		$arrFilter['actionName']="slieshow";
		$rsSlide = $dbAPi->getDataByAPI($arrFilter);
		$rsSlide = json_decode($rsSlide, true);
		if($rsSlide['code']=="SUCCESS"){
			$this->view->slide  =$rsSlide['result'];
		}
		
		$dbGb = new Application_Model_DbTable_DbGlobal();
		//$this->view->slide  =$dbGb->getMobileSliding();
		
		$limitRecord = $dbGb->limitListView();
		$limitRecord = empty($limitRecord)?1:$limitRecord;
		$search=array(
			'endDate'=>date('Y-m-d')
		);
		$search['limitRecord']=$limitRecord;
		
		$dbScore = new Section_Model_DbTable_DbScore();
		$row = $dbScore->getScoreLists($search);
		$this->view->rowScore=$row;
		
		$message=$this->getRequest()->getParam("message");
		if(!empty($message)){
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$this->view->messageAlert = $tr->translate($message);
		}
    }

   

}







