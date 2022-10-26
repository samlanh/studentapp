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
		$colunmname='title_en';
		$label = 'name_en';
		$teacherName = "teacher_name_en";
		$branch = "branch_nameen";
		$month = "month_en";
		$subjectTitle='subject_titleen';
		if ($currentLang==1){
			$teacherName='teacher_name_kh';
			$colunmname='title';
			$label = 'name_kh';
			$branch = "branch_namekh";
			$month = "month_kh";
			$subjectTitle='subject_titlekh';
		}
		$sql="SELECT 
				grd.*
				,(SELECT br.$branch FROM `rms_branch` AS br WHERE br.br_id=grd.branchId LIMIT 1) As branchName
				,(SELECT br.branch_namekh FROM `rms_branch` AS br  WHERE br.br_id = grd.branchId LIMIT 1) AS branchNameKh
				,(SELECT br.branch_nameen FROM `rms_branch` AS br  WHERE br.br_id = grd.branchId LIMIT 1) AS branchNameEn
				,(SELECT $label FROM `rms_view` WHERE TYPE=19 AND key_code =grd.forType LIMIT 1) as examTypeTitle
				,CASE
					WHEN grd.forType = 2 THEN grd.forSemester
					ELSE (SELECT $month FROM `rms_month` WHERE id=grd.forMonth  LIMIT 1) 
				END AS forMonthTitle
				,g.group_code AS  groupCode
				,(SELECT t.$teacherName FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherName
				,(SELECT t.teacher_name_kh FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherNameKh
				,(SELECT t.teacher_name_en FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teaccherNameEng
				,(SELECT t.signature FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherSigature
				,(SELECT t.tel FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherTel
				,(SELECT CONCAT(acad.fromYear,'-',acad.toYear) FROM rms_academicyear AS acad WHERE acad.id=g.academic_year LIMIT 1) AS academicYear
				,(SELECT rms_items.$colunmname FROM `rms_items` WHERE rms_items.`id`=`g`.`degree` AND rms_items.type=1 LIMIT 1) AS degreeTitle
				,(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=`g`.`grade` AND rms_itemsdetail.items_type=1 LIMIT 1) AS gradeTitle
				,(SELECT $label FROM rms_view WHERE `type`=4 AND rms_view.key_code= `g`.`session` LIMIT 1) AS sessionTitle
				,(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS roomName
		";
		
		$sql.=" FROM rms_studentassessment AS grd 
					JOIN rms_group AS g ON grd.groupId=g.id 
					LEFT JOIN rms_studentassessment_detail AS assDe ON grd.id=assDe.assessmentId 
			WHERE   grd.status=1 ";
		
		$where ='';
		$where.=" AND assDe.studentId = ".$this->getUserId();

		if(!empty($search['academicYear'])){
			$where.=" AND g.academic_year =".$search['academicYear'];
		}
		if(!empty($search['examType'])){
			$where.=" AND grd.forType=".$search['examType'];
			if($search['examType']==1){
				if(!empty($search['month'])){
					$where.=" AND grd.forMonth=".$search['month'];
				}	
			}else{
				if(!empty($search['forSemester'])){
					$where.=" AND grd.forSemester=".$search['forSemester'];
				}
			}
		}
		
		$order=" GROUP BY grd.id  ORDER BY grd.id DESC ";
		if(!empty($search['LimitStart'])){
			$order.=" LIMIT ".$search['LimitStart'].",".$search['limitRecord'];
		}else if(!empty($search['limitRecord'])){
			$order.=" LIMIT ".$search['limitRecord'];
		}
		return $db->fetchAll($sql.$where.$order);
	}
	
	function moreEvaluationRecord($data){//ajaxloadmore 
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
		
		$row = $this->getStudentTotalEvaluation($filter);
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
				if($currentlang==1){
					$forMonthTitle = $dbGb->getNumberInkhmer($forMonthTitle);
					
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
									<span class="row-items-info">'.$tr->translate("CLASS_NAME").' <strong class="mark-title">'.$attedance['groupCode'].'</strong></span>
									<span class="row-items-info">'.$tr->translate("ACADEMIC_YEAR").' <strong class="mark-title">'.$attedance['academicYear'].'</strong></span>
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
		$colunmname='title_en';
		$label = 'name_en';
		$teacherName = "teacher_name_en";
		$branch = "branch_nameen";
		$month = "month_en";
		$subjectTitle='subject_titleen';
		if ($currentLang==1){
			$teacherName='teacher_name_kh';
			$colunmname='title';
			$label = 'name_kh';
			$branch = "branch_namekh";
			$month = "month_kh";
			$subjectTitle='subject_titlekh';
		}
		$sql="SELECT 
				grd.*
				,(SELECT br.$branch FROM `rms_branch` AS br WHERE br.br_id=grd.branchId LIMIT 1) As branchName
				,(SELECT br.branch_namekh FROM `rms_branch` AS br  WHERE br.br_id = grd.branchId LIMIT 1) AS branchNameKh
				,(SELECT br.branch_nameen FROM `rms_branch` AS br  WHERE br.br_id = grd.branchId LIMIT 1) AS branchNameEn
				,(SELECT $label FROM `rms_view` WHERE TYPE=19 AND key_code =grd.forType LIMIT 1) as examTypeTitle
				,CASE
					WHEN grd.forType = 2 THEN grd.forSemester
					ELSE (SELECT $month FROM `rms_month` WHERE id=grd.forMonth  LIMIT 1) 
				END AS forMonthTitle
				,g.group_code AS  groupCode
				,(SELECT t.$teacherName FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherName
				,(SELECT t.teacher_name_kh FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherNameKh
				,(SELECT t.teacher_name_en FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teaccherNameEng
				,(SELECT t.signature FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherSigature
				,(SELECT t.tel FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherTel
				,(SELECT CONCAT(acad.fromYear,'-',acad.toYear) FROM rms_academicyear AS acad WHERE acad.id=g.academic_year LIMIT 1) AS academicYear
				,(SELECT rms_items.$colunmname FROM `rms_items` WHERE rms_items.`id`=`g`.`degree` AND rms_items.type=1 LIMIT 1) AS degreeTitle
				,(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=`g`.`grade` AND rms_itemsdetail.items_type=1 LIMIT 1) AS gradeTitle
				,(SELECT $label FROM rms_view WHERE `type`=4 AND rms_view.key_code= `g`.`session` LIMIT 1) AS sessionTitle
				,(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS roomName
				,(SELECT rt.rating FROM rms_rating AS rt WHERE rt.id=assDe.ratingId LIMIT 1) AS ratingTitle
				,(SELECT c.comment FROM rms_comment AS c WHERE c.id = assDe.commentId LIMIT 1) AS commentTitle
		";
		
		$sql.=" FROM rms_studentassessment AS grd 
					JOIN rms_group AS g ON grd.groupId=g.id 
					LEFT JOIN rms_studentassessment_detail AS assDe ON grd.id=assDe.assessmentId 
			WHERE   grd.status=1 ";
		
		$sql.=" AND assDe.studentId = ".$this->getUserId();
		if(!empty($search['evaluationId'])){
			$sql.=" AND grd.id =".$search['evaluationId'];
		}
		return $db->fetchAll($sql);
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
				if($currentlang==1){
					$forMonthTitle = $dbGb->getNumberInkhmer($forMonthTitle);
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
							<h5>'.$commentItems['examTypeTitle'].' '.$forMonthTitle.'</h5>
							<span class="modal-info">'.$tr->translate("CLASS_NAME").' <strong class="mark-title">'.$commentItems['groupCode'].'</strong> '.$tr->translate("ACADEMIC_YEAR").' <strong class="mark-title">'.$commentItems['academicYear'].'</strong></span>
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
