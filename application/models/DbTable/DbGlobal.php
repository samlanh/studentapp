<?php

class Application_Model_DbTable_DbGlobal extends Zend_Db_Table_Abstract
{
    // set name value
	public function setName($name){
		$this->_name=$name;
	}
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function getUserId(){
		$sessionStudent=new Zend_Session_Namespace(SYSTEM_SES);
		$userId = empty($sessionStudent->stuID)?0:$sessionStudent->stuID;
		return $userId;
   }
	function currentlang(){
		$session_lang=new Zend_Session_Namespace('lang');
		$lang = $session_lang->lang_id;
		if (empty($session_lang->lang_id)){
			$lang = 1;
		}
		return $lang;
	}
	function getMonthInkhmer($monthNum){
		$monthKH = array(
			"01"=>"មករា",
			"02"=>"កុម្ភៈ",
			"03"=>"មីនា",
			"04"=>"មេសា",
			"05"=>"ឧសភា",
			"06"=>"មិថុនា",
			"07"=>"កក្កដា",
			"08"=>"សីហា",
			"09"=>"កញ្ញា",
			"10"=>"តុលា",
			"11"=>"វិច្ឆិកា",
			"12"=>"ធ្នូ"
		);
		$monthChar = empty($monthKH[$monthNum])?"":$monthKH[$monthNum];
		return $monthChar;
	}
	public function getDayInkhmerBystr($str){
    	
    	$rs=array(
    			'Mon'=>'ច័ន្ទ',
    			'Tue'=>'អង្គារ',
    			'Wed'=>'ពុធ',
    			'Thu'=>"ព្រហ",
    			'Fri'=>"សុក្រ",
    			'Sat'=>"សៅរី",
    			'Sun'=>"អាទិត្យ");
    	if($str==null){
    		return $rs;
    	}else{
    	return $rs[$str];
    	}
    
    }
	function getNumberInkhmer($number){
    	$khmernumber = array("០","១","២","៣","៤","៥","៦","៧","៨","៩");
    	$spp = str_split($number);
    	$num="";
    	foreach ($spp as $ss){
    		if (!empty($khmernumber[$ss])){
    			$num.=$khmernumber[$ss];
    		}else{
    			$num.=$ss;
    		}
    	}
    	return $num;
    }
	function limitListView(){
		$limited = 5;
		return $limited;
	}
	function systemLink(){
		$systemLink = "http://192.168.0.103/camappgit/psst/public/";
		$key = new Application_Model_DbTable_DbKeycode();
		$dataInfo=$key->getKeyCodeMiniInv(TRUE);
		if(!empty($dataInfo['systemLink'])){
			$systemLink= $dataInfo['systemLink'];
		}
		return $systemLink;
	}
	public function getAllLanguage(){
		$db = $this->getAdapter();
		$sql="SELECT * FROM `ln_language` AS l WHERE l.`status`=1 ORDER BY l.ordering ASC";
		return $db->fetchAll($sql);
	}
	function getMobileSliding(){
		$db = $this->getAdapter();
		$sql="SELECT msl.* FROM mobile_slideshow AS msl";
		$sql.="";
		$sql.="  ";
	   	return $db->fetchAll($sql);
	}
	
	public function getContactAndAbout($arrFilter){
    	$db = $this->getAdapter();
    	try{
    		$currentLang = $this->currentlang();
    		
    		$sql=" 
			SELECT
				l.*,
				ld.title,
				ld.description
    		FROM `mobile_location` AS l,
				`mobile_location_detail` AS ld
    		WHERE l.id=ld.location_id
				AND ld.lang= $currentLang ";
    		$sql.=" LIMIT 1 ";
    		$row = $db->fetchRow($sql);
    		
			$sql=" SELECT 
						ad.title,ad.description
					FROM `mobile_about` AS a,
						`mobile_about_detail` AS ad
					WHERE a.id=ad.abouts_id
						AND ad.lang= $currentLang AND a.status=1 ";
    		if (!empty($arrFilter['isForHome'])){
				$sql.=" AND a.isForHome = 1 ";
			}
    		$rowabout = $db->fetchAll($sql);
			
			$arrReturn = array(
				'aboutUS'=>$rowabout
				,'contacting'=>$row
			);
    		return $arrReturn;
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
	function getAllViewByType($type=1){
    	$db=$this->getAdapter();
		$currentLang = $this->currentlang();
		$colunmname='name_en';
		if ($currentLang==1){
			$colunmname='name_kh';
		}
    	$sql="
			SELECT v.key_code AS id,
			v.$colunmname AS name
			FROM `rms_view` AS v WHERE v.status=1 ";
        $sql.=" AND v.type= ".$type;
        $oder=" ORDER BY v.key_code ASC ";
    	return $db->fetchAll($sql.$oder);
    }
	function getAllMonth($condiction=array()){
    	$db=$this->getAdapter();
		$currentLang = $this->currentlang();
		$colunmname='month_en';
		if ($currentLang==1){
			$colunmname='month_kh';
		}
    	$sql="
			SELECT m.id,
			m.$colunmname AS name
			FROM `rms_month` AS m WHERE m.status=1 ";
        $oder=" ORDER BY m.id ASC ";
    	return $db->fetchAll($sql.$oder);
    }
	function getAcademicYear($condiction=array()){
    	$db=$this->getAdapter();
    	$sql="
			SELECT ay.id,
				CONCAT(ay.fromYear,'-',ay.toYear) AS name
			FROM rms_academicyear AS ay WHERE ay.status=1 ";
        $oder=" ORDER BY ay.fromYear DESC ";
    	return $db->fetchAll($sql.$oder);
    }
	function getAllDegree(){
  	$db = $this->getAdapter();
  	
  	$currentLang = $this->currentlang();
  	$colunmname='title_en';
  	if ($currentLang==1){
  		$colunmname='title';
  	}
  	
  	$this->_name = "rms_items";
  	$sql="SELECT m.id, m.$colunmname AS name FROM $this->_name AS m WHERE m.status=1 ";
  	$sql.=" AND m.type=1 ";
  	/*
	if (!empty($schooloption)){
  		$schooloptionParam = explode(",", $schooloption);
  		$s_whereee = array();
  		foreach ($schooloptionParam as $schooloptionId){
  			$s_whereee[] = $schooloptionId." IN (m.schoolOption)";
  		}
  		$sql .=' AND ( '.implode(' OR ',$s_whereee).')';
  	}
	*/
  	$sql .=' ORDER BY m.schoolOption ASC,m.type DESC,m.ordering DESC, m.title ASC';	
  	return $db->fetchAll($sql);
  }
	function does_url_exists($url) {
	
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($code == 200) {
			$status = true;
		} else {
			$status = false;
		}
		curl_close($ch);
		return $status;
	}
	
	public function getDiscipline(){
    	$db = $this->getAdapter();
    	try{
    		$currentLang = $this->currentlang();
			$sql=" SELECT 
						ad.title,
						ad.description
					FROM `mobile_disciplinenote` AS a,
						`mobile_disciplinenote_detail` AS ad
					WHERE a.id=ad.displicipline_id
						AND ad.lang= $currentLang 
						AND a.status=1 ";
			$sql.=" ORDER BY a.ordering ASC ";
    		$row = $db->fetchAll($sql);
    		return $row;
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
	public function getGradingSystem(){
    	$db = $this->getAdapter();
    	try{
    		$currentLang = $this->currentlang();
			$sql=" SELECT 
						ad.title,
						ad.description
					FROM `mobile_grading_system` AS a,
						`mobile_grading_system_detail` AS ad
					WHERE a.id=ad.grading_id
						AND ad.lang= $currentLang 
						AND a.status=1 ";
			$sql.=" ORDER BY a.id ASC ";
    		$row = $db->fetchAll($sql);
    		return $row;
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
	public function getNewsEvents(){
    	$db = $this->getAdapter();
    	try{
    		$currentLang = $this->currentlang();
			$sql=" SELECT 
						a.*,
						ad.title,
						ad.description
					FROM `mobile_news_event` AS a,
						`mobile_news_event_detail` AS ad
					WHERE a.id=ad.news_id
						AND ad.lang= $currentLang 
						AND a.status=1 ";
			$sql.=" ORDER BY a.publish_date DESC,a.id DESC ";
    		$row = $db->fetchAll($sql);
    		return $row;
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
}
?>