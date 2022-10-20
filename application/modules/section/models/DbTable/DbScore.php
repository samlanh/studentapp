<?php class Section_Model_DbTable_DbScore extends Zend_Db_Table_Abstract{

	protected $_name = 'rms_score';
    public function getUserId(){
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	return $_dbgb->getUserId();
    }
    public function getScoreLists($search)
	{ // សម្រាប់លទ្ធផលប្រចាំខែ មិនលម្អិត
		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		
		$label = "name_en";
		$grade = "rms_itemsdetail.title_en";
		$degree = "rms_items.title_en";
		$branch = "b.branch_nameen";
		$month = "month_en";
		$teacherName= "teacher_name_en";
		if($lang==1){// khmer
			$teacherName = "teacher_name_kh";
			$label = "name_kh";
			$grade = "rms_itemsdetail.title";
			$degree = "rms_items.title";
			$branch = "b.branch_namekh";
			$month = "month_kh";
		}
		$sql="SELECT
				s.*
				,st.`stu_id`
				,g.`branch_id`
				,(SELECT $branch FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS branchName
				,(SELECT b.photo FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS branchLogo
				,(SELECT b.school_namekh FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS schoolNameKh
				,(SELECT b.school_nameen FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS schoolNameEng
		   	
				,g.`group_code` AS groupCode
				,`g`.`degree` as degreeId
			
				,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYearTitle
				
				,(SELECT $degree FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degreeTitle
				,(SELECT $grade FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS gradeTitle
		   
				,`g`.`semester` AS `semester`
				,(SELECT $label	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`
				,(SELECT t.$teacherName FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherName
				,(SELECT t.teacher_name_kh FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherNameKh
				,(SELECT t.teacher_name_en FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teaccherNameEng
				,(SELECT t.signature FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherSigature
				,(SELECT $label FROM `rms_view` WHERE TYPE=19 AND key_code =s.exam_type LIMIT 1) as forTypeTitle
				,CASE
					WHEN s.exam_type = 2 THEN s.for_semester
				ELSE (SELECT $month FROM `rms_month` WHERE id=s.for_month  LIMIT 1) 
				END AS forMonthTitle
				
				,sm.total_score AS totalScore
				,sm.total_avg AS totalAvg
				,g.max_average/2 AS passAvrage
				,(SELECT SUM(amount_subject) FROM `rms_group_subject_detail` WHERE rms_group_subject_detail.group_id=g.`id` LIMIT 1) AS amountSubject
				,(SELECT SUM(amount_subject_sem) FROM `rms_group_subject_detail` WHERE rms_group_subject_detail.group_id=g.`id` LIMIT 1) AS amountSubjectsem
				,(SELECT rms_items.pass_average FROM `rms_items` WHERE rms_items.id=g.degree AND  rms_items.type=1 LIMIT 1) as averagePass
				,FIND_IN_SET( 
					sm.total_avg, 
					(
						SELECT GROUP_CONCAT( smSecond.total_avg ORDER BY total_avg DESC )
						FROM rms_score_monthly AS smSecond ,rms_score AS sSecond WHERE
						sSecond.`id`=smSecond.`score_id`
						AND sSecond.group_id= s.`group_id`
						AND sSecond.id=s.`id`
					)
				) AS rank
				,(SELECT COUNT(gds.gd_id)  FROM `rms_group_detail_student` AS gds WHERE gds.group_id = g.id AND gds.is_maingrade=1 ) AS amountStudent
   		FROM
		   	`rms_score` AS s,
		   	`rms_score_monthly` AS sm,
		   	`rms_student` AS st,
		   	`rms_group` AS g
   		WHERE
		   	st.`stu_id`=sm.`student_id`
		   	AND g.`id` = s.`group_id`
		   	AND s.`id`=sm.`score_id`
		   	AND s.status = 1 ";
   	
		$sql.=" AND st.stu_id = ".$this->getUserId();
		$where='';
		if(!empty($search['searchBox'])){
			$s_where=array();
			$s_search=addslashes(trim($search['searchBox']));
			$s_search = str_replace(' ', '', addslashes(trim($search['searchBox'])));
			$s_where[]= " REPLACE(g.group_code,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(s.title_score,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(s.max_score,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(s.note,' ','') LIKE '%{$s_search}%'";
			
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if(!empty($search['academicYear'])){
			$where.=" AND g.academic_year=".$search['academicYear'];
		}
		if(!empty($search['examType'])){
			$where.=" AND s.exam_type=".$search['examType'];
			if($search['examType']==1){
				if(!empty($search['forMonth'])){
					$where.=" AND s.for_month=".$search['forMonth'];
				}	
			}
		}
		$where.=" ORDER BY s.id DESC";
		if(!empty($search['LimitStart'])){
			$where.=" LIMIT ".$search['LimitStart'].",".$search['limitRecord'];
		}else if(!empty($search['limitRecord'])){
			$where.=" LIMIT ".$search['limitRecord'];
		}
		
		return $db->fetchAll($sql.$where);
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
