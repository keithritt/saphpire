<?

//Db = new Db('barmend', 'dev');

//poze($oDb);

//@TODO - make this smart enough to be able to just pass in a username and password and schema



class Db
{
  use CustomDb;
  //private
  //  $sSchema = SCHEMA,
  //  $sEnv = ENV;

  //public static $_sSchema = 'master';
  //public static $_sEnv = DB_ENV;
  //public static $_oConn = null;

  public static $oMySqli = null;

  public static $oMaster = null;
  //public static $oBarmend = null;
  //public static $bTransaction = false;

  //public static $_ = array();

  public static $iErrorCount = 0;

  public static $aKeywords = array(
    'null',
    'now()'
  );

  //public static function __callStatic($sFunction, $aArgs)
 // {
 //   Log::deprecated_code('Db::__callStatic()');
 // }

  public static function get_schemas()
  {
    return array_merge(array('master'), self::get_custom_schemas());
  }

  public static function static_init($sSchema)
  {
    //hit();

    //print('_static_init(('.$sSchema.')');
    //hit();
    //line();
    //pr($sSchema);
    //expose($sSchema);

    //var_dump($sSchema);

    $sVar = 'o'.ucfirst(strtolower($sSchema));
    //print $sVar;

    if(!isset(self::$$sVar))
      self::$$sVar = new Db($sSchema);

    return self::$$sVar;

    if($sSchema == 'master')
    {
      //print '<br>hit if';
      //var_dump(DB_ENV);
      //die();

      $sDb = self::get_db_name($sSchema);
      //print(self::get_user('select_row', $sDb));
      //print(self::get_pass('select_row', $sDb));
      //die();
      self::$oMySqli = new mysqli('localhost', self::get_user('select_row', $sDb), self::get_pass('select_row', $sDb), $sDb);
        //stop();
    }
    else
      print 'hit else';
  }



  public static function get_conn($sSchema)
  {
    $sVar = 'o'.ucfirst($sSchema);
    return self::$$sVar;
  }





  public function __call($sFunction, $aArgs)
  {
    //pr('call('.$sFunction.')');
    return $this->call_function($sFunction, $aArgs);
  }

  private function call_function($sFunction, $aArgs)
  {
    //pr('call_function('.$sFunction.')');
    //expose($bStatic);
    //expose_backtrace();

    //expose($aArgs);

    $sSql = $aArgs[0];

    switch($sFunction)
    {
      case 'select_rows_and_count':
        $sSql = str_replace("SELECT", "SELECT SQL_CALC_FOUND_ROWS", $sSql);
        // intentionally not breaking here
      case 'select_row':
      case 'select_rows':
      case 'select_array': // special case - selects exactly 2 columns first one is index 2nd is key
      case 'insert':
      case 'update':
      case 'delete':
      case 'admin':
        //if($bStatic)
        //  return self::exec($bStatic, $sSql, $sFunction); //@TODO this is triggering strict standards errors
        //else
          //expose($sSql);
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
    //hit();
    //pr('Db->__construct('.$sSchema.', '.$sEnv.')');
    //expose($sEnv);
    //expose(ENV);
    $this->sSchema = $sSchema; //strtolower(Util::coalesce($sSchema, 'master')); //used to default to SCHEMA
    $this->sEnv = strtolower(Util::coalesce($sEnv, DB_ENV));
    //expose($this->sEnv);
    $this->bTransaction = false;
    $this->create_conn('update');
  }

  public function __destruct()
  {
    //pr('db->destruct()');
    if($this->bTransaction)
    {
      if(Request::$bDebugMode)
        die('Db object killed while still in a transaction');
    }
  }

  private function create_conn($sType)
  {
    //hit();

    //$sSchema = $this->sSchema;
    //$sEnv = $this->sEnv;


    //expose($sSchema);
    //expose($sEnv);
    //  if(self::$sSchema == 'iamjacksjourney')
    //    $sDb = 'oloop_iamjacksjourney_prod';
    //  else
    $sDb = self::get_db_name($this->sSchema, $this->sEnv);

    //var_dump($sDb);
    //stop();

    //expose($sType);
    //expose($sEnv);

    //die();

    //line();
    //print(self::get_user($sType, $sDb));
    //print '<br>hit line'.__line__;
    //die();
    //line();
    //expose(self::get_pass($sType, $sEnv));
    //line();

    //die();

    //expose(self::get_user($sType, $sDb));
    //expose(self::get_pass($sType, $sDb));

    $this->oConn = new mysqli('localhost', self::get_user($sType, $sDb), self::get_pass($sType, $sDb), $sDb);
    //expose($this->oConn->connect_errno);//($mysqli->connect_errno)
    switch($this->oConn->connect_errno)
    {
      case 0:
        break;
      default:
        //line();
        //stop();
        Log::error('DB Connection Failed: '.$this->oConn->connect_error);
        //stop();
        break;
    }
    $this->oConn ;
  }



  public function begin()
  {
    if($this->bTransaction)
    {
      Log::error('Attempting to nest multiple begin()s');
    }
    $this->bTransaction = true;
    return $this->exec('BEGIN', 'begin', null, false);
  }

  public function commit()
  {
    if(!$this->bTransaction)
    {
      Log::error('Attempting commit() without a begin()');
    }
    $this->bTransaction = false;
      return $this->exec('COMMIT', 'commit', null, false);
  }

  public function rollback()
  {
    if(!$this->bTransaction)
    {
      Log::error('Attempting rollback() without a begin()');
    }
    $this->bTransaction = false;
    return $this->exec('ROLLBACK', 'rollback', null, false);
  }



  //private @TODO - get this back to being private - runsql needs it to be public for now
  public function exec($sSql, $sType, $iThreshold = 100, $bRecordStat = true)
  {
    //pr('*******Db::Exec()');
    //expose($bStatic);
    //expose_backtrace();

      $sSchema = $this->sSchema;
      $sEnv = $this->sEnv;
      $oConn = $this->oConn;


    if(is_null($oConn))
    {
      expose_backtrace();
    }

    //expose($sSchema);
    //expose($sEnv);
    //pr($sType);
    //pr($sEnv);
    //expose(self::$sSchema);


    //$this->$sSchema = strtolower(Util::coalesce($sSchema, $thissSchema));
    //self::$sEnv = strtolower(Util::coalesce($sEnv, self::$sEnv));

   // expose(self::$sSchema);
   // if($sSchema == 'iamjacksjourney')
   //     $sEnv = 'prod';



    //pr($sSchema);
    //pr($sEnv);

    // only allow selects in production
    if( $sEnv == 'prod' &&
        Request::$bDebugMode &&
        in_array($sType, array('insert', 'update', 'delete'))
       // && !in_array($sSchema, array('iamjacksjourney'))
        )
      return false;

    $fStart = microtime(true);

    //expose(self::get_user($sType, $sEnv));
    //expose(self::get_pass($sType, $sEnv));
    //die();

   // if($sSchema == 'iamjacksjourney')
   //   $sDb = 'oloop_iamjacksjourney_prod';
   // else
   //   $sDb = 'oloop_'.$sSchema.'_'.$sEnv;


   // $oConn = new mysqli('localhost', self::get_user($sType, $sEnv), self::get_pass($sType, $sEnv), $sDb);
    //if(is_null(static::$oConn))
    //self::create_conn($sType);
    //expose($oConn->dump_debug_info());
    //expose($sSql);
    $oRes = $oConn->query($sSql);
    //expose($oRes);

    $vRet = null;
    if(!$oRes) // had an error executing query
    {
      DB::$iErrorCount++;
      //pr(DB::$iErrorCount);
      //expose($sSql);
      if(DB::$iErrorCount > 100 || (Request::$bDebugMode && DB::$iErrorCount > 3))
        return; // avoid infinite loops
      $vRet =  false;
      $sError = $oConn->error;
      $sSqlClean = str_replace(array("\n", "\r"), ' ', $sSql);
      $sErrorClean = str_replace(array("\n", "\r"), ' ', $sError);

      $aDebug = debug_backtrace();

      unset($aDebug[0], $aDebug[1]);
      //expose($aDebug[2]);
      foreach($aDebug as $aStep)
      {
        //expose($aStep['file']);
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
        //stop();
      }




      //expose($bRecordStat);
      //stop();
      if($bRecordStat)
      {
        //expose($sSql);
        //die();
        //line();
        Log::_error($sErrorClean, Log::PRIORITY_CRITICAL, Log::TYPE_SQL_ERROR, $sSqlClean, $sFile, $iLine);
        //line();
      }

      if(defined('DIE_ON_SQL_ERROR') && DIE_ON_SQL_ERROR)
        die();

      return false;
    }
    else
    {
      //expose($sType);
      switch($sType)
      {
        case 'insert':
          //line();
          //expose($sSql);
          $vRet =  $oConn->insert_id;
          //expose($vRet);
          //die();
          break;
        case 'update':
          $vRet = $oConn->affected_rows;
          break;
        case 'select_row':
          //line();
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
        case 'select_array': // returns an array with the first column as the index and second as the key
          $vRet = array();
          while($aRow = $oRes->fetch_array(MYSQLI_NUM))
            //expose($aRow);
            $vRet[$aRow[0]] = $aRow[1];

          break;
        case 'select_rows_and_count':
          //line();
          $vRet = array();
          while($aRow = $oRes->fetch_array(MYSQLI_ASSOC))
            $vRet[] = $aRow;

          $sSql = "SELECT FOUND_ROWS()";
          $oRes2 = $oConn->query($sSql)->fetch_assoc();
          $vRet['count'] = $oRes2['FOUND_ROWS()'];
          break;
      }
    }
    //line();
    //self::$oConn->close();

    $fTotalTime = (microtime(true) - $fStart) * 1000;
    //expose($fTotalTime);

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




   // expose($vRet);
    //line();
    return $vRet;

  }

  public static function keyword($sKeyword)
  {
    if(!in_array($sKeyword, self::$aKeywords))
      Log::error('Unknown SQL keyword: '.$sKeyword);

    return $sKeyword;
  }

  public static function esc($sString)
  {
    //pr('Db->esc('.$sString.')');
    //line();
    //expose(self::get_user('select_row', 'dev'));
    //stop();
    if(is_null($sString))
      return 'NULL';
    //if(function_exists('mysql_escape_string'))
    //if(!isset($_SESSION['oMySqli']))
    try
    {
      // not sure why - but previously used $_SESSION['oMySqli']

      $sString = self::$oMaster->oConn->real_escape_string($sString);
    }
    catch(Exception $oE)
    {
      $sString = "`".$sString."`";
    }
    //else
    //{
      //$sString = addslashes($sString);
    //}

    //$sString = str_replace(array('%', '_'), array('\\%', '\\_'), $sString);


    return "'".$sString."'";
  }

  // format a unix timestamp to a mysql datetime
  public static function datetime($iTimestamp, $bAddQuotes = true)
  {
		//pr('Db::dateime('.$iTimestamp.')');

		if(!is_int($iTimestamp)) // for the timesstamp to unix
			$iTimestamp = Util::strtotime($iTimestamp);
    //expose($iTimestap);
			$sRet =  date('Y-m-d H:i:s', $iTimestamp);
    if($bAddQuotes)
      $sRet = "'".$sRet."'";
    return $sRet;
  }

  public static function date($sDate, $bAddQuotes = true)
  {

    //pr('Db::date('.$sDate.')');
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

  public static function get_type($sSql)
  {
    $sSql = trim($sSql);
    $aWords = explode(' ', $sSql);
    //expose($aWords);
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
    //hit();
    //pr('build_where');
    //expose($aLookups);
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

       // expose($aTmp);

      return implode($aTmp, ' AND ');
  }

  public static function get_in_sql($aValues, $sDataType = null)
  {
    $aValues = (array)$aValues;
    //expose($aValues);
    $sRet = '(';
    $sDataType = Util::coalesce($sDataType, 'string');
    switch($sDataType)
    {
      case 'string':
        array_walk($aValues, 'Db::esc');
        expose($aValues);
        break;
      case 'int':
      case 'integer':
      case 'num':
      case 'number':
        break;
    }

    $sRet.= implode(',', $aValues);

    $sRet.= ')';
    return $sRet;
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
      //expose($sSql);

      $aRes =  $this->select_row($sSql);
      if(count($aRes))
        return $aRes['col'];
      else
        return false;
  }



}