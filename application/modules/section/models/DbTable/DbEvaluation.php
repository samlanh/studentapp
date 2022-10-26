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
	
	
	function getStudentTotalEvaluationInfo($search){
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$currentlang= $dbGb->currentlang();
		
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$label = "name_en";
		$branchName = "branch_nameen";
		$teacherName= "teacher_name_en";
	   	if($currentlang==1){// khmer
	   		$teacherName= "teacher_name_kh";
	   		$branchName = "branch_namekh";
	   		$label = "name_kh";
	   		
	   	}
		$studentId = $this->getUserId();
		$sql="
			SELECT
				sat.`group_id`
				,(SELECT b.".$branchName." FROM rms_branch as b WHERE b.br_id=		sat.branch_id LIMIT 1) AS branchName
				,(SELECT br.branch_namekh FROM `rms_branch` AS br  WHERE br.br_id = sat.branch_id LIMIT 1) AS branchNameKh
				,(SELECT br.branch_nameen FROM `rms_branch` AS br  WHERE br.br_id = sat.branch_id LIMIT 1) AS branchNameEn
				,g.group_code AS groupCode
				,(SELECT t.$teacherName FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherName
				,satd.`attendence_status`
				,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYear
				,(SELECT `r`.`room_name` FROM `rms_room` `r` WHERE (`r`.`room_id` = `g`.`room_id`)) AS `roomName`
			
				,COUNT(satd.`attendence_status`) AS total
				,sat.`date_attendence`
				,DATE_FORMAT(sat.`date_attendence`,'%Y%m') AS yearMonth
				,satd.description
				,(SELECT COUNT(satd2.id) FROM `rms_student_attendence_detail` AS satd2,`rms_student_attendence` AS sat2  WHERE sat2.`type`=2 AND sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$studentId AND satd2.`attendence_status`=1 AND DATE_FORMAT(sat2.`date_attendence`,'%m%Y') = DATE_FORMAT(sat.`date_attendence`,'%m%Y') ) AS countSmallMistake
				,(SELECT COUNT(satd2.id) FROM `rms_student_attendence_detail` AS satd2,`rms_student_attendence` AS sat2  WHERE sat2.`type`=2 AND sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$studentId AND satd2.`attendence_status`=2 AND DATE_FORMAT(sat2.`date_attendence`,'%m%Y') = DATE_FORMAT(sat.`date_attendence`,'%m%Y') ) AS countMediumMistake
				,(SELECT COUNT(satd2.id) FROM `rms_student_attendence_detail` AS satd2,`rms_student_attendence` AS sat2  WHERE sat2.`type`=2 AND sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$studentId AND satd2.`attendence_status`=3 AND DATE_FORMAT(sat2.`date_attendence`,'%m%Y') = DATE_FORMAT(sat.`date_attendence`,'%m%Y') ) AS countBigMistake
				,(SELECT COUNT(satd2.id) FROM `rms_student_attendence_detail` AS satd2,`rms_student_attendence` AS sat2  WHERE sat2.`type`=2 AND sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$studentId AND satd2.`attendence_status`=4 AND DATE_FORMAT(sat2.`date_attendence`,'%m%Y') = DATE_FORMAT(sat.`date_attendence`,'%m%Y') ) AS countOtherMistake

		";
		$sql.="
			FROM
				`rms_student_attendence` AS sat 
				 JOIN `rms_group` AS g ON g.id=sat.group_id
				LEFT JOIN`rms_student_attendence_detail` AS satd ON sat.`id`= satd.`attendence_id`
				
		";
		$sql.="
			WHERE
				
				 sat.type=2
				AND sat.`status`=1
		";
		$yearMonth = empty($search['id'])?'':$search['id'];
		$groupID = empty($search['group'])?0:$search['group'];
		
		$sql.=" AND sat.group_id IN (".$groupID.")";
		
		if(!empty($yearMonth)){
			$sql.=" AND DATE_FORMAT(sat.`date_attendence`,'%Y%m') = ".$yearMonth;
		}
		
		$sql.=" GROUP BY sat.`group_id`
			,DATE_FORMAT(sat.`date_attendence`,'%Y%m') ";
		$sql.=" ORDER BY DATE_FORMAT(sat.`date_attendence`,'%Y%m') DESC ";
		$sql.=" LIMIT 1 ";
		
			
		return $db->fetchRow($sql);
	}
	
	function getStudentEvaluationDetail($search){
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$currentlang= $dbGb->currentlang();
		
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$label = "name_en";
		$branchName = "branch_nameen";
		$teacherName= "teacher_name_en";
	   	if($currentlang==1){// khmer
	   		$teacherName= "teacher_name_kh";
	   		$branchName = "branch_namekh";
	   		$label = "name_kh";
	   		
	   	}
		$studentId = $this->getUserId();
		$sql="
			SELECT
			sat.`group_id`
			,g.group_code AS groupCode
			,satd.`attendence_status`
			,CASE
					WHEN satd.`attendence_status` = 1 THEN '".$tr->translate("SMALL_MISTACK")."'
					WHEN satd.`attendence_status` = 2 THEN '".$tr->translate("MEDIUM_MISTACK")."'
					WHEN satd.`attendence_status` = 3 THEN '".$tr->translate("BIG_MISTACK")."'
					WHEN satd.`attendence_status` = 4 THEN '".$tr->translate("OTHER")."'
				
			END AS attendenceStatusTitle
			,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYear
			,(SELECT `r`.`room_name` FROM `rms_room` `r` WHERE (`r`.`room_id` = `g`.`room_id`)) AS `roomName`
			,sat.`date_attendence`
			,DATE_FORMAT(sat.`date_attendence`,'%Y%m%d') AS yearMonth
			,satd.description
		";
		$sql.="
			FROM
			`rms_student_attendence` AS sat 
			 JOIN `rms_group` AS g ON g.id=sat.group_id
			LEFT JOIN`rms_student_attendence_detail` AS satd ON sat.`id`= satd.`attendence_id`
				
		";
		$sql.="
			WHERE
				
				 sat.type=2
				AND sat.`status`=1 
		";
		$yearMonth = empty($search['id'])?'':$search['id'];
		$groupID = empty($search['group'])?0:$search['group'];
		
		$sql.=" AND sat.group_id IN (".$groupID.")";
		
		if(!empty($yearMonth)){
			$sql.=" AND DATE_FORMAT(sat.`date_attendence`,'%Y%m') = ".$yearMonth;
		}
		
		$sql.=" GROUP BY sat.`group_id`
				,sat.`date_attendence`
			ORDER BY sat.`date_attendence` DESC";
						
		return $db->fetchAll($sql);
	}
}
