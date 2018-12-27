<?

class Request
{
  public static $bDebugMode = true; // @TODO - change to false
  public static $iPageViewId;
  public static $iCodeLevel = 1;
  public static $iDomainId = null;
  public static $iBuildId = 7;

  public static function init()
  {
    switch(DOMAIN)
    {
      case 'saphpire.net': self::$iDomainId = 1; break;

      default:
        Log::error('Unknown Domain: '.DOMAIN);
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
		//@TODO - figure out a more elegant solution then this
		if(strpos($_SERVER['REQUEST_URI'], 'record_page_request'))
			 return;

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

			self::$iPageViewId = Db::$oMaster->insert($sSql);
	}

  public static function update_page_view()
  {
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

    Db::$oMaster->update($sSql);
  }

  function update_page_view_timings()
  {
    $aData = $_POST;
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

    Db::$oMaster->update($sSql);

    $sSql = "
    SELECT
      session_id
    FROM
      page_views
    WHERE
      id = ".(int)$aData['iPageViewId'];

    $aRow = Db::$oMaster->select_row($sSql);

    $oModel = Model::init('master', 'sessions', Db::$oMaster);
    $oModel->fetch(Session::$iId, '0 or 1');
    $oModel->last_update_ts = Db::datetime('now', false);
    $oModel->domain_id = Request::$iDomainId;
    $oModel->create_ts = Util::coalesce($oModel->create_ts, Db::datetime('now', false));
    $oModel->save();
  }

  //TODO - look into adding a js redirect if output is already sent
  public static function redirect($sUrl, $sDummy = null)
  {
    if(self::$bDebugMode)
    {
      if(isset($sDummy))
        Log::deprecated_code('2nd parameter populated for redirect()');

      if(!Perm::$bChecked)
        Log::error('no permission check on: '.self::get_full_url());

      $sOb = ob_get_contents();

      if($sOb != '')
      {
        pr('redirect to <a href="'.$sUrl.'">'.$sUrl.'</a> ignored because there is data in the output buffer');
        stop();
      }
    }

    header("Refresh:0;url=".$sUrl);
    die();
  }

  public static function get_full_url()
  {
    return @$_SERVER["HTTP_HOST"].@$_SERVER['REQUEST_URI'];
  }
}