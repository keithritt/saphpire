<?
$sEnv = 'dev'; // temp hack

define('APPPATH', '/public_html/'.$sEnv.'/application/');
define('ENVIRONMENT', $sEnv);

require_once(APPPATH.'/libraries/autoload.php');

$aEnvs = array('dev', 'beta','prod');
$aAccs = array('apache');
$aPws = array('dev' => '', 'beta' => '', 'prod' => '');
$aKeys = array('apache' => substr(md5($aPws['prod']),0, 10), 'zak' => substr(md5($aPws['zak']),0, 10));
$aTypes = array('select','insert','update','delete','admin');

$sUserPrefix = '';


foreach($aEnvs as $sEnv)
{
  print "
    $sEnv";
  foreach($aTypes as $sType)
  {
    print "
      $sType
        user: ".Db::get_user($sType, $sEnv)."
        pass: ".Db::get_pass($sType, $sEnv);
  }
}
print "
";

die();



print "
public static function get_pass(\$sType, \$sEnv)
{
  switch(\$sEnv)
  {";
foreach($aEnvs as $sEnv)
{
  print "
    case '$sEnv':";
    switch($sEnv)
    {
      case 'dev':
        print "
      switch(ACCESS)
      {";
        foreach($aAccs as $sAcc)
        {
          print "
        case '$sAcc':";
          switch($sAcc)
          {
            case 'apache':
              print "
          return decrypt('".encrypt($aPws['dev'], $aKeys['apache'])."');";
              break;
          }
        }
        print "
      }";
        break;
      case 'beta':
        print "
      switch(ACCESS)
      {";
        foreach($aAccs as $sAcc)
        {
          print "
        case '$sAcc':";
          switch($sAcc)
          {
            case 'apache':
              print "
          return decrypt('".encrypt($aPws['beta'], $aKeys['apache'])."');";
              break;
            case 'zak':
              print "
          return decrypt('".encrypt($aPws['beta'], $aKeys['zak'])."');";
              break;
          }
        }
        print "
      }";
        break;
      case 'prod':
        print "
      switch(ACCESS)
      {";
          foreach($aAccs as $sAcc)
          {
            print "
        case '$sAcc':";
            switch($sAcc)
            {
              case 'apache':
                print "
          switch(\$sType)
          {";
                foreach($aTypes as $sType)
                {
                  print "
            case '$sType':
              return decrypt('".encrypt($aPws['prod'].substr($sType, 0, 1), $aKeys['apache'])."');";                     
                }
              
                print "
          }";
                break;
            }
          }
        print"
      }";
    }
}

print "  
  }
}
";
