<?

class Runsql
{
  function __construct($iBuildId, $sEnv, $sMode)
  {
    line();
    //die();
    $this->iBuildId = $iBuildId;
    $this->sEnv = $sEnv;
    $this->sMode = $sMode;

    $this->validate();

    $this->aSql = array();
    $this->iBatchId = 0;
    $this->increment_batch();
    $this->iErrorCount = 0;
  }

  function increment_batch()
  {
    $this->iBatchId++;
    $this->aSql[$this->iBatchId] = array();
  }

  function apply($sSchema, $sType, $sSql)
  {
    hit();
    if($this->sMode == 'apply')
      $this->aSql[$this->iBatchId][] = array('schema' => $sSchema, 'type' => $sType, 'sql' => $sSql);
  }

  function revert($sSchema, $sType, $sSql)
  {
    if($this->sMode == 'revert')
      $this->aSql[$this->iBatchId][] = array('schema' => $sSchema, 'type' => $sType, 'sql' => $sSql);
  }

  function run()
  {
    pr('Runsql->run()');
    pr('mode = '.$this->sMode);

    //stop();

    if($this->sMode == 'apply')
      Log::resume();
    else
      Log::pause();

    //expose($this->aSql);
    //expose((memory_get_peak_usage()/100000));
    foreach($this->aSql as $aBatchSql)
    {

      foreach($aBatchSql as $aSqlData)
      {
        expose($aSqlData);
        //$sType = Db::get_type($aSqlData['sql']);
        //expose($sType);
        pr('Executing: '.substr($aSqlData['sql'], 0, 100));
        line();
        //@TODO - add logic for some js show/hide
        //if($this->sMode == 'revert')
        //  print '<div style="display:none;">';

        //expose($aSqlData['schema']);
        switch($aSqlData['schema'])
        {
          case 'barmend':
            //line();
            Db::static_init('barmend');
            $oDb = Db::$oBarmend;
            //Expose($oDb);
            //stop();

            break;
          case 'master':
            line();
            $oDb = Db::$oMaster;
            break;
          case 'rippeddiesel':
            line();
            Db::static_init('rippeddiesel');
            $oDb = Db::$oRippeddiesel;
            //expose($oDb);
            //stop();
            break;
          case 'hashdare':
            Db::static_init('hashdare');
            $oDb = Db::$oHashdare;
            break;
          case 'keithandjess':
            //line();
            Db::static_init('keithandjess');
            //line();
            $oDb = Db::$oKeithandjess;
           // line();
            break;

        }
        $vRes = $oDb->exec($aSqlData['sql'], $aSqlData['type'], $aSqlData['schema'], $this->sEnv);
       // if($this->sMode == 'revert')
       //   print '</div>';

        //line();
        if($vRes === false)
        {
          if($this->sMode == 'apply')
            die();
          $this->iErrorCount++;
        }
        //die();
      }
    }
    Log::resume();
  }

  function validate()
  {
    if(!is_numeric($this->iBuildId))
    {
      print "Invalid Build ID: ".$iBuildId;
      $this->print_help();
    }

    if(!in_array($this->sEnv, array('dev', 'beta', 'prod')))
    {
      print "Invalid Env: ".$this->Env;
      $this->print_help();
    }

    if(!in_array($this->sMode, array('apply', 'revert')))
    {
      print "Invalid Mode: ".$this->sMode;
      $this->print_help();
    }
  }

  function print_help()
  {
    print "\n";
    die("format should be: php push_sql.php {build_id} {env} {mode} \n");
  }


}