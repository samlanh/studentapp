<?php class Section_Model_DbTable_DbEvaluation extends Zend_Db_Table_Abstract{

	protected $_name = 'rms_score';
    public function getUserId(){
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	return $_dbgb->getUserId();
    }
	function getStudentEnvaluation($search=null){
		$db=$this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		
		$dbAPi = new Application_Model_DbTable_DbGetAPI();
		$arrFilter = $search;
		$arrFilter['actionName']="studentEvaluation";
		$arrFilter['studentId']=$this->getUserId();
		$row = $dbAPi->getDataByAPI($arrFilter);
		
		$row = json_decode($row, true);
		if($row['code']=="SUCCESS"){
			return $row['result'];    
		}
		
		
	}
	
	function moreEvaluationRecord($data){//ajaxloadmore 
		$db = $this->getAdapter();
		
		$dbGb  = new Application_Model_DbTable_DbGlobal();
		$currentlang = $dbGb->currentlang();
		$limitRecord = $dbGb->limitListView();
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
		
		$row = $this->getStudentEnvaluation($filter);
		$string="";
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
		
		if(!empty($row)){ 
			foreach($row AS $attedance){
				
				$issueDay = date("d",strtotime($attedance['issueDate'])); 
							
				$issueMonth = date("M",strtotime($attedance['issueDate'])); 
				$issueMonthKey = date("m",strtotime($attedance['issueDate'])); 
				
				$issueYear = date("Y",strtotime($attedance['issueDate'])); 
				$forMonthTitle = $attedance['forMonthTitle']; 
				$academicYear = $attedance['academicYear']; 
				if($currentlang==1){
					$forMonthTitle = $dbGb->getNumberInkhmer($forMonthTitle);
					$academicYear = $dbGb->getNumberInkhmer($academicYear);
					
					$issueDay = $dbGb->getNumberInkhmer($issueDay);
					$issueMonth = $dbGb->getMonthInkhmer($issueMonthKey);
					$issueYear = $dbGb->getNumberInkhmer($issueYear);
					
				}
				
				$string.='
					<div class="row lists-view-icon-row  mrg-0">
						<div class="col s3 blg-row-left">
							<span class="title-flex">
								'.$attedance['examTypeTitle'].'
								<span class="title-flex-sub">'.$forMonthTitle.'</span>
							</span>
						</div>
						<div class="col s9 blg-row-right">
							<div class="row mrg-0 info-blg">
								<div class="col s6">
									<span class="row-items-info"><strong class="mark-title">'.$academicYear.'</strong></span>
									<span class="row-items-info">'.$tr->translate("CLASS_NAME").' <strong class="mark-title">'.$attedance['groupCode'].'</strong></span>
									<span class="row-items-info">'.$tr->translate("ROOM").' <strong class="mark-title">'.$attedance['roomName'].'</strong></span>
									<span class="row-items-info">'.$tr->translate("ISSUE_DATE").' <strong class="mark-title">'.$issueDay."-".$issueMonth."-".$issueYear.'</strong></span>
								</div>
								<div class="col s6">
									<span class="row-items-info">'.$tr->translate("TEACHER").' <strong class="mark-title">'.$attedance['teacherName'].'</strong></span>
									<span class="row-items-info">'.$tr->translate("PHONE").' <strong class="mark-title">'.$attedance['teacherTel'].'</strong></span>
									<a class="waves-effect waves-light btn btn-rounded lighten-2" onClick="getPopupContent('.$attedance['id'].')">
										'.$tr->translate("MORE_DETAIL").'
									</a>
								</div>
							</div>								
						</div>
					</div>
						
					
				';
			
			}
		}
		$array = array(
			'countitem'=>count($row),
			'htmlRecord'=>$string,
			'trackPage'=>$totalLimitStart,
			
			);
		return $array;
	}
	
	function getStudentEvaluationDetail($search){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		
		$dbAPi = new Application_Model_DbTable_DbGetAPI();
		$arrFilter = $search;
		$arrFilter['actionName']="studentEvaluationDetail";
		$arrFilter['studentId']=$this->getUserId();
		$row = $dbAPi->getDataByAPI($arrFilter);
		
		$row = json_decode($row, true);
		if($row['code']=="SUCCESS"){
			return $row['result'];    
		}
		
		
	}
	
	function evaluationContent($data){//ajaxloadmore 
		$db = $this->getAdapter();
		
		$dbGb   = new Application_Model_DbTable_DbGlobal();
		$currentlang = $dbGb ->currentlang();
		
		$filter = $data;
		
		$row = $this->getStudentEvaluationDetail($filter);
		$string="";
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
		$stringHead="";
		$string.='<ul class="collection with-header collection-popup-info">';
		if(!empty($row)){ 
			
			foreach($row AS $key =>  $commentItems){
				$issueDay = date("d",strtotime($commentItems['issueDate'])); 
							
				$issueMonth = date("M",strtotime($commentItems['issueDate'])); 
				$issueMonthKey = date("m",strtotime($commentItems['issueDate'])); 
				
				$issueYear = date("Y",strtotime($commentItems['issueDate'])); 
				$forMonthTitle = $commentItems['forMonthTitle']; 
				
				$splitComment = explode('/', $commentItems['commentTitle']);
				$commentKh="";
				$commentEng="";
				if(count($splitComment)==2){
					$commentKh=$splitComment[0];
					$commentEng=$splitComment[1];
				}
				$splitRating = explode('/', $commentItems['ratingTitle']);
				$ratingKh="";
				$ratingEng="";
				if(count($splitRating)==2){
					$ratingKh=$splitRating[0];
					$ratingEng=$splitRating[1];
				}
				
				$commentTitle = $commentEng;
				$ratingTitle = $ratingEng;
				$numRow = $key+1;
				$academicYear = $commentItems['academicYear'];
				if($currentlang==1){
					$forMonthTitle = $dbGb->getNumberInkhmer($forMonthTitle);
					$academicYear = $dbGb->getNumberInkhmer($academicYear);
					
					$issueDay = $dbGb->getNumberInkhmer($issueDay);
					$issueMonth = $dbGb->getMonthInkhmer($issueMonthKey);
					$issueYear = $dbGb->getNumberInkhmer($issueYear);
					$numRow = $dbGb->getNumberInkhmer($numRow);
					$commentTitle = $commentKh;
					$ratingTitle = $ratingKh;
				}
				
				if($key==0){
					$stringHead='
						<div class="modal-header ">
							<div class="expand-modal center" onClick="expanding(event)" >
								<i class="mdi mdi-minus"></i>
							</div>
							<h5>'.$commentItems['examTypeTitle'].' '.$forMonthTitle.'</h5>
							<span class="modal-info">'.$tr->translate("CLASS_NAME").' <strong class="mark-title">'.$commentItems['groupCode'].'</strong> '.$tr->translate("ACADEMIC_YEAR").' <strong class="mark-title">'.$academicYear.'</strong></span>
						</div>
					';
				}
				
				
				
				$string.='
					<li class="collection-item">
						<div class="row mrg-0 ">
							<div class="items-info-left col s9">
								'.$numRow.'./'.$commentTitle.'
							</div>
							<div class="items-info-right col s3">
								<span class="secondary-content">'.$ratingTitle.'</span>
							</div>
						</div>
					</li>
				';
			
			}
		}
		$string.='</ul>';
		
		$string=$stringHead.$string;
		$array = array(
			'htmlRecord'=>$string,
			
			);
		return $array;
	}
}
