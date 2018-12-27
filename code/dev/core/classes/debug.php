<?
//neatly formats a var_dump

//note php_sapi_name(): 'cgi-fcgi' is for web and cron



class Debug
{
  public static $bStopped = false;
  public static $bEmailMode = false;
  public static $sEmailAdr;
  public static $bIsAjax;

  public static function init()
  {
    if(Request::$bDebugMode)
    {
      error_reporting(E_ALL);
    }
    //else
    //  self::$bDebugMode = false;

  }

  public static function expose($vVar)
  {
    //print "\n expose()";
  	if(Request::is_ajax())
    {
     // line();
    		return;
    }
    //else
    // line();

    if(is_string($vVar))
    {
      //line();
      $vVar = str_replace('<', '{', $vVar);
      $vVar = str_replace('>', '}', $vVar);
    }
    //else
    //  line();


    //print 'expose()';
    //var_dump(ENVIRONMENT);
    //var_dump(ENV);

    if(defined('LOG_FILE_MODE'))
      $sMode = 'cli'; // bit of a hack for scheduler
    else
      $sMode = php_sapi_name();

    switch($sMode)
    {
      case 'cli':
        var_dump($vVar);
        $aDebug = debug_backtrace();
        $aDebug = $aDebug[0];
        print 'hit line '.$aDebug['line'].' file: '.$aDebug['file']."\n";
  			return; // no need for html in cli
        break;
      default:
        //var_dump($_SESSION);
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
      print '<pre><code>';
      @var_dump($vVar);
      print '</code></pre>';
      $sId = 'expose_'.uniqid();
      print '<span style="cursor: pointer; font-size: 12px;" onclick="document.getElementById(\''.$sId.'\').style.display = \'block\';"> + </span>';
      print '<div id="'.$sId.'" style="display:none; font-size: 12px;">'.Util::array_isset($aTrace, 'file', 'unknown file').
            ' : '.Util::array_isset($aTrace, 'line', 'unknown line').'</div>';
      print '</div>';

    }
  }


  public static function pr($sText)
  {
    //print "\npr()";
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
        //die();
        //$aDebug = $aDebug[0];

        print '<br class="pr"><span style="font-family:verdana; background: white; color: black; font-family: verdana; font-size: 12px; padding: 10px; spacing: 10px;">'.$sText.'</span>';
        $sId = 'pr_'.uniqid();
        print '<span style="cursor: pointer;" onclick="document.getElementById(\''.$sId.'\').style.display = \'block\';"> + </span>';
        print '<div id="'.$sId.'" style="display:none; font-size: 12px; font-family: verdana; background: white; color: black;">'.$aTrace['file'].' : '.$aTrace['line'].'</div><br>';
        break;
    }
  }

  public static function line()
  {
    //print "\nDebug::line()";
    //var_dump(Request::$bDebugMode);

    //print "\nLOG_FILE_MODE = ";

    //var_dump(LOG_FILE_MODE);

  	//print php_sapi_name();

    //var_dump(defined('LOG_FILE_MODE'));

   // die();

    if(defined('LOG_FILE_MODE') && LOG_FILE_MODE)
      $sMode = 'cli'; // but of a hack for scheduler
    else
      $sMode = php_sapi_name();

    //print "\n hit line ".__line__;

    //var_dump($sMode);

    //die();
    switch($sMode)
    {
      case 'cli':
          //$aDebug = debug_backtrace();
          //$aDebug = $aDebug[0];
          // @TODO - add microtime
          $aTraces = debug_backtrace();
          foreach($aTraces as $aTrace)
          {
            //var_dump($aTrace);
            //print "<br>hit debug.php line: ".__line__;
            if(isset($aTrace['file']) && strpos('_'.$aTrace['file'], 'debug.php'))
              continue;
            break;
          }

          print date('n/j/y H:i:s ').'hit line '.$aTrace['line'].' file: '.$aTrace['file']."\n";
          //die();
  				return; // no need for html cli
        break;
      default:

        //var_dump(Request::$bDebugMode);
        //print "<br>hit debug.php line: ".__line__;
        if(!Request::$bDebugMode)
        {
          //print "<br>hit debug.php line: ".__line__;
          return;
        }

        //var_dump(Request::$bIsAjax);

        if(Request::is_ajax())
          return;
        //else
         // print "<br>hit debug.php line: ".__line__;

          $aTraces = debug_backtrace();
          foreach($aTraces as $aTrace)
          {
            //print "<br>hit debug.php line: ".__line__;
            if(isset($aTrace['class'], $aTrace['function']) && $aTrace['class'] == 'Debug' && $aTrace['function'] == 'line')
              continue;
            break;
          }
          //print "<br>hit debug.php line: ".__line__;
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
    //print "\n expose_backtrace()";
    if(!Request::$bDebugMode)
      return;
    $aTmp = array();
    $aTraces = debug_backtrace();
    foreach($aTraces as $aTrace)
    {
  		//var_dump($aTrace);
  		if(isset($aTrace['file']))
      	$sTmp = 'File: '.$aTrace['file'].' Line: '.$aTrace['line'].' ';
  		//else
  	//	{
  	//		line();
    //			expose($aTrace);
    //			die();//
    //		}
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
    //unset($aDebug[0]);
    self::expose($aTmp);
    //die();
  }

  public static function expose_html($vHtml)
  {
    //print '<pre><textarea style="width:98%; height:200px;">'.$sHtml.'</textarea></pre>';

      $aTraces = debug_backtrace();
      foreach($aTraces as $aTrace)
      {
        if(isset($aTrace['file']) && strpos('_'.$aTrace['file'], 'debug.php'))
        //if(isset($aTrace['class'], $aTrace['function']) && $aTrace['class'] == 'Debug' && $aTrace['function'] == 'expose')
          continue;

        break;
      }

      if(is_array($vHtml))
      {
        foreach($vHtml as $sHtml)
          print self::expose_html($sHtml);

        return;
      }



      print '<div style="background: white; border: 2px solid red; text-align: left; color:black; margin: 2px; z-index:9999; font-family:verdana;">';
      print '<pre>';
      print '<textarea style="width:98%; height:200px;">'.$vHtml.'</textarea>';
      print '</pre>';
      $sId = 'expose_'.uniqid();
      print '<span style="cursor: pointer; font-size: 12px;" onclick="document.getElementById(\''.$sId.'\').style.display = \'block\';"> + </span>';
      print '<div id="'.$sId.'" style="display:none; font-size: 12px;">'.$aTrace['file'].' : '.$aTrace['line'].'</div>';
      print '</div>';
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
      //case 'cgi-fcgi':
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
    //expose(FSTART);
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
  		//case 'cgi-fcgi':
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
    //hit();
    self::$bStopped = true;
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

  public static function hit()
  {
    //pr('fire()');
    $aDebug = debug_backtrace();




    self::pr($aDebug[2]['class'].'->'.$aDebug[2]['function'].'()');
    if(isset($aDebug[2]['args']))
      self::expose($aDebug[2]['args']);
    //print '<pre>';
    //var_dump($sArgs);
    //var_dump($aDebug[2]);
    //die();
  }

  public static function email_start($sEmail)
  {
    hit();
    //$bRes = mail('simplifiedweb@gmail.com', 'test', 'test');
    //pr('mail() result = ');
    //expose($bRes);
    //die();
    if(!Request::$bDebugMode)
      return;
    self::$bEmailMode = true;
    self::$sEmailAdr = $sEmail;
    ob_start();
  }

  public static function email_end()
  {
    hit();
    if(!Request::$bDebugMode)
      return;

    $sMsg = '<html><body>';
    $sMsg.= ob_get_flush();
    $sMsg.= '</body</html>';

    $sHeaders = "MIME-Version: 1.0\r\n";
    $sHeaders.= "Content-Type: text/html; charset=ISO-8859-1\r\n";
    expose(self::$sEmailAdr);
    mail(self::$sEmailAdr, 'debug data', $sMsg, $sHeaders);
    self::$bEmailMode = false;
  }
}


function expose($sVar){Debug::expose($sVar);}
function pr($sText){Debug::pr($sText);}
function line(){Debug::line();}
function hit(){Debug::hit();}
function expose_backtrace(){Debug::expose_backtrace();}
function expose_html($sHtml){Debug::expose_html($sHtml);}
function check_mem($sText = null){Debug::check_mem($sText);}
function check_time($sText = null){Debug::check_time($sText);}
function stop($sMsg = null){Debug::stop($sMsg);}

// disabling for now
set_error_handler('my_error_handler');
set_exception_handler('my_exception_handler');

function my_error_handler($iErrNo, $sErrStr, $sErrFile, $iErrLine, $aErrContext)
{

  //expose(error_reporting());
  // todo - see if this is slowing down the site
  if(error_reporting() == 0 && $iErrNo == E_NOTICE)  // code was preceded with an @
    return;

    //print '<br>my_error_handler('.$sErrStr.')';

  //pr('my_error_handler('.$iErrNo.', '.$sErrStr.', '.$sErrFile.', '.$iErrLine.')');

  //print '<br>my_error_handler()';
  // make sure the log class is loaded
  //line();
  require_once(CODE_PATH.'/core/classes/db.php');
  //line();
  require_once(CODE_PATH.'/core/classes/auth.php');
  //line();
  require_once(CODE_PATH.'/core/classes/model.php');
  //line();
  require_once(CODE_PATH.'/core/classes/log.php');
  //line();
  //require_once(CLASS_PATH.'libraries/universal_definitions.php');
  //require_once(CLASS_PATH.'libraries/universal_functions.php');

  //var_dump($iErrNo);

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

  //line();


  //expose($iErrNo);
  //expose($sErrStr);
  //expose($sErrFile);
  //expose($iErrLine);
  //expose($aErrContext);

    $aParams = array(
      'msg' => $sErrStr,
      'file' => str_replace(CODE_PATH, '', $sErrFile),
      'line' => $iErrLine,
      'type_id' => Log::TYPE_PHP_ERROR,
      'priority_id' => Log::PRIORITY_CRITICAL,
      'cat' => $iErrNo,
      );

    //line();

    Log::error($aParams);
    //line();

  //print '<br>';



}

function my_exception_handler($oE)
{
  pr('my_exception_handler()');



  Log::error($oE->getMessage());

  //expose($oE->getMessage());
  //expose_backtrace();
  //if(Request::$bDebugMode)
  //  stop();
}

//asdf();

//$asdf = $asdf[4];

