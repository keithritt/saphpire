<?
$_GET['debug'] = 1;
//print 'hit line '.__line__;

//print "top of scheduler3.php\n";

//print "php_sapi_name() = ".php_sapi_name()."\n";

define('LOG_FILE_MODE', true); 


  $sDbEnv = $argv[1];

  //var_dump($argv);

if(isset($argv[2]))
  $iGroupId = $argv[2];
else
  $iGroupId = 'master'; // technically not an integer

//var_dump($sDbEnv);



$sCodeEnv = explode('/', str_replace('/home3/oloop/public_html/', '', __file__));
$sCodeEnv = $sCodeEnv[0];
//print 'hit line '.__line__;
//var_dump($sCodeEnv);

//print 'hit line '.__line__;
//var_dump($sDbEnv);

//stop();

set_time_limit(0);
date_default_timezone_set('America/Chicago');
define('APPPATH', '/home3/oloop/public_html/'.$sCodeEnv.'/application/');
define('ENVIRONMENT', $sDbEnv);
//print 'hit line '.__line__;
require_once(APPPATH.'/libraries/autoload.php');
//print "\n hit line ".__line__;
//stop();

//expose($iGroupId);

//line();
//die();

$iEndTs = time() + (MINUTE * 14);

Log::$bPrint = true;
Log::write("New Scheduler Iteration for Group: ".$iGroupId);

$oScheduler = new Scheduler($sDbEnv, $iGroupId);

if($oScheduler->iGroupId == 'master')
{
  //$oScheduler->log('this is the master');
  $aGroups = $oScheduler->GetAllGroups();

  //expose($aGroups);

  foreach($aGroups as $iGroupId)
  {
    //$iPid = pcntl_fork();
    //expose($iPid);

    //expose($iGroupId);

    // since pctnl_fork() isnt available - kick off new scripts manually

    $sCmd = "php /home3/oloop/public_html/".ENVIRONMENT."/bin/scheduler.php ".ENVIRONMENT." ".$iGroupId."  >> /home3/oloop/public_html/".ENVIRONMENT."/logs/scheduler.log &";
    $oScheduler->log('Executing: '.$sCmd);
    $aOutput = array();
    exec($sCmd, $aOutput);

    //expose($aOutput);
  }

  $oScheduler->log('killing master');
  die(); // master only has to kick off children
}
//else
//  $oScheduler->log('this is NOT the master');




while(time() < $iEndTs)
{
  // check to see if there is already a scheduler running
  $sCmd = "ps ux |grep 'php /home3/oloop/public_html/".ENVIRONMENT."/bin/scheduler.php ".ENVIRONMENT." ".$oScheduler->iGroupId."' |grep -v grep";
  //$oScheduler->log('Executing: '.$sCmd);
  $aOutput = array();
  exec($sCmd, $aOutput);

  //expose($aOutput);

  if(count($aOutput) > 1)
  {
    $oScheduler->log('Scheduler already running - sleeping...');
    sleep(15);
    continue;
  }
  


  try
  {
    //$oScheduler->log('Scheduler Iteration: '.date(DATETIME_FORMAT_SHORT));
    $oScheduler->SetNextRunTimestamps();
    $oScheduler->GetNextJob();
  }
  catch(Exception $oE)
  {
    die('Error occured in scheduler: '.$oE->getMessage());
  }

  $oScheduler->log('sleeping...');
  sleep(15);

  //die();
}

class Scheduler
{
  const STATUS_DISABLED = TYPE_SCHEDULER_STATUS_DISABLED;
  const STATUS_READY = TYPE_SCHEDULER_STATUS_READY;
  const STATUS_RUNNING = TYPE_SCHEDULER_STATUS_RUNNING;

  public function __destruct()
  {
    print "\nScheduler->__destruct()";
  }

  public function log($sMsg)
  {
    Log::Write('GROUP '.$this->iGroupId.' : '.$sMsg);
  }

  public function __construct($sEnv, $iGroupId)
  {
    switch($sEnv)
    {
      case 'dev':
      case 'beta':
      case 'prod':
        break;
      default:
        die('use format: php bin/scheduler.php {env}');
    }

    $this->iGroupId = $iGroupId;

    // since this currently only uses php includes we can assume that each time we restart nothing is running
    // no longer true
    $sSql = "
    UPDATE
      scheduler
    SET
      status_id = ".(int)self::STATUS_READY.",
      last_update_ts = now()
    WHERE
      status_id = ".(int)self::STATUS_RUNNING;

    //Db::update($sSql, 'master');
  }

  public function SetNextRunTimestamps()
  {
    $this->log('SetNextRunTimestamps()');
    // make sure all items have a next_run_ts
    $sSql = "
    SELECT
      id,
      cron
    FROM
      scheduler
    WHERE
      next_run_ts IS NULL 
      AND group_id = ".(int)$this->iGroupId."
      AND status_id <> ".self::STATUS_DISABLED;

    //$this->log($sSql);

    $aRows = Db::select_rows($sSql, 'master');
    expose($aRows);
    foreach($aRows as $aRow)
    {
      $sSql = "
      UPDATE
        scheduler
      SET
        next_run_ts = ".Db::datetime(get_next_run_time($aRow['cron'])).",
        last_update_ts = now()
      WHERE
        id = ".(int)$aRow['id'];
      //$this->log($sSql);
      Db::update($sSql, 'master');
      //pr(get_next_run_time($aRow['cron']));
      //pr(Db::datetime(get_next_run_time($aRow['cron'])));
    }
  }

  public function GetNextJob()
  {
    $this->log('GetNextJob()');
    $sSql = "
    SELECT
      id,
      file,
      cron
    FROM
      scheduler
    WHERE
      next_run_ts < now()
      AND group_id = ".(int)$this->iGroupId."
      AND status_id = ".self::STATUS_READY."
    ORDER BY
      next_run_ts
    LIMIT 
      1";

    //$this->log($sSql);
    $aRow = Db::select_row($sSql, 'master');

    //expose($aRow);

    if(count($aRow))
    {
      // set the status of the job to running


      // execute the job
      //$iStart = time();
      //print 'job started on '.Db::datetime(time());
      $this->SetStatus($aRow['id'], self::STATUS_RUNNING);
      $this->RunJob($aRow['file']);
      

      $sSql = "
      UPDATE
        scheduler
      SET
        next_run_ts = ".Db::datetime(get_next_run_time($aRow['cron'])).",
        last_update_ts = now()
      WHERE
        id = ".(int)$aRow['id'];
      Db::update($sSql, 'master');

      $this->SetStatus($aRow['id'], self::STATUS_READY);

    }
    else
        $this->log("no jobs ready");
  }

  private function RunJob($sFile)
  {
    $this->log('RunJob()');
    //$iStart = time();
    $this->log('running job: '.$sFile);
    $this->log('started on '.date(DATETIME_FORMAT_SHORT));

    include(PUBLIC_HTML_PATH.'/'.ENV.'/bin/'.$sFile.'.php');
  }

  private function SetStatus($iId, $iStatus)
  {
    $this->log('SetStatus('.$iId.', '.$iStatus.')');
    $sSql = "
    UPDATE
      scheduler
    SET
      status_id = ".(int)$iStatus.",
      last_update_ts = now()
    WHERE
      id = ".(int)$iId;

    Db::update($sSql, 'master');
  }

  public function GetAllGroups()
  {
    $this->log('GetAllGroups');
    $sSql = "
    SELECT DISTINCT
      group_id
    FROM
      scheduler
    ORDER BY
      group_id";

    $aRows = Db::select_rows($sSql, 'master');

    //expose($aRows);

    $aRet = array();
    foreach($aRows as $aRow)
      $aRet[] = $aRow['group_id'];


    return $aRet;





  }

}









