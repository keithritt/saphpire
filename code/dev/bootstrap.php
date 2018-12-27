<?
ob_start();

require_once('autoload.php');

Db::static_init('master');
Session::init();
Request::init();
Session::upsert_record();
Debug::init();
Auth::init();

$aArgs = array();

$sRequestUri = strtolower($_SERVER['REQUEST_URI']);
$sRequestUri = ltrim($sRequestUri, '/');

if($sRequestUri != '')
{
	$iPos = strpos($sRequestUri, '?');
	if($iPos)
		$sRequestUri = substr($sRequestUri, 0, $iPos);

	$aArgs = explode('/', $sRequestUri);

	$sController = $aArgs[0];

	if(isset($aArgs[1]))
		$sMethod = $aArgs[1];

	unset($aArgs[0], $aArgs[1]);
}

if(!isset($sController))
{
  if((Session::fetch_login()))
    $sController = 'home';
  else
    $sController = 'homepage';
}

if(!isset($sMethod))
  $sMethod = 'init';

require_once(CODE_PATH.'/controllers/master.php');

if(file_exists(CODE_PATH.'/domains/'.DOMAIN.'/controllers/domain.php'))
  require_once(CODE_PATH.'/domains/'.DOMAIN.'/controllers/domain.php');
else
  require_once(CODE_PATH.'/controllers/domain.php');

if(method_exists('Domain', 'bootstrap'))
  Domain::bootstrap($sController, $sMethod, $aArgs);
else
{
  $aControllerFiles = array(
    CODE_PATH.'/domains/'.DOMAIN.'/controllers/'.$sController.'.php',
    CODE_PATH.'/controllers/'.$sController.'.php',
    'unknown',
  );

  foreach($aControllerFiles as $sFile)
  {
    if($sFile == 'unknown') // @TODO - temp hack - fix
      Log::Error('Unknown Controller');

    if(file_exists($sFile))
    {
      require_once($sFile);
      Master::launch($sMethod, $aArgs);
      break;
    }
  }
}