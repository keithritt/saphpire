<?

class Master
{
  public static $aData = array();

  public static $aJs = array();
  public static $aCss = array();
  public static $sFooterJs = '';

  public static $iDomainUserId;

  public static $aRegions = array(); // different areas that js and css can be inserted

  public static function launch($sMethod, $aArgs)
  {
    //hit();
    //expose($_SESSION);
    //pr('Master::launch('.$sMethod.')');
    //expose($aArgs);
    $oController = new Controller();
    //line();
    $aArgs = array_values($aArgs);
    //line();
    //expose($aArgs);
    switch(count($aArgs))
    {
      case 0:
        //line();
        $oController->$sMethod();
        break;
      case 1:
      //line();
        $oController->$sMethod($aArgs[0]);
        break;
      case 2:
        $oController->$sMethod($aArgs[0], $aArgs[1]);
        break;
      case 3:
        $oController->$sMethod($aArgs[0], $aArgs[1], $aArgs[2]);
        break;
      case 4:
        $oController->$sMethod($aArgs[0], $aArgs[1], $aArgs[2], $aArgs[3]);
        break;
      case 5:
        $oController->$sMethod($aArgs[0], $aArgs[1], $aArgs[2], $aArgs[3], $aArgs[4]);
        break;
      default:
        $oController->$sMethod($aArgs[0], $aArgs[1], $aArgs[2], $aArgs[3], $aArgs[4]);
        Log::error('Master::launch() with over 5 arguments');
        break;

    }

  }

	function __construct()
	{
		//hit();
    //stop();
    //line();

		//Session::init();
		//Request::record_page_view();
    //line();
		//define('PAGE_VIEW_ID', $this->iPageViewId);

		register_shutdown_function(array('Request', 'update_page_view'));




    if(Request::$bDebugMode)
    {
      //Util::line();
        self::$aRegions[] = 'head_start';     //views/start.php
        self::$aRegions[] = 'body_end';       //views/end.php

        self::$aRegions = array_combine(self::$aRegions, self::$aRegions);
    }



		//expose($this->iPageViewId);
    //line();


		//$this->bRequireLogin = @Util::coalesce($this->bRequireLogin, true);
    //line();


		$this->oDb = new Db('master');

    $this->set_domain_user_id();
    //expose(self::$iDomainUserId);
    //line();

		//$this->data = array();

    //$this->aCss = array();
    //$this->aJs = array();
    $this->aSubTemplates = @Util::coalesce($this->aSubTemplates, array());

    //line();



    // TODO -  not sure if im 100% happy with this being in the constructor - cant always do perm checks this early
    //line();
    //$this->check_login();
    Login::check();
    //line();


		//expose($_POST);
		if(isset($_POST['bAllowViewPw']))
			Auth::check_allow_view_pass($_POST['bAllowViewPw']);
		//expose($_COOKIE);
		$this->bAllowView = @Util::coalesce($_POST['bAllowView'], $_SESSION['bAllowView'], $_COOKIE['bAllowView'], (ENV == 'prod'));

    //line();

    $oMobileDetect = new Mobile_Detect;
    if($oMobileDetect->isMobile())
      $this->sScreenSize = 'xs';
    elseif($oMobileDetect->isTablet())
      $this->sScreenSize = 'sm';
    else
      $this->sScreenSize = 'lg'; //@TODO - determine way to tell md from lg - use session data

    //line();

		//stop();


		//setlocale(LC_MONETARY, 'en_US');
	}

  public function init()
  {

    pr('Master->init()');
  }

	// generic answer based on barmend - should override in site specific controller
	protected function invalid_perm_hijack($sMsg = null)
	{
		//pr('_invalid_perm_hijack()');
		//stop();
		$this->sSubJumboContent = Util::coalesce($sMsg, 'You don\'t have access to view this page.');
		$this->set_navbar_links();
    print $this->get_view('custom/domains/'.DOMAIN.'/views/inside.php');
    stop();
	}



	public function __destruct()
	{
		//pr('__destruct()');
		if(class_exists('Perm') && !Perm::$bChecked && !Request::$bRedirect)
    {
      //line();
      //expose(Request::$bRedirect);
			Log::error('no permission checked on: '.Request::get_full_url());
    }

    //expose(Debug::$bStopped);

    //expose($_REQUEST);

    //if(Request::$bDebugMode && !Request::$bRedirect && !isset($_REQUEST['page_view_id']) && !Request::$bIsAjax && !Debug::$bStopped  && !isset($this->bIncludedStartView, $this->bIncludedEndView))
    //  Log::error('start and end views not included');

    if(Debug::$bEmailMode)
      Debug::email_end();
	}

  protected function get_templates($aViews)
  {
    Log::deprecated_code('get_templates()');
    return $this->get_views($aViews);
  }

  protected function get_template($sView, $sViewId = null)
  {
    Log::deprecated_code('get_template()');
    return $this->get_view($sView, $sViewId);
  }

  protected function get_views($aViews)
  {
    $sRet = '';
    foreach($aViews as $sView)
      $sRet.= $this->get_view($sView);

    return $sRet;
  }


	protected function get_view($sView, $sViewId = null) // $sViewId can be used in the view
	{
		//pr('get_view('.$sView.')');
    //return;
    //stop();
    // need to deprecate
    $sTemplateId = $sViewId;

		//$sDefaultCss

		$sRet = '';
		$sRet.= "\n<!--BEGIN VIEW $sView -->\n";

		//if(!isset($this->sLayout))
		//		Log::error('Missing Layout');

		//pr($sTemplate);

		//expose(file_exists(PUBLIC_HTML_PATH.'/'.ENV.'/application/templates/'.$sTemplate));

		//stop();

		//expose($sTemplate);

    $sFile = CODE_PATH.'/'.$sView;

    if(file_exists($sFile))
		  $sRet.= require($sFile);
    else
      Log::error(array('msg' => 'Missing view: '.$sFile, 'offset' => 1));

		$sRet.= "\n<!--END VIEW $sView -->\n";

		return $sRet;
	}

  // this is currently an exact copy of get_view() - not sure how to keep them as the same functions
  // since some views use $this->variables - it cant be static - but we need a static version of it for classes
  public static function get_static_view($sView, $sViewId = null)
  {
    $sRet = '';
    $sRet.= "\n<!--BEGIN VIEW $sView -->\n";

    $sFile = CODE_PATH.'/'.$sView;

    if(file_exists($sFile))
      $sRet.= require($sFile);
    else
      Log::error(array('sMsg' => 'Missing view: '.$sView, 'iOffset' => 1));

    $sRet.= "\n<!--END VIEW $sView -->\n";

    return $sRet;
  }




  public static function check_region($sRegion)
  {
    //pr('check_region('.$sRegion.')');
    if(!in_array($sRegion, self::$aRegions))
    {
      pr('available regions: ');
      expose(self::$aRegions);
      //expose(self::$aRegions);
      Log::error('Invalid Region: '.$sRegion);

    }
  }

  // TODO - add a check to make sure the file exists - and perhaps get minified/non minified versions based on debug mode
  public static function add_js($sFile, $sRegion = 'body_end', $bAddBuildId = true)
  {
    //pr($sFile);

    if(Request::$bDebugMode)
      self::check_region($sRegion);



    //@TODO - stop doing this str_replace
    //$sFile = str_replace('/code/dev/js/', '/code/dev/core/js/', $sFile);
    //$sFile = str_replace('/code/dev/domains/', '/code/dev/custom/domains/', $sFile);

    if(Request::$bDebugMode && (!file_exists(ltrim($sFile, '/')) || strpos($sFile, '/') != 0) )
      Log::Error('add_js() - invalid file: '.$sFile);

  	//pr('add_js('.$sFile.')');
    //expose_backtrace();
  	if($bAddBuildId)
    {
  		$sSuffix = '?v='.Request::$iBuildId;
      if(Request::$bDebugMode)
        $sSuffix.= '&t='.time();
    }
  	else
  		$sSuffix = '';

  	//$oThis = &get_instance();
  	self::$aJs[$sRegion][] = $sFile.$sSuffix;
  }

  public static function add_css($sFile, $sRegion = 'head_start', $bAddBuildId = true)
  {
    if(Request::$bDebugMode)
      self::check_region($sRegion);

    //pr($sFile);
    //TODO - stop doing the str_replace
    //$sFile = str_replace('/code/dev/css/', '/code/dev/core/css/', $sFile);
    //$sFile = str_replace('/code/dev/domains/', '/code/dev/custom/domains/', $sFile);

    //pr($sFile);

    if(Request::$bDebugMode && (!file_exists(ltrim($sFile, '/')) || strpos($sFile, '/') != 0) )
      Log::Error('add_css() - invalid file: '.$sFile);




  	if($bAddBuildId)
    {
  		$sSuffix = '?v='.Request::$iBuildId;
      if(Request::$bDebugMode)
        $sSuffix.= '&t='.time();
    }
  	else
  		$sSuffix = '';

  	//$oThis = &get_instance();
  	self::$aCss[$sRegion][] = $sFile.$sSuffix;
  }

  // TODO - put this back to protected
  //protected - had to make this public so the reporter class could call it
  public function add_modal($aConfig)
  {
    //pr('add_modal');
    //hit();
    $sModalId = $aConfig['id'];
    unset($aConfig['id']);
    //expose($aConfig);
    $this->aModals[] = $sModalId;
    //expose($this->aModals);

    $this->aTemplateData[$sModalId] = $aConfig;
  }

  public static function add_footer_js($sJs)
  {
    //if(!isset($this->sFooterJs))
    //    $this->sFooterJs = '';
    self::$sFooterJs.= "$sJs ; \n";
  }


  // would like to deprecate these eventually - leaving copies for now
  // methods thats start with _ should accept arrays
  public static function _get($sVar)
  {
    if(isset(self::$aData[$sVar]))
      return self::$aData[$sVar];
  }

  public static function _set($sVar, $vVal)
  {

    self::$aData[$sVar] = $vVal;
  }

  public static function get_var($sVar)
  {
    if(isset(self::$aData[$sVar]))
      return self::$aData[$sVar];
  }

  public static function set_var($sVar, $vVal)
  {

    self::$aData[$sVar] = $vVal;
  }


  protected function set_domain_user_id($bFastmode = true)
  {
    self::$iDomainUserId = Session::get('domain_user_id');
    if(isset(self::$iDomainUserId))
    {
      Session::set_cookie('domain_user_id', self::$iDomainUserId);
      return;
    }

    self::$iDomainUserId = Session::get_cookie('domain_user_id');
    if(isset(self::$iDomainUserId))
    {
      Session::set('domain_user_id', self::$iDomainUserId);
      return;
    }

    if($bFastmode)
      return; // no db lookups in fast mode

    $sSql = "
      SELECT
        max(domain_user_id) domain_user_id
      FROM
        users
      WHERE
        domain_id = ".(int)Request::$iDomainId;

      $aRow = Db::$oMaster->select_row($sSql);

      self::$iDomainUserId = Util::Coalesce($aRow['domain_user_id'], 0);
      self::$iDomainUserId++;

      $oModel = Model::init('master', 'users', $this->oDb);
      $oModel->domain_id = Request::$iDomainId;
      $oModel->domain_user_id = self::$iDomainUserId;
      $oModel->save();

      Session::set('domain_user_id', self::$iDomainUserId);
      Session::set_cookie('domain_user_id', self::$iDomainUserId);
  }
}