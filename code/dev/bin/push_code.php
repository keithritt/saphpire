<?
if(php_sapi_name() != 'cli')
  die('script can only be run in command line mode');

// @TODO - not sure if i like this
if(getcwd() != '/home3/oloop/public_html/saphpire/code/dev/bin')
  die('script can only be run from: /home3/oloop/public_html/saphpire/code/dev/bin');


define('CODE_ENV', 'dev');

//var_dump(getcwd());



define('SAPHPIRE_PATH', str_replace('code/'.CODE_ENV.'/bin', '', getcwd()));
//print SAPHPIRE_PATH;

//var_dump(SAPHPIRE_PATH);
//print 'asdf';
//die();
require_once(SAPHPIRE_PATH.'/code/'.CODE_ENV.'/autoload.php');



if(!isset($argv[2]))
  die("\n format should be: php push_code.php {build_id} {env} \n");

$iBuildId = $argv[1];
$sEnv = $argv[2];

if(!isset($iBuildId))
  die("\n format should be: php push_code.php {build_id} {env} \n");

line();


switch($sEnv)
{
  case 'beta':
  case 'zak':
  case 'prod':
    break;
  default:
    die("\n format should be: php push_code.php {build_id} {env} \n");
}

line();

//stop();

// execute sql

// create zip file
if($sEnv == 'prod' || $sEnv == 'beta')
{
  line();
  $sCmd = "zip -r ".SAPHPIRE_PATH."/tmp/version_zips/".$iBuildId.".zip ".SAPHPIRE_PATH."/code/dev/";
  var_dump($sCmd);
  line();
  //stop();
  passthru($sCmd, $aRet);
  var_dump($aRet);
}

stop();

// copy dev to tmp_$sEnv
$sCmd = "cp -r ".PUBLIC_HTML_PATH."/dev/. ".PUBLIC_HTML_PATH."/tmp_".$sEnv;
var_dump($sCmd);
passthru($sCmd, $aRet);
var_dump($aRet);

// for zak make backup copy of auth file
if($sEnv == 'zak')
{
  $sCmd = "cp ".PUBLIC_HTML_PATH."/".$sEnv."/application/libraries/authentication.php ".PUBLIC_HTML_PATH."/tmp_".$sEnv."/application/libraries/authentication.php";
  var_dump($sCmd);
  passthru($sCmd, $aRet);
  var_dump($aRet);
}

// delete $sEnv
$sCmd = "rm -rf ".PUBLIC_HTML_PATH."/".$sEnv;
var_dump($sCmd);
passthru($sCmd, $aRet);
var_dump($aRet);

// rename tmp_$sEnv to $sEnv
$sCmd = "mv ".PUBLIC_HTML_PATH."/tmp_".$sEnv."/ ".PUBLIC_HTML_PATH."/".$sEnv;
var_dump($sCmd);
passthru($sCmd, $aRet);
var_dump($aRet);

print "\n script completed in ".(time() - $iStart)." seconds \n";

