<?

class Controller extends Domain
{
  public function __construct()
  {
    // TODO - add necessary permission checks
    Perm::ignore();
    parent::__construct();
  }

  function record_js_timexx()
  {
    record_js_time($_POST);
  }
  
  function record_page_request()
  {
    Request::update_page_view_timings();
    print 'ok';
  }

  function record_js_error()
  {
    ignore_user_abort(true); // make sure this runs even if the user leaves the page while the ajax is running
    // using write() insted of error in order to avoid printing error message and hitting die() statement
    $aParams = array(
      'sMsg' => $_POST['sError'],
      'iPriorityId' => Log::PRIORITY_CRITICAL,
      'iTypeId' => Log::TYPE_JS_ERROR,
      'sFile' => $_POST['sUrl'],
      'iLine' => $_POST['iLine'],
      );

    Log::write($aParams);
    print 'ok';
  }

  function index()
  {
  }

  function get_report_data()
  {
    require_once(CODE_PATH.'/classes/reporter.php');

    if(isset($_REQUEST['sExtra']))
    {
      Controller::_Set('extra', json_decode($_REQUEST['sExtra']));
    }

    $this->oReporter = new Reporter($_REQUEST['sReport']);

    $this->oReporter->set_page($_REQUEST['iPage']);
    $this->oReporter->set_limit($_REQUEST['iLimit']);
    @$this->oReporter->set_sort($_REQUEST['sSortCol'], $_REQUEST['sSortDir']);

    $this->oReporter->set_sql();
    $this->oReporter->set_body_html();

    print $this->oReporter->sBodyHtml;
  }
}
