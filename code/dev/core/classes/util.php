<?

class Util
{
  public static function coalesce()
  {
    //pr('coalesce()');
    //Log::deprecated_code();
      foreach(func_get_args() as $vArg)
      {
        if(isset($vArg))
          return $vArg;

      }
      return null;
  }

  // mini version of a coalesce() that doesnt require @
  // returns an array value if its there default val if not
  public static function array_isset($aArray, $vKey, $vDefault = null)
  {
    if(array_key_exists($vKey, $aArray))
      return $aArray[$vKey];
    else
      return $vDefault;
  }

  public static function is_int($vInt)
  {
    //expose(gettype($vInt));
    switch(gettype($vInt))
    {
      case 'object':
        return false;
      default :
        //expose(strval(trim($vInt)));
        //expose(ctype_digit(strval(ltrim(trim($vInt), '-'))));
        return(ctype_digit((string)ltrim(trim($vInt), '-')));
    }
  }

  public static function strtotime($sString)
  {
    //expose('_strtotime()');
    $iTs = strtotime($sString);

    if(!$iTs)
      Log::error('false returned by Util::strtotime('.$sString.')');

    return $iTs;
  }

  public static function pluralize($iCount, $sSingular = null, $sPlural = null)
  {
    if(isset($sSingular))
    {
      // this is the new/correct usage of pluralize()
      $aParams['count'] = $iCount;
      $aParams['singular'] = $sSingular;
      $aParams['plural'] = $sPlural;
    }
    else
    {
      Log::deprecated_code('old usage of Util::pluralize()');
      $aParams = $iCount;
    }
    //expose($aParams);
    return self::_pluralize($aParams);
  }

  public static function _pluralize($aParams)
  {
    //hit();
    if($aParams['count'] == 1)
      return $aParams['singular'];
    else
    {
      //line();
      if(!isset($aParams['plural']))
        $aParams['plural'] = $aParams['singular'].'s';
      //expose($aParams['plural']);
      return $aParams['plural'];
    }
  }

  public static function boolean_to_string($bBoolean)
  {
    if($bBoolean)
      return 'true';
    else
      return 'false';
  }

  public static function format_phone($params)
  {
    //expose($params);
    if(is_array($params))
      extract($params);
    else
      $phone = $params;

    $phone_parts = self::phone_parts($phone);

    switch($format)
    {
      default: // (123) 456-7890
        if($phone_parts['area'] && $phone_parts['pre'] && $phone_parts['post'])
          return '('.$phone_parts['area'].') '.$phone_parts['pre'].'-'.$phone_parts['post'];
        if($phone_parts['pre'] && $phone_parts['post'])
          return $phone_parts['pre'].'-'.$phone_parts['post'];
        break;
    }
  }

  // splits a phone into its separate parts
  public static function phone_parts($params)
  {
    if(is_array($params))
      extract($params);
    else
      $phone = $params;

    $phone = self::clean_phone($phone);


    $ret = array();
    if(strlen($phone) == 10)
    {
      $ret['area'] = substr($phone, 0, 3);
      $ret['pre'] = substr($phone, 3, 3);
      $ret['post'] = substr($phone, 6, 4);
    }

    if(strlen($phone) == 7)
    {
      $ret['pre'] = substr($phone, 0, 3);
      $ret['post'] = substr($phone, 3, 4);
    }

    return $ret;
  }

  // removed unwanted characters from a phone #
  public static function clean_phone($params)
  {
    //expose('clean_phone()');
    if(is_array($params))
      extract($params);
    else
      $phone = $params;

    $bad_chars = array('(', ')', '-', ' ', '"', "'", ';');
    return str_replace($bad_chars, '', $phone);
  }

  // dont like this being in util
  public static function get_types($iParentTypeId)
  {
    $sSql = "
    SELECT
      id,
      display
    FROM
      ".MASTER_SCHEMA.".types
    WHERE
      parent_type_id = ".(int)$iParentTypeId."
    ORDER BY
      ord";

    //expose($sSql);
    return DB::$oMaster->select_rows($sSql);
    //expose($aTypes);
  }

  public static function get_type_dropdown($iParentTypeId, $sName, $aAttributes = array())
  {
    $aTypes = self::get_types($iParentTypeId);

    //expose($aTypes);

    $aOptions = array();

    foreach($aTypes as $aTypes)
    {
      $aOptions[$aTypes['id']] = $aTypes['display'];
    }
    //expose($aOptions);

    return Form::get_dropdown($sName, $aOptions, null, $aAttributes);
  }

  // easy way to get a lot of html space characters
  public static function sp($iNum = 1)
  {
    $sRet = '';
    for($iX = 1; $iX <= $iNum; $iX++)
      $sRet.= '&nbsp;';

    return $sRet;
  }


  // easy way to get a lot of html space characters
  public static function br($iNum = 1)
  {
    $sRet = '';
    for($iX = 1; $iX <= $iNum; $iX++)
      $sRet.= '<br />';

    return $sRet;
  }

  // take x date formates and return the most recent one
  public static function max_date($aDates)
  {
    if(!is_array($aDates))
      $aDates = func_get_args();
    $iMaxTs = -99999999999;
    $sRet = $aDates[0]; // default to first item
    foreach($aDates as $sDate)
    {
      $iTmpTs = strtotime($sDate);
      if($iTmpTs > $iMaxTs)
      {
        $iMaxTs = $iTmpTs;
        $sRet = $sDate;
      }
    }

    return $sRet;

  }


  public static function get_random_pastel()
  {
    $aPastels = array(
      //'#FF7575',
      '#9669FE',
      '#57BCD9',
      '#72FE95',
      '#FFFF99',
      );

      $iCount = count($aPastels);

      //$iTmp = rand(0, $iCount);

      static $iRand = -1;
      if($iRand == -1)
        $iRand = rand(0, $iCount - 1);

      //$iRand = $iCount % $iRand;

      //expose($iRand);

      $sRet =  $aPastels[$iRand];

      if($iRand == $iCount - 1)
          $iRand = 0;
      else
          $iRand++;

      return $sRet;
  }

  // @TODO - current this will not print $0.00
  public static function format_money($iAmount)
  {
    //expose($iAmount);
    if(empty($iAmount))
      return '';
    return '$'.number_format($iAmount, 2);
  }




}