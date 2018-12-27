<?

class Login
{
  use CustomLogin;

  public static $bLoggedIn = false;
  //public static $iId;
  //public static $sFirstName;
  //public static $LastName;
  //public static $sEmail;

  private static $aConfig;

  private static $bRequireLogin = true;

  public static function init()
  {
    //hit();
    //pr('Login->init()');
    //expose($_POST);

    // get settings


    //expose(self::$aConfig);




    $aRet = Session::get('login');

    //expose($aRet);
    //stop();
    if(count($aRet))
    {
      self::$bLoggedIn = true;
      //self::$iId = $aRet['member_data']['id'];
      //self::$sFirstName = $aRet['member_data']['first_name'];
      //self::$LastName = $aRet['member_data']['last_name'];
      //self::$sEmail = $aRet['member_data']['email'];
    }
    //else
    //  line();
  }

  private static function set_config()
  {
    //@TODO = need to relook of how the config setting are handled
    $aDefaults = Config::get(CODE_PATH.'/core/config/login.php');
    //line();
    //pr(DOMAIN_PATH);
    $aCustom = (array)Config::get(DOMAIN_PATH.'/config/login.php');

    //expose($aDefaults);
    //expose($aCustom);
    //stop();

    self::$aConfig = array_merge($aDefaults, $aCustom);

    //expose(self::$aConfig);
  }

  public static function set_session_data()
  {

  }

  public static function ignore()
  {
    self::$bRequireLogin = false;
  }

  public static function check()
  {
    if(self::$bRequireLogin && !self::$bLoggedIn)
    {

        //line();
        Perm::$bChecked = true; // avoid log errors for no perm check
        Request::redirect('/login/leave');
    }
  }

  // in addition to verifying - creates session data on success
  public static function _verify_login($aParams)
  {
   // hit();

    self::set_config();
    //expose(self::$aConfig);
    //stop();

    switch(self::$aConfig['validation'])
    {
      case 'email||pw_hash':
      case 'email_or_user_name||pw_hash':
        //line();
        $sEmail = $aParams['email'];
        //expose($sEmail);
        $sPwHash = $aParams['pw_hash'];

       // expose(self::$aConfig['schema']);
        //stop();

        $oMember = Model::init(self::$aConfig['schema'], 'members', Db::get_conn(self::$aConfig['schema']));
        $aLookups = array('email' => $sEmail);
        if(self::$aConfig['schema'] == 'master')
          $aLookups['domain_id'] = Request::$iDomainId;

        $oMember->fetch($aLookups, 'any');

        //expose($oMember->id);
        if(is_null($oMember->id))
        {
          if(Request::$iDomainId != 2) // barmend.com @TODO - remove from core
          {
            unset($aLookups['email']);
            $aLookups['user_name'] = $sEmail;
          }
          $oMember->fetch($aLookups, 'any');


          //expose($oMember->id);
        }
        //expose($_REQUEST);

        //expose($oMember->password);
        //expose(Auth::create_hash('password'));
        //stop();
        $sPwCol = self::$aConfig['password_col'];
       // expose($sPwCol);
       // expose($sPwHash);
       // expose($oMember->$sPwCol);
        $bPassAuth = self::verify_hash($sPwHash, $oMember->$sPwCol);

       // expose($oMember->id);

        //if($oMember->id == 8) // santi  @TODO - DO NOT CHECK INTO PROD
        //  $bPassAuth = true;

        //expose($bPassAuth);
        if($bPassAuth)
        {
          $aLoginData = array();
          $aLoginData['id'] = $oMember->id;
          $aLoginData['first_name'] = $oMember->first_name;
          $aLoginData['last_name'] = $oMember->last_name;
          $aLoginData['email'] = $oMember->email;

          if(self::$aConfig['validation'] == 'email_or_user_name||pw_hash')
            $aLoginData['user_name'] = $oMember->user_name;

          //expose($aLoginData);


          Session::set_cookie('pw_hash', $sPwHash);

        }
        break;
      case 'master_hash':
        $sPwHash = $aParams['pw_hash'];
        $bPassAuth = self::verify_hash($sPwHash, Auth::$sMasterHash);
        $aLoginData = array();
        //expose($bPassAuth);
        break;
      default:
        Log::error('Unknown validation type: '.self::$aConfig['validation']);
        break;
    }
    //return true;


    if($bPassAuth)
    {
      $aLoginData = self::get_custom_login_data($aLoginData);

      //$aLoginData['validation'] = self::$aConfig['validation'];

      //expose($aLoginData);

      Session::set('login', $aLoginData);
    }

    return $bPassAuth;
  }

  // compares a hash created by js  with what is stored in the database
  public static function verify_hash($sJsHash, $sDbHash)
  {
    //hit();
    //pr('verify_hash('.$sJsHash.', '.$sDbHash.')');
    //expose($_REQUEST);
    $aParts = explode('|', $sJsHash);
    //expose($aParts);
    $iTs = $aParts[0];
    $iAge = time() - $iTs;
    //expose($iAge);
    if(!Request::$bDebugMode && $iAge > 60)
      return false;

    $sHash1 = $aParts[1];
    //$sOrigJsWord = $aParts[2];
    //expose($sOrigJsWord);

    $sHash2 = Auth::create_hash($iTs.$sDbHash);

    if($sHash1 == $sHash2)
      return true;
    else
    {
      // try the master pw
      $sHash2 = Auth::create_hash($iTs.Auth::$sMasterHash);
      return ($sHash1 == $sHash2);
    }
  }

  public static function clear()
  {
    Session::clear('login');
    Session::set_cookie('login_hash', null, -1, '/');
  }
}