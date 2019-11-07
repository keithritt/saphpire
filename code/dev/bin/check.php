<?
$sDir = $argv[1];
define(DIR, $sDir);
$sCodeEnv = explode('/', str_replace('/public_html/', '', __file__));
$sCodeEnv = $sCodeEnv[0];

set_time_limit(0);
date_default_timezone_set('America/Chicago');
define('APPPATH', '/home3/oloop/public_html/'.$sCodeEnv.'/application/');

define('ENVIRONMENT', $sDbEnv);
require_once(APPPATH.'/libraries/autoload.php');

$_REQUEST['aFiles'] = array();
scan_dir(PUBLIC_HTML_PATH."/".DIR);

asort($_REQUEST['aFiles']);

$iOldestTs = current($_REQUEST['aFiles']) + 5;

$_REQUEST['aFiles'] = array_reverse($_REQUEST['aFiles'], true);

foreach($_REQUEST['aFiles'] as $sFile => $iTs)
{
  if($iTs > $iOldestTs)
    print $sFile.' last updated '.date(DATETIME_FORMAT_SHORT, $iTs)."\n";
}

function scan_dir($sPath)
{
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

  $aFiles = scandir($sPath);
  foreach($aFiles as $sFile)
  {
    if(in_array($sFile, $aSkipDirs))
      continue;

    $sFile = $sPath.'/'.$sFile;

    if(in_array($sFile, $aSkipDirs))
      continue;

    if(is_dir($sFile))
    {
      scan_dir($sFile);
    }
    else
    {
      $iTs = @filemtime($sFile);
      if($iTs)
        $_REQUEST['aFiles'][$sFile] = $iTs;
    }
  } 
}
