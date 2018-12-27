<?
class Domain extends Master
{
  public function __construct()
  {
    // by default include jquery and bootstrap
    $this->add_js('/third_party/jquery/2.2.0/compressed.js');
    $this->add_js('/third_party/bootstrap/3.3.6/js/bootstrap.min.js');
    $this->add_js('/code/'.CODE_ENV.'/js/util.js');

    $this->add_css('/third_party/bootstrap/3.3.6/css/bootstrap.css');
    $this->add_css('/code/'.CODE_ENV.'/css/default.css');
    parent::__construct();
  }
}