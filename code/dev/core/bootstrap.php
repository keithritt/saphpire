<?
ob_start();


require_once('autoload.php');

//line();
//stop();

//My::_init_var('oMaster');
// currently DB::$oMaster is very commonly referenced  - need to decide on a preferred method
Auth::init();
//line();

Db::static_init('master');
//line();
//Db::_static_init('barmend');
Session::init();

//var_dump($sTmpDomain);
Request::init($sTmpDomain);
unset($sTmpDomain);
//Session::upsert_record();

//line();
///stop();
Debug::init();
//line();
//stop();

Login::init();

//line();




//line();

$aArgs = array();


//expose($_SERVER);
//stop();

$sRequestUri = strtolower($_SERVER['REQUEST_URI']);
$sRequestUri = ltrim($sRequestUri, '/');
$iPos = strpos($sRequestUri, '?');
//expose($iPos);
if(is_int($iPos))
{
  //line();
  $sRequestUri = substr($sRequestUri, 0, $iPos);
}

//expose($sRequestUri);

if($sRequestUri != '')
{



	$aArgs = explode('/', $sRequestUri);

	//expose($aArgs);

	$sController = $aArgs[0];

	if(isset($aArgs[1]) && trim($aArgs[1]) != '')
		$sMethod = $aArgs[1];
	//else/
	//	$sMethod = 'default';

	unset($aArgs[0], $aArgs[1]);

  //line();



}


//expose($sController);

if(!isset($sController))
{
  //expose(Session::fetch_login());
  //@TODO - allow websites to override this logic
  if(Login::$bLoggedIn)
    $sController = 'home';
  else
    $sController = 'homepage';
}

//expose($sController);


if(!isset($sMethod))
  $sMethod = 'init';

//expose($sController);
//expose($sMethod);

//stop();

require_once(CODE_PATH.'/core/controllers/master.php');





if(file_exists(CODE_PATH.'/custom/domains/'.DOMAIN.'/controllers/domain.php'))
{
  //line();
  //pr(CODE_PATH.'/custom/domains/'.DOMAIN.'/controllers/domain.php');
  require_once(CODE_PATH.'/custom/domains/'.DOMAIN.'/controllers/domain.php');
  //line();
}
else
{
  //line();
  require_once(CODE_PATH.'/core/controllers/domain.php');
}

if(method_exists('Domain', 'bootstrap'))
  Domain::bootstrap($sController, $sMethod, $aArgs);
else
{
  //line();
  $aControllerFiles = array(
    'custom_folder' => CODE_PATH.'/custom/domains/'.DOMAIN.'/controllers/'.$sController.'/'.$sMethod.'.php',
    'custom_default' => CODE_PATH.'/custom/domains/'.DOMAIN.'/controllers/'.$sController.'.php',
    'core_folder' => CODE_PATH.'/core/controllers/'.$sController.'/'.$sMethod.'.php',
    'core_default' => CODE_PATH.'/core/controllers/'.$sController.'.php',
    'unknown' =>  null,
  );

  //expose($aControllerFiles);

  foreach($aControllerFiles as $sType => $sFile)
  {
    //pr($sFile);
    if($sType == 'unknown') // @TODO - temp hack - fix
      Log::Error('Unknown Controller');

    if(file_exists($sFile))
    {
      // check for a group class
      switch($sType)
      {
        case 'custom_folder':
        //@TODO - combine this with the core_folder logic to avoid duplicate code - currently no examples of that
          $sGroupFile = CODE_PATH.'/custom/domains/'.DOMAIN.'/controllers/'.$sController.'/group.php';
          //expose($aArgs);
          $sController = $sMethod; // not sure if this has any benefit after the file has been determined

          if(isset($aArgs[2]) && trim($aArgs[2]) != '')
          {
            $sMethod = $aArgs[2];
            unset($aArgs[2]);
          }
          else
            $sMethod = 'init';

          //expose($sController);
          //expose($sMethod);
          break;
        default:
          $sGroupFile = null;
      }

      if(isset($sGroupFile) && file_exists($sGroupFile))
        require_once($sGroupFile);

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