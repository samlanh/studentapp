<?php

class Section_PaymentController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/home';
    public function init()
    {    	
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	
	}

    public function indexAction()
    {
    	
		$dbPmt = new Section_Model_DbTable_DbPayment();
		$param = $this->getRequest()->getParams();
		if(isset($param['search'])){
			$arrFilter=$param;
		}
		else{
			$arrFilter = array(
					'searchBox' 	=> '',
					'academicYear'	=> '',
					'paymentMethod' => '',
					'startDate'		=> '',
					'endDate'		=>date("d-m-Y")
				);
		}
		
		
		$this->view->queryString = empty($_SERVER['QUERY_STRING'])?"":"?".$_SERVER['QUERY_STRING'];
		
		$_dbGb  = new Application_Model_DbTable_DbGlobal();
		$limitRecord = $_dbGb->limitListView();
		$limitRecord = empty($limitRecord)?1:$limitRecord;
		
		//$allRow = $dbPmt->getCountAllPayment($arrFilter);
		$allRow = count($dbPmt->getAllPayment($arrFilter));
		$this->view->allRow = $allRow;
		
		
		$arrFilter['limitRecord']=$limitRecord;
		$row = $dbPmt->getAllPayment($arrFilter);
		$this->view->row = $row;
		
		$this->view->arrFilter = $arrFilter;
		$formFilter = new Application_Form_FrmSearch();
		$frmsearch = $formFilter->FrmSearch();
		$this->view->formFilter = $frmsearch;
		
    }

	function morerecordAction(){
		$db = new Section_Model_DbTable_DbPayment();
		if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
				$param = $this->getRequest()->getParams();
				$_data['searchBox']		= empty($param['searchBox'])?'':$param['searchBox'];
				$_data['academicYear']	= empty($param['academicYear'])?'':$param['academicYear'];
				$_data['paymentMethod']	= empty($param['paymentMethod'])?'':$param['paymentMethod'];
				$_data['startDate']		= empty($param['startDate'])?'':$param['startDate'];
				$_data['endDate']		= empty($param['endDate'])?'':$param['endDate'];
			$record = $db->morePaymentRecord($_data);
			print_r(Zend_Json::encode($record));exit();	
			
    	}
		
	}
	
	function detailAction()
	{
		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		$dbPmt = new Section_Model_DbTable_DbPayment();
		
		$row = $dbPmt->getPaymentInfoByID($id);
		$this->view->row = $row;
		
		$rowDetail = $dbPmt->getPaymentDetail($id);
		$this->view->rowDetail = $rowDetail;
	}
	
	function detailcontentAction()
	{
		$db = new Section_Model_DbTable_DbPayment();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$record = $db->detailContent($_data);
			print_r(Zend_Json::encode($record));exit();
    	}
	}

}







