<?php class Section_Model_DbTable_DbAttendance extends Zend_Db_Table_Abstract{

	protected $_name = 'rms_score';
    public function getUserId(){
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	return $_dbgb->getUserId();
    }
	function getGroupOfStudent(){
		$db = $this->getAdapter();
		$sql="SELECT 
				GROUP_CONCAT(gds.group_id) 
			FROM `rms_group_detail_student` AS gds 
				
				
			";
		$sql.="
			WHERE 
				 gds.is_maingrade=1
				AND gds.is_current=1
		";
		$sql.=" AND gds.stu_id=".$this->getUserId();
		return $db->fetchOne($sql);
	}
    function getStudentTotalAttendance($search=array()){
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$currentlang= $dbGb->currentlang();
		
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$label = "name_en";
		$branchName = "branch_nameen";
	   	if($currentlang==1){// khmer
	   		
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
				,satd.`attendence_status`
				,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYear
				,(SELECT `r`.`room_name` FROM `rms_room` `r` WHERE (`r`.`room_id` = `g`.`room_id`)) AS `roomName`
			
				,COUNT(satd.`attendence_status`) AS total
				,sat.`date_attendence`
				,DATE_FORMAT(sat.`date_attendence`,'%Y%m') AS yearMonth
				,satd.description
				,(SELECT COUNT(satd2.id) FROM `rms_student_attendence_detail` AS satd2,`rms_student_attendence` AS sat2  WHERE sat.`group_id` = sat2.`group_id` AND sat2.`type`=1 AND sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$studentId AND satd2.`attendence_status`=2 AND DATE_FORMAT(sat2.`date_attendence`,'%m%Y') = DATE_FORMAT(sat.`date_attendence`,'%m%Y') ) AS countNoPermission
				,(SELECT COUNT(satd2.id) FROM `rms_student_attendence_detail` AS satd2,`rms_student_attendence` AS sat2  WHERE sat.`group_id` = sat2.`group_id` AND sat2.`type`=1 AND sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$studentId AND satd2.`attendence_status`=3 AND DATE_FORMAT(sat2.`date_attendence`,'%m%Y') = DATE_FORMAT(sat.`date_attendence`,'%m%Y') ) AS countPermission
				,(SELECT COUNT(satd2.id) FROM `rms_student_attendence_detail` AS satd2,`rms_student_attendence` AS sat2  WHERE sat.`group_id` = sat2.`group_id` AND sat2.`type`=1 AND sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$studentId AND satd2.`attendence_status`=4 AND DATE_FORMAT(sat2.`date_attendence`,'%m%Y') = DATE_FORMAT(sat.`date_attendence`,'%m%Y') ) AS countLate
				,(SELECT COUNT(satd2.id) FROM `rms_student_attendence_detail` AS satd2,`rms_student_attendence` AS sat2  WHERE sat.`group_id` = sat2.`group_id` AND sat2.`type`=1 AND sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$studentId AND satd2.`attendence_status`=5 AND DATE_FORMAT(sat2.`date_attendence`,'%m%Y') = DATE_FORMAT(sat.`date_attendence`,'%m%Y') ) AS countEalyLeave

		";
		$sql.="
			FROM
				`rms_student_attendence` AS sat 
				 JOIN `rms_group` AS g ON g.id=sat.group_id
				LEFT JOIN`rms_student_attendence_detail` AS satd ON sat.`id`= satd.`attendence_id`
				
		";
		$sql.="
			WHERE
				
				 sat.type=1
				AND sat.`status`=1
		";
		$groupList = $this->getGroupOfStudent();
		$groupList = empty($groupList)?0:$groupList;
		$sql.=" AND sat.group_id IN (".$groupList.")";
		
		if(!empty($search['month'])){
			$sql.=" AND DATE_FORMAT(sat.`date_attendence`,'%m') = ".$search['month'];
		}
		if(!empty($search['academicYear'])){
			$sql.=" AND g.academic_year = ".$search['academicYear'];
		}
		$sql.=" GROUP BY sat.`group_id`
			,DATE_FORMAT(sat.`date_attendence`,'%Y%m') ";
		$sql.=" ORDER BY DATE_FORMAT(sat.`date_attendence`,'%Y%m') DESC ";
		
		
		if(!empty($search['LimitStart'])){
			$sql.=" LIMIT ".$search['LimitStart'].",".$search['limitRecord'];
		}else if(!empty($search['limitRecord'])){
			$sql.=" LIMIT ".$search['limitRecord'];
		}
			
		return $db->fetchAll($sql);
	}
	
	function moreAttendanceRecord($data){//ajaxloadmore 
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
		
		$row = $this->getStudentTotalAttendance($filter);
		$string="";
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
		
		$tAbsent 	=empty($data['tAbsent'])?0:$data['tAbsent'];
		$tPermission=empty($data['tPermission'])?0:$data['tPermission'];
		$tLate 		=empty($data['tLate'])?0:$data['tLate'];
		$tEalyLeave =empty($data['tEalyLeave'])?0:$data['tEalyLeave'];
	
		if(!empty($row)){ 
			foreach($row AS $attedance){
				
				$tAbsent 	=$tAbsent+$attedance['countNoPermission'];
				$tPermission=$tPermission+$attedance['countPermission'];
				$tLate 		=$tLate+$attedance['countLate'];
				$tEalyLeave =$tEalyLeave+$attedance['countEalyLeave'];
				
				$academicYear = $attedance['academicYear']; 
				
				$yearAtt = date("Y",strtotime($attedance['date_attendence'])); 
				$monthAtt = date("M",strtotime($attedance['date_attendence'])); 
				$monthKey = date("m",strtotime($attedance['date_attendence'])); 
				
				$countAbsent 		= sprintf('%02d',$attedance['countNoPermission']);
				$countPermission 	= sprintf('%02d',$attedance['countPermission']);
				$countLate 			= sprintf('%02d',$attedance['countLate']);
				$countEalyLeave 	= sprintf('%02d',$attedance['countEalyLeave']);
							
				if($currentlang==1){
					$academicYear = $_dbGb->getNumberInkhmer($academicYear);
					
					$yearAtt = $_dbGb->getNumberInkhmer($yearAtt);
					$monthAtt = $_dbGb->getMonthInkhmer($monthKey);
					
					$countAbsent 	= $_dbGb->getNumberInkhmer($countAbsent);
					$countPermission = $_dbGb->getNumberInkhmer($countPermission);
					$countLate 		= $_dbGb->getNumberInkhmer($countLate);
					$countEalyLeave = $_dbGb->getNumberInkhmer($countEalyLeave);
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
									<span class="row-items-info"><strong class="mark-title">'.$academicYear.'</strong></span>
									<span class="row-items-info">'.$tr->translate("CLASS_NAME").' <strong class="mark-title">'.$attedance['groupCode'].'</strong></span>
									<span class="row-items-info">'.$tr->translate("ROOM").' <strong class="mark-title">'.$attedance['roomName'].'</strong></span>
									<a class="waves-effect waves-light btn btn-rounded  lighten-2" onClick="getPopupContent('. $attedance['yearMonth'].','.$attedance['group_id'].')" >
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
									<span class="row-items-info counting text-right "><strong class="mark-title">'.$countAbsent." ".$tr->translate("TIME_UNIT").'</strong></span>
									<span class="row-items-info counting text-right "><strong class="mark-title">'.$countPermission." ".$tr->translate("TIME_UNIT").'</strong></span>
									<span class="row-items-info counting text-right "><strong class="mark-title">'.$countLate." ".$tr->translate("TIME_UNIT").'</strong></span>
									<span class="row-items-info counting text-right "><strong class="mark-title">'.$countEalyLeave." ".$tr->translate("TIME_UNIT").'</strong></span>
								
								</div>
							</div>
						</div>
					</div>
				
				';
			
			}
		}
		
		$tAbsentLB 		= sprintf('%02d',$tAbsent);
		$tPermissionLB 	= sprintf('%02d',$tPermission);
		$tLateLB 			= sprintf('%02d',$tLate);
		$tEalyLeaveLB 	= sprintf('%02d',$tEalyLeave);
		if($currentlang==1){
			$tAbsentLB 	= $_dbGb->getNumberInkhmer($tAbsentLB);
			$tPermissionLB = $_dbGb->getNumberInkhmer($tPermissionLB);
			$tLateLB 		= $_dbGb->getNumberInkhmer($tLateLB);
			$tEalyLeaveLB = $_dbGb->getNumberInkhmer($tEalyLeaveLB);
		}
		$totalPageHtml='
			<div class="container">
				<div class="row mrg-0 ">
					<div class="col s3 text-center">
						<span class="page-footer-info">'.$tr->translate("NO_PERMISSION_SHORT_CUT").'</span>
						<span class="page-footer-info"><strong>'.$tAbsentLB.'</strong></span>
					</div>
					<div class="col s3 text-center">
						<span class="page-footer-info">'.$tr->translate("PERMISSION_SHORT_CUT").'</span>
						<span class="page-footer-info"><strong>'.$tPermissionLB.'</strong></span>
					</div>
					<div class="col s3 text-center">
						<span class="page-footer-info">'.$tr->translate("LATE_SHORT_CUT").'</span>
						<span class="page-footer-info"><strong>'.$tLateLB.'</strong></span>
					</div>
					<div class="col s3 text-center">
						<span class="page-footer-info">'.$tr->translate("EARLY_LEAVE_SHORT_CUT").'</span>
						<span class="page-footer-info"><strong>'.$tEalyLeaveLB.'</strong></span>
					</div>
				</div>
			</div>
		';

		$array = array(
			'countitem'=>count($row),
			'htmlRecord'=>$string,
			'trackPage'=>$totalLimitStart,
			'totalPageHtml'=>$totalPageHtml,
			
			);
		return $array;
	}
	
	
	function getStudentTotalAttendanceInfo($search){
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
				,(SELECT COUNT(satd2.id) FROM `rms_student_attendence_detail` AS satd2,`rms_student_attendence` AS sat2  WHERE sat2.`type`=1 AND sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$studentId AND satd2.`attendence_status`=2 AND DATE_FORMAT(sat2.`date_attendence`,'%m%Y') = DATE_FORMAT(sat.`date_attendence`,'%m%Y') ) AS countNoPermission
				,(SELECT COUNT(satd2.id) FROM `rms_student_attendence_detail` AS satd2,`rms_student_attendence` AS sat2  WHERE sat2.`type`=1 AND sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$studentId AND satd2.`attendence_status`=3 AND DATE_FORMAT(sat2.`date_attendence`,'%m%Y') = DATE_FORMAT(sat.`date_attendence`,'%m%Y') ) AS countPermission
				,(SELECT COUNT(satd2.id) FROM `rms_student_attendence_detail` AS satd2,`rms_student_attendence` AS sat2  WHERE sat2.`type`=1 AND sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$studentId AND satd2.`attendence_status`=4 AND DATE_FORMAT(sat2.`date_attendence`,'%m%Y') = DATE_FORMAT(sat.`date_attendence`,'%m%Y') ) AS countLate
				,(SELECT COUNT(satd2.id) FROM `rms_student_attendence_detail` AS satd2,`rms_student_attendence` AS sat2  WHERE sat2.`type`=1 AND sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$studentId AND satd2.`attendence_status`=5 AND DATE_FORMAT(sat2.`date_attendence`,'%m%Y') = DATE_FORMAT(sat.`date_attendence`,'%m%Y') ) AS countEalyLeave

		";
		$sql.="
			FROM
				`rms_student_attendence` AS sat 
				 JOIN `rms_group` AS g ON g.id=sat.group_id
				LEFT JOIN`rms_student_attendence_detail` AS satd ON sat.`id`= satd.`attendence_id`
				
		";
		$sql.="
			WHERE
				
				 sat.type=1
				AND sat.`status`=1
		";
		$yearMonth = empty($search['id'])?'':$search['id'];
		$groupID = empty($search['group'])?0:$search['group'];
		
		$sql.=" AND sat.group_id IN (".$groupID.")";
		$sql.=" AND satd.stu_id = $studentId ";
		if(!empty($yearMonth)){
			$sql.=" AND DATE_FORMAT(sat.`date_attendence`,'%Y%m') = ".$yearMonth;
		}
		
		$sql.=" GROUP BY sat.`group_id`
			,DATE_FORMAT(sat.`date_attendence`,'%Y%m') ";
		$sql.=" ORDER BY DATE_FORMAT(sat.`date_attendence`,'%Y%m') DESC ";
		$sql.=" LIMIT 1 ";
		
			
		return $db->fetchRow($sql);
	}
	
	function getStudentAttendanceDetail($search){
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
			,satd.`type`
			,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYear
			,CASE
					WHEN satd.`attendence_status` = 2 THEN '".$tr->translate("NO_PERMISSION")."'
					WHEN satd.`attendence_status` = 3 THEN '".$tr->translate("PERMISSION")."'
					WHEN satd.`attendence_status` = 4 THEN '".$tr->translate("LATE")."'
					WHEN satd.`attendence_status` = 5 THEN '".$tr->translate("EARLY_LEAVE")."'
				
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
				
				 sat.type=1
				AND sat.`status`=1 
		";
		$yearMonth = empty($search['id'])?'':$search['id'];
		$groupID = empty($search['group'])?0:$search['group'];
		
		$sql.=" AND sat.group_id IN (".$groupID.")";
		$sql.=" AND satd.stu_id = $studentId ";
		
		if(!empty($yearMonth)){
			$sql.=" AND DATE_FORMAT(sat.`date_attendence`,'%Y%m') = ".$yearMonth;
		}
		
		$sql.=" GROUP BY sat.`group_id`
				,sat.`date_attendence`
			ORDER BY sat.`date_attendence` DESC";
						
		return $db->fetchAll($sql);
	}
	
	function detailContent($data){
		$db = $this->getAdapter();
		
		$dbGb   = new Application_Model_DbTable_DbGlobal();
		$currentlang = $dbGb ->currentlang();
		
		$filter = $data;
		
		$row = $this->getStudentAttendanceDetail($filter);
		$string="";
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
		$stringHead="";
		$stringFooter="";
		
		
		$countAbsent =	0; 		//2
		$countPermission = 	0;	//3
		$countLate = 	0;		//4
		$countEalyLeave = 	0;	//5
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
				
				if($attedance['attendence_status']==2){
					$countAbsent = $countAbsent+1;
				}else if($attedance['attendence_status']==3){
					$countPermission = $countPermission+1;
				}else if($attedance['attendence_status']==4){
					$countLate = $countLate+1;
				}else if($attedance['attendence_status']==5){
					$countEalyLeave = $countEalyLeave+1;
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
			
			$countAbsent = sprintf('%02d',$countAbsent);
			$countPermission = sprintf('%02d',$countPermission);
			$countLate = sprintf('%02d',$countLate);
			$countEalyLeave = sprintf('%02d',$countEalyLeave);
			
			if($currentlang==1){
				$countAbsent 		= $dbGb->getNumberInkhmer($countAbsent);
				$countPermission 	= $dbGb->getNumberInkhmer($countPermission);
				$countLate 			= $dbGb->getNumberInkhmer($countLate);
				$countEalyLeave 	= $dbGb->getNumberInkhmer($countEalyLeave);
			}
				
			$stringFooter.='
				<div class="content-footer content-total">
					<div class="row mrg-0 ">
						<div  class="col s3 text-center">
							<span class="modal-info">'.$tr->translate("NO_PERMISSION_SHORT_CUT").'</span>
							<span class="modal-info"><strong>'.$countAbsent.'</strong></span>
						</div>
						<div  class="col s3 text-center">
							<span class="modal-info">'.$tr->translate("PERMISSION_SHORT_CUT").'</span>
							<span class="modal-info"><strong>'.$countPermission.'</strong></span>
						</div>
						<div  class="col s3 text-center">
							<span class="modal-info">'.$tr->translate("LATE_SHORT_CUT").'</span>
							<span class="modal-info"><strong>'.$countLate.'</strong></span>
						</div>
						<div  class="col s3 text-center">
							<span class="modal-info">'.$tr->translate("EARLY_LEAVE_SHORT_CUT").'</span>
							<span class="modal-info"><strong>'.$countEalyLeave.'</strong></span>
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
