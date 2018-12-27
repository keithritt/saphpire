<?

class Auth
{
  use CustomAuth;

  public static $sAccess = 'apache',
                //$sEncryptionKey,
                $sEncryptionCipher,
                $sEncryptionMode,
                $sEncryptionIv
                //$sMasterPass,
                //$sMasterHash
                ;

  public static function init()
  {
    //hit();
    //expose_backtrace();
    //self::$sAccess = 'apache';
    //
    self::$sEncryptionCipher = MCRYPT_RIJNDAEL_256;
    self::$sEncryptionMode = MCRYPT_MODE_ECB;
    self::$sEncryptionIv = time();

    //line();

    //expose(self::encrypt(self::$sMasterPass));
    //stop();
    //self::$sMasterPass = 'b25bb4306f94f08f3bda'; // not sure if this is used anymore
    //self::$sMasterHash = '94f08f3bda';
  }

  public static function encrypt($sValue, $sKey = null)
  {
    //hit();
    //print 'encrypt('.$sValue.', '.$sKey.')';
    $sKey = Util::coalesce($sKey, self::$sEncryptionKey);

    //expose($sKey);

    return base64_encode(mcrypt_encrypt(self::$sEncryptionCipher, $sKey, $sValue, self::$sEncryptionMode, self::$sEncryptionIv));
  }

  public static function decrypt($sValue, $sKey = null)
  {
    //hit();
    //self::init(); //@TODO remove
    //expose($sValue);
    //expose(self::$sEncryptionIv);
    $sKey = Util::coalesce($sKey, self::$sEncryptionKey);
    //var_dump($sKey);
    //stop();
    //expose(self::$sEncryptionKey);
    //expose(self::$sEncryptionCipher);
    //expose(self::$sEncryptionMode);
    //die();
    //return
    $sRet = mcrypt_decrypt(self::$sEncryptionCipher, $sKey, base64_decode($sValue), self::$sEncryptionMode, self::$sEncryptionIv);
    //expose($sRet);
    return $sRet;
    //die();
  }



  public static function create_hash($sWord, $bPrivate = true)
  {
    //pr('create_hash('.$sWord.')');
    //expose($bPrivate);
    //  $sWord = trim($sWord);
    //  if($bPrivate)
    //  {
    //    $sTmp = $sWord;
    //    $sWord.= trim(self::$sEncryptionKey);
    //  }
    //  $sWord = strtolower($sWord);
      $sWord = md5($sWord);
      $sWord = substr($sWord, 0, 10);
      $sWord = strtolower($sWord);

    //  if($bPrivate)
    //    $sWord.= self::create_hash($sTmp, false);

    //pr($sWord);

    return $sWord;
  }

  public static function get_public_hash($sHash)
  {
    return substr($sHash, 9);
  }


}