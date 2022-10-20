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
    	
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$this->view->slide  =$dbGb->getMobileSliding();
		
		$limitRecord = $dbGb->limitListView();
		$limitRecord = empty($limitRecord)?1:$limitRecord;
		$search=array(
			'endDate'=>date('Y-m-d')
		);
		$search['limitRecord']=$limitRecord;
		
		$dbScore = new Section_Model_DbTable_DbScore();
		$row = $dbScore->getScoreLists($search);
		$this->view->rowScore=$row;
    }

   

}







