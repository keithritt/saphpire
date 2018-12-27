<?

class Request
{
  public static $bDebugMode = true; // @TODO - change to false
  public static $iPageViewId;
  public static $iCodeLevel = 1;
  public static $iDomainId = null;
  public static $iBuildId = 7;

  //private static $oDb = null;

  public static function init()
  {
    //pr('Request::init()');
    //self::$oDb = new Db();
    switch(DOMAIN)
    {
      case 'officialloop.com': self::$iDomainId = 1; break;
      case 'barmend.com': self::$iDomainId = 2; break;
      case 'oldtowndrafthouse.com': self::$iDomainId = 3; break;
      case 'oldtowndraughthouse.com': self::$iDomainId = 4; break;
      case 'corner-bar.com': self::$iDomainId = 5; break;
      case 'checkpointcheck.com': self::$iDomainId = 6; break;
      case 'brewskistavern.com': self::$iDomainId = 7; break;
      case 'keithritt.net': self::$iDomainId = 8; break;
      case 'iamjackjourney.com': self::$iDomainId = 9; break;
      case 'liquororderform.com': self::$iDomainId = 10; break;
      case 'nc17clock.com': self::$iDomainId = 11; break;
      case 'mystery3.com': self::$iDomainId = 12; break;
      case 'safire.tech': self::$iDomainId = 13; break;
      case 'saphpire.net': self::$iDomainId = 14; break;
      case 'louop.com': self::$iDomainId = 15; break;

      default:
        //line();
        Log::error('Unknown Domain: '.DOMAIN);
        //line();
        break;
    }
    self::record_page_view();
  }

	public static function is_ajax()
	{
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']);
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
				Log::Error('Get String > 4000 chars');
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
				Log::Error('Post String > 4000 chars');
				$sPostString = substr($sPostString, 0, 4000);
			}
		}
		else
			$sPostString = null;

    //line();

		$sSql ="
		INSERT INTO
			page_views
			(
				session_id,
				url,
				get_params,
				post_params,
				ts
			)
			VALUES
			(
				".(int)Session::$iId.",
				".Db::esc(REQUEST_URL).",
				".Db::esc($sGetString).",
				".Db::esc($sPostString).",
				now()
			)";

			//expose($sSql);
			self::$iPageViewId = Db::$oMaster->insert($sSql);
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

  function update_page_view_timings()
  {
    $aData = $_POST;
    //expose($aData);
    $sSql = "
    UPDATE
      page_views
    SET
      php_time = ".(float)$aData['sPhpTime'].",
      js_time = ".(float)$aData['sJsTime'].",
      total_time = ".(float)$aData['sTotalTime'].",
      ajax_time = ".(float)$aData['sAjaxTime']."
    WHERE
      id = ".(int)$aData['iPageViewId'];

      //expose($sSql);

    Db::$oMaster->update($sSql);

    $sSql = "
    SELECT
      session_id
    FROM
      page_views
    WHERE
      id = ".(int)$aData['iPageViewId'];

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
    //line();
    $oModel->create_ts = Util::coalesce($oModel->create_ts, Db::datetime('now', false));
//line();
    //expose($oModel->sFqTable);
    $oModel->save();


  }

  //TODO - look into adding a js redirect if output is already sent
  public static function redirect($sUrl, $sDummy = null)
  {
    //pr('Request::redirect('.$sUrl.')');
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