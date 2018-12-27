<?

Class Controller extends Domain
{
  public function __construct()
  {
    //pr('Controller->__construct()');
    Perm::ignore();
    Login::ignore();
    parent::__construct();


    $this->aRedirects = Config::get(DOMAIN_PATH.'/config/login.php', 'redirects');
  }

  public function init() // aka log in
  {
    //hit();
    //pr('[Login]Controller->init()');
    //expose($_POST);


    $bPassAuth = Login::_verify_login($_POST);

    //expose($bPassAuth);

    //expose(Session::get('login'));

    //$oMember = Model::init('master', 'members', $this->aSettings['db_conn']);
    //$oMember->fetch(array('email' => $_REQUEST['email'], 'domain_id' => Request::$iDomainId), 'any');

    //expose($bPassAuth);
    if($bPassAuth)
      Request::redirect(@Util::Coalesce($this->aRedirects['login'], '/'));
    else
      Request::redirect(@Util::Coalesce($this->aRedirects['invalid'], '/'));
  }

  public function leave()
  {
    //pr('leave()');
    Login::clear();
    Request::redirect(@Util::Coalesce($this->aRedirects['logout'], '/'));
  }
}