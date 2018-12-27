<?

class Perm
{

	public static $bChecked = false;
	public static $bRequireLogin = true;

	const _PUBLIC = 0;
	// very much not a fan of hard coding these barmend specific perms here
	const EDIT_FOOD_MENU = 1;
	const EDIT_DRINK_MENU = 2;
	const EDIT_WEBSITE = 3;
	const EDIT_CALENDAR = 4;

	// need to account for multiple member logins
	// $bMajor  if false - used to determine options on a page (ie menu)
	// if true - used to detmine if page renders at all
	public static function check($iPermission, $bMajor = false, $iMemberId = null)
	{
		//pr('Perm->check('.$iPermission.')');
		if($bMajor)
			self::$bChecked = true;

		if($iPermission == self::_PUBLIC)
			return true;

		//pr('check('.$iPermission.', '.$iMemberId.')');
		if(is_null($iMemberId)) // just base it on the session data
		{
			$aPerms = Session::get('login||member_data');
			//expose($aPerms);
			$aPerms = current($aPerms);
			//expose($aPerms);
			$aPerms = $aPerms['permissions'];
			//expose($aPerms);
		}

		return in_array($iPermission, $aPerms);

		//expose($aPerms);
	}



	public static function ignore()
	{
		//pr();
		self::$bRequireLogin = false; // not sure how i feel about this
		self::$bChecked = true;
	}
}