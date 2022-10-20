<?php

class Application_Model_DbTable_DbStudentAuth extends Zend_Db_Table_Abstract
{
    // set name value
	public function setName($name){
		$this->_name=$name;
	}
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	//function get user info from database
	public function getStudentAuth($data)
	{		
		$account = empty($data['account'])?"":$data['account'];
		$password = empty($data['password'])?"":$data['password'];
		$db = $this->getAdapter();
		if (!empty($account)){	
			$sql=" SELECT s.* FROM rms_student AS s WHERE 1 AND s.status=1 ";
			$sql.= " AND ".$db->quoteInto('s.stu_code=?', $account);
			$sql.= " AND ".$db->quoteInto('s.password=?', md5($password));
			$sql.=" LIMIT 1 ";
			$row=$db->fetchRow($sql);
			if(!$row) return NULL;
			return $row;
			
		}else {
			return null;
		}
	}
	public function getStudentInfo(){
		$db = $this->getAdapter();
		
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$userId = $dbGb->getUserId();
		$userId = empty($userId)?0:$userId;
		$sql=" SELECT s.* ";
		$sql.=" ,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) as studenLatinName ";
		$sql.=" FROM rms_student AS s ";
		$sql.=" WHERE 1 AND s.status=1 ";
		$sql.= " AND ".$db->quoteInto('s.stu_id=?', $userId);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
}
?>