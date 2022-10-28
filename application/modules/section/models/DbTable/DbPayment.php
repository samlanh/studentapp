<?php class Section_Model_DbTable_DbPayment extends Zend_Db_Table_Abstract{

	protected $_name = 'rms_score';
    public function getUserId(){
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	return $_dbgb->getUserId();
    }
	function getCountAllPayment($search=null){
    	$_dbGb  = new Application_Model_DbTable_DbGlobal();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db=$this->getAdapter();
		
    	$sql=" SELECT 
    				count(sp.id)
 			   FROM 
    				rms_student AS s,
					rms_student_payment AS sp
				WHERE 
					s.stu_id=sp.student_id 
					";
    	
	    	
			$from_date =(empty($search['startDate']))? '1': " sp.create_date >= '".date("Y-m-d",strtotime($search['startDate']))." 00:00:00'";
	    	$to_date = (empty($search['endDate']))? '1': " sp.create_date <= '".date("Y-m-d",strtotime($search['endDate']))." 23:59:59'";
	    	$where = " AND ".$from_date." AND ".$to_date;
			$where.=" AND sp.status = 1 ";
	    	if(!empty($search['searchBox'])){
	    		$s_where=array();
	    		$s_search=addslashes(trim($search['searchBox']));
	    		$s_search = str_replace(' ', '', addslashes(trim($search['searchBox'])));
	    		$s_where[]= " REPLACE(sp.receipt_number,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(sp.paid_amount,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(sp.balance_due,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(sp.number,' ','') LIKE '%{$s_search}%'";
	    		
	    		$where.=' AND ('.implode(' OR ', $s_where).')';
	    	}
	    	if(!empty($search['academicYear'])){
	    		$where.=" AND sp.academic_year=".$search['academicYear'];
	    	}
			if(!empty($search['paymentMethod'])){
	    		$where.=" AND sp.payment_method=".$search['paymentMethod'];
	    	}
			$where.=" AND sp.student_id = ".$this->getUserId();
	    	$order=" ORDER BY sp.create_date DESC";
			return $db->fetchOne($sql.$where.$order);
    }
	 function getAllPayment($search=null){
    	$_dbGb  = new Application_Model_DbTable_DbGlobal();
    	
    
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db=$this->getAdapter();
    	$lang = $_dbGb->currentlang();
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$branch = "branch_namekh";
    		$grade = "rms_itemsdetail.title";
    		$degree = "rms_items.title";
    	}else{ // English
    		$label = "name_en";
    		$branch = "branch_nameen";
    		$grade = "rms_itemsdetail.title_en";
    		$degree = "rms_items.title_en";
    	}
		
    	$sql=" SELECT 
    				sp.*
    				,(SELECT $branch FROM `rms_branch` WHERE br_id=sp.branch_id LIMIT 1) AS branchName
    				,sp.receipt_number AS receiptNo
					,sp.create_date AS paymentDate
	    			,(CASE WHEN sp.data_from=3 THEN s.serial ELSE s.stu_code END) AS stuCode
	    			,(CASE WHEN s.stu_khname IS NULL OR s.stu_khname='' THEN s.stu_enname ELSE s.stu_khname END) AS stuName
					,(SELECT g.group_code FROM `rms_group` AS g WHERE g.id = sp.group_id LIMIT 1) AS groupCode
	    			,(SELECT CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1),'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=sp.academic_year) AS academicYear
					,(SELECT $label FROM `rms_view` WHERE type=8 AND key_code=sp.payment_method LIMIT 1) AS paymentMethod
					,sp.number AS methodSerialNumber
	 		       ,(SELECT CONCAT(u.last_name,'-',u.first_name) FROM rms_users AS u WHERE u.id = sp.user_id LIMIT 1) AS userName
				   ,(SELECT $label FROM rms_view WHERE TYPE=10 AND key_code = sp.is_void LIMIT 1) AS voidTitle
	 		       
 			   FROM 
    				rms_student AS s,
					rms_student_payment AS sp
				WHERE 
					s.stu_id=sp.student_id 
					";
    	
	    	$from_date =(empty($search['startDate']))? '1': " sp.create_date >= '".date("Y-m-d",strtotime($search['startDate']))." 00:00:00'";
	    	$to_date = (empty($search['endDate']))? '1': " sp.create_date <= '".date("Y-m-d",strtotime($search['endDate']))." 23:59:59'";
	    	$where = " AND ".$from_date." AND ".$to_date;
			$where.=" AND sp.status = 1 ";
	    	if(!empty($search['searchBox'])){
	    		$s_where=array();
	    		$s_search=addslashes(trim($search['searchBox']));
	    		$s_search = str_replace(' ', '', addslashes(trim($search['searchBox'])));
	    		$s_where[]= " REPLACE(sp.receipt_number,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(sp.paid_amount,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(sp.balance_due,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(sp.number,' ','') LIKE '%{$s_search}%'";
	    		
	    		$where.=' AND ('.implode(' OR ', $s_where).')';
	    	}
	    	if(!empty($search['academicYear'])){
	    		$where.=" AND sp.academic_year=".$search['academicYear'];
	    	}
			if(!empty($search['paymentMethod'])){
	    		$where.=" AND sp.payment_method=".$search['paymentMethod'];
	    	}
			$where.=" AND sp.student_id = ".$this->getUserId();
	    	$order=" ORDER BY sp.create_date DESC";
			if(!empty($search['LimitStart'])){
				$order.=" LIMIT ".$search['LimitStart'].",".$search['limitRecord'];
			}else if(!empty($search['limitRecord'])){
	    		$order.=" LIMIT ".$search['limitRecord'];
	    	}
			return $db->fetchAll($sql.$where.$order);
    }
	
	function morePaymentRecord($data){//ajaxloadmore 
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
		
		$row = $this->getAllPayment($filter);
		$string="";
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
		$stickyBlog=$data['stickyValue']; 
		if(!empty($row)){ 
			foreach($row AS $payment){
				
				$yearPMT = date("Y",strtotime($payment['paymentDate'])); 
				$dayPMT = date("d",strtotime($payment['paymentDate'])); 
				$monthPMT = date("M",strtotime($payment['paymentDate'])); 
				$monthKey = date("m",strtotime($payment['paymentDate'])); 

				if($currentlang==1){
					$yearPMT = $_dbGb->getNumberInkhmer($yearPMT);
					$dayPMT = $_dbGb->getNumberInkhmer($dayPMT);
					$monthPMT = $_dbGb->getMonthInkhmer($monthKey);
					
				}
				
				if($stickyBlog!=date("mY",strtotime($payment['paymentDate']))){
					$string.='<div class="stickyBlog">
								<div class="stickyTitle">
									'.$monthPMT."-".$yearPMT.'
								</div>
							</div>';
				}
				$stickyBlog =date("mY",strtotime($payment['paymentDate']));
				$string.='
				<div class="container">
					<div class="ui-grids payment_list-row">
						<div class="row mrg-0">
							<div class="col s7 blg-payment-left">
								<h3 class="title-payment">'.$payment['receiptNo'].'</h3>
								<span class="payment-info"><strong class="mark-title">'.$dayPMT."-".$monthPMT."-".$yearPMT.'</strong></span>
								<span class="payment-info"><strong class="mark-title">'.$payment['academicYear'].'</strong></span>
								<span class="payment-info">	'.$tr->translate("CLASS_NAME").' <strong class="mark-title">'.$payment['groupCode'].'</strong></span>
								<span class="payment-info">	'.$tr->translate("CASHIER").' <strong class="mark-title">'.$payment['userName'].'</strong></span>
							</div>
							<div class="col s5 blg-payment-right">
								<h2 class="amount">$ '.number_format($payment['paid_amount'],2).'</h2>
								<span class="payment-info">'.$tr->translate("PMT_METHOD").' <strong class="mark-title">'.$payment['paymentMethod'].'</strong></span>
								';
								if($payment['payment_method']!=1){
								$string.='
								<span class="payment-info"> <strong class="mark-title">'.$payment['methodSerialNumber'].'</strong></span>
								';
								}
								if($payment['is_void']==1){
								$string.='<span class="payment-info"> <strong class="mark-title">'.$payment['voidTitle'].'</strong></span>';
								}
								$string.='
								<div class="spacer-small"></div>
								<div class="spacer-small"></div>
								<a class="waves-effect waves-light btn btn-rounded  lighten-2" onClick="getPopupContent('.$payment['id'].')" >
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
			'stickyValue'=>$stickyBlog,
			
			);
		return $array;
	}
	
	
	 function getPaymentInfoByID($paymentId){
    	$_dbGb  = new Application_Model_DbTable_DbGlobal();
    
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db=$this->getAdapter();
    	$lang = $_dbGb->currentlang();
		
			$label = "name_en";
			$schooName = "school_nameen";
    		$branch = "branch_nameen";
    		$grade = "rms_itemsdetail.title_en";
    		$degree = "rms_items.title_en";
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$schooName = "school_namekh";
    		$branch = "branch_namekh";
    		$grade = "rms_itemsdetail.title";
    		$degree = "rms_items.title";
    	}
		
    	$sql=" SELECT 
    				sp.*
    				,(SELECT $schooName FROM `rms_branch` WHERE br_id=sp.branch_id LIMIT 1) AS schoolName
    				,(SELECT $branch FROM `rms_branch` WHERE br_id=sp.branch_id LIMIT 1) AS branchName
    				,sp.receipt_number AS receiptNo
					,sp.create_date AS paymentDate
	    			,(CASE WHEN sp.data_from=3 THEN s.serial ELSE s.stu_code END) AS stuCode
	    			,(CASE WHEN s.stu_khname IS NULL OR s.stu_khname='' THEN s.stu_enname ELSE s.stu_khname END) AS stuName
					,(SELECT g.group_code FROM `rms_group` AS g WHERE g.id = sp.group_id LIMIT 1) AS groupCode
	    			,(SELECT CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1),'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=sp.academic_year) AS academicYear
					,(SELECT $label FROM `rms_view` WHERE type=8 AND key_code=sp.payment_method LIMIT 1) AS paymentMethod
					,sp.number AS methodSerialNumber
	 		       ,(SELECT CONCAT(u.last_name,'-',u.first_name) FROM rms_users AS u WHERE u.id = sp.user_id LIMIT 1) AS userName
				   ,(SELECT $label FROM rms_view WHERE TYPE=10 AND key_code = sp.is_void LIMIT 1) AS voidTitle
	 		       
 			   FROM 
    				rms_student AS s,
					rms_student_payment AS sp
				WHERE 
					s.stu_id=sp.student_id 
					";
			$where=" AND sp.status = 1 ";
			$where.=" AND sp.student_id = ".$this->getUserId();
			$where.=" AND sp.id = ".$paymentId;
			
		return $db->fetchRow($sql.$where);
    }
	
	public function getPaymentDetail($id){
    	$db = $this->getAdapter();
    	$_db  = new Application_Model_DbTable_DbGlobal();
    	$lang = $_db->currentlang();
		
			$label = "name_en";
			$branch = "branch_nameen";
			$grade = "rms_itemsdetail.title_en";
			$degree = "rms_items.title_en";
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$branch = "branch_namekh";
    		$grade = "rms_itemsdetail.title";
    		$degree = "rms_items.title";
    	}
    	$sql=" SELECT 
					spd.*
			    	,(SELECT $grade FROM `rms_itemsdetail` WHERE id=spd.itemdetail_id LIMIT 1) AS itemsName
			    	,(SELECT items_type FROM `rms_itemsdetail` WHERE id=spd.itemdetail_id LIMIT 1) AS itemsType
			    	,(SELECT $label FROM `rms_view` WHERE  `type`=6 AND key_code= spd.payment_term LIMIT 1) AS paymentTerm
    			FROM 
			    	rms_student_payment as sp,
			    	rms_student_paymentdetail AS spd ";
    	$sql.='WHERE sp.id=spd.payment_id 
    		AND spd.payment_id = '.$id;
		return $db->fetchAll($sql);    	
    }
	
	
	function detailContent($data){
		$db = $this->getAdapter();
		
		$dbGb   = new Application_Model_DbTable_DbGlobal();
		$currentlang = $dbGb ->currentlang();
		
		$filter = $data;
		$data['id'] = empty($data['id'])?0:$data['id'];
		$paymentID = $data['id'];
		$row = $this->getPaymentDetail($paymentID);
		$rowInfo = $this->getPaymentInfoByID($paymentID);
		
		$string="";
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
		$stringHead="";
		$stringFooter="";
		
		$yearPMT = date("Y",strtotime($rowInfo['paymentDate'])); 
		$dayPMT = date("d",strtotime($rowInfo['paymentDate'])); 
		$monthPMT = date("M",strtotime($rowInfo['paymentDate'])); 
		$monthKey = date("m",strtotime($rowInfo['paymentDate'])); 
		
		if($currentlang==1){
			$yearPMT = $dbGb->getNumberInkhmer($yearPMT);
			$dayPMT = $dbGb->getNumberInkhmer($dayPMT);
			$monthPMT = $dbGb->getMonthInkhmer($monthKey);
			
		}
				
		$stringHead='
			<div class="modal-header ">
				<h5>'.$rowInfo['receiptNo'].' '.$dayPMT."-".$monthPMT."-".$yearPMT.'</h5>
				<span class="modal-info">'.$tr->translate("CLASS_NAME").' '.$rowInfo['groupCode'].' '.$tr->translate("ACADEMIC_YEAR").' '.$rowInfo['academicYear'].'</span>
			</div>
		';
		if(!empty($row)){ 
			$string.='<ul class="collection with-header collection-popup-info">';
			foreach($row AS $key =>  $pmtDetail){
				$num = $key+1; 
				$qty = $pmtDetail['qty']; 
				if($currentlang==1){
					$num = $dbGb->getNumberInkhmer($num);
					$qty = $dbGb->getNumberInkhmer($qty);
				}
				
				
				$string.='
					<li class="collection-item">
						<div class="row mrg-0 ">
							<div class="items-info-left col s9">
								 '.$num.".) ".$pmtDetail['itemsName'].'
								<small class="description-items">'.$qty." ".$pmtDetail['paymentTerm'].'</small>';
						if($pmtDetail['is_onepayment']==0){
							
							$yearStart = date("Y",strtotime($pmtDetail['start_date'])); 
							$dayStart = date("d",strtotime($pmtDetail['start_date'])); 
							$monthStart = date("M",strtotime($pmtDetail['start_date'])); 
							$monthKeyStart= date("m",strtotime($pmtDetail['start_date'])); 
							
							$yearValid = date("Y",strtotime($pmtDetail['validate'])); 
							$dayValid = date("d",strtotime($pmtDetail['validate'])); 
							$monthValid = date("M",strtotime($pmtDetail['validate'])); 
							$monthKeyValid= date("m",strtotime($pmtDetail['validate'])); 
							
							if($currentlang==1){
								$yearStart = $dbGb->getNumberInkhmer($yearStart);
								$dayStart = $dbGb->getNumberInkhmer($dayStart);
								$monthStart = $dbGb->getMonthInkhmer($monthKeyStart);
								
								$yearValid = $dbGb->getNumberInkhmer($yearValid);
								$dayValid = $dbGb->getNumberInkhmer($dayValid);
								$monthValid = $dbGb->getMonthInkhmer($monthKeyValid);
								
							}
		
						$string.='<small class="description-items">'.$tr->translate("Validation")." : ".$dayStart."-".$monthStart."-".$yearStart.' '.$tr->translate("Validation_To").' '.$dayValid."-".$monthValid."-".$yearValid.'</small>';
						}
				$string.='	</div>
							<div class="items-info-right col s3">
								<span class="secondary-content">'.number_format($pmtDetail['paidamount'],2).' $</span>
							</div>
						</div>
					</li>
				';
			
			}
			$string.='</ul>';
			
				
			$stringFooter.='
				<div class="content-footer content-total">
					<div class="row mrg-0 ">
						<div  class="col s6 text-right">
							<span class="modal-info">'.$tr->translate("TOTAL_PAYMENT").'</span>
						</div>
						<div  class="col s6 text-right">
							<span class="modal-info"><strong>'.number_format($rowInfo['grand_total'],2).' $</strong></span>
						</div>
					</div>
					<div class="row mrg-0 ">
						<div  class="col s6 text-right">
							<span class="modal-info">'.$tr->translate("CREDIT_MEMO").'</span>
						</div>
						<div  class="col s6 text-right">
							<span class="modal-info"><strong>'.number_format($rowInfo['credit_memo'],2).' $</strong></span>
						</div>
					</div>
					<div class="row mrg-0 ">
						<div  class="col s6 text-right">
							<span class="modal-info">'.$tr->translate("PAID_AMOUNT").'</span>
						</div>
						<div  class="col s6 text-right">
							<span class="modal-info"><strong>'.number_format($rowInfo['paid_amount'],2).' $</strong></span>
						</div>
					</div>
					<div class="row mrg-0 ">
						<div  class="col s6 text-right">
							<span class="modal-info">'.$tr->translate("BALANCE").'</span>
						</div>
						<div  class="col s6 text-right">
							<span class="modal-info"><strong>'.number_format($rowInfo['balance_due'],2).' $</strong></span>
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
