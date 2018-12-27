<?

class Request
{
  use CustomRequest;

  public static $bDebugMode = true;// @TODO - change to false
  public static $bRedirect = false;
  public static $iPageViewId;
  public static $iCodeLevel = 1;
  public static $iDomainId = null;
  public static $sDomain = null;
  public static $sDefaultSchema = 'master';

  // @TODO - setting to public for santiritter.com (viewing pics)
  //private
  public static $bIsAjax = null;

  //private static $oDb = null;

  public static function init($sDomain)
  {
    //print '<br>Request->init('.$sDomain.')';
    if(isset($_REQUEST['debug_mode']))
    {
      //$asdf = false;
      //expose(isset($asdf));
      //line();
      Session::set('debug_mode', (boolean)$_REQUEST['debug_mode']);
    }

    //var_dump($_REQUEST);


    $bTmp = Session::get('debug_mode');
    //expose($bTmp);
    if(isset($bTmp))
      self::$bDebugMode = $bTmp;
    else
      self::$bDebugMode = (CODE_ENV == 'dev');

    //expose($_POST);

    //var_dump(Request::$bDebugMode);
    //stop();

    if(self::$bDebugMode)
    {
      $oModel = Model::init('master', 'domains', Db::$oMaster);

      if(isset($_GET['debug_domain_id']))
      {
        $oModel->fetch($_GET['debug_domain_id']);
        $sDomain = $oModel->domain;
        Session::set('debug_domain_id', (int)$_GET['debug_domain_id']);
      }
      else
      {
        $iTmpId = Session::get('debug_domain_id');
        if($iTmpId)
        {
          $oModel->fetch($iTmpId);
          $sDomain = $oModel->domain;
        }
      }
      //pr($sDomain);
      //stop();
    }



    self::$sDomain = $sDomain;
    //@TODO - check to make sure these functions exist first (or create them in the parent)
    self::set_domain_id();
    //self::$sDefaultSchema = 'master';
    self::set_default_schema();

    //expose(self::$sDomain);

    //self::$oDb = new Db();
    //expose(DOMAIN);


    //@TODO - not sure if i want to create this constant or use the class var
    define('DOMAIN', self::$sDomain);
    define('DOMAIN_PATH', CODE_PATH.'/custom/domains/'.self::$sDomain);

    // simple debug only test to make sure our domain is in our table
    if(self::$bDebugMode)
    {
      $oModel = Model::init('master', 'domains', Db::$oMaster);
      $oModel->fetch(array('id' => self::$iDomainId, 'domain' => self::$sDomain));
    }

    // if we open up an ajax call via firebug - is_ajax() will be false - this is a hack to see if we are viewing an ajax call in a 'non ajax' mode
    //if(isset($_POST['parent_id']))
    //  self::$bIsAjax = true;


    self::record_page_view();
  }

	public static function is_ajax()
	{
    //print '<br>is_ajax()';
    //var_dump(self::$bIsAjax);
    //die();
    if(self::$bDebugMode && isset(Debug::$bIsAjax)) // allow debugging tool to override server based setting
    {
      //print 'debug var';
      self::$bIsAjax = Debug::$bIsAjax;
    }
    elseif(isset($_SERVER['HTTP_X_REQUESTED_WITH']))
    {
     //print 'server var';
      self::$bIsAjax = true;
    }
    //else
    //  print 'hit else';

    //var_dump(self::$bIsAjax);

    return self::$bIsAjax = (boolean)self::$bIsAjax;
	}

	private static function record_page_view()
	{
		//pr('record_page_view()');
		//upsert_session_id();
		//Session::upsert_record();
    //line();
		//expose_backtrace();
		//$sSessionId = session_id();

		//expose($_SERVER);

    //expose($_REQUEST);

    if(isset($_REQUEST['file']) && $_REQUEST['file'] == 'push_sql.php')
      return; //@TODO - remove - this is only here for deployment 7 which edits the sessions table

		//@TODO - figure out a more elegant solution then this
		if(strpos($_SERVER['REQUEST_URI'], 'record_page_request'))
			 return;

    //line();

		//expose('record_page_view()');
		//determine request string



		if(count($_GET))
		{
			$sGetString = json_encode($_GET);
			if(strlen($sGetString) > 4000)
			{
				//Log::Error('Get String > 4000 chars');
				$sGetString = substr($sGetString, 0, 4000);
			}
		}
		else
			$sGetString = null;

		if(count($_POST))
		{
			$sPostString = json_encode($_POST);
			if(strlen($sPostString) > 4000)
			{
				//@TODO - photo uploads are over 4k chars and not sure how to ignore them - disabling error for now
        //Log::Error('Post String > 4000 chars');

				$sPostString = substr($sPostString, 0, 4000);
			}
		}
		else
			$sPostString = null;

      $oModel = Model::init('master', 'page_views', Db::$oMaster);
      if(isset(Session::$iId))
        $oModel->session_id = Session::$iId;
      //else
      //  $oModel->session_name = Session::$sName;
      $oModel->url = REQUEST_URL;
      $oModel->get_params = $sGetString;
      $oModel->post_params = $sPostString;
      $oModel->ts = Db::keyword('now()');
      self::$iPageViewId = $oModel->save();

      //expose(self::$iPageViewId);


			//expose($sSql);
			//self::$iPageViewId = Db::$oMaster->insert($sSql);
      if(!self::$iPageViewId)
        self::$iPageViewId = 1; // make sure it has something to avoid js errors
			//expose($iPageViewId);
	}

  public static function update_page_view()
  {
    //pr('update_page_view()');
    //sleep(5);
    //expose('update_page_view()');
    //expose(PAGE_VIEW_ID);
    //if(!defined('CODE_LEVEL'))
    //    define('CODE_LEVEL', 1);
    //expose(CODE_LEVEL);

    $sSql = "
      UPDATE
        page_views
      SET
        peak_mem_usage = ".(int)memory_get_peak_usage().",
        code_level = ".(int)self::$iCodeLevel;

    // if this is an ajax call - record the php time and parent id
    if(isset($_POST['iParentId']))
    {
      $fPhpTime = number_format((microtime(true) - START_TIME), 2);
      $sSql.= ", php_time = ".(float)$fPhpTime.",
      total_time = ".(float)$fPhpTime.",
      parent_id = ".(int)$_POST['iParentId'];
    }

    $sSql.= "
      WHERE
        id = ".(int)self::$iPageViewId;

      //expose($sSql);

    Db::$oMaster->update($sSql);
  }

  function update_page_view_data()
  {
    //return;
    $aData = $_POST;
    //expose($aData);

    if(isset(Session::$iId))
      $sSessionNameUpdate = 'session_name = null,';
    else
      $sSessionNameUpdate = '';

    // @TODO - use model class once it no longer requires a select in order to run an update
    $sSql = "
    UPDATE
      page_views
    SET
      session_id = ".(int)Session::$iId.",
      ".$sSessionNameUpdate."
      php_time = ".(float)$aData['php_time'].",
      js_time = ".(float)$aData['js_time'].",
      total_time = ".(float)$aData['total_time'].",
      ajax_time = ".(float)$aData['ajax_time']."
    WHERE
      id = ".(int)$aData['page_view_id'];

      //expose($sSql);

    Db::$oMaster->update($sSql);

    return; // this logic is handled by Util::verify_session_data

    $sSql = "
    SELECT
      session_id
    FROM
      page_views
    WHERE
      id = ".(int)$aData['page_view_id'];

    //expose($sSql);

    $aRow = Db::$oMaster->select_row($sSql);

    //expose(Db::datetime('now', false));
    //stop();

    //expose(Session::$iId);
    //stop();

    //global $oThis;
    //line();
    $oModel = Model::init('master', 'sessions', Db::$oMaster);
    //line();
    $oModel->fetch(Session::$iId, '0 or 1');
    //line();
    $oModel->last_update_ts = Db::datetime('now', false);
    $oModel->domain_id = Request::$iDomainId;
    $oModel->domain_user_id = Master::$iDomainUserId;
    //line();
    if(is_null($oModel->create_ts))
      $oModel->create_ts = Db::datetime('now', false);
//line();
    //expose($oModel->sFqTable);
    $oModel->save();


  }

  //TODO - look into adding a js redirect if output is already sent
  //TODO - put in logic to avoid redirect loops
  public static function redirect($sUrl, $sDummy = null)
  {
    //pr('Request::redirect('.$sUrl.')');
    //hit();
    self::$bRedirect = true;
    //line();
    //expose_backtrace();
    if(self::$bDebugMode)
    {
      if(isset($sDummy))
        Log::deprecated_code('2nd parameter populated for redirect()');

      if(!Perm::$bChecked)
        Log::error('no permission check on: '.self::get_full_url());

      //expose_backtrace();

      $sOb = ob_get_contents();



      //$sOb = ob_start();
      //$sOb = ob_get_flush();

      //expose($sOb);
      if($sOb != '')
      {
        //print $sOb;
        //expose($sOb);
        pr('redirect to <a href="'.$sUrl.'">'.$sUrl.'</a> ignored because there is data in the output buffer');
        stop();
      }
    }

    header("Refresh:0;url=".$sUrl);
    die();
  }

  public static function get_full_url()
  {
    //expose($_SERVER);
    return @$_SERVER["HTTP_HOST"].@$_SERVER['REQUEST_URI'];
  }



}