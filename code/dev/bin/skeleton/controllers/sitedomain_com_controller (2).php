<?php

require_once(APPPATH.'/controllers/bar_controller.php');

class Sitedomain_com_controller extends Bar_controller
{
	const BAR_ID = 0;

	protected
		$js = array(
				'/application/js/jquery.js',
				'/application/bootstrap/js/bootstrap.min.js',
				'/application/js/universal_functions.js',
			),
		$css = array(
				'/application/bootstrap/css/bootstrap.css',
				'/application/css/default.css',
				'/application/css/{DOMAIN_FOLDER}/default.css',
			);
	

	function __construct()
	{
		parent::__construct();

		$this->data['iBarId'] = (int)self::BAR_ID;
		$this->get_menus();
	}
}
