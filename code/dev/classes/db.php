<?

class Db
{
  public static $oMaster = null;
  public static $oBarmend = null;
  public static $iErrorCount = 0;

  public static function __callStatic($sFunction, $aArgs)
  {
    Log::deprecated_code('Db::__callStatic()');
  }

  public static function static_init($sSchema)
  {
    switch($sSchema)
    {
      case 'master':
        self::$oMaster = new Db($sSchema);
        break;
    }
  }

  public function __call($sFunction, $aArgs)
  {
    return $this->call_function($sFunction, $aArgs);
  }

  private function call_function($sFunction, $aArgs)
  {
    $sSql = $aArgs[0];

    switch($sFunction)
    {
      case 'select_rows_and_count':
        $sSql = str_replace("SELECT", "SELECT SQL_CALC_FOUND_ROWS", $sSql);
        // intentionally not breaking here
      case 'select_row':
      case 'select_rows':
      case 'insert':
      case 'update':
      case 'delete':
      case 'admin':
          return $this->exec($sSql, $sFunction);
        break;
    default:
      Log::error('Unknown function: Db->'. $sFunction);
      //@TODO - log in non debug mode
    }
  }

  // want to switch this to only allow connecting as 1 schema
  public function __construct($sSchema, $sEnv = null)
  {
    $this->sSchema = $sSchema; //strtolower(Util::coalesce($sSchema, 'master')); //used to default to SCHEMA
    $this->sEnv = strtolower(Util::coalesce($sEnv, DB_ENV));
    $this->bTransaction = false;
    $this->create_conn('update');
  }

  public function __destruct()
  {
    if($this->bTransaction)
    {
      if(Request::$bDebugMode)
        die('Db object killed while still in a transaction');
    }
  }

  private function create_conn($sType)
  {
    $sSchema = $this->sSchema;
    $sEnv = $this->sEnv;
    $sDb = 'oloop_'.$sSchema.'_'.$sEnv;

    $oConn = new mysqli('localhost', self::get_user($sType, $sEnv), self::get_pass($sType, $sEnv), $sDb);
    $this->oConn = $oConn;
    return $oConn;
  }

  public function begin()
  {
      if($this->bTransaction)
    {
    if(Request::$bDebugMode)
      die('Attempting to nest multiple begins');
    }
    $this->bTransaction = true;
    return $this->exec('BEGIN', 'begin', null, false);
  }

  public function commit()
  {
    if(!$this->bTransaction)
    {
      if(Request::$bDebugMode)
        die('Attempting commit without a begin');
    }
    $this->bTransaction = false;
      return $this->exec('COMMIT', 'commit', null, false);
  }

  public function rollback()
  {
    if(!$this->bTransaction)
    {
      if(Request::$bDebugMode)
        die('Attempting rollback without a begin');
      //TODO - log non debug mode
    }
  $this->bTransaction = false;
    return $this->exec('ROLLBACK', 'rollback', null, false);
  }

  private function exec($sSql, $sType, $iThreshold = 100, $bRecordStat = true)
  {
    $sSchema = $this->sSchema;
    $sEnv = $this->sEnv;
    $oConn = $this->oConn;

    if(is_null($oConn))
    {
      expose_backtrace();
    }

    // only allow selects in production
    if( $sEnv == 'prod' &&
        Request::$bDebugMode &&
        in_array($sType, array('insert', 'update', 'delete')))
      return false;

    $fStart = microtime(true);

    $oRes = $oConn->query($sSql);

    $vRet = null;
    if(!$oRes) // had an error executing query
    {
      DB::$iErrorCount++;

      if(DB::$iErrorCount > 100 || (Request::$bDebugMode && DB::$iErrorCount > 3))
        return; // avoid infinite loops
      $vRet =  false;
      $sError = $oConn->error;
      $sSqlClean = str_replace(array("\n", "\r"), ' ', $sSql);
      $sErrorClean = str_replace(array("\n", "\r"), ' ', $sError);

      $aDebug = debug_backtrace();

      unset($aDebug[0], $aDebug[1]);

      foreach($aDebug as $aStep)
      {
        if(stripos($aStep['file'], 'model'))
          continue;

        $sFile = $aStep['file'];
        $iLine = $aStep['line'];

        break;
      }

      if(Request::$bDebugMode)
      {
        pr('SQL_ERROR @ '.$sFile.' line '.$iLine);
        expose($sError);
        expose($sSql);
      }

      if($bRecordStat)
      {
        Log::_error($sErrorClean, Log::PRIORITY_CRITICAL, Log::TYPE_SQL_ERROR, $sSqlClean, $sFile, $iLine);
      }

      if(defined('DIE_ON_SQL_ERROR') && DIE_ON_SQL_ERROR)
        die();

      return false;
    }
    else
    {
      switch($sType)
      {
        case 'insert':
          $vRet =  $oConn->insert_id;
          break;
        case 'update':
          $vRet = $oConn->affected_rows;
          break;
        case 'select_row':
          if($oRes->num_rows)
            $vRet = $oRes->fetch_assoc();
          else
            $vRet = array();
          break;
        case 'select_rows':
          $vRet = array();
          while($aRow = $oRes->fetch_array(MYSQLI_ASSOC))
            $vRet[] = $aRow;

          break;
        case 'select_rows_and_count':
          $vRet = array();
          while($aRow = $oRes->fetch_array(MYSQLI_ASSOC))
            $vRet[] = $aRow;

          $sSql = "SELECT FOUND_ROWS()";
          $oRes2 = $oConn->query($sSql)->fetch_assoc();
          $vRet['count'] = $oRes2['FOUND_ROWS()'];
          break;
      }
    }

    $fTotalTime = (microtime(true) - $fStart) * 1000;

    if($fTotalTime > $iThreshold)
    {
      // record error
    }
    //line();
    if($bRecordStat)
    {
      //$sMd5 = _md5($sSql);
      //record_stat($fTotalTime, 'query_exec', $sMd5, DOMAIN);
    }

    return $vRet;
  }

  public static function keyword($sString)
  {
    switch($sString)
    {
      case 'null':
      case 'now()':
        return $sString;
      default:
          die('Unknown sql keyword: '.$sString);
    }
  }

  public static function esc($sString)
  {
    if(is_null($sString))
      return 'NULL';

    try
    {
      // not sure why - but previously used $_SESSION['oMySqli']
      $oMySqli = new mysqli('localhost', self::get_user('select_row', 'dev'), self::get_pass('select_row', 'dev'), 'oloop_master_dev');
      $sString = $oMySqli->real_escape_string($sString);
    }
    catch(Exception $oE)
    {
      $sString = "`".$sString."`";
    }

    return "'".$sString."'";
  }

  // format a unix timestamp to a mysql datetime
  public static function datetime($iTimestamp, $bAddQuotes = true)
  {
		if(!is_int($iTimestamp)) // for the timesstamp to unix
			$iTimestamp = Util::strtotime($iTimestamp);

			$sRet =  date('Y-m-d H:i:s', $iTimestamp);
    if($bAddQuotes)
      $sRet = "'".$sRet."'";
    return $sRet;
  }

  public static function date($sDate, $bAddQuotes = true)
  {
    $sRet =  date('Y-m-d', Util::strtotime($sDate));
    if($bAddQuotes)
      $sRet = "'".$sRet."'";
    return $sRet;
  }

  public static function int($iInt)
  {
    if(is_null($iInt))
      return 'null';
    else
      return (int)$iInt;
  }

  public static function float($fFloat)
  {
    if(is_null($fFloat))
      return 'null';
    else
      return (float)$fFloat;
  }

  public static function get_user($sType, $sEnv)
  {
    switch($sEnv)
    {
      case 'dev':
        switch(Auth::$sAccess)
        {
          case 'apache':
            //line();
            return Auth::decrypt('/3ZnkOnfddpM7CMcIuj79M1Js4cKD889lBc253sFrJY=');
        }
      case 'beta':
        switch(Auth::$sAccess)
        {
          case 'apache':
            return Auth::decrypt('6zpbp02bJ81YwKJ2Vu2/QjEIK644zfAllg1dA3hXRRM=');
        }
      case 'prod':
        switch(Auth::$sAccess)
        {
          case 'apache':
            switch($sType)
            {
              case 'select':
              case 'select_row':
              case 'select_rows':
              case 'select_rows_and_count':
                //line();
                return Auth::decrypt('jOcaVn6HkNXMuCbwmlG2aBFLMCPKgofkhAy0BdoyuWw=');
              case 'insert':
              //line();
                return Auth::decrypt('5XG9UK08SoanP3Ypc+dgvCslHsK34uj+TPX0JLqGidc=');
              case 'update':
              //line();
                return Auth::decrypt('yGQN0aaRpcSxm11gG1NBenmJaswyPK18XOXEWkyoMNg=');
              case 'delete':
              //line();
                return Auth::decrypt('mRSPcihHEiiS/VPkPJbUKz0RChLzK5XP8P+1xEkTxBQ=');
              case 'admin':
              //line();
                return Auth::decrypt('gLd2mE3rOyatAdEaUcdBYaYYWpzDqP2BalZuRvfDRHY=');
            }
        }
    }
  }

  public static function get_pass($sType, $sEnv)
  {
    switch($sEnv)
    {
      case 'dev':
        switch(Auth::$sAccess)
        {
          case 'apache':
            return Auth::decrypt('JzV3AiU55AU6lv54ZFbe2hOx9n5qronFsR0sJasH7cE=');
        }
      case 'beta':
        switch(Auth::$sAccess)
        {
          case 'apache':
            return Auth::decrypt('1+cBI+YQtD9vaP5RhsT+mjNu/YyCb/DoJq/Nh9Esu58=');
        }
      case 'prod':
        switch(Auth::$sAccess)
        {
          case 'apache':
            switch($sType)
            {
              case 'select':
              case 'select_row':
              case 'select_rows':
        case 'select_rows_and_count':
                return Auth::decrypt('kD7cXrndIwjKsZ5hwjbM3wR0pK4xzb2VUdDNK+fGZVo=');
              case 'insert':
                return Auth::decrypt('UpwsHZhQ99H+7PNiuSdrzNTbDelXgmRnarOwSqXrSeA=');
              case 'update':
                return Auth::decrypt('BS1y2u5cqoCnzLojV3GEEaal8Z/sy6Okqwlu4m5HmMA=');
              case 'delete':
                return Auth::decrypt('MXoLqVFS0Y5l7CCKUoMrS0wKWPog7JddVNKqxwo9t6A=');
              case 'admin':
                return Auth::decrypt('6sHbQmT1/6TWKERE6Rm3T2/wE1mBlvxXOWuUN+wzM9E=');
            }
        }
    }
  }

  public static function get_type($sSql)
  {
    $sSql = trim($sSql);
    $aWords = explode(' ', $sSql);
    $sIntro = strtoupper($aWords[0]);
    switch($sIntro)
    {
      //case 'ALTER':
      case 'CREATE':
      case 'DROP':
      case 'TRUNCATE':
        return 'admin';
      case 'UPDATE':
        return 'update';
      case 'SELECT';
        return 'select';
      case 'INSERT':
        return 'insert';
      case 'DELETE':
        return 'delete';
      default:
        switch(ENV)
        {
          case 'dev':
          case 'beta':
            print '<br>Unknown intro word:';
            expose($sIntro);
            die();
        }
    }
  }

  public static function build_where($aLookups)
  {
    $aTmp = array();
    foreach($aLookups as $sCol => $vVal)
    {
      if(is_int($vVal))
        $aTmp[] = $sCol.' = '.(int)$vVal;
      elseif(is_numeric($vVal))
        $aTmp[] = $sCol.' = '.$vVal;
      else
        $aTmp[] = $sCol.' = '.self::esc($vVal);
    }
      return implode($aTmp, ' AND ');
  }

  // not sure how much i like this being here
  public  function _get_id_from_id($sColumn, $sTable, $iId)
  {
     $sSql = "
      SELECT
        $sColumn col
      FROM
        $sTable
      WHERE
        id = ".(int)$iId;

      $aRes =  $this->select_row($sSql);
      if(count($aRes))
        return $aRes['col'];
      else
        return false;
  }
}