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
	public function loginmoreAction(){
		$zendRequest = new Zend_Controller_Request_Http();
		$arrayStudentList = $zendRequest->getCookie(SYSTEM_SES.'arrayStudentList');
		
		$arrayStudentList = stripslashes($arrayStudentList);
		$arrayStudentList = json_decode($arrayStudentList, true);
		
		//print_r($arrayStudentList);exit();
		
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$currentId = $dbGb->getUserId();
		
		if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
			
			$dbAPi = new Application_Model_DbTable_DbGetAPI();
			$data['actionName']="authWeb";
			$data['methodPost']="POST";
						
			$rs = $dbAPi->getDataByAPI($data);
			$rs = json_decode($rs, true);
			
			if($rs['code']=="SUCCESS"){
				$studentRS = $rs['result'];
				if(!empty($studentRS)){
					
					if($currentId==$studentRS['id']){
						Application_Form_FrmMessage::redirectUrl("/home?message=THIS_CURRENT_ACCOUNT_ALREADY_LOGIN_PLS_LOGIN_NEW_ACCOUNT");	
						exit();
					}
					$arrayStudentList['keyStudent'.$studentRS['id']]= $studentRS;
					
					$jsonArrayStudentList = json_encode($arrayStudentList);
					setcookie(SYSTEM_SES.'stuID', $studentRS['id'], time() + (86400 * 30), '/');// 86400 = 1 day
					setcookie(SYSTEM_SES.'stuCode', $studentRS['stuCode'], time() + (86400 * 30), '/');
					setcookie(SYSTEM_SES.'arrayStudentList', $jsonArrayStudentList, time() + (86400 * 30), '/');
					
					Application_Form_FrmMessage::redirectUrl("/home?message=SUCCESS_ADDED_ACCOUNT");	
				}
			}
			
    	}
		
	}
	
	public function switchingAction(){
		$zendRequest = new Zend_Controller_Request_Http();
		$arrayStudentList = $zendRequest->getCookie(SYSTEM_SES.'arrayStudentList');
		
		$arrayStudentList = stripslashes($arrayStudentList);
		$arrayStudentList = json_decode($arrayStudentList, true);
		
		$stuId = $this->getRequest()->getParam('stuId');
		$stuId = empty($stuId)?0:$stuId;
		
		if(!empty($arrayStudentList)){
			
			foreach($arrayStudentList AS $accRow){
				if($stuId==$accRow['id']){
					
					setcookie(SYSTEM_SES.'stuID', $accRow['id'], time() + (86400 * 30), '/');// 86400 = 1 day
					setcookie(SYSTEM_SES.'stuCode', $accRow['stuCode'], time() + (86400 * 30), '/');
					
					Application_Form_FrmMessage::redirectUrl("/home?message=SWTICHING_STUDENT_SUCCESSFULLY");	
					exit();
				}
			}
		}
	}
	public function removingAction(){
		
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$currentId = $dbGb->getUserId();

		$zendRequest = new Zend_Controller_Request_Http();
		$arrayStudentList = $zendRequest->getCookie(SYSTEM_SES.'arrayStudentList');
		
		$arrayStudentList = stripslashes($arrayStudentList);
		$arrayStudentList = json_decode($arrayStudentList, true);
		
		$studentArrList = $arrayStudentList;
		
		$stuId = $this->getRequest()->getParam('stuId');
		$stuId = empty($stuId)?0:$stuId;
		
		if($currentId==$stuId){
			Application_Form_FrmMessage::redirectUrl("/home?message=CANNOT_REMOVE_CURRENT_STUDENT");	
			exit();
		}
		if(!empty($arrayStudentList)){
			foreach($arrayStudentList AS $accRow){
				if($stuId==$accRow['id']){
					unset($studentArrList['keyStudent'.$stuId]);
					$jsonArrayStudentList = json_encode($studentArrList);
					setcookie(SYSTEM_SES.'arrayStudentList', $jsonArrayStudentList, time() + (86400 * 30), '/');
					Application_Form_FrmMessage::redirectUrl("/home?message=REMOVING_STUDENT_SUCCESSFULL");	
					exit();
				}
			}
		}
	}
	
}







