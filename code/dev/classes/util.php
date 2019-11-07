<?

class Util
{
  public static function coalesce()
  {
      foreach(func_get_args() as $vArg)
      {
        if(isset($vArg))
          return $vArg;

      }
      return null;
  }

  public static function is_int($vInt)
  {
    switch(gettype($vInt))
    {
      case 'object':
        return false;
      default :
        return(ctype_digit(strval(trim($vInt))));
    }
  }

  public static function strtotime($sString)
  {
    $iTs = strtotime($sString);

    if(!$iTs)
      Log::error('false returned by Util::strtotime('.$sString.')');

    return $iTs;
  }

  public static function pluralize($aParams)
  {
    if(!isset($aParams['plural']))
      $aParams['plural'] = $aParams['singular'].'s';

    if($aParams['count'] == 1)
      return $aParams['singular'];
    else
      return $aParams['plural'];
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
      parent_type_id = ".(int)$iParentTypeId;

    return DB::$oMaster->select_rows($sSql);
  }

  public static function _get_type_dropdown($iParentTypeId, $sName, $aAttributes = array())
  {
    $aTypes = self::get_types($iParentTypeId);

    $aOptions = array();

    foreach($aTypes as $aTypes)
    {
      $aOptions[$aTypes['id']] = $aTypes['display'];
    }
    return Form::_get_dropdown($sName, $aOptions, null, $aAttributes);
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
