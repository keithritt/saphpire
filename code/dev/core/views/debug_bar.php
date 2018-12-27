<?

if(Request::$iDomainId == 18)
  return;

require_once(CODE_PATH.'/core/classes/form.php');

$aHtml = array();

$aDomains = array();

$sSql = "
  SELECT
    id,
    domain
  FROM
    domains
  ORDER BY
    domain";

$aRows = Db::$oMaster->select_rows($sSql);
foreach($aRows as $aRow)
  $aDomains[$aRow['id']] = $aRow['domain'];
  //$aFiles = scandir(PUBLIC_HTML_PATH.'/'.ENV.'/application/controllers');
  //unset($aFiles[0], $aFiles[1]); // remove . and ..

  //foreach($aFiles as $sFile)
  //{
  //  if(in_array($sFile, array('brewskistavern_com', 'keithritt_net', 'officialloop_com', 'bars')))
  //    continue;
  //  if(is_dir(PUBLIC_HTML_PATH.'/'.ENV.'/application/controllers/'.$sFile))
//     $aDomains[$sFile] = str_replace(array('_', 'corner.bar'), array('.', 'corner-bar'), $sFile);
//  }


$aHtml[] = '

<div id="debug_bar" style="
background: white;
color: black;
font-family: verdana;
border: 2px solid black;
z-index: 1000;
padding:2px;
font-size: 16px;
clear: both;
xmargin-top: 300px;
margin-bottom: 50px;
xposition: absolute;
xbottom: 0;
xleft:0;
xright:0;
">
  request id: '.Request::$iPageViewId.'
  php time: '.$this->fPhpTime.'
  js time: <span id="debug_js_time"></span>
  ajax time: <span id="debug_ajax_time"></span>
  total time: <span id="debug_total_time"></span>
  last ajax time: <span id="debug_last_ajax_time"></span>
  environment: '.Form::get_dropdown('debug_env', array('dev' => 'dev', 'beta' => 'beta', 'prod' => 'prod'), DB_ENV,
    array('onchange' => "window.location=document.URL+'".($_SERVER['QUERY_STRING'] == '' ? '?' : '&')."debug_env='+this.value;")).'
  domain: '.Form::get_dropdown('debug_site', $aDomains, Request::$iDomainId,
  array('onchange' => "window.location='".BASE_URL."?debug_domain_id=' + this.value;"));
$aHtml[] = '
  <a href="'.BASE_URL.'/site_admin/php_info">phpinfo()</a>
  <a href="'.BASE_URL.'/site_admin/session">session</a>
  <a href="'.BASE_URL.'/site_admin/constants">constants</a>
</div>';

//$aHtml = array();

//if(Request::$iDomainId == 19) // santi ritter.com and barmend.com
 // $aHtml = array();

return implode($aHtml, "\n");

