<?

class Config
{
  public static $vRet;
  public static $sPrimaryLookup;

  public static function get($sFile, $sPrimaryLookup = null, $aExtraLookup = null)
  {
    //hit();
    //pr('Config::get('.$sFile.')');
    //if(is_null($sPrimaryLookup))
    //  Log::deprecated_code('Config::get() with null for $sPrimaryLookup');
    self::$sPrimaryLookup = $sPrimaryLookup;
    self::$vRet = null;

    if(isset($aExtraLookup))
      $aParts = explode('|', $aExtraLookup);

    if(file_exists($sFile))
    {
      ///line();
      //expose($aParts[0]);

      //line();
      include($sFile);
      //expose(self::$aData);

      //TODO - add logic for drilling down for extralookups
      //return self::$aData;
      //line();
    }
    else
    {
      //line();
      //return null;
    }

    return self::$vRet;
  }
}