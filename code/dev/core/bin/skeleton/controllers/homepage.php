<?php

class Homepage extends Sitedomain_com_controller 
{
	function index()
	{
		// Add some data
		$this->data['sTitle'] = '';

		// Bind the data
		$this->load->vars($this->data);

		// start include
		$this->load->view('generics/start');

		// load the view - don't forget to create views/<site>/ first
		$this->load->view(DOMAIN_FOLDER.'/homepage_v');

		// end include
		$this->load->view('generics/end');
	
	}

	function _get_custom_content($sType, $iBarId = null)
	{
	}
}

