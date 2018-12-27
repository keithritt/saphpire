<?
ob_start();


require_once('autoload.php');

//line();
//stop();

Db::static_init('master');
Session::init();

Request::init();
Session::upsert_record();

//line();
///stop();
Debug::init();
//line();
//stop();
Auth::init();




//line();

$aArgs = array();


//expose($_SERVER);
//stop();

$sRequestUri = strtolower($_SERVER['REQUEST_URI']);
$sRequestUri = ltrim($sRequestUri, '/');
//var_dump($sRequestUri);

if($sRequestUri != '')
{

	$iPos = strpos($sRequestUri, '?');
	//var_dump($iPos);
	if($iPos)
		$sRequestUri = substr($sRequestUri, 0, $iPos);

	//var_dump($sRequestUri);

	$aArgs = explode('/', $sRequestUri);

	//var_dump($aArgs);

	$sController = $aArgs[0];

	if(isset($aArgs[1]))
		$sMethod = $aArgs[1];
	//else/
	//	$sMethod = 'default';

	unset($aArgs[0], $aArgs[1]);

  //line();



}

if(!isset($sController))
{
  //expose(Session::fetch_login());
  if((Session::fetch_login()))
    $sController = 'home';
  else
    $sController = 'homepage';
}

if(!isset($sMethod))
  $sMethod = 'init';

//stop();

require_once(CODE_PATH.'/controllers/master.php');

if(file_exists(CODE_PATH.'/domains/'.DOMAIN.'/controllers/domain.php'))
  require_once(CODE_PATH.'/domains/'.DOMAIN.'/controllers/domain.php');
else
  require_once(CODE_PATH.'/controllers/domain.php');

if(method_exists('Domain', 'bootstrap'))
  Domain::bootstrap($sController, $sMethod, $aArgs);
else
{
  //line();
  $aControllerFiles = array(
    CODE_PATH.'/domains/'.DOMAIN.'/controllers/'.$sController.'.php',
    CODE_PATH.'/controllers/'.$sController.'.php',
    'unknown',
  );

  foreach($aControllerFiles as $sFile)
  {
    //pr($sFile);
    if($sFile == 'unknown') // @TODO - temp hack - fix
      Log::Error('Unknown Controller');

    if(file_exists($sFile))
    {
      //pr('file_exists');
      require_once($sFile);
      Master::launch($sMethod, $aArgs);
      break;
    }
    //else
    //  pr('file does not exist');
  }

  //Log::error('Unknown Controller');
  /*

  if(file_exists(CODE_PATH.'/domains/'.DOMAIN.'/'.$sController.'.php'))
  {
    line();
    require_once(CODE_PATH.'/domains/'.DOMAIN.'/'.$sController.'.php');

    break;
    //$sController = 'Controller';
  }
  elseif(file_exists(CODE_PATH.'/controllers/'.$sController.'.php'))
  {
    line();
    pr(CODE_PATH.'/controllers/'.$sController.'.php');
    require_once(CODE_PATH.'/controllers/'.$sController.'.php');
    //$sController = 'Controller';
  }
  else
  {
    line();
    pr('hit else');
  }

  //$sController = ucfirst($sController);


  //print '<br>controller = '.$sController;
  //print '<br>method = '.$sMethod;
  //print '<br>';
  //print '<br>method = '.

  //var_dump($aArgs);

  //line();

  Master::launch($sMethod, $aArgs);

  //line();
  */
}