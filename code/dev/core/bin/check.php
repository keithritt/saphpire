<?




$sDir = $argv[1];

define(DIR, $sDir);

//var_dump($argv);
//die();

$sCodeEnv = explode('/', str_replace('/home3/oloop/public_html/', '', __file__));
$sCodeEnv = $sCodeEnv[0];



set_time_limit(0);
date_default_timezone_set('America/Chicago');
define('APPPATH', '/home3/oloop/public_html/'.$sCodeEnv.'/application/');

define('ENVIRONMENT', $sDbEnv);
require_once(APPPATH.'/libraries/autoload.php');
//die();

$_REQUEST['aFiles'] = array();
scan_dir(PUBLIC_HTML_PATH."/".DIR);

asort($_REQUEST['aFiles']);

$iOldestTs = current($_REQUEST['aFiles']) + 5;
//expose($iOldestTs);

$_REQUEST['aFiles'] = array_reverse($_REQUEST['aFiles'], true);
//expose($_REQUEST['aFiles']);

foreach($_REQUEST['aFiles'] as $sFile => $iTs)
{
  if($iTs > $iOldestTs)
    print $sFile.' last updated '.date(DATETIME_FORMAT_SHORT, $iTs)."\n";
}

function scan_dir($sPath)
{
  //print "scan_dir($sPath)\n";

  $aSkipDirs = array
  (
    '.', 
    '..',
    PUBLIC_HTML_PATH.'/'.DIR.'/user_guide',
    PUBLIC_HTML_PATH.'/'.DIR.'/application/img',
    PUBLIC_HTML_PATH.'/'.DIR.'/application/js/ext',
    PUBLIC_HTML_PATH.'/'.DIR.'/application/js/tiny_mce',
    PUBLIC_HTML_PATH.'/'.DIR.'/application/js/scriptaculous',
    PUBLIC_HTML_PATH.'/'.DIR.'/application/bootstrap',

  );

  //expose($aSkipDirs);
  //die();

  $aFiles = scandir($sPath);
  //unset($aFiles[0], $aFiles[1]);
  foreach($aFiles as $sFile)
  {
    if(in_array($sFile, $aSkipDirs))
      continue;

    $sFile = $sPath.'/'.$sFile;

    if(in_array($sFile, $aSkipDirs))
      continue;

    //print "$sFile \n";
    //die();

    if(is_dir($sFile))
    {
      //$aStat = stat($sFile);
      scan_dir($sFile);
    }
    else
    {
      $iTs = @filemtime($sFile);
      if($iTs)
        $_REQUEST['aFiles'][$sFile] = $iTs;
      //expose($_REQUEST['aFiles']);
      //die();
    }

    //expose($sTs);
    //die();
  } 

  //


}