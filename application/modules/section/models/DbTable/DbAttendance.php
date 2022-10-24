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
				,(SELECT COUNT(satd2.id) FROM `rms_student_attendence_detail` AS satd2,`rms_student_attendence` AS sat2  WHERE sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$studentId AND satd2.`attendence_status`=2 AND DATE_FORMAT(sat2.`date_attendence`,'%m%Y') = DATE_FORMAT(sat.`date_attendence`,'%m%Y') ) AS countNoPermission
				,(SELECT COUNT(satd2.id) FROM `rms_student_attendence_detail` AS satd2,`rms_student_attendence` AS sat2  WHERE sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$studentId AND satd2.`attendence_status`=3 AND DATE_FORMAT(sat2.`date_attendence`,'%m%Y') = DATE_FORMAT(sat.`date_attendence`,'%m%Y') ) AS countPermission
				,(SELECT COUNT(satd2.id) FROM `rms_student_attendence_detail` AS satd2,`rms_student_attendence` AS sat2  WHERE sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$studentId AND satd2.`attendence_status`=4 AND DATE_FORMAT(sat2.`date_attendence`,'%m%Y') = DATE_FORMAT(sat.`date_attendence`,'%m%Y') ) AS countLate
				,(SELECT COUNT(satd2.id) FROM `rms_student_attendence_detail` AS satd2,`rms_student_attendence` AS sat2  WHERE sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$studentId AND satd2.`attendence_status`=5 AND DATE_FORMAT(sat2.`date_attendence`,'%m%Y') = DATE_FORMAT(sat.`date_attendence`,'%m%Y') ) AS countEalyLeave

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
					<div class="row attedance-row mrg-0">
						<div class="col s3 blg-attedance-left">
							<span class="attedance-monthly">'.$monthAtt.'
							<span class="attedance-year">'.$yearAtt.'</span>
							</span>
							
						</div>
						<div class="col s9 blg-attedance-right">
							<div class="row mrg-0 info-blg">
								<div class="col s6">
									<span class="attedance-info">'.$tr->translate("CLASS_NAME").' <strong class="mark-title">'.$attedance['groupCode'].'</strong></span>
									<span class="attedance-info">'.$tr->translate("ACADEMIC_YEAR").' <strong class="mark-title">'.$attedance['academicYear'].'</strong></span>
									<span class="attedance-info">'.$tr->translate("ROOM").' <strong class="mark-title">'.$attedance['roomName'].'</strong></span>
									<a class="waves-effect waves-light btn btn-rounded  lighten-2" href="'.$baseurl.'/section/attendance/detail/id/'.$attedance['yearMonth'].'">
										'.$tr->translate("MORE_DETAIL").'
									</a>
								</div>
								<div class="col s3">
									<span class="attedance-info text-right">'.$tr->translate("NO_PERMISSION_SHORT_CUT").'</span>
									<span class="attedance-info text-right">'.$tr->translate("PERMISSION_SHORT_CUT").'</span>
									<span class="attedance-info text-right">'.$tr->translate("LATE_SHORT_CUT").'</span>
									<span class="attedance-info text-right">'.$tr->translate("EARLY_LEAVE_SHORT_CUT").'</span>
								</div>
								<div class="col s3">
									<span class="attedance-info counting text-right "><strong class="mark-title">'.sprintf('%02d',$attedance['countNoPermission'])." ".$tr->translate("TIME_UNIT").'</strong></span>
									<span class="attedance-info counting text-right "><strong class="mark-title">'.sprintf('%02d',$attedance['countPermission'])." ".$tr->translate("TIME_UNIT").'</strong></span>
									<span class="attedance-info counting text-right "><strong class="mark-title">'.sprintf('%02d',$attedance['countLate'])." ".$tr->translate("TIME_UNIT").'</strong></span>
									<span class="attedance-info counting text-right "><strong class="mark-title">'.sprintf('%02d',$attedance['countEalyLeave'])." ".$tr->translate("TIME_UNIT").'</strong></span>
								
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
}
