<?php

class Section_EvaluationController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/home';
    public function init()
    {    	
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	
	}

    public function indexAction()
    {
    	$param = $this->getRequest()->getParams();
		if(isset($param['search'])){
			$arrFilter=$param;
		}
		else{
			$arrFilter = array(
					'searchBox' => '',
					'academicYear'=> '',
					'startDate'=> '',
					'month'=> '',
					'endDate'=>date('Y-m-d')
				);
		}
		
		$this->view->queryString = empty($_SERVER['QUERY_STRING'])?"":"?".$_SERVER['QUERY_STRING'];
		
		$_dbGb  = new Application_Model_DbTable_DbGlobal();
		$limitRecord = $_dbGb->limitListView();
		$limitRecord = empty($limitRecord)?1:$limitRecord;
		
		
		$dbAtt = new Section_Model_DbTable_DbEvaluation();
		//$arrFilter = array();
		
		$allRow = count($dbAtt->getStudentEnvaluation($arrFilter));
		$this->view->allRow = $allRow;
		$arrFilter['limitRecord']=$limitRecord;
		
		$this->view->row  =$dbAtt->getStudentEnvaluation($arrFilter);
		
		$formFilter = new Application_Form_FrmSearch();
		$frmsearch = $formFilter->FrmSearch();
		$this->view->formFilter = $frmsearch;
    }
	
	function morerecordAction(){
		$db = new Section_Model_DbTable_DbEvaluation();
		if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
			$param = $this->getRequest()->getParams();
			$_data['searchBox']		= empty($param['searchBox'])?'':$param['searchBox'];
			$_data['academicYear']	= empty($param['academicYear'])?'':$param['academicYear'];
			$_data['month']	= empty($param['month'])?'':$param['month'];
		
		
			$record = $db->moreEvaluationRecord($_data);
			print_r(Zend_Json::encode($record));exit();
    	}
		
	}
	
	function detailAction()
	{
		
		
		$param = $this->getRequest()->getParams();
		if(empty($param)){
			$this->_redirect("/section/evaluation");
		}
		$dbAtt = new Section_Model_DbTable_DbEvaluation();
		$row = $dbAtt->getStudentEnvaluationInfo($param);
		$this->view->row = $row;
		
		$rowDetail = $dbAtt->getStudentEvaluationDetail($param);
		$this->view->rowDetail = $rowDetail;
	}	

   

}







