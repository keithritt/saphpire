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
    self::$sEncryptionKey = 'e899d2983e';
    self::$sEncryptionCipher = MCRYPT_RIJNDAEL_256;
    self::$sEncryptionMode = MCRYPT_MODE_ECB;
    self::$sEncryptionIv = time();
    self::$sMasterPass = 'b25bb4306f94f08f3bda';
  }

  public static function encrypt($sValue, $sKey = null)
  {

    //print 'encrypt('.$sValue.', '.$sKey.')';
    $sKey = Util::coalesce($sKey, self::$sEncryptionKey);

    return base64_encode(mcrypt_encrypt(self::$sEncryptionCipher, $sKey, $sValue, self::$sEncryptionMode, self::$sEncryptionIv));
  }

  public static function decrypt($sValue, $sKey = null)
  {
    self::init(); //@TODO remove
    //expose($sValue);
    //expose(self::$sEncryptionIv);
    $sKey = Util::coalesce($sKey, self::$sEncryptionKey);
    //expose($sKey);
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

  public static function check_allow_view_pass($sPass)
  {
    //expose(encrypt($sPass));

    $bPass = false;
    switch(strtolower($sPass))
    {
      case 'asdf';
      case 'kenny';
      case '1008';
      case '1234';
      case '5678';
      case 'bill':
        $bPass = true;
        break;
      default:
        //TODO - appearently this isnt working
        //require_once(APPPATH.'controllers/login.php');
        //$bPass = (Parent_Login::_encrypt($sPass) == MASTER_PASSWORD);
        break;

    }

    Session::set('bAllowView', (int)$bPass);
    //save_permanent('bAllowView', (int)$bPass);

  }

  public static function create_hash($sWord, $bPrivate = true)
  {
    //pr('create_hash('.$sWord.')');
    //expose($bPrivate);
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