<?
class Controller extends Domain
{
  public function __construct()
  {
    Perm::ignore();
    parent::__construct();
  }

  public function init()
  {
    pr('Controllers->Homepage->init()');
  }
}