<?

class Controller extends Domain
{
  function __construct()
  {
    //$this->bRequireLogin = false;

    //expose(Perm::check(Perm::_DEBUG, true));
    if(!Perm::check(Perm::_DEBUG, true))
      stop('Invalid perm');
    parent::__construct();
  }

  function index()
  {
    //print '<br>index()';
  }


  function php_info()
  {
    phpinfo();
  }

  function reports($sReport)
  {
    //line();
    //pr($sReport);
    $this->add_js('/application/js/jquery.js');
    $this->add_js('/application/bootstrap/js/bootstrap.min.js');
    $this->add_js('/application/js/universal_functions.js');

    $this->add_css('/application/bootstrap/css/bootstrap.css');
    $this->add_css('/application/css/default.css');

    $this->oReporter = new Reporter($sReport);
    $this->oReporter->run_report();

    print $this->get_template('generics/report_v.php');

  }

  function constants()
  {
    expose(get_defined_constants());
  }

  function session($sMode = 'view', $sEditKey = null)
  {
    ob_start(); // going the ob route so we can use expose

    switch($sMode)
    {
      case 'view':

        foreach(array('session' => $_SESSION, 'cookie' => $_COOKIE) as $sSessionType => $aSessionData)
        {
          print '<h3 style="display:inline;">'.$sSessionType.'</h3>';
          print ' <a href="/site_admin/session/add_'.$sSessionType.'">Add New</a><br><br>';
          foreach($aSessionData as $sKey =>$vVal)
          {
            $sContentId = $sSessionType.'_contents_'.$sKey;
            $sShowId = $sSessionType.'_show_'.$sKey;
            $sHideId = $sSessionType.'_hide_'.$sKey;
            print $sKey;
            print ' <span id = "'.$sShowId.'" onclick="
              document.getElementById(\''.$sContentId.'\').style.display = \'block\';
              document.getElementById(\''.$sShowId.'\').style.display = \'none\';
              document.getElementById(\''.$sHideId.'\').style.display = \'inline\';
              "
            style="cursor: pointer; color:blue; text-decoration: underline;">show</span>';
            print ' <span id = "'.$sHideId.'" onclick="
              document.getElementById(\''.$sContentId.'\').style.display = \'none\';
              document.getElementById(\''.$sShowId.'\').style.display = \'inline\';
              document.getElementById(\''.$sHideId.'\').style.display = \'none\';
              "
            style="cursor: pointer; color:blue; text-decoration: underline;">hide</span>';
            print ' <a href="/site_admin/session/delete_'.$sSessionType.'/'.$sKey.'">delete</a>';
            print ' <a href="/site_admin/session/edit_'.$sSessionType.'/'.$sKey.'">edit</a>';
            print '<div id="'.$sContentId.'" style="display:none;">';
            expose($vVal);
            print '</div>';
            print '<br><br>';
          }
        }
        break;
      case 'delete_session';
        //expose($sEditKey);
        unset($_SESSION[$sEditKey]);
        //expose($_SESSION);
        redirect('/site_admin/session');
        break;
      case 'delete_cookie':
        setcookie($sEditKey, null, -1, '/');
        redirect('/site_admin/session');
        break;
      case 'add_session':
      case 'add_cookie':
      case 'edit_session':
      case 'edit_cookie':
        switch($sMode)
        {
          case 'add_session':
            $sType = 'session';
            $sKeyVal = '';
            $sValVal = '';
            $sTypeVal = null;
            break;
          case 'add_cookie':
            $sType = 'cookie';
            $sKeyVal = '';
            $sValVal = '';
            $sPathVal = '/';
            $sExpireVal = 0;
            $sDomainVal = '';
            break;
          case 'edit_session':
            $sType = 'session';
            $sKeyVal = $sEditKey;

            $sTypeVal = gettype($_SESSION[$sEditKey]);
            switch($sTypeVal)
            {
              case 'array':
              case 'object':
                $sValVal = serialize($_SESSION[$sEditKey]);
                break;
              default:
                $sValVal = $_SESSION[$sEditKey];
                break;
            }
            break;
          case 'edit_cookie':
            $sType = 'cookie';
            $sKeyVal = $sEditKey;
            $sValVal = $_COOKIE[$sEditKey];
            $sPathVal = '/';
            $sExpireVal = 0;
            $sDomainVal = '';
            break;
        }
        print '
        <form action="/site_admin/session/save" method="post">
          <input type="hidden" name="type" value="'.$sType.'">
          Key: <input type="text" name="key" value="'.$sKeyVal.'"><br>

          Value:<br>
          <textarea name="value" style="width:100%; height: 300px;">'.$sValVal.'</textarea>';

          if($sType == 'cookie')
          {
            print '
            Expire: <input type="text" name="expire" value="'.$sExpireVal.'"><br>
            Path: <input type="text" name="path" value="'.$sPathVal.'"><br>';
            //Domain: <input type="text" name="domain" value="'.$sDomainVal.'"><br>

          }
          else
          {
            print '
            Type:
            <select name="var_type">';
            foreach(array('string', 'int', 'float', 'boolean', 'array', 'object','null') as $sVarType)
            {
              if($sVarType == $sTypeVal)
                $sSelect = 'selected="selected"';
              else
                $sSelect = '';
              print '<option '.$sSelect.' value="'.$sVarType.'">'.$sVarType.'</option>';
            }

            print '</select><br>';
          }

        print '
        <input type="submit" value="save">
        </form>
        <a href="/site_admin/session">cancel</a>';

        break;
      case 'save':
        //expose($_POST);

        switch($_POST['type'])
        {
          case 'session':
            switch($_POST['var_type'])
            {
              case 'string':
                $vVal = (string)$_POST['value'];
                break;
              case 'int':
                $vVal = (int)$_POST['value'];
                break;
              case 'float':
                $vVal = (float)$_POST['value'];
                break;
              case 'boolean':
                $vVal = (boolean)$_POST['value'];
                break;
              case 'array':
                expose($_POST['value']);
                $vVal = (array)unserialize($_POST['value']);
                break;
              case 'object':
                $vVal = (object)unserialize($_POST['value']);
                break;
              case 'null':
                $vVal = null;
                break;
              default:
                stop('unknown var type: '.$_POST['var_type']);
              break;
            }
            $_SESSION[$_POST['key']] = $vVal;
            break;
          case 'cookie':
            //expose($_POST);
            //string $name [, string $value [, int $expire = 0 [, string $path [, string $domain
            setcookie($_POST['key'], $_POST['value'], $_POST['expire'], $_POST['path']);
            break;
        }
        //redirect('/site_admin/session');
        Request::redirect('/site_admin/session');
        break;
    }

   // $sContent =
    $this->sContent = ob_get_clean();
    print $this->get_view('core/views/content.php');
  }
}