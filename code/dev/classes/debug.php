<?
class Debug
{
  public static function init()
  {
    if(CODE_ENV == 'dev')
    {
      Request::$bDebugMode = true;
      error_reporting(E_ALL);
    }
  }

  public static function expose($var)
  {
  	if(Request::is_ajax())
    		return;
    if(defined('LOG_FILE_MODE'))
      $sMode = 'cli'; // bit of a hack for scheduler
    else
      $sMode = php_sapi_name();

    switch($sMode)
    {
      case 'cli':
        var_dump($var);
        $aDebug = debug_backtrace();
        $aDebug = $aDebug[0];
        print 'hit line '.$aDebug['line'].' file: '.$aDebug['file']."\n";
  			return; // no need for html in cli
        break;
      default:
        if(!Request::$bDebugMode)
          return;

      if(!isset($_REQUEST['bFirstExpose']) )//&& in_array($_SERVER['SERVER_NAME'], array('dev.barmend.com', 'dev.')))
      {
        print '<br><br>';
        $_REQUEST['bFirstExpose'] = 1;
      }
      $aTraces = debug_backtrace();
      foreach($aTraces as $aTrace)
      {
        if(isset($aTrace['file']) && strpos('_'.$aTrace['file'], 'debug.php'))
        //if(isset($aTrace['class'], $aTrace['function']) && $aTrace['class'] == 'Debug' && $aTrace['function'] == 'expose')
          continue;

        break;
      }
      print '<div style="background: white; border: 2px solid red; text-align: left; color:black; margin: 2px; z-index:9999; font-family:verdana;">';
      print '<pre>';
      @var_dump($var);
      print '</pre>';
      $sId = 'expose_'.uniqid();
      print '<span style="cursor: pointer; font-size: 12px;" onclick="document.getElementById(\''.$sId.'\').style.display = \'block\';"> + </span>';
      print '<div id="'.$sId.'" style="display:none; font-size: 12px;">'.$aTrace['file'].' : '.$aTrace['line'].'</div>';
      print '</div>';

    }
  }
  public static function pr($sText)
  {
    if(Request::is_ajax())
        return;

    if(defined('LOG_FILE_MODE') && LOG_FILE_MODE)
      $sMode = 'cli'; // but of a hack for scheduler
    else
      $sMode = php_sapi_name();

    switch($sMode)
    {
      case 'cli':
        print date('n/j/y H:i:s ').$sText."\n";
        return; // no need for html in cli
        break;
      default:
        if(!Request::$bDebugMode)
          return;
        $aTraces = debug_backtrace();

        foreach($aTraces as $aTrace)
        {
          if(isset($aTrace['class'], $aTrace['function']) && $aTrace['class'] == 'Debug' && $aTrace['function'] == 'pr')
            continue;
          break;
        }

        print '<br class="pr"><span style="font-family:verdana; background: white; color: black; font-family: verdana; font-size: 12px;">'.$sText.'</span>';
        $sId = 'pr_'.uniqid();
        print '<span style="cursor: pointer;" onclick="document.getElementById(\''.$sId.'\').style.display = \'block\';"> + </span>';
        print '<div id="'.$sId.'" style="display:none; font-size: 12px; font-family: verdana; background: white; color: black;">'.$aTrace['file'].' : '.$aTrace['line'].'</div>';
        break;
    }
  }

  public static function line()
  {
    if(defined('LOG_FILE_MODE') && LOG_FILE_MODE)
      $sMode = 'cli'; // but of a hack for scheduler
    else
      $sMode = php_sapi_name();

    switch($sMode)
    {
      case 'cli':
          $aTraces = debug_backtrace();
          foreach($aTraces as $aTrace)
          {
            if(isset($aTrace['file']) && strpos('_'.$aTrace['file'], 'debug.php'))
              continue;
            break;
          }

          print date('n/j/y H:i:s ').'hit line '.$aTrace['line'].' file: '.$aTrace['file']."\n";
  				return; // no need for html cli
        break;
      default:
        if((!Request::$bDebugMode) && false) //@TODO - remove hack
        {
          return;
        }
          $aTraces = debug_backtrace();
          foreach($aTraces as $aTrace)
          {
            //print "<br>hit debug.php line: ".__line__;
            if(isset($aTrace['class'], $aTrace['function']) && $aTrace['class'] == 'Debug' && $aTrace['function'] == 'line')
              continue;
            break;
          }
          print '<div style="color: black; background: white; font-family: verdana; font-size: 14px;">';
          print '<br>hit line '.$aTrace['line'];
          $sId = 'line_'.uniqid();
          print '<span style="cursor: pointer;" onclick="document.getElementById(\''.$sId.'\').style.display = \'block\';"> + </span>';
          print '<div id="'.$sId.'" style="display:none;">'.$aTrace['file'].'</div>';
          print '</div>';
          break;
    }
  }

  public static function expose_backtrace()
  {
    if(!Request::$bDebugMode)
      return;
    $aTmp = array();
    $aTraces = debug_backtrace();
    foreach($aTraces as $aTrace)
    {
  		if(isset($aTrace['file']))
      	$sTmp = 'File: '.$aTrace['file'].' Line: '.$aTrace['line'].' ';
      if(isset($aTrace['function']))
      {
        if(isset($aTrace['class']))
          $sTmp.= $aTrace['class'].'::';
        $sTmp.= $aTrace['function'].'(';
        if(isset($aTrace['args']) && count($aTrace['args']))
  			{
          try
  				{
  					$sTmp.= @json_encode($aTrace['args']);
  				}
  				catch(Exception $oE){}
  			}
        $sTmp.= ')';
      }
      $aTmp[] = $sTmp;
    }
    self::expose($aTmp);
  }

  public static function check_mem($sText = null)
  {
    $sText = 'Current Memory: '.number_format(memory_get_usage() / 1000000, 2).' MB ';
    $sText.= 'Peak Memory: '.number_format(memory_get_peak_usage() / 1000000, 2).' MB ';
    if(LOG_FILE_MODE)
      $sMode = 'cli'; // but of a hack for scheduler
    else
      $sMode = php_sapi_name();

    switch($sMode)
    {
      case 'cli':
        print $sText."\n";
        return; // no need for html in cli
        break;
      default:
        if(!Request::$bDebugMode)
          return;
        $aDebug = debug_backtrace();
        $aDebug = $aDebug[0];

        print '<br><span style="font-family:verdana;">'.$sText.'</span>';
        $sId = 'pr_'.uniqid();
        print '<span style="cursor: pointer;" onclick="document.getElementById(\''.$sId.'\').style.display = \'block\';"> + </span>';
        print '<div id="'.$sId.'" style="display:none;">'.$aDebug['file'].' : '.$aDebug['line'].'</div>';
        break;
    }
  }

  public static function check_time($sText = null)
  {
    if(!defined('FSTART'))
      define('FSTART', microtime(true));
    $sText = 'Total Time: '.number_format(microtime(true) - FSTART, 4);
  	  if(!isset($_REQUEST['fCheckTime']))
  		$_REQUEST['fCheckTime'] = FSTART;
    $sText.= ' Lap Time: '.number_format(microtime(true) - $_REQUEST['fCheckTime'], 4);

    if(defined('LOG_FILE_MODE') && LOG_FILE_MODE)
      $sMode = 'cli'; // but of a hack for scheduler
    else
      $sMode = php_sapi_name();

    switch($sMode)
    {
      case 'cli':
        print $sText."\n";
  			return; // no need for html in cli
        break;
      default:
        if(!Request::$bDebugMode)
          return;
        $aDebug = debug_backtrace();
        $aDebug = $aDebug[0];

        print '<br><span style="font-family:verdana;">'.$sText.'</span>';
        $sId = 'pr_'.uniqid();
        print '<span style="cursor: pointer;" onclick="document.getElementById(\''.$sId.'\').style.display = \'block\';"> + </span>';
        print '<div id="'.$sId.'" style="display:none;">'.$aDebug['file'].' : '.$aDebug['line'].'</div>';
        break;
    }
  	$_REQUEST['fCheckTime'] = microtime(true);
  }

  public static function stop($sMsg = null)
  {
    if(!Request::$bDebugMode)
      die($sMsg);

    if(class_exists('Permission'))
      Permission::$bChecked = true;

    $aTraces = debug_backtrace();
    foreach($aTraces as $aTrace)
    {
      //var_dump($aTrace);
      if(isset($aTrace['file']) && !strpos('_'.$aTrace['file'], 'debug.php'))
      {
        pr('stop() triggered @ File: '.$aTrace['file'].' Line: '.$aTrace['line']);
        die($sMsg);
      }
    }
    die($sMsg);
  }
}


function expose($sVar){Debug::expose($sVar);}
function pr($sText){Debug::pr($sText);}
function line(){
  Debug::line();}
function expose_backtrace(){Debug::expose_backtrace();}
function check_mem($sText = null){Debug::check_mem($sText);}
function check_time($sText = null){Debug::check_time($sText);}
function stop(){Debug::stop();}

// disabling for now
set_error_handler('my_error_handler');
set_exception_handler('my_exception_handler');

function my_error_handler($iErrNo, $sErrStr, $sErrFile, $iErrLine, $aErrContext)
{
  // todo - see if this is slowing down the site
  if(error_reporting() == 0 && $iErrNo == E_NOTICE)  // code was preceded with an @
    return;

  // make sure the log class is loaded
  require_once(CODE_PATH.'/classes/db.php');
  require_once(CODE_PATH.'/classes/auth.php');
  require_once(CODE_PATH.'/classes/model.php');
  require_once(CODE_PATH.'/classes/log.php');
  switch($iErrNo)
  {
    case E_WARNING: //2
      $sErrorType = 'Warning';
      break;
    case E_NOTICE: // 8
      $sErrorType = 'Notice';
      break;
    case E_STRICT: // 2048
      return; // disable for now
      $sErrorType = 'Strict';
      break;
    case E_RECOVERABLE_ERROR: //4096
      $sErrorType = 'Recoverable';
      break;
    default:
      expose($iErrNo);
      $sErrorType = 'Unknown';
  }

    $aParams = array(
      'sMsg' => $sErrStr,
      'sFile' => str_replace(CODE_PATH, '', $sErrFile),
      'iLine' => $iErrLine,
      'iTypeId' => Log::TYPE_PHP_ERROR,
      'iPriorityId' => Log::PRIORITY_CRITICAL,
      'sCat' => $iErrNo,
      );

    Log::error($aParams);

}

function my_exception_handler($oE)
{
  pr('my_exception_handler()');

  expose($oE->getMessage());
  expose_backtrace();
  if(Request::$bDebugMode)
    stop();
}
