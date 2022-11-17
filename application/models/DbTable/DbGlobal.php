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
		$zendRequest = new Zend_Controller_Request_Http();
		$userId = $zendRequest->getCookie(SYSTEM_SES.'stuID');
		$userId = empty($userId)?0:$userId;
		//$sessionStudent=new Zend_Session_Namespace(SYSTEM_SES);
		//$userId = empty($sessionStudent->stuID)?0:$sessionStudent->stuID;
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
	
	
	public function getNewsEvents($search = array()){
    	$db = $this->getAdapter();
    	try{
			
			$dbAPi = new Application_Model_DbTable_DbGetAPI();
			$arrFilter = $search;
			$arrFilter['actionName']="news";
			$arrFilter['LimitStart']=empty($search['LimitStart'])?null:$search['LimitStart'];
			$arrFilter['limitRecord']=empty($search['limitRecord'])?null:$search['limitRecord'];
			$rsQuery = $dbAPi->getDataByAPI($arrFilter);
			$rsQuery = json_decode($rsQuery, true);
			if($rsQuery['code']=="SUCCESS"){
				return $rsQuery['result']['normal_news'];    
			}
    		
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
	public function getNewsEventsDetail($id){
    	$db = $this->getAdapter();
    	try{
			$dbAPi = new Application_Model_DbTable_DbGetAPI();
			$arrFilter = array();
			$arrFilter['actionName']="newsDetail";
			$arrFilter['id']=$id;
			$rsQuery = $dbAPi->getDataByAPI($arrFilter);
			$rsQuery = json_decode($rsQuery, true);
			if($rsQuery['code']=="SUCCESS"){
				return $rsQuery['result'];    
			}
			
    		
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
	function moreNewsEvents($data){
		$db = $this->getAdapter();
		
		$systemLink = $this->systemLink();
		$currentlang = $this->currentlang();
		$limitRecord = $this->limitListView();
		$limitRecord = empty($limitRecord)?1:$limitRecord;
		
		$totalLimitStart= $limitRecord;
		$filter = $data;
		$filter['limitRecord'] = $limitRecord;
		if(!empty($data['page'])){
			if($data['page']<$limitRecord){
				$data['page'] = $limitRecord;
			}
			$filter['LimitStart'] = $data['page'];
			$filter['limitRecord'] = $limitRecord;
			$totalLimitStart = $data['page'] + $limitRecord;
		}
		
		$rs = $this->getNewsEvents($filter);
		$string="";
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
		
		if(!empty($rs)){ 
			foreach($rs AS $row){
				
				$strDescription = mb_substr(strip_tags($row['description']), 0, 150, "UTF-8");
				 
				$yearAtt = date("Y",strtotime($row['publish_date'])); 
				$monthAtt = date("M",strtotime($row['publish_date'])); 
				$days = date("d",strtotime($row['publish_date'])); 
				$monthKey = date("m",strtotime($row['publish_date'])); 
				
				$images = $baseurl."/images/no-photo.png";
				$imageFeature = $systemLink.'/images/newsevent/'.$row['image_feature'];
				if ($this->does_url_exists($imageFeature)){
					$images = $systemLink.'/images/newsevent/'.$row['image_feature'];
				}
				if($currentlang==1){
					
					$yearAtt = $this->getNumberInkhmer($yearAtt);
					$monthAtt = $this->getMonthInkhmer($monthKey);
					$days = $this->getNumberInkhmer($days);
					
					//$strDescription = mb_substr(strip_tags($row['description']), 0, 200, "UTF-8");
				}
				
				$string.='
					<div class="col s12">
						<div class="blog-img-wrap">
							<a class="img-wrap" href="'.$images.'" data-fancybox="images" data-caption="'.$row['title'].'">
							<img class="z-depth-1" style="width: 100%;" src="'.$images.'">
							</a>
						</div>
						<div class="blog-info">
							<a href="'.$baseurl.'/utility/news/detail/id/'.$row['id'].'" >                    
								<h5 class="title">'.$row['title'].'</h5>
							</a>
							<span class="small date"><i class="mdi mdi-calendar-clock "></i> '.$days." ".$monthAtt." ".$yearAtt.'</span>
														  
							<p class="bot-0 text">'.$strDescription.'...</p>
							<div class="spacer"></div>
							<div class="divider"></div>
							<div class="spacer"></div>
						</div>
					</div>
				
				';
			}
		}
		
		$array = array(
			'htmlRecord'=>$string,
			'trackPage'=>$totalLimitStart,
			
			);
			
		return $array;
	}
	
}
?>