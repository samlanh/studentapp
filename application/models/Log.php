<?php 
/*
 * @author Sophen
 * Date 26 June 2012
 */
class Application_Model_Log
{
  	//For write user log file
	public function writeUserLog($id='')
	{
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$action=$request->getActionName();
		$controller=$request->getControllerName();
		$module=$request->getModuleName();
						
		//$session = new Zend_Session_Namespace(SYSTEM_SES);
		//$user_name = $session->user_name;
		
		$zendRequest = new Zend_Controller_Request_Http();
		$stuID = $zendRequest->getCookie(SYSTEM_SES.'stuID');
		$stuCode = $zendRequest->getCookie(SYSTEM_SES.'stuCode');
		
		$file = "../logs/user.log";
		if (!file_exists($file)) touch($file);
		//Mode a append at the end of file
		$Handle = fopen($file, 'a');
		$stringData = "[".date("Y-m-d H:i:s")."]"." [user]:".$stuCode." [module]:".$module." [controller]:".$controller. " [action]:".$action." [id]:".$id. "\n";
        fwrite($Handle, $stringData);
        fclose($Handle);	
	}
}
?>