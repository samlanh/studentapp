<?php class Section_Model_DbTable_DbSchedule extends Zend_Db_Table_Abstract{

	protected $_name = 'rms_score';
    public function getUserId(){
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	return $_dbgb->getUserId();
    }
    function getStudentSchedule($search=array()){
		$db=$this->getAdapter();
		
		$_dbgb = new Application_Model_DbTable_DbGlobal();
	   	$lang = $_dbgb->currentlang();
		
		$label = "name_en";
		$grade = "rms_itemsdetail.title_en";
		$degree = "rms_items.title_en";
		$branchName = "branch_nameen";
		$subjectTitle = "subject_titleen";
			
	   	if($lang==1){// khmer
	   		$subjectTitle = "subject_titlekh";
	   		$branchName = "branch_namekh";
	   		$label = "name_kh";
	   		$grade = "rms_itemsdetail.title";
	   		$degree = "rms_items.title";
	   	}
		$day = empty($search['day'])?0:$search['day'];
		$sql="
		SELECT 
			(SELECT sj.subject_titlekh FROM `rms_subject` AS sj WHERE sj.id = schDetail.subject_id LIMIT 1) AS subjectTitleKh
			,(SELECT sj.subject_titleen FROM `rms_subject` AS sj WHERE sj.id = schDetail.subject_id LIMIT 1) AS subjectTitleEng
			,(SELECT sj.".$subjectTitle." FROM `rms_subject` AS sj WHERE sj.id = schDetail.subject_id LIMIT 1) AS subjectTitle
			,(SELECT te.teacher_name_kh FROM rms_teacher AS te WHERE te.id = schDetail.techer_id LIMIT 1 ) AS teaccherNameKh
			,(SELECT te.teacher_name_en FROM rms_teacher AS te WHERE te.id = schDetail.techer_id LIMIT 1 ) AS teaccherNameEng
			,(SELECT name_kh FROM rms_view WHERE rms_view.key_code=schDetail.day_id AND rms_view.type=18 LIMIT 1)AS daysKh
			,(SELECT t.title FROM rms_timeseting AS t WHERE t.value =schDetail.from_hour LIMIT 1) AS fromHourTitle
			,(SELECT t.title FROM rms_timeseting AS t WHERE t.value =schDetail.to_hour LIMIT 1) AS toHourTitle
			
			,(SELECT b.".$branchName." FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS branchName
			,(SELECT br.branch_namekh FROM `rms_branch` AS br  WHERE br.br_id = g.branch_id LIMIT 1) AS branchNameKh
			,(SELECT br.branch_nameen FROM `rms_branch` AS br  WHERE br.br_id = g.branch_id LIMIT 1) AS branchNameEn
			,(SELECT b.school_nameen FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS schoolNameen
			,(SELECT b.photo FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS branchLogo
			
			,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYear
			,`g`.`group_code` AS groupCode
			,`g`.`degree` AS degree_id
			,`g`.`grade` AS gradeId
			,(SELECT `r`.`room_name` FROM `rms_room` `r` WHERE (`r`.`room_id` = `g`.`room_id`)) AS `roomName`
			,(SELECT $degree FROM `rms_items`	WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1)  LIMIT 1) as degreeTitle
			,(SELECT $grade FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1) as gradeTitle
					
			,schDetail.*
		FROM 
			rms_group_reschedule AS schDetail
			,rms_group_schedule AS sch
			,rms_group_detail_student AS grd
			,rms_group AS g
		WHERE 
			sch.id =schDetail.main_schedule_id
			AND grd.group_id =sch.group_id
			
			AND g.id =sch.group_id
			AND g.is_use =1
			AND g.is_pass =2
			
			AND grd.is_current =1
			AND grd.is_maingrade =1
		";
		$sql.=" AND grd.stu_id=".$this->getUserId();
		if(!empty($day)){
			$sql.=" AND schDetail.day_id=".$day;
		}
		if(!empty($search['degree'])){
			$sql.=" AND g.degree=".$search['degree'];
		}
		
		$sql.=" ORDER BY schDetail.day_id ASC ,schDetail.from_hour ASC ";
		return $db->fetchAll($sql);
	}
   
   function moreScoreRecord($data){//ajaxloadmore 
		$db = $this->getAdapter();
		
		$_dbGb  = new Application_Model_DbTable_DbGlobal();
		$currentlang = $_dbGb->currentlang();
		$limitRecord = $_dbGb->limitListView();
		$limitRecord = empty($limitRecord)?1:$limitRecord;
		
		$totalLimitStart= $limitRecord;
		$filter = array();
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
				if($currentlang==1){
					$rank = $_dbGb->getNumberInkhmer($rank);
					$forMonthTitle = $_dbGb->getNumberInkhmer($forMonthTitle);
				}
				$string.='
				<div class="ui-grids scoreList">
					<div class="row ">
						<div class="col s8 blg-score-left">
							<h3 class="title-score">'.$score['forTypeTitle'].' <strong class="mark-title">'.$forMonthTitle.'</strong> </h3>
							<span class="score-info">'.$tr->translate("CLASS_NAME").' <strong class="mark-title">'.$score['groupCode'].'</strong></span>
							<span class="score-info">'.$tr->translate("ACADEMIC_YEAR").' <strong class="mark-title">'.$score['academicYearTitle'].'</strong></span>
							<span class="score-info">'.$tr->translate("TEACHER").' <strong class="mark-title">'.$score['teacherName'].'</strong></span>
							<span class="score-info">'.$tr->translate("AMT_STUDENT").'	<strong class="mark-title">'.sprintf('%02d',$score['amountStudent'])." ".$tr->translate("STU_UNIT").'</strong></span>
							</div>
						<div class="col s4 blg-score-right">
							<h2 class="ranking">'.$rank.'</h2>
							<span class="score-info">'.$tr->translate("TOTAL_SCORE").' <strong class="mark-title">'.$score['totalScore'].'</strong></span>
							<span class="score-info">'.$tr->translate("AVERAGE").' <strong class="mark-title">'.$score['totalAvg'].'</strong></span>
							<div class="spacer-small"></div>
							<div class="spacer-small"></div>
							<a class="waves-effect waves-light btn btn-rounded  lighten-2" href="'.$baseurl.'/section/score/detail/id/'.$score['id'].'">
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
}
