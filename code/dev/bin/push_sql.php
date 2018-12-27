<?

require_once('runsql.php');

if(php_sapi_name() == 'cgi-fcgi') // web
{
    //pr(time());
    $argv = array();
    if(isset($_REQUEST['args'] ))
    {
      foreach($_REQUEST['args'] as $iKey => $sVal)
        $argv[$iKey + 1] = $sVal;
    }
}

$iBuildId = $argv[1];
$sEnv = strtolower($argv[2]);
$sMode = strtolower($argv[3]);
if(isset($argv[4]))
	$sCodeEnv = strtolower($argv[4]);
else
	$sCodeEnv = $sEnv;

//var_dump($argv);



//$sEnv = 'dev'; // temp hack

define('APPPATH', '/home3/oloop/public_html/'.$sEnv.'/application/');
define('ENVIRONMENT', $sEnv);
define('DIE_ON_SQL_ERROR', false); // setting to false to allow invalid sql to not trigger die()

//print "\n hit push sql.php line ".__line__;
require_once(APPPATH.'/libraries/autoload.php');
//print "\n hit push sql.php line ".__line__;

if($sMode == 'full')
	$aModes = array('revert', 'apply');
else
	$aModes = array($sMode);

//expose($aModes);
//stop();

$iTotalErrorCount = 0;

foreach($aModes as $sMode)
{

	$oRunSql = new RunSql($iBuildId, $sEnv, $sMode);

	$sSqlFile = PUBLIC_HTML_PATH.'/'.$sCodeEnv.'/deployment/'.$iBuildId.'/sql.php';


	if(file_exists($sSqlFile))
	  require($sSqlFile);
	else
	  die("file not found: $sSqlFile \n");


	//print "\n hit push sql.php line ".__line__;

	$oRunSql->run();

	pr('Error Count: '.$oRunSql->iErrorCount);

	$iTotalErrorCount+= $oRunSql->iErrorCount;
}

pr('total error count: '.$iTotalErrorCount);

pr('end push_sql.php');