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
		$dataInfo=$key->getCurrentKeyCodeMiniInv(TRUE);
		if(!empty($dataInfo['systemLink'])){
			$systemLink= $dataInfo['systemLink'];
		}
		return $systemLink;
	}
	public function getAllLanguage(){
		
		$dbAPi = new Application_Model_DbTable_DbGetAPI();
		$arrFilter = array();
		$arrFilter['actionName']="systemLanguage";
		$rsQuery = $dbAPi->getDataByAPI($arrFilter);
		$rsQuery = json_decode($rsQuery, true);
		if($rsQuery['code']=="SUCCESS"){
			return $rsQuery['result'];    
		}
		
	}
	
	function getAllViewByType($type=1){
		
		$dbAPi = new Application_Model_DbTable_DbGetAPI();
		$arrFilter = array();
		$arrFilter['actionName']="systemViewType";
		$arrFilter['type']=$type;
		$rsQuery = $dbAPi->getDataByAPI($arrFilter);
		$rsQuery = json_decode($rsQuery, true);
		if($rsQuery['code']=="SUCCESS"){
			return $rsQuery['result'];    
		}
		
    }
	function getAllMonth($condiction=array()){
		
		$dbAPi = new Application_Model_DbTable_DbGetAPI();
		$arrFilter = $condiction;
		$arrFilter['actionName']="monthOfTheYear";
		$rsQuery = $dbAPi->getDataByAPI($arrFilter);
		$rsQuery = json_decode($rsQuery, true);
		if($rsQuery['code']=="SUCCESS"){
			return $rsQuery['result'];    
		}
    }
	
	function getAcademicYear($condiction=array()){
		
		$dbAPi = new Application_Model_DbTable_DbGetAPI();
		$arrFilter = $condiction;
		$arrFilter['actionName']="systemAcademicYear";
		$rsQuery = $dbAPi->getDataByAPI($arrFilter);
		$rsQuery = json_decode($rsQuery, true);
		if($rsQuery['code']=="SUCCESS"){
			return $rsQuery['result'];    
		}
    	
    }
	function getAllDegree(){
		
		$dbAPi = new Application_Model_DbTable_DbGetAPI();
		$arrFilter = array();
		$arrFilter['actionName']="systemStudyDegree";
		$rsQuery = $dbAPi->getDataByAPI($arrFilter);
		$rsQuery = json_decode($rsQuery, true);
		if($rsQuery['code']=="SUCCESS"){
			return $rsQuery['result'];    
		}
		
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