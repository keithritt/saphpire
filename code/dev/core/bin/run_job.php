<?

//pr('run_job.ph');

//require_once('runsql.php');

//ini_set('error_reporting', E_ALL);

$sDbEnv = null;

//expose($_REQUEST);



if(php_sapi_name() == 'cgi-fcgi') // web
{
		$argv = array();
    if(isset($_REQUEST['args'] ))
    {
  		foreach($_REQUEST['args'] as $iKey => $sVal)
  			$argv[$iKey + 1] = $sVal;
    }
    //else
      //print

		//$sFile = $_REQUEST['args'];
		//$sDbEnv = $_REQUEST['db_env'];
}
//else

//@TODO - determine env from file path is argv2 is not set
{
    if(isset($argv[1]))
		  $sFile = $argv[1];
    else
      print_help();

    //die();
    //var_dump($argv[2]);
    //var_dump($argv);
    //die();
    if(isset($argv[2]))
		  $sDbEnv = strtolower($argv[2]);
}

//var_dump($sFile);
//var_dump($sDbEnv);

//die();

//$sMode = strtolower($argv[3]);

//$oRunSql = new RunSql($iBuildId, $sEnv, $sMode);

//$sEnv = 'dev'; // temp hack

$sCodeEnv = explode('/', str_replace('/home3/oloop/public_html/', '', __file__));
//expose($sCodeEnv);
$sCodeEnv = $sCodeEnv[2];


$sDbEnv = Util::coalesce($sDbEnv, $sCodeEnv);

//expose($sDbEnv);


//define('APPPATH', '/home3/oloop/public_html/'.$sCodeEnv.'/application/');
//define('ENVIRONMENT', $sDbEnv);

//require_once(APPPATH.'/libraries/autoload.php');

$sFile.= '.php'; //@TODO - check for duplicate .php extensions
//expose($sFile);

//print $sFile;
//die();

//$sSqlFile = PUBLIC_HTML_PATH.'/'.$sEnv.'/deployment/'.$iBuildId.'/sql.php';
if(file_exists($sFile))
  require_once($sFile);
else
{
  pr('file not found');
  expose($sFile);
  print_help();
}

function print_help()
{
  print('<br>sample script to run data populator: <br>php bin/run_job.php /home3/oloop/public_html/dev/deployment/5/data_populator dev full');
  print('<br>or via web: http://officialloop.com/dev/bin/view.php?file=run_job.php&args[]=/home3/oloop/public_html/dev/deployment/5/data_populator&args[]=dev&args[]=full');
  print '<br><br>';
  print('http://dev.officialloop.com/bin/view.php?file=push_sql.php&args[]=5&args[]=dev&args[]=apply');
  $sMsg = @"file not found: $sFile \n";
  die($sMsg);
}




