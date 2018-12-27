<?

class Master
{
  public static $aData = array();

  public static $aJs = array();
  public static $aCss = array();
  public static $sFooterJs = '';

  public static function launch($sMethod, $aArgs)
  {
    //line();
    //pr('Master::launch('.$sMethod.')');
    //expose($aArgs);
    $oController = new Controller();
    //line();
    $aArgs = array_values($aArgs);
    switch(count($aArgs))
    {
      case 0:
        $oController->$sMethod();
        break;
      case 1:
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
		//pr('Master->__construct()');
    //line();

		//Session::init();
		//Request::record_page_view();
    //line();
		//define('PAGE_VIEW_ID', $this->iPageViewId);

		register_shutdown_function(array('Request', 'update_page_view'));
		//expose($this->iPageViewId);
    //line();


		//$this->bRequireLogin = @Util::coalesce($this->bRequireLogin, true);
    //line();


		$this->oDb = new Db('master');
    //line();

		//$this->data = array();

    //$this->aCss = array();
    //$this->aJs = array();
    $this->aSubTemplates = @Util::coalesce($this->aSubTemplates, array());

    //line();



    // TODO -  not sure if im 100% happy with this being in the constructor - cant always do perm checks this early
    //line();
    $this->check_login();
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
		$this->_set_navbar_links();
    print $this->get_template(DOMAIN_FOLDER.'/inside_v.php');
	}

  private function check_login()
  {
    //pr('Master::check_login');
    //expose(Perm::$bRequireLogin);
    if(Perm::$bRequireLogin)
    {
      $this->aLogin = Session::fetch_login();
      //expose($this->aLogin);
      //stop();
      if(!$this->aLogin)
      {
        //Perm::ignore();
        //@TODO - set some flashdata to display a sesson timeout issue
        Request::redirect('/login/leave');
      }
    }
    //else
    //  Perm::$bChecked = true;
  }

	public function __destruct()
	{
		//pr('__destruct()');
		if(class_exists('Permission') && !Permission::$bChecked)
			Log::error('no permission check on: '.get_full_url());
	}

  protected function get_templates($aTemplates)
  {
    $sRet = '';
    foreach($aTemplates as $sTemplate)
      $sRet.= $this->get_template($sTemplate);

    return $sRet;
  }

	protected function get_template($sTemplate, $sTemplateId = null) // $aTemplate can be used in the template
	{
		//pr('get_template('.$sTemplate.')');
    //stop();

		//$sDefaultCss

		$sRet = '';
		$sRet.= "\n<!--BEGIN TEMPLATE $sTemplate -->\n";

		//if(!isset($this->sLayout))
		//		Log::error('Missing Layout');

		//pr($sTemplate);

		//expose(file_exists(PUBLIC_HTML_PATH.'/'.ENV.'/application/templates/'.$sTemplate));

		//stop();

		//expose($sTemplate);

    $sFile = CODE_PATH.'/'.$sTemplate;

    if(file_exists($sFile))
		  $sRet.= require($sFile);
    else
      Log::error(array('sMsg' => 'Missing template: '.$sTemplate, 'iOffset' => 1));

		$sRet.= "\n<!--END TEMPLATE $sTemplate -->\n";

		return $sRet;
	}

  // TODO - add a check to make sure the file exists - and perhaps get minified/non minified versions based on debug mode



  public static function add_js($sFile, $sRegion = 'body_end', $bAddBuildId = true)
  {
    //pr($sFile);
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
    $sModalId = $aConfig['id'];
    unset($aConfig['id']);
    //expose($aConfig);
    $this->aModals[] = $sModalId;

    $this->aTemplateData[$sModalId] = $aConfig;
  }

  public static function add_footer_js($sJs)
  {
    //if(!isset($this->sFooterJs))
    //    $this->sFooterJs = '';
    self::$sFooterJs.= "$sJs ; \n";
  }

  public static function _get($sVar)
  {
    if(isset(self::$aData[$sVar]))
      return self::$aData[$sVar];
  }

  public static function _set($sVar, $vVal)
  {
    self::$aData[$sVar] = $vVal;
  }

}