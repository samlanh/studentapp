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
				,(SELECT t.tel FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherTel
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
				if(!empty($search['month'])){
					$where.=" AND s.for_month=".$search['month'];
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
							<a class="waves-effect waves-light btn btn-rounded  lighten-2" onClick="getPopupContent('.$score['id'].');" >
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
				,(SELECT t.tel FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherTel
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
		   	`rms_group` AS g
   		WHERE
		   	 g.`id` = s.`group_id`
		   	AND s.`id`=sm.`score_id`
		   	AND s.status = 1 ";
			
		$scoreId = empty($filter['id'])?0:$filter['id'];
		$sql.=" AND sm.student_id = ".$this->getUserId();
		$sql.=" AND s.id = ".$scoreId;
		$where='';
		
		return $db->fetchRow($sql.$where);
	}
	function getSubjectByGroup($filter){
		
		$db = $this->getAdapter();
		$scoreId = empty($filter['id'])?0:$filter['id'];
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		
		$subjectTitle = "subject_titleen";
		if($lang==1){// khmer
			$subjectTitle = "subject_titlekh";
			
		}
		
		$sql="
		SELECT 
			
			s.*
			,gsjd.subject_id AS subjectId
			,(SELECT CONCAT(sj.$subjectTitle) FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS subjectTitle
			FROM `rms_score` AS s 
				JOIN rms_group_subject_detail AS gsjd ON s.group_id =gsjd.group_id 
				
			WHERE 
			s.id =$scoreId
		";
		if($filter['exam_type']==1){
			$sql.=" AND gsjd.amount_subject > 0 ";
		}else{
			$sql.=" AND gsjd.amount_subject_sem > 0 ";
		}
		return $db->fetchAll($sql);
	}
	 function getScoreBySubject($filter){
		$db = $this->getAdapter();
		
		$scoreId = empty($filter['id'])?0:$filter['id'];
		$subjectId = empty($filter['subjectId'])?0:$filter['subjectId'];
		$sql="SELECT
				sd.`score`,
				sd.score_cut,
				sd.`subject_id`
				,sd.amount_subject
			FROM  `rms_score_detail` AS sd
			WHERE sd.`score_id`=$scoreId AND sd.`subject_id`=$subjectId ";
		$sql.=" AND sd.`student_id`=".$this->getUserId();
		return $db->fetchRow($sql);
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
