<?php

class IndexController extends Zend_Controller_Action
{

	const REDIRECT_URL = '/home';
	
    public function init()
    {
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');  
    }

    public function indexAction()
    {
		
		$zendRequest = new Zend_Controller_Request_Http();
		$stuID = $zendRequest->getCookie(SYSTEM_SES.'stuID');
		
    	//$sessionStudent=new Zend_Session_Namespace(SYSTEM_SES);
    	//$stuID = $sessionStudent->stuID;

    	if (!empty($stuID)){
    		$this->_redirect("/home");
    	}
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$this->view->allLang  =$dbGb->getAllLanguage();
		
		//$arrFilter = array(
					//'isForHome'=>1
					//);
		//$rs = $dbGb->getContactAndAbout($arrFilter);
		//$this->view->aboutUs  =$rs['aboutUS'];    
		//$this->view->contact  =$rs['contacting']; 
		
		//$this->view->slide  =$dbGb->getMobileSliding();
		
		
		//$this->view->course  =$dbGb->getSchoolCourse();
		
		$dbAPi = new Application_Model_DbTable_DbGetAPI();
		
		$arrFilter = array(
					'actionName'=>"introductionhome"
					);
					
		$rs = $dbAPi->getDataByAPI($arrFilter);
		$rs = json_decode($rs, true);
		if($rs['code']=="SUCCESS"){
			$this->view->introductionHome  =$rs['result'];
			//print_r($rs['result']);exit();
		}
		
		$arrFilter['actionName']="slieshow";
		$rsSlide = $dbAPi->getDataByAPI($arrFilter);
		$rsSlide = json_decode($rsSlide, true);
		if($rsSlide['code']=="SUCCESS"){
			$this->view->slide  =$rsSlide['result'];
		}
		
		$arrFilter['actionName']="course";
		$rsCourse = $dbAPi->getDataByAPI($arrFilter);
		$rsCourse = json_decode($rsCourse, true);
		if($rsCourse['code']=="SUCCESS"){
			$this->view->course  =$rsCourse['result'];
		}
		
		$arrFilter['actionName']="contactus";
		$arrFilter['isForHome']=1;
		$rsContactus = $dbAPi->getDataByAPI($arrFilter);
		$rsContactus = json_decode($rsContactus, true);
		if($rsContactus['code']=="SUCCESS"){
			
			$this->view->aboutUs  =$rsContactus['result']['about'];    
			$this->view->contact  =$rsContactus['result']['contact'];  
		}
    }
    
    public function logoutAction()
    {
        // action body
        if($this->getRequest()->getParam('value')==1){        	
        	$aut=Zend_Auth::getInstance();
        	$aut->clearIdentity();        	
        	$sessionStudent=new Zend_Session_Namespace(SYSTEM_SES);
			$sessionStudent->unsetAll();

			setcookie(SYSTEM_SES.'stuID', null, -1, '/'); 
			setcookie(SYSTEM_SES.'stuCode', null, -1, '/'); 
			setcookie(SYSTEM_SES.'password', null, -1, '/'); 
			
			Application_Form_FrmMessage::redirectUrl("/");
			exit();
        	if(!empty($sessionStudent->stuID)){
	        	//$log=new Application_Model_DbTable_DbUserLog();
				//$log->insertLogout($sessionStudent->stuID);
	        	
        	}
        } 
    }
	function loginAction(){
		
		$zendRequest = new Zend_Controller_Request_Http();
		$stuID = $zendRequest->getCookie(SYSTEM_SES.'stuID');
		
		//$sessionStudent=new Zend_Session_Namespace(SYSTEM_SES);
    	//$stuID = $sessionStudent->stuID;
    	if (!empty($stuID)){
    		$this->_redirect("/home");
    	}
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
					$sessionStudent=new Zend_Session_Namespace(SYSTEM_SES);
					$sessionStudent->stuID 		= $studentRS['id'];
					$sessionStudent->stuCode	= $studentRS['stuCode'];
					$sessionStudent->password	= $password;
					$sessionStudent->lock();
					
					setcookie(SYSTEM_SES.'stuID', $studentRS['id'], time() + (86400 * 30), '/');// 86400 = 1 day
					setcookie(SYSTEM_SES.'stuCode', $studentRS['stuCode'], time() + (86400 * 30), '/');
					
					Application_Form_FrmMessage::redirectUrl("/home");	
				}
			}
		
			/**
			$dbSt = new Application_Model_DbTable_DbStudentAuth();
    		$studentRS = $dbSt->getStudentAuth($data);
			$password = empty($data['password'])?"":$data['password'];
			if(!empty($studentRS)){
				
				$sessionStudent=new Zend_Session_Namespace(SYSTEM_SES);
				$sessionStudent->stuID 		= $studentRS['stu_id'];
				$sessionStudent->stuCode	= $studentRS['stu_code'];
				$sessionStudent->password	= $password;
				$sessionStudent->lock();
				
				setcookie(SYSTEM_SES.'stuID', $studentRS['stu_id'], time() + (86400 * 30), '/');// 86400 = 1 day
				setcookie(SYSTEM_SES.'stuCode', $studentRS['stu_code'], time() + (86400 * 30), '/');
				setcookie(SYSTEM_SES.'password', $password, time() + (86400 * 30), '/');
				
				Application_Form_FrmMessage::redirectUrl("/home");	
			}
			
			*/
    	}
	}
	
	function validateAction(){
		if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
			
			$dbAPi = new Application_Model_DbTable_DbGetAPI();
			$data['actionName']="authWeb";
			$data['methodPost']="POST";
						
			$rs = $dbAPi->getDataByAPI($data);
			$rs = json_decode($rs, true);
			
			if($rs['code']=="SUCCESS"){
				echo 1;
				exit();
			}
    		echo 0;
    		exit();
			/***
			$dbSt = new Application_Model_DbTable_DbStudentAuth();
    		$studentRS = $dbSt->getStudentAuth($data);
			if(!empty($studentRS)){
				echo 1;
				exit();
			}
    		echo 0;
    		exit();
			***/
    	}
	}

	function validatechangepassAction(){
		if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
			
			$zendRequest = new Zend_Controller_Request_Http();
			$stuID = $zendRequest->getCookie(SYSTEM_SES.'stuID');
			
			
			$data['studentId']=empty($stuID)?0:$stuID;
			$data['currentPassword']=empty($data['currentPassword'])?0:$data['currentPassword'];
			$data['newPassword']=empty($data['newPassword'])?0:$data['newPassword'];
			
			$dbAPi = new Application_Model_DbTable_DbGetAPI();
			$data['actionName']="changePassword";
			$data['methodPost']="POST";
						
			$rs = $dbAPi->getDataByAPI($data);
			$rs = json_decode($rs, true);
			if($rs['code']=="SUCCESS"){
				echo 1;
				exit();
			}
    		echo 0;
    		exit();
			
    	}
	}

    public function errorAction()
    {
        // action body
        
    }
    public function  dashboardAction(){
    	$this->_helper->layout()->disableLayout();
    }
   
    function changelangeAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$session_lang=new Zend_Session_Namespace('lang');
    		$session_lang->lang_id=$data['lange'];
    		Application_Form_FrmLanguages::getCurrentlanguage($data['lange']);
    		print_r(Zend_Json::encode(2));
    		exit();
    	}
    }

    
	
	
	
}





