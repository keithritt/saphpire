<?

//@TODO - possibly move these definitions to classes
define('START_TIME', microtime(true));
//var_dump(getcwd());
//die();
//define('CODE_PATH', 'code/'.CODE_ENV);
if(!defined('SAPHPIRE_PATH'))
  define('SAPHPIRE_PATH', getcwd());
define('CODE_PATH', SAPHPIRE_PATH.'/code/'.CODE_ENV);
define('DB_ENV', CODE_ENV);
define('SCHEMA_PREFIX', 'oloop_');
define('PROTOCOL', 'http');
if(isset($_SERVER['SERVER_NAME']))
  define('BASE_URL', PROTOCOL.'://'.$_SERVER['SERVER_NAME']); // note: no trailing /

define('DATETIME_FORMAT_SHORT', 'n/j/y g:i a');
define('DATE_FORMAT_SHORT', 'n/j/y');
define('DATE_FORMAT_SHORT2', 'n/j/Y'); // full year
define('TIME_FORMAT_SHORT', 'g:i a');

if(isset($_SERVER['SERVER_NAME']))
{
  //print "\n hit line ".__line__;
  $aTmp = explode('.', strtolower($_SERVER['SERVER_NAME']));
  switch(count($aTmp))
  {
    case 2:
      // assume production
      define('URL_PREFIX', '');
      define('DOMAIN', $aTmp[0].'.'.$aTmp[1]);
      break;
    case 3:
      if($aTmp[0] == 'www')
        define('URL_PREFIX', ''); // defaulting to omit the www to avoid needing mulitple SSL certificates for a domain
      else
        define('URL_PREFIX', $aTmp[0].'.');
      define('DOMAIN', $aTmp[1].'.'.$aTmp[2]);
      break;
  }
}
else
{
  //print "\n hit line ".__line__;
  define('URL_PREFIX', ENV.'.');
  //print "\n hit line ".__line__;
  define('DOMAIN', 'keithritt.net');
  //print "\n hit line ".__line__;
  define('DOMAIN_ID', 0);
}

// not sure how much i like this method
define('MASTER_SCHEMA', SCHEMA_PREFIX.'master'.'_'.DB_ENV);

define('DELIMITER', ':|:');
define('DELIMITER_LENGTH', strlen(DELIMITER));


if(isset($_SERVER['REQUEST_URI']))
  define('REQUEST_URL', $_SERVER['REQUEST_URI']);
//tmp hack to detect coalsce()
function coalesce()
{
  Log::deprecated_code('coalesce()');
}

//print "<br>hit line : ".__line__;
//die();

// tmp
//ini_set('memory_limit', '20M');

//print "<br>hit line : ".__line__;
//die();

//var_dump(CODE_PATH);


//@TODO - move autoloading to controllers
require_once(CODE_PATH.'/classes/session.php');

//print "<br>hit line : ".__line__;
//die();


require_once(CODE_PATH.'/classes/request.php');

require_once(CODE_PATH.'/classes/debug.php');

//print "<br>hit line : ".__line__;
//die();

//line();

//die();

//var_dump(ASDF);
//stop();
require_once(CODE_PATH.'/classes/util.php');
require_once(CODE_PATH.'/classes/auth.php');

require_once(CODE_PATH.'/classes/db.php');
require_once(CODE_PATH.'/classes/form.php');
require_once(CODE_PATH.'/classes/log.php');
require_once(CODE_PATH.'/classes/model.php');
require_once(CODE_PATH.'/config/types.php');
require_once(CODE_PATH.'/classes/perm.php');
require_once(SAPHPIRE_PATH.'/third_party/Mobile_Detect/2.8.17/Mobile_Detect.php');