<?php

class Section_CalendarController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/home';
    public function init()
    {    	
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	
	}

    public function indexAction()
    {
    	$this->_helper->layout()->disableLayout();
		
    }
	
	

   

}







