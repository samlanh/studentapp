<?php class Section_Model_DbTable_DbSchedule extends Zend_Db_Table_Abstract{

	protected $_name = 'rms_score';
    public function getUserId(){
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	return $_dbgb->getUserId();
    }
    function getStudentSchedule($search=array()){
		$db=$this->getAdapter();
		
		$dbAPi = new Application_Model_DbTable_DbGetAPI();
		$arrFilter = $search;
		$arrFilter['actionName']="studentSchedule";
		$arrFilter['studentId']=$this->getUserId();
		$row = $dbAPi->getDataByAPI($arrFilter);
		
		$row = json_decode($row, true);
		if($row['code']=="SUCCESS"){
			return $row['result'];    
		}
		
		
	}
   
   
}
