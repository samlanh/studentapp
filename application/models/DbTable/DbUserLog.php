<?php

class Application_Model_DbTable_DbUserLog extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_user_log';

	
    public static function writeMessageError($err)
    {
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$action=$request->getActionName();
    	$controller=$request->getControllerName();
    	$module=$request->getModuleName();
    
    	//$session = new Zend_Session_Namespace(SYSTEM_SES);
    	//$user_name = $session->user_name;
		
		$dbSt = new Application_Model_DbTable_DbStudentAuth();
		$student = $dbSt->getStudentInfo();
		$studenName = empty($student['stu_khname'])?"":$student['stu_khname'];
		if(empty($studenName)){
			$studenName = empty($student['studenLatinName'])?"":$student['studenLatinName'];
		}
		$studenLatinName = empty($student['studenLatinName'])?"":$student['studenLatinName'];
		$stuCode = empty($student['stu_code'])?"":$student['stu_code'];

    
    	$file = "../logs/user.log";
    	if (!file_exists($file)) touch($file);
    	$Handle = fopen($file, 'a');
    	$stringData = "[".date("Y-m-d H:i:s")."]"." Student name=>".$studenName." module=>".$module."controller name=>:".$controller. " action =>".$action." error name=>".$err. "\n";
    	fwrite($Handle, $stringData);
    	fclose($Handle);
    }
}

