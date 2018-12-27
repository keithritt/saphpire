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
    //pr('record_js_time()');
    //expose('asdf');
    //die();
    //taking to long
    //return;
    //$iStart = time();
    record_js_time($_POST);
    //$iEnd = time();
    //expose($iEnd - $iStart);
  }


  function record_page_request()
  {
    Request::update_page_view_timings();
    print 'ok';
  }

  function record_js_error()
  {
    //expose($_POST);
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
    //Log::_error($_POST['sError'], Log::PRIORITY_CRITICAL, Log::TYPE_JS_ERROR);
    //record_js_error($_POST);
    print 'ok';
  }

  function index()
  {
    //print '<br>index()';
  }

  function get_report_data()
  {
    //pr('get_report_data()');
    //expose($_REQUEST);

    require_once(CODE_PATH.'/classes/reporter.php');

    //expose($_REQUEST);

    if(isset($_REQUEST['sExtra']))
    {
      //line();
      Controller::_Set('extra', json_decode($_REQUEST['sExtra']));
    }

    $this->oReporter = new Reporter($_REQUEST['sReport']);



    //pr($_REQUEST['sReport']);

    $this->oReporter->set_page($_REQUEST['iPage']);
    $this->oReporter->set_limit($_REQUEST['iLimit']);
    //line();

    @$this->oReporter->set_sort($_REQUEST['sSortCol'], $_REQUEST['sSortDir']);
    //die();
    //line();
    $this->oReporter->set_sql();
    //line();
    $this->oReporter->set_body_html();

    print $this->oReporter->sBodyHtml;

    //expose($this->oReporter->sSql);


    //expose($iCount);
    //expose($aRows);
  }

  function get_calendar_data()
  {
    //expose($_REQUEST);
    //expose($_SESSION['aData']);
    switch($_REQUEST['sCalendar'])
    {
      case 'barmend_admin':
        //line();
        global $oThis;
        // very hackish - not a fan
        $oThis->iBarId = $_SESSION['aData']['login']['bar_data']['id'];
        break;
      case 'bar_calendar':
        global $oThis;
        // very hackish - not a fan
        $oThis->iBarId = 2;     // hack - hard coding cb for now
        break;
     //case ''
    }
    $this->oCalendar = new Calendar($_REQUEST['sCalendar'], $_REQUEST['iMonth'], $_REQUEST['iYear']);
    print $this->oCalendar->get_html();
  }
}