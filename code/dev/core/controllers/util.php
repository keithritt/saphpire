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
    //return;
    $this->set_domain_user_id(false);
    $this->verify_session_data();
    Request::update_page_view_data();

    //Session::upsert_record();
    print 'ok';
  }

  function verify_session_data()
  {
    //pr('verify_session_data()');

    $oModel = Model::init('master', 'sessions', Db::$oMaster);
    if(isset(Session::$iId))
    {
      // make sure the session id and name are valid and match

      $oModel->fetch(array('id' => Session::$iId, 'name' => Session::$sName, '1 or more'));

    }
    else
    {
      $oModel->fetch(array('name' => Session::$sName.''), 'any', 'last_update_ts DESC');
      switch($oModel->iCount)
      {
        case 0:
          $oModel->name = Session::$sName;
          $oModel->domain_id = Request::$iDomainId;
          $oModel->domain_user_id = Controller::$iDomainUserId;
          $oModel->ip_address = $_SERVER['REMOTE_ADDR'];
          $oModel->user_agent = $_SERVER['HTTP_USER_AGENT'];
          $oModel->create_ts = Db::keyword('now()');
          $oModel->last_update_ts = Db::keyword('now()');

          Session::$iId = $oModel->save();
          break;
        case 1:
          Session::$iId = $oModel->id;
          $oModel->last_update_ts = Db::keyword('now()');
          $oModel->save();
          break;
        default:
          Session::$iId = $oModel->id;
          foreach($oModel as $iKey => $aData)
          {
            if($iKey == 0)
              continue;

            $oModel->delete();
          }
          Log::error('duplicate rows in session table with session name: '.Session::$sName);

          break;
      }

      Session::perma_set('session_id', Session::$iId);
      //expose($oModel->id);
      //expose($oModel->last_update_ts);
    }
  }

  function record_js_error()
  {
    expose($_POST);
    ignore_user_abort(true); // make sure this runs even if the user leaves the page while the ajax is running
    // using write() insted of error in order to avoid printing error message and hitting die() statement
    $aParams = array(
      'msg' => $_POST['error'],
      'priority_id' => Log::PRIORITY_CRITICAL,
      'type_id' => Log::TYPE_JS_ERROR,
      'file' => Util::array_isset($_POST, 'url'),
      'line' => Util::array_isset($_POST, 'line'),
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

    require_once(CODE_PATH.'/core/classes/reporter.php');


    //expose($_REQUEST);

    if(isset($_REQUEST['extra']))
    {
      //line();
      Controller::_Set('extra', json_decode($_REQUEST['extra']));
    }

    $this->oReporter = new Reporter($_REQUEST['report']);



    //pr($_REQUEST['sReport']);

    //$this->oReporter->set_page($_REQUEST['page']);
    //$this->oReporter->set_limit($_REQUEST['limit']);
    //line();

    //@$this->oReporter->set_sort($_REQUEST['sort_col'], $_REQUEST['sort_dir']);
    //die();
    //line();
    //$this->oReporter->set_sql();
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