<?

class Auth
{

  public static $sAccess = 'apache',
                $sEncryptionKey,
                $sEncryptionCipher,
                $sEncryptionMode,
                $sEncryptionIv,
                $sMasterPass;

  public static function init()
  {
    self::$sAccess = 'apache';
    self::$sEncryptionKey = 'xxxxxxxx';
    self::$sEncryptionCipher = MCRYPT_RIJNDAEL_256;
    self::$sEncryptionMode = MCRYPT_MODE_ECB;
    self::$sEncryptionIv = time();
    self::$sMasterPass = 'xxxxxxxxxxx';
  }

  public static function encrypt($sValue, $sKey = null)
  {
    $sKey = Util::coalesce($sKey, self::$sEncryptionKey);
    return base64_encode(mcrypt_encrypt(self::$sEncryptionCipher, $sKey, $sValue, self::$sEncryptionMode, self::$sEncryptionIv));
  }

  public static function decrypt($sValue, $sKey = null)
  {
    self::init(); //@TODO remove
    $sKey = Util::coalesce($sKey, self::$sEncryptionKey);
    $sRet = mcrypt_decrypt(self::$sEncryptionCipher, $sKey, base64_decode($sValue), self::$sEncryptionMode, self::$sEncryptionIv);
    return $sRet;
  }

  public static function create_hash($sWord, $bPrivate = true)
  {
      $sWord = trim($sWord);
      if($bPrivate)
      {
        $sTmp = $sWord;
        $sWord.= trim(self::$sEncryptionKey);
      }
      $sWord = strtolower($sWord);
      $sWord = md5($sWord);
      $sWord = substr($sWord, 0, 10);
      $sWord = strtolower($sWord);

      if($bPrivate)
        $sWord.= self::create_hash($sTmp, false);

    return $sWord;
  }

  public static function get_public_hash($sHash)
  {
    return substr($sHash, 9);
  }
}
