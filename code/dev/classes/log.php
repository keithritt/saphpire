<?

class Log
{
  //private static

  const TYPE_MSG = TYPE_LOG_MSG;
  const TYPE_PHP_ERROR = TYPE_LOG_PHP_ERROR;
  const TYPE_JS_ERROR = TYPE_LOG_JS_ERROR;
  const TYPE_SQL_ERROR = TYPE_LOG_SQL_ERROR;
  const TYPE_SQL_SLOW = TYPE_LOG_SQL_SLOW;
  const TYPE_DEPRECATED = TYPE_LOG_DEPRECATED;

  const PRIORITY_LOW = TYPE_LOG_PRIORITY_LOW;					       // kept for 1 day - not currently enforced
  const PRIORITY_MEDIUM = TYPE_LOG_PRIORITY_MEDIUM;			     // kept for 1 week
  const PRIORITY_HIGH = TYPE_LOG_PRIORITY_HIGH;				       // kept for 1 month
  const PRIORITY_CRITICAL = TYPE_LOG_PRIORITY_CRITICAL;		   // kept for 3 months - included in daily email
  const PRIORITY_EMERGENCY = TYPE_LOG_PRIORITY_EMERGENCY;		 // triggers email and text - never deleted

  public static $iDomainId = 1; // @TODO - hardcoding DOMAIN_OFFICIALLOOP_COM;
  public static $iPriorityId = self::PRIORITY_MEDIUM;
  public static $bPrint = false;
  private static $bPaused = false;

  public static function write($aParams)
  {
    // ingore if the log is paused
    if(self::$bPaused)
    {
      return;
    }

    if(is_array($aParams))
      extract($aParams);
    else
      $sMsg = $aParams;

    if(static::$bPrint)
      pr($sMsg);

    $iPriorityId = @Util::coalesce($iPriorityId, static::$iPriorityId);

    if(!isset($sExpireTs))
    {
      switch($iPriorityId)
      {
        case self::PRIORITY_LOW:
          $sExpireTs = Db::datetime(strtotime('+1 day'), false);
          break;
        case self::PRIORITY_MEDIUM:
          $sExpireTs = Db::datetime(strtotime('+7 days'), false);
          break;
        case self::PRIORITY_HIGH:
          $sExpireTs = Db::datetime(strtotime('+30 days'), false);
          break;
        default:
        case self::PRIORITY_CRITICAL:
          $sExpireTs = Db::datetime(strtotime('+90 days'), false);
          break;
        case self::PRIORITY_EMERGENCY:
          $sExpireTs = null;
          break;
      }
    }

    $iTypeId = @Util::coalesce($iTypeId, self::TYPE_MSG);
    $iDomainId = @Util::coalesce($iDomainId, static::$iDomainId);

    if(!isset($sFile, $iLine))
    {
      $aDebug = debug_backtrace();
      foreach($aDebug as $aStep)
      {
          if(stripos($aStep['file'], 'log.php'))
            continue;

          $sFile = str_replace(CODE_ENV, '', $aStep['file']);
          $iLine = $aStep['line'];

          break;
      }
    }

    $oLog = Model::init('master', 'logs', Db::$oMaster);
		//line();
    $oLog->create_ts = Db::datetime(time(), false);
    $oLog->expire_ts = $sExpireTs;
    $oLog->domain_id = $iDomainId;
    $oLog->type_id = $iTypeId;
    $oLog->priority_id = $iPriorityId;
    $oLog->file = $sFile;
    $oLog->line = $iLine;

    if(isset($sMsg))
      $oLog->msg = $sMsg;
    if(isset($sCat))
      $oLog->cat = $sCat;
    if(isset($sExtra))
      $oLog->extra = $sExtra;

    if(defined('PAGE_VIEW_ID'))
      $oLog->page_view_id = PAGE_VIEW_ID;
    if(defined('SCHEDULER_ID'))
      $oLog->scheduler_id = SCHEDULER_ID;

    $oLog->save();
  }

  public static function _error($sMsg, $iPriorityId = self::PRIORITY_CRITICAL, $iTypeId = self::TYPE_PHP_ERROR, $sExtra = null, $sFile = null, $iLine = null)
  {
    return self::error(array('sMsg' => $sMsg, '', 'iPriorityId' => $iPriorityId, 'iTypeId' => $iTypeId, 'sExtra' => $sExtra, 'sFile' => $sFile, 'iLine' => $iLine));
  }

  public static function error($aParams)
  {
    if(self::$bPaused)
      return;

		if(!is_array($aParams))
    {
				$sMsg = $aParams;
      $aParams = array();
      $aParams['sMsg'] = $sMsg;
    }
		//line();
    $aParams['iTypeId'] = @Util::coalesce($aParams['iTypeId'], self::TYPE_PHP_ERROR);
    self::write($aParams);
		if(Request::$bDebugMode) //&& php_sapi_name() == 'cgi-fcgi'
		{
      if(!isset($aParams['sFile'], $aParams['iLine']))
      {
        $aTraces = debug_backtrace();
        $iWalk = 0;
        foreach($aTraces as $aTrace)
        {
          $iWalk++;
          if(isset($aParams['iOffset']) && $iWalk <= $aParams['iOffset'])
            continue;
          $aParams['sFile'] = $aTrace['file'];
          $aParams['iLine'] = $aTrace['line'];
          break;

        }
      }

			@pr('ERROR: '.$aParams['sCat'].': '.$aParams['sMsg'].' '.$aParams['sFile'].' '.$aParams['iLine']);

			expose_backtrace();

      self::$bPaused = true; // avoid infinite loops
			stop();
		}
  }

	public static function deprecated_code($sMsg = null)
	{
    if(is_null($sMsg))
      Log::deprecated_code('Log::deprecated_code() without $sMsg');
		$aParams = array();
		$aTrace = debug_backtrace();
		$aTrace = $aTrace[1];

		$aParams['sMsg'] = $sMsg;//. '<br/> @ File: '.$aTrace['file'].' line: '.$aTrace['line'];
		$aParams['sCat'] = 'Deprecated Code';
    $aParams['sFile'] = $aTrace['file'];
    $aParams['iLine'] = $aTrace['line'];
		self::error($aParams);
	}

  public static function pause()
  {
    self::$bPaused = true;
  }

  public static function resume()
  {
    self::$bPaused = false;
  }
}
