<?php class Section_Model_DbTable_DbScore extends Zend_Db_Table_Abstract{

	protected $_name = 'rms_score';
    public function getUserId(){
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	return $_dbgb->getUserId();
    }
    
}
