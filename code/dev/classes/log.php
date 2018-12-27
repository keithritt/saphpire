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
  //public static $oDb = null;
  public static $bPrint = false;
  private static $bPaused = false;

  public static function write($aParams)
  {
    //pr('Log::write()');
    //expose($aParams);
    // ingore if the log is paused
    if(self::$bPaused)
    {
     // line();
      return;
    }
    //else
    //  line();

    //line();

    //self::init();
    //line();
    if(is_array($aParams))
      extract($aParams);
    else
      $sMsg = $aParams;

    //line();

    if(static::$bPrint)
      pr($sMsg);

    //ine();

		//line();

    $iPriorityId = @Util::coalesce($iPriorityId, static::$iPriorityId);

    //line();

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

    //line();

    $iTypeId = @Util::coalesce($iTypeId, self::TYPE_MSG);
    $iDomainId = @Util::coalesce($iDomainId, static::$iDomainId);

    //line();

    if(!isset($sFile, $iLine))
    {
      //line();
      $aDebug = debug_backtrace();
      foreach($aDebug as $aStep)
      {
          if(stripos($aStep['file'], 'log.php'))
            continue;

          $sFile = str_replace(CODE_ENV, '', $aStep['file']);
          $iLine = $aStep['line'];

          break;
      }
      //expose($aDebug);
      //stop();

    }
			//line();
    //expose($sCat);
    //expose($sSubCat);

    //line();

    //expose(Db::$oMaster);

    //stop();

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

    //expose(PAGE_VIEW_ID);
    //stop();
    if(defined('PAGE_VIEW_ID'))
      $oLog->page_view_id = PAGE_VIEW_ID;
    if(defined('SCHEDULER_ID'))
      $oLog->scheduler_id = SCHEDULER_ID;


    //pr('save log');
    $oLog->save();
		//line();

    //expose($oLog->aData);
    //die();
    //expose($sExpireTs);
  }

  public static function _error($sMsg, $iPriorityId = self::PRIORITY_CRITICAL, $iTypeId = self::TYPE_PHP_ERROR, $sExtra = null, $sFile = null, $iLine = null)
  {
    //pr('Log::_error()');
    return self::error(array('sMsg' => $sMsg, '', 'iPriorityId' => $iPriorityId, 'iTypeId' => $iTypeId, 'sExtra' => $sExtra, 'sFile' => $sFile, 'iLine' => $iLine));
  }

  public static function error($aParams)
  {

    //expose(self::$bPaused);
    // ignore if the log is paused
    //line();
    if(self::$bPaused)
      return;

    //line();




		//expose(php_sapi_name());
		//expose($aParams);
		if(!is_array($aParams))
    {
      //line();
				$sMsg = $aParams;
      $aParams = array();
      $aParams['sMsg'] = $sMsg;
    }
    //pr('Log->error('.$aParams['sMsg'].')');
		//line();
    $aParams['iTypeId'] = @Util::coalesce($aParams['iTypeId'], self::TYPE_PHP_ERROR);
    //line();
    self::write($aParams);
		//line();
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
			//line();
      //expose($aParams);
			@pr('ERROR: '.$aParams['sCat'].': '.$aParams['sMsg'].' '.$aParams['sFile'].' '.$aParams['iLine']);
      //line();
			expose_backtrace();

      self::$bPaused = true; // avoid infinite loops
      //line();
			stop();
		}
  }

	public static function deprecated_code($sMsg = null)
	{
    //pr('Log::deprecated_code('.$sMsg.')');
    if(is_null($sMsg))
      Log::deprecated_code('Log::deprecated_code() without $sMsg');
		$aParams = array();
		$aTrace = debug_backtrace();
		$aTrace = $aTrace[1];
		//expose($aTrace);
    //stop();
		$aParams['sMsg'] = $sMsg;//. '<br/> @ File: '.$aTrace['file'].' line: '.$aTrace['line'];
		$aParams['sCat'] = 'Deprecated Code';
    $aParams['sFile'] = $aTrace['file'];
    $aParams['iLine'] = $aTrace['line'];
		self::error($aParams);
	}

  //private static function init()
  //{
    //pr('Log::init()');
    //if(is_null(static::$oDb))
    //{
    //  line();
     // self::$oDb = new Db();
    //  line();
   // }
   // else
   //   line();
  //}

  public static function pause()
  {
    //pr('Log::pause()');
    self::$bPaused = true;
  }

  public static function resume()
  {
    //pr('Log::resume()');
    self::$bPaused = false;
  }
}