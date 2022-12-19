<?php class Section_Model_DbTable_DbCalendar extends Zend_Db_Table_Abstract{

	protected $_name = 'rms_score';
    public function getUserId(){
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	return $_dbgb->getUserId();
    }

    function getHoliday($search=array()){
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$currentlang= $dbGb->currentlang();
		
		$dbAPi = new Application_Model_DbTable_DbGetAPI();
		$arrFilter = $search;
		$actionName='getholiday';
		if(!empty($search['mothHoliday'])){
			$actionName.='&mothHoliday='.$search['mothHoliday'];
			
		}
		if(!empty($search['currentLang'])){
			$actionName.='&currentLang='.$search['currentLang'];
				
		}
		$arrFilter['actionName']=$actionName;
		$arrFilter['studentId']=$this->getUserId();
		$row = $dbAPi->getDataByAPI($arrFilter);
		$row = json_decode($row, true);
		$results = array();
		if($row['code']=="SUCCESS"){
			$results =  $row['result'];    
		}
		return $results;
	}
	
}
