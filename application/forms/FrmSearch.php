<?php 
Class Application_Form_FrmSearch extends Zend_Dojo_Form {
	protected $tr;
	protected $tvalidate ;//text validate
	protected $filter;
	protected $text;
	protected $date;
	protected $tarea=null;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
	}
	public function FrmSearch($_data=null){
	
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$searchBox = new Zend_Form_Element_Text('searchBox');
		$searchBox->setAttribs(
				array(
					'class'=>'validate',
				)
		);
		$searchBox->setDecorators(array(array('ViewHelper'),));//remove zend html tage
		$searchBox->setValue($request->getParam("searchBox"));
		
		$startDate = new Zend_Form_Element_Text('startDate');
		$startDate->setAttribs(
				array(
					'class'=>'datepicker datepicker7',
					
				)
			);
		$startDate->setDecorators(array(array('ViewHelper'),));//remove zend html tage
		$_date = $request->getParam("startDate");

		if(!empty($_date)){
			//$_date = date('Y-m-01');
			$startDate->setValue(date("d-m-Y",strtotime($_date)));
		}
		
		
		$endDate = new Zend_Form_Element_Text('endDate');
		$endDate->setAttribs(array(
				'class'=>'datepicker datepicker7',
				'required'=>'true',
				'placeholder'=>$this->tr->translate("END_DATE"),
			));
		$endDate->setDecorators(array(array('ViewHelper'),));//remove zend html tage
		$_date = $request->getParam("endDate");
		
		if(empty($_date)){
			$_date = date("d-m-Y");
		}
		$endDate->setValue(date("d-m-Y",strtotime($_date)));
		
		$_arrOptPaymentMethod = array(""=>"",);//$this->tr->translate("CHOOSE_PAYMENT_METHOD")
		$rs = $_dbgb->getAllViewByType(8);
		if(!empty($rs))foreach ($rs As $rows)$_arrOptPaymentMethod[$rows['id']]=$rows['name'];
		$paymentMethod = new Zend_Form_Element_Select("paymentMethod");
		$paymentMethod->setMultiOptions($_arrOptPaymentMethod);
		$paymentMethod->setAttribs(
			array(
				'class'=>' browser-default',
				'placeholder'=>$this->tr->translate("CHOOSE_PAYMENT_METHOD"),
				)
			);
		$paymentMethod->setDecorators(array(array('ViewHelper'),));//remove zend html tage		
		$paymentMethod->setValue($request->getParam("paymentMethod"));
	
		$_arrOptMonth = array(""=>"",);
		$rs = $_dbgb->getAllMonth();
		if(!empty($rs))foreach ($rs As $rows)$_arrOptMonth[$rows['id']]=$rows['name'];
		$month = new Zend_Form_Element_Select("month");
		$month->setMultiOptions($_arrOptMonth);
		$month->setAttribs(
			array(
				'class'=>' browser-default',
				'placeholder'=>$this->tr->translate("CHOOSE_MONTH"),
				)
			);
		$month->setDecorators(array(array('ViewHelper'),));//remove zend html tage		
		$month->setValue($request->getParam("month"));

		$_arrOptAca = array(""=>"",);//$this->tr->translate("CHOOSE_ACADMIC_YEAR")
		$rs = $_dbgb->getAcademicYear();
		if(!empty($rs))foreach ($rs As $rows)$_arrOptAca[$rows['id']]=$rows['name'];
		$academicYear = new Zend_Form_Element_Select("academicYear");
		$academicYear->setMultiOptions($_arrOptAca);
		$academicYear->setAttribs(
			array(
				'class'=>' browser-default',
				'placeholder'=>$this->tr->translate("CHOOSE_ACADMIC_YEAR"),
				)
			);
		$academicYear->setDecorators(array(array('ViewHelper'),));//remove zend html tage		
		$academicYear->setValue($request->getParam("academicYear"));
		
		$_arrOptDegree = array(""=>"",);//$this->tr->translate("CHOOSE_DEGREE")
		$rs = $_dbgb->getAllDegree();
		if(!empty($rs))foreach ($rs As $rows)$_arrOptDegree[$rows['id']]=$rows['name'];
		$degree = new Zend_Form_Element_Select("degree");
		$degree->setMultiOptions($_arrOptDegree);
		$degree->setAttribs(
			array(
				'class'=>' browser-default',
				'placeholder'=>$this->tr->translate("CHOOSE_DEGREE"),
				)
			);
		$degree->setDecorators(array(array('ViewHelper'),));//remove zend html tage		
		$degree->setValue($request->getParam("degree"));
		
		
		$_arrOptExamType = array(""=>"",);//$this->tr->translate("CHOOSE_EXAM_TYPE")
		$rs = $_dbgb->getAllViewByType(19);
		if(!empty($rs))foreach ($rs As $rows)$_arrOptExamType[$rows['id']]=$rows['name'];
		$examType = new Zend_Form_Element_Select("examType");
		$examType->setMultiOptions($_arrOptExamType);
		$examType->setAttribs(
			array(
				'class'=>' browser-default',
				'placeholder'=>$this->tr->translate("CHOOSE_EXAM_TYPE"),
				'onChange'=>'checkingExam();',
				)
			);
		$examType->setDecorators(array(array('ViewHelper'),));//remove zend html tage		
		$examType->setValue($request->getParam("examType"));
		
		$_arrOptExamType = array(
				""=>""
				,1=>$this->tr->translate("SEMESTER1")
				,2=>$this->tr->translate("SEMESTER2")
		);//$this->tr->translate("CHOOSE_SEMESTER")
		
		$forSemester = new Zend_Form_Element_Select("forSemester");
		$forSemester->setMultiOptions($_arrOptExamType);
		$forSemester->setAttribs(
			array(
				'class'=>' browser-default',
				'placeholder'=>$this->tr->translate("CHOOSE_SEMESTER"),
				)
			);
		$forSemester->setDecorators(array(array('ViewHelper'),));//remove zend html tage		
		$forSemester->setValue($request->getParam("forSemester"));
		
		if(!empty($_data)){
			
		}
		
		
		$this->addElements(
			array(
				$searchBox
				,$startDate
				,$endDate
				,$month 
				,$academicYear 
				,$paymentMethod 
				,$examType
				,$degree 
				,$forSemester 
				)
			);
		return $this;
	}
	
}