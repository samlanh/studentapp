<?php class Section_Model_DbTable_DbMistake extends Zend_Db_Table_Abstract{

	protected $_name = 'rms_score';
    public function getUserId(){
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	return $_dbgb->getUserId();
    }
	
    function getStudentTotalMistake($search=array()){
		
		$dbAPi = new Application_Model_DbTable_DbGetAPI();
		$arrFilter = $search;
		$arrFilter['actionName']="studentAttendance";
		$arrFilter['type']=2;
		$arrFilter['studentId']=$this->getUserId();
		$row = $dbAPi->getDataByAPI($arrFilter);
		$row = json_decode($row, true);
		if($row['code']=="SUCCESS"){
			return $row['result'];    
		}
		
		
	}
	
	function moreMistakeRecord($data){//ajaxloadmore 
		$db = $this->getAdapter();
		
		$_dbGb  = new Application_Model_DbTable_DbGlobal();
		$currentlang = $_dbGb->currentlang();
		$limitRecord = $_dbGb->limitListView();
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
		
		$row = $this->getStudentTotalMistake($filter);
		$string="";
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
		
		if(!empty($row)){ 
			foreach($row AS $attedance){
				
				$yearAtt = date("Y",strtotime($attedance['date_attendence'])); 
				$monthAtt = date("M",strtotime($attedance['date_attendence'])); 
				$monthKey = date("m",strtotime($attedance['date_attendence'])); 
				if($currentlang==1){
					$yearAtt = $_dbGb->getNumberInkhmer($yearAtt);
					$monthAtt = $_dbGb->getMonthInkhmer($monthKey);
				}
				
				
				$string.='
					<div class="row lists-view-icon-row  mrg-0">
						<div class="col s3 blg-row-left">
							<span class="title-flex">'.$monthAtt.'
								<span class="title-flex-sub">'.$yearAtt.'</span>
							</span>
						</div>
						<div class="col s9 blg-row-right">
							<div class="row mrg-0 info-blg">
								<div class="col s6">
									<span class="row-items-info">'.$tr->translate("CLASS_NAME").' <strong class="mark-title">'.$attedance['groupCode'].'</strong></span>
									<span class="row-items-info">'.$tr->translate("ACADEMIC_YEAR").' <strong class="mark-title">'.$attedance['academicYear'].'</strong></span>
									<span class="row-items-info">'.$tr->translate("ROOM").' <strong class="mark-title">'.$attedance['roomName'].'</strong></span>
									<a class="waves-effect waves-light btn btn-rounded  lighten-2" href="'.$baseurl.'/section/envaluation/detail/id/'.$attedance['yearMonth'].'?group='.$attedance['group_id'].'">
										'.$tr->translate("MORE_DETAIL").'
									</a>
								</div>
								<div class="col s3">
									<span class="row-items-info text-right">'.$tr->translate("NO_PERMISSION_SHORT_CUT").'</span>
									<span class="row-items-info text-right">'.$tr->translate("PERMISSION_SHORT_CUT").'</span>
									<span class="row-items-info text-right">'.$tr->translate("LATE_SHORT_CUT").'</span>
									<span class="row-items-info text-right">'.$tr->translate("EARLY_LEAVE_SHORT_CUT").'</span>
								</div>
								<div class="col s3">
									<span class="row-items-info counting text-right "><strong class="mark-title">'.sprintf('%02d',$attedance['countNoPermission'])." ".$tr->translate("TIME_UNIT").'</strong></span>
									<span class="row-items-info counting text-right "><strong class="mark-title">'.sprintf('%02d',$attedance['countPermission'])." ".$tr->translate("TIME_UNIT").'</strong></span>
									<span class="row-items-info counting text-right "><strong class="mark-title">'.sprintf('%02d',$attedance['countLate'])." ".$tr->translate("TIME_UNIT").'</strong></span>
									<span class="row-items-info counting text-right "><strong class="mark-title">'.sprintf('%02d',$attedance['countEalyLeave'])." ".$tr->translate("TIME_UNIT").'</strong></span>
								
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
	
	
	function getStudentMistakeDetail($search){
		
		$dbAPi = new Application_Model_DbTable_DbGetAPI();
		$arrFilter = $search;
		$arrFilter['actionName']="studentAttendanceDetail";
		$arrFilter['type']=2;
		$arrFilter['studentId']=$this->getUserId();
		$row = $dbAPi->getDataByAPI($arrFilter);
		$row = json_decode($row, true);
		if($row['code']=="SUCCESS"){
			return $row['result'];    
		}
	}
	
	
	function detailContent($data){
		$db = $this->getAdapter();
		
		$dbGb   = new Application_Model_DbTable_DbGlobal();
		$currentlang = $dbGb ->currentlang();
		
		$filter = $data;
		
		$row = $this->getStudentMistakeDetail($filter);
		$string="";
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
		$stringHead="";
		$stringFooter="";
		
		
		$countSmallMistake 	=	0; 	//1
		$countMediumMistake = 	0;	//2
		$countBigMistake 	= 	0;	//3
		$countOtherMistake 	= 	0;	//4
		if(!empty($row)){ 
			$string.='<ul class="collection with-header collection-popup-info">';
			foreach($row AS $key =>  $attedance){
				
				$academicYear = $attedance['academicYear']; 
				
				$yearAtt = date("Y",strtotime($attedance['date_attendence'])); 
				$monthAtt = date("M",strtotime($attedance['date_attendence'])); 
				$monthKey = date("m",strtotime($attedance['date_attendence'])); 
				$dayAtt = date("d",strtotime($attedance['date_attendence'])); 
				
				$numRow = $key+1;
				if($currentlang==1){
					$academicYear = $dbGb->getNumberInkhmer($academicYear);
					
					$yearAtt = $dbGb->getNumberInkhmer($yearAtt);
					$monthAtt = $dbGb->getMonthInkhmer($monthKey);
					$numRow = $dbGb->getNumberInkhmer($numRow);
					$dayAtt = $dbGb->getNumberInkhmer($dayAtt);
				}
				if($key==0){
					$stringHead='
						<div class="modal-header ">
							<h5>'.$monthAtt.'</h5>
							<span class="modal-info">'.$tr->translate("CLASS_NAME").' <strong class="mark-title">'.$attedance['groupCode'].'</strong> '.$tr->translate("ACADEMIC_YEAR").' <strong class="mark-title">'.$academicYear.'</strong></span>
						</div>
					';
				}
				
				if($attedance['attendence_status']==1){
					$countSmallMistake = $countSmallMistake+1;
				}else if($attedance['attendence_status']==2){
					$countMediumMistake = $countMediumMistake+1;
				}else if($attedance['attendence_status']==3){
					$countBigMistake = $countBigMistake+1;
				}else if($attedance['attendence_status']==4){
					$countOtherMistake = $countOtherMistake+1;
				}
				
				
				$string.='
					<li class="collection-item">
						<div class="row mrg-0 ">
							<div class="items-info-left col s9">
								<i class="mdi mdi-calendar-clock "></i> '.$dayAtt."-".$monthAtt."-".$yearAtt.'
								<small class="description-items">'.$attedance['description'].'</small>
							</div>
							<div class="items-info-right col s3">
								<span class="secondary-content">'.$attedance['attendenceStatusTitle'].'</span>
							</div>
						</div>
					</li>
				';
			
			}
			$string.='</ul>';
			
			$countSmallMistake = sprintf('%02d',$countSmallMistake);
			$countMediumMistake = sprintf('%02d',$countMediumMistake);
			$countBigMistake = sprintf('%02d',$countBigMistake);
			$countOtherMistake = sprintf('%02d',$countOtherMistake);
			
			if($currentlang==1){
				$countSmallMistake 		= $dbGb->getNumberInkhmer($countSmallMistake);
				$countMediumMistake 	= $dbGb->getNumberInkhmer($countMediumMistake);
				$countBigMistake 			= $dbGb->getNumberInkhmer($countBigMistake);
				$countOtherMistake 	= $dbGb->getNumberInkhmer($countOtherMistake);
			}
				
			$stringFooter.='
				<div class="content-footer content-total">
					<div class="row mrg-0 ">
						<div  class="col s3 text-center">
							<span class="modal-info">'.$tr->translate("SMALL_MISTACK_SHORT_CUT").'</span>
							<span class="modal-info"><strong>'.$countSmallMistake.'</strong></span>
						</div>
						<div  class="col s3 text-center">
							<span class="modal-info">'.$tr->translate("MEDIUM_MISTACK_SHORT_CUT").'</span>
							<span class="modal-info"><strong>'.$countMediumMistake.'</strong></span>
						</div>
						<div  class="col s3 text-center">
							<span class="modal-info">'.$tr->translate("BIG_MISTACK_SHORT_CUT").'</span>
							<span class="modal-info"><strong>'.$countBigMistake.'</strong></span>
						</div>
						<div  class="col s3 text-center">
							<span class="modal-info">'.$tr->translate("OTHER").'</span>
							<span class="modal-info"><strong>'.$countOtherMistake.'</strong></span>
						</div>
					</div>
				</li>
			';
		}else{
			$stringFooter.='
				<div class="empty-content">
					<div class="row mrg-0 ">
						<div  class="col s12 text-center">
							<h3 ><i class="mdi mdi-cloud-off-outline"></i> '.$tr->translate("EMPTY_RECORD").'</h3>
						</div>
					</div>
				</li>
			';
		}
		
		
		$string=$stringHead.$string.$stringFooter;
		$array = array(
			'htmlRecord'=>$string,
			
			);
		return $array;
	}
}
