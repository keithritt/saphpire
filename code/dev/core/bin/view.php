<?

$bSkipBootstrap = true;

require_once('../../../../index.php');
define('SAPHPIRE_PATH', str_replace('code/'.CODE_ENV.'/core/bin', '', getcwd()));
//print SAPHPIRE_PATH;
//print 'asdf';
//die();

//var_dump(getcwd());
require_once('../autoload.php');

//line();



//this is copied from bootstrap.php - need to consolidate this logic
Auth::init();
Db::static_init('master');
Session::init();

//expose($_REQUEST);

//var_dump($sTmpDomain);
Request::init($sTmpDomain);
unset($sTmpDomain);
//Session::upsert_record();

//line();
///stop();
Debug::init();
//line();
//stop();
Auth::init();




//if(isset($_POST['bAllowViewPw']))
//  Auth::check_allow_view_pass($_POST['bAllowViewPw']);

//expose($_COOKIE);
//$bAllowView = @Util::coalesce($_POST['bAllowView'], $_SESSION['bAllowView'], $_COOKIE['bAllowView'], (CODE_ENV == 'prod'));

//var_dump($bAllowView);


if(Session::get('bAllowView') || true)
{
  //print '<br>index.php';


  //var_dump($aFiles);
  //print '<br><br>';

  if(isset($_REQUEST['file']))
  {
    $sFile = urldecode($_REQUEST['file']);
    $aWhiteList = array(
        'run_job.php',
        'push_sql.php',
        'oragami_box.php');
    if(!in_array($sFile, $aWhiteList))
      print '<br> file must be white listed to be run via apache <a href="view.php">back</a>';
    else
    {
      //line();
      //var_dump($sFile);
      //expose($sFile);
      include($sFile);
    }
  }
  else
  {
    $aFiles = scandir('.');
    unset($aFiles[0], $aFiles[1], $aFiles[array_search('.htaccess', $aFiles)]);
    foreach($aFiles as $sFile)
    {
      print '<a href="view.php?file='.urlencode($sFile).'">'.$sFile.'</a><br>';
    }
  }
}
else
{
  print '<form method="post"><input type="password" name="bAllowViewPw" autocomplete="off" autofocus><input type="submit" value="go"></form>';
  //stop();
}
