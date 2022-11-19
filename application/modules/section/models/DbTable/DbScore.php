<?php class Section_Model_DbTable_DbScore extends Zend_Db_Table_Abstract{

	protected $_name = 'rms_score';
    public function getUserId(){
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	return $_dbgb->getUserId();
    }
    public function getScoreLists($search)
	{ // សម្រាប់លទ្ធផលប្រចាំខែ មិនលម្អិត
		$db = $this->getAdapter();
		
		$dbAPi = new Application_Model_DbTable_DbGetAPI();
		$arrFilter = $search;
		$arrFilter['actionName']="studentScore";
		$arrFilter['studentId']=$this->getUserId();
		$row = $dbAPi->getDataByAPI($arrFilter);
		
		$row = json_decode($row, true);
		if($row['code']=="SUCCESS"){
			return $row['result'];    
		}
		
		
   }
   
   function moreScoreRecord($data){//ajaxloadmore 
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
		
		$row = $this->getScoreLists($filter);
		$string="";
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
		
		if(!empty($row)){ 
			foreach($row AS $score){
				$rank = $score['rank']; 
				$forMonthTitle = $score['forMonthTitle']; 
				
				$academicYearTitle = $score['academicYearTitle']; 
				$totalScore = $score['totalScore'];
				$totalAvg = $score['totalAvg'];
				$amountStudent = sprintf('%02d',$score['amountStudent']);
					
				if($currentlang==1){
					$rank = $_dbGb->getNumberInkhmer($rank);
					$forMonthTitle = $_dbGb->getNumberInkhmer($forMonthTitle);
					$academicYearTitle = $_dbGb->getNumberInkhmer($academicYearTitle);
						
					$totalScore = $_dbGb->getNumberInkhmer($totalScore);
					$totalAvg = $_dbGb->getNumberInkhmer($totalAvg);
					$amountStudent = $_dbGb->getNumberInkhmer($amountStudent);
				}
				$string.='
				<div class="ui-grids scoreList">
					<div class="row ">
						<div class="col s7 blg-score-left">
							<h3 class="title-score">'.$score['forTypeTitle'].' <strong class="mark-title">'.$forMonthTitle.'</strong> </h3>
							<span class="score-info"><strong class="mark-title">'.$academicYearTitle.'</strong></span>
							<span class="score-info">'.$tr->translate("CLASS_NAME").' <strong class="mark-title">'.$score['groupCode'].'</strong></span>
							<span class="score-info">'.$tr->translate("TEACHER").' <strong class="mark-title">'.$score['teacherName'].'</strong></span>
							<span class="score-info">'.$tr->translate("AMT_STUDENT").'	<strong class="mark-title">'.$amountStudent." ".$tr->translate("STU_UNIT").'</strong></span>
							</div>
						<div class="col s5 blg-score-right">
							<h2 class="ranking">'.$rank.'</h2>
							<span class="score-info">'.$tr->translate("TOTAL_SCORE").' <strong class="mark-title">'.$totalScore.'</strong></span>
							<span class="score-info">'.$tr->translate("AVERAGE").' <strong class="mark-title">'.$totalAvg.'</strong></span>
							<div class="spacer-small"></div>
							<div class="spacer-small"></div>
							<a class="waves-effect waves-light btn btn-rounded  lighten-2 preloader-trigger " onClick="getPopupContent('.$score['id'].');" >
								'.$tr->translate("MORE_DETAIL").'
							</a>
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
	
	
	public function getScoreInfoById($filter)
	{ 
		
		$dbAPi = new Application_Model_DbTable_DbGetAPI();
		$arrFilter = $filter;
		$arrFilter['actionName']="scoreInformation";
		$arrFilter['studentId']=$this->getUserId();
		$arrFilter['id']=empty($filter['id'])?0:$filter['id'];
		$row = $dbAPi->getDataByAPI($arrFilter);
		
		$row = json_decode($row, true);
		if($row['code']=="SUCCESS"){
			return $row['result'];    
		}
		
		
	}
	function getSubjectByGroup($filter){
		
		
		$dbAPi = new Application_Model_DbTable_DbGetAPI();
		$arrFilter = $filter;
		$arrFilter['actionName']="subjectByGroup";
		$arrFilter['studentId']=$this->getUserId();
		$arrFilter['id']=empty($filter['id'])?0:$filter['id'];
		$arrFilter['exam_type']=empty($filter['exam_type'])?0:$filter['exam_type'];
		$row = $dbAPi->getDataByAPI($arrFilter);
		
		$row = json_decode($row, true);
		if($row['code']=="SUCCESS"){
			return $row['result'];    
		}
		
	}
	 function getScoreBySubject($filter){
		 
		$dbAPi = new Application_Model_DbTable_DbGetAPI();
		$arrFilter = $filter;
		$arrFilter['actionName']="studentScoreBySubject";
		$arrFilter['studentId']=$this->getUserId();
		$arrFilter['id']=empty($filter['id'])?0:$filter['id'];
		$arrFilter['subjectId']=empty($filter['subjectId'])?0:$filter['subjectId'];
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
		
		$scoreInfo = $this->getScoreInfoById($filter);
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
		
		$string="";
		$stringHead="";
		$stringFooter="";
		
		$rank = $scoreInfo['rank']; 
		$forMonthTitle = $scoreInfo['forMonthTitle']; 
		$academicYearTitle = $scoreInfo['academicYearTitle']; 
		$totalScore = $scoreInfo['totalScore'];
		$totalAvg = $scoreInfo['totalAvg'];
		$amountStudent = sprintf('%02d',$scoreInfo['amountStudent']);
		if($currentlang==1){
			$rank = $dbGb->getNumberInkhmer($rank);
			$forMonthTitle = $dbGb->getNumberInkhmer($forMonthTitle);
			$academicYearTitle = $dbGb->getNumberInkhmer($academicYearTitle);
			
			$totalScore = $dbGb->getNumberInkhmer($totalScore);
			$totalAvg = $dbGb->getNumberInkhmer($totalAvg);
			$amountStudent = $dbGb->getNumberInkhmer($amountStudent);
		}
		$stringHead='
			<div class="modal-header ">
				<h5>'.$scoreInfo['forTypeTitle'].' '.$forMonthTitle.'</h5>
				<span class="modal-info">'.$tr->translate("CLASS_NAME").' <strong class="mark-title">'.$scoreInfo['groupCode'].'</strong> '.$tr->translate("ACADEMIC_YEAR").' <strong class="mark-title">'.$academicYearTitle.'</strong></span>
			</div>
		';
		
		$filter['exam_type']=$scoreInfo['exam_type'];
		$row = $this->getSubjectByGroup($filter);
		$arrScoreFilter = array('id'=>$scoreInfo['id']);
		if(!empty($row)){ 
			$string.='<ul class="collection with-header collection-popup-info">';
			foreach($row AS $key =>  $rowSubject){
				
				$arrScoreFilter['subjectId'] = $rowSubject['subjectId'];
				$score = $this->getScoreBySubject($arrScoreFilter);
				if($score['score_cut']>=$score['score']){
					$tscoreSubject = 0;
				}else{
					$tscoreSubject = $score['score']-$score['score_cut'];
				}
				if($currentlang==1){
					$tscoreSubject = $dbGb->getNumberInkhmer($tscoreSubject);
				}
				$string.='
					<li class="collection-item">
						<div class="row mrg-0 ">
							<div class="items-info-left col s9">
								'.$rowSubject['subjectTitle'].'
							</div>
							<div class="items-info-right col s3">
								<span class="secondary-content">'.$tscoreSubject.' '.$tr->translate("POINT_SHORT_CUT").'</span>
							</div>
						</div>
					</li>
				';
			
			}
			$string.='</ul>';
			
			
				
			$stringFooter.='
				<div class="content-footer content-total">
					<ul class="collection collection-footer">
						<li class="collection-item collection-item-footer">
							<div class="row mrg-0 ">
								<div class="items-info-left col s9">
									'.$tr->translate("TOTAL_SCORE").' 
								</div>
								<div class="items-info-right col s3">
									<span class="secondary-content">'.$totalScore.' '.$tr->translate("POINT_SHORT_CUT").'</span>
								</div>
							</div>
						</li>
						<li class="collection-item collection-item-footer">
							<div class="row mrg-0 ">
								<div class="items-info-left col s9">
									'.$tr->translate("AVERAGE").'  
								</div>
								<div class="items-info-right col s3">
									<span class="secondary-content">'.$totalAvg.'</span>
								</div>
							</div>
						</li>
						<li class="collection-item collection-item-footer">
							<div class="row mrg-0 ">
								<div class="items-info-left col s9">
									'.$tr->translate("RANKING").'  
								</div>
								<div class="items-info-right col s3">
									<span class="secondary-content">'.$rank.'</span>
								</div>
							</div>
						</li>
					</ul>
				</div>
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
