<?

class Controller extends Domain
{
  public function __construct()
  {
    Perm::ignore();

    require_once(CODE_PATH.'/core/classes/image.php');

    Request::$bIsAjax = true;
    //expose($_SERVER);
    $sUrl = $_SERVER['REQUEST_URI'];
    //expose($sUrl);
    //expose(Request::$sDomain);
    $sUrl = 'http://'.Request::$sDomain.str_replace('content', 'cdn', $sUrl);

    //expose($sFile);
    //die();

    //assume an image for now

    $oImage = new Image();
    $oImage->load_from_url($sUrl);
    $oImage->render();
    //print file_get_contents($sFile);
    //expose($sContent);
    //print 'cdn';
    //stop();
    die();
  }
}

//http://officialloop.com/cdn/images/icons/bullet_arrow_down.png
//http://officialloop.com/cdn/images/icons/bullet_arrow_down.png