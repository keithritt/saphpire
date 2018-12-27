<?
class Session
{
	private static $aData = array();
	private static $aFlashData = null;
	private static $bInitialized = false;

	public static $iId = null;
	public static $sName = null;

	//public static $oDb = null;

	// this is first custom function called - can only use native php functions
	public static function init()
	{
		//pr('Session::init()');
		//stop();
		if(self::$bInitialized)
		{
			if(method_exists('Log', 'error'))
				Log::error('Multiple calls to Session::init()');
			return;
		}

		//print('Session->init()');

		self::$iId = self::get_cookie('session_id');
		self::$sName = self::get_cookie('session_name');

		if(isset(self::$iId, self::$sName))
		{
			//line();
			session_id(self::$sName);
			//define('SESSION_ID', self::$iId);
		}
		else
		{
			//line();
			session_regenerate_id();
		}

		session_start();

		self::$sName = session_id();
		//define('SESSION_NAME', self::$sName);
		self::set_cookie('session_name', self::$sName);


		//expose($_SESSION);
		if(isset($_SESSION['aData']))
		{
			//expose($_SESSION);
			Session::$aData = $_SESSION['aData'];
		}
		else
			Session::$aData = array();

		if(isset($_SESSION['aFlashData']))
			Session::$aFlashData = $_SESSION['aFlashData'];
		else
			Session::$aFlashData = null;

		if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']))
			unset($_SESSION['aFlashData']);

		//self::$oDb = new Db();

		//self::upsert_record();

		self::$bInitialized = true;

		//expose(static::$aData);
		//die();
	}

	public static function upsert_record()
	{
		//expose_backtrace();
		//stop();
		//pr('Session::upsert_record()');
		// self::$sName is assumed to be populated
		if(is_null(self::$iId))
		{
			//line();
			// see if there is a row already in the session table
			$sSql = "
			SELECT
				MAX(id) id
			FROM
				sessions
			WHERE
				name = ".Db::esc(self::$sName);

			//expose($sSql);

			//line();

			$aRow = Db::$oMaster->select_row($sSql);
			//line();

			//expose($aRow);

			if(isset($aRow['id']))
			{
				self::$iId = $aRow['id'];
				$bInsert = false;
			}
			else
				$bInsert = true;

		}
		else
			$bInsert = true;

		if($bInsert)
		{
			//line();
			// insert a new row
			$sSql = "
			INSERT INTO
				sessions
			(
				name,
				domain_id,
				ip_address,
				user_agent,
				create_ts,
				last_update_ts
			)
			VALUES
			(
				".Db::esc(session_id()).",
				".(int)Request::$iDomainId.",
				".Db::esc($_SERVER['REMOTE_ADDR']).",
				".Db::esc($_SERVER['HTTP_USER_AGENT']).",
				now(),
				now()
			)";
			//expose($sSql);
			//$iInsertId =
		  //line();
			self::$iId = Db::$oMaster->insert($sSql);
		}





		self::set_cookie('session_id', self::$iId);

		//stop();





	}

	public static function set($sLookup, $vVal)
	{
		//pr('Session::set('.$sLookup.')');
		//expose($vVal);
		$aParts = explode('||', $sLookup);
		//expose($aParts);
		// TODO - find a better way to do this
		switch(count($aParts))
		{
			case 1:
				static::$aData[$aParts[0]] = $vVal;
				break;
			case 2:
				static::$aData[$aParts[0]][$aParts[1]] = $vVal;
				break;
			case 3:
				static::$aData[$aParts[0]][$aParts[1]][$aParts[2]] = $vVal;
				break;
			case 4:
				static::$aData[$aParts[0]][$aParts[1]][$aParts[2]][$aParts[3]] = $vVal;
				break;
			case 5:
				static::$aData[$aParts[0]][$aParts[1]][$aParts[2]][$aParts[3]][$aParts[4]] = $vVal;
				break;
			case 6:
				static::$aData[$aParts[0]][$aParts[1]][$aParts[2]][$aParts[3]][$aParts[4]][$aParts[5]] = $vVal;
				break;
			case 7:
				static::$aData[$aParts[0]][$aParts[1]][$aParts[2]][$aParts[3]][$aParts[4]][$aParts[5]][$aParts[6]] = $vVal;
				break;
			case 8:
				static::$aData[$aParts[0]][$aParts[1]][$aParts[2]][$aParts[3]][$aParts[4]][$aParts[5]][$aParts[6]][$aParts[7]] = $vVal;
				break;
			case 9:
				static::$aData[$aParts[0]][$aParts[1]][$aParts[2]][$aParts[3]][$aParts[4]][$aParts[5]][$aParts[6]][$aParts[7]][$aParts[8]] = $vVal;
				break;
			case 10:
				static::$aData[$aParts[0]][$aParts[1]][$aParts[2]][$aParts[3]][$aParts[4]][$aParts[5]][$aParts[6]][$aParts[7]][$aParts[8]][$aParts[9]] = $vVal;
				break;
			case 11:
				static::$aData[$aParts[0]][$aParts[1]][$aParts[2]][$aParts[3]][$aParts[4]][$aParts[5]][$aParts[6]][$aParts[7]][$aParts[8]][$aParts[9]][$aParts[10]] = $vVal;
				break;
			default:
				Log::error('Using Session->set with a unknown # of $aParts: '.count($aParts));
				break;
		}
		//static::$aData[$aParts[0]] = $vVal;
		//expose(static::$aData);

		// sync with session
		// TODO - make sure resaving the entire array isnt a waste
		$_SESSION['aData'] = static::$aData;
	}

	//same as fetch but uses $_SESSION instead
	// TODO consolidate get and fetch
	// TODO - add default value
	public static function get($sLookup)
	{
		if(!isset($_SESSION['aData'])) // session has expired
			return null;

		$aParts = explode('||', $sLookup);
		//expose($aParts);
		// TODO - find a better way to do this



		$vRet = $_SESSION['aData'];
		foreach($aParts as $sPart)
		{
			if(isset($vRet[$sPart]))
				$vRet = $vRet[$sPart];
			else
				return null;
		}
		//line();
		//expose($vRet);
		return $vRet;
	}




	// TODO - this leaves the key in the array - need to unset that as well
	public static function clear($sLookup)
	{
		//pr('Session::clear()');
		self::set($sLookup, null);
	}

	public static function set_flashdata($sLookup, $vVal)
	{

		$_SESSION['aFlashData'][$sLookup] = $vVal;
	}

	public static function fetch_flashdata($sLookup)
	{
		if(isset(static::$aFlashData[$sLookup]))
			return static::$aFlashData[$sLookup];
		else
			return null;
	}

	// TODO - figure out domain param
	public static function set_cookie($sName, $sValue, $iExpire = 0, $sPath = '/', $sDomain = null)
	{
			//pr('Session::set_cookie('.$sName.')');
			//expose($sValue);
			//if($sValue == null)
			//		Log::deprecated_code('');
			$bRes = setcookie($sName, $sValue, $iExpire, $sPath);
			//var_dump($bRes);
			if(!$bRes && method_exists('Log', 'error'))
				Log::error('set_cookie() failed.');
	}

	public static function get_cookie($sName)
	{
		if(isset($_COOKIE[$sName]))
			return $_COOKIE[$sName];
		else
			return null;
	}

	// TODO - would like this moved to a login utility class  - but currently there is no such class - only a controller
	public static function fetch_login()
	{
		//pr('Session::fetch_login()');

		///stop();
		$aRet = Session::get('login');
		//expose($aRet);
		if(isset($aRet))
			return $aRet;

		// if the session is expired - check cookie - and relog the user in

		//expose($_COOKIE);

		$sLoggedInUsers = '0';

		$sPublicHashes = Session::get_cookie('public_hash');
		if(isset($sPublicHashes) && $sPublicHashes != '')
		{
			//line();
			$aPublicHashes = explode(',', $sPublicHashes);
			$sLoggedInUsers = Session::get_cookie('logged_in_users');
		}

		//expose($sLoggedInUsers);
		//stop();

		if(isset($sLoggedInUsers) && $sLoggedInUsers != '0')
		{
			//pr('hit if');
			//if(file_exists(APPPATH.'controllers/'.DOMAIN_FOLDER.'/login_controller.php'))
		//		require_once(APPPATH.'controllers/'.DOMAIN_FOLDER.'/login_controller.php');

			$sLoginType = Util::coalesce(Session::get('login_type'), 'barmend');
			expose($sLoginType);
			//stop();

			switch($sLoginType)
			{
				case 'barmend':

					$oDb = new Db('barmend');

					$aLoggedInUsers = explode(',', $sLoggedInUsers);
					foreach($aLoggedInUsers as $iKey => $iMemberId)
					{
						//expose($iKey);
						//expose($iMemberId);
						$oMember = Model::init('barmend', 'members', $oDb);
						$oMember->fetch($iMemberId, '0 or 1');
						if(isset($oMember->password))
						{
							$sUserHash = Auth::get_public_hash($oMember->password);
							if($sUserHash == $aPublicHashes[$iKey])
							{
								//pr('hit if');
								//Login_controller::_log_in_user($iMemberId);
							}
							//else
								//pr('hit else');
							//expose($aPublicHashes[$iKey]);
						}
						//else
							//pr('hit else');
						//expose($oMember);
						//
					}

					//expose($aLoggedInUsers);
					//expose($_SESSION);

					break;
				case 'oloop':
					pr('oloop login');
					//expose($sLoggedInUsers);
					if($sLoggedInUsers == '1')
					{
						$aLoginData = array('first_name' => 'Keith');
						Session::set('login', $aLoginData);
					}
					break;
			}


		}


		//stop();

		return Session::get('login');
	}



}