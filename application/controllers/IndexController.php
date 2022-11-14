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
		$arrFilter = array(
					'isForHome'=>1
					);
		$rs = $dbGb->getContactAndAbout($arrFilter);
		$this->view->aboutUs  =$rs['aboutUS'];    
		$this->view->contact  =$rs['contacting'];    
		$this->view->slide  =$dbGb->getMobileSliding();
		$this->view->allLang  =$dbGb->getAllLanguage();
		
		$this->view->course  =$dbGb->getSchoolCourse();
		
		$dbAPi = new Application_Model_DbTable_DbGetAPI();
		$rs = $dbAPi->getDataByAPI("introductionhome");
		$rs = json_decode($rs, true);
		
		if($rs['code']=="SUCCESS"){
			$this->view->introductionHome  =$rs['result'];
			//print_r($rs['result']);exit();
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
    	}
	}
	
	function validateAction(){
		if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
			$dbSt = new Application_Model_DbTable_DbStudentAuth();
    		$studentRS = $dbSt->getStudentAuth($data);
			if(!empty($studentRS)){
				echo 1;
				exit();
			}
    		echo 0;
    		exit();
    	}
	}

    public function changepasswordAction()
    {
        // action body
        if ($this->getRequest()->isPost()){ 
			$session_user=new Zend_Session_Namespace(SYSTEM_SES);    		
    		$pass_data=$this->getRequest()->getPost();
    		if ($pass_data['password'] == $session_user->pwd){
    			    			 
				$db_user = new Application_Model_DbTable_DbUsers();				
				try {
					$db_user->changePassword($pass_data['new_password'], $session_user->user_id);
					$session_user->unlock();	
					$session_user->pwd=$pass_data['new_password'];
					$session_user->lock();
					Application_Form_FrmMessage::Sucessfull('ការផ្លាស់ប្តូរដោយជោគជ័យ', self::REDIRECT_URL);
				} catch (Exception $e) {
					Application_Form_FrmMessage::message('ការផ្លាស់ប្តូរត្រូវបរាជ័យ');
				}				
    		}
    		else{
    			Application_Form_FrmMessage::message('ការផ្លាស់ប្តូរត្រូវបរាជ័យ');
    		}
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





