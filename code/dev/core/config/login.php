<?

switch(Config::$sPrimaryLookup)
{
  default:
    Config::$vRet = array
    (
      'schema' => 'master',
      'validation' => 'email||pw_hash',
      'redirects' => array
      (
        'login' => '/',
        'logout' => '/',
        'invalid' => '/',
      ),
      'password_col' => 'password',
    );
    break;
};