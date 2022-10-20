<?php

class Section_ScoreController extends Zend_Controller_Action
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
		
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search = array(
				'searchBox' => '',
				'academicYear' => '',
				'paymentMethod' => '',
				
				'startDate'=> '',
				'endDate'=>date('Y-m-d')
			);
		}
		
		$limitRecord = $dbGb->limitListView();
		$limitRecord = empty($limitRecord)?1:$limitRecord;
		
	
		$dbScore = new Section_Model_DbTable_DbScore();
		$allRow = count($dbScore->getScoreLists($search));
		$this->view->allRow = $allRow;
		
		$search['limitRecord']=$limitRecord;
		$row = $dbScore->getScoreLists($search);
		$this->view->row=$row;
    }
	
	function morerecordAction(){
		$db = new Section_Model_DbTable_DbScore();
		if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
				$param = $this->getRequest()->getParams();
				$_data['searchBox']		= empty($param['searchBox'])?'':$param['searchBox'];
				$_data['academicYear']	= empty($param['academicYear'])?'':$param['academicYear'];
				$_data['examType']		= empty($param['examType'])?'':$param['examType'];
				$_data['startDate']		= empty($param['startDate'])?'':$param['startDate'];
				$_data['endDate']		= empty($param['endDate'])?'':$param['endDate'];
				
			
    	}
		$record = $db->moreScoreRecord($_data);
    	print_r(Zend_Json::encode($record));exit();
	}

   function detailAction(){
   }

}







