<?

class Master
{
  public static $aData = array();

  public static $aJs = array();
  public static $aCss = array();
  public static $sFooterJs = '';

  public static function launch($sMethod, $aArgs)
  {
    $oController = new Controller();
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
		register_shutdown_function(array('Request', 'update_page_view'));
		$this->oDb = new Db('master');
    $this->aSubTemplates = @Util::coalesce($this->aSubTemplates, array());

    // TODO -  not sure if im 100% happy with this being in the constructor - cant always do perm checks this early
    $this->check_login();
		if(isset($_POST['bAllowViewPw']))
			Auth::check_allow_view_pass($_POST['bAllowViewPw']);
		$this->bAllowView = @Util::coalesce($_POST['bAllowView'], $_SESSION['bAllowView'], $_COOKIE['bAllowView'], (ENV == 'prod'));

    $oMobileDetect = new Mobile_Detect;
    if($oMobileDetect->isMobile())
      $this->sScreenSize = 'xs';
    elseif($oMobileDetect->isTablet())
      $this->sScreenSize = 'sm';
    else
      $this->sScreenSize = 'lg'; //@TODO - determine way to tell md from lg - use session data

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
    if(Perm::$bRequireLogin)
    {
      $this->aLogin = Session::fetch_login();
      if(!$this->aLogin)
      {
        //@TODO - set some flashdata to display a sesson timeout issue
        Request::redirect('/login/leave');
      }
    }
  }

	public function __destruct()
	{
		if(class_exists('Permission') && !Permission::$bChecked)
			Log::error('no permission check on: '.get_full_url());
	}

	protected function get_template($sTemplate, $sTemplateId = null)
	{
		$sRet = '';
		$sRet.= "\n<!--BEGIN TEMPLATE $sTemplate -->\n";


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
    if(Request::$bDebugMode && (!file_exists(ltrim($sFile, '/')) || strpos($sFile, '/') != 0) )
      Log::Error('add_js() - invalid file: '.$sFile);
  	if($bAddBuildId)
    {
  		$sSuffix = '?v='.Request::$iBuildId;
      if(Request::$bDebugMode)
        $sSuffix.= '&t='.time();
    }
  	else
  		$sSuffix = '';

  	self::$aJs[$sRegion][] = $sFile.$sSuffix;
  }

  public static function add_css($sFile, $sRegion = 'head_start', $bAddBuildId = true)
  {
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
