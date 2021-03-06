<?

$sHtml = '';

if(isset(Controller::$aJs['body_end']))
{
	foreach((array)Controller::$aJs['body_end'] as $sJs)
		$sHtml.= '<script type="text/javascript" src="'.$sJs.'" ></script>'."\n";
}

if(isset(Controller::$sFooterJs))
	$sHtml.= "<script>".Controller::$sFooterJs."</script>";

$fPhpTime = number_format((microtime(true) - START_TIME), 2);

if(Request::$bDebugMode)
{
	$aDomains = array();
$sHtml.= '

<div id="debug_bar" style="
background: white;
color: black;
font-family: verdana;
border: 2px solid black;
z-index: 1000;
padding:2px;
font-size: 16px;
clear: both;
margin-bottom: 50px;
xposition: absolute;
xbottom: 0;
xleft:0;
xright:0;
">
  php time: '.$fPhpTime.'
  js time: <span id="debug_js_time"></span>
	ajax time: <span id="debug_ajax_time"></span>
	total time: <span id="debug_total_time"></span>
	last ajax time: <span id="debug_last_ajax_time"></span>
  environment: '.Form::_get_dropdown('debug_env', array('dev' => 'dev', 'beta' => 'beta', 'prod' => 'prod'), DB_ENV,
    array('onchange' => "window.location=document.URL+'".($_SERVER['QUERY_STRING'] == '' ? '?' : '&')."debug_env='+this.value;")).'
  domain: '.Form::_get_dropdown('debug_site', $aDomains, DOMAIN,
  array('onchange' => "window.location='".BASE_URL."'?debug_site='+this.value;"));
$sHtml.= '
  <a href="'.BASE_URL.'/site_admin/php_info">phpinfo()</a>
  <a href="'.BASE_URL.'/site_admin/session">session</a>
  <a href="'.BASE_URL.'/site_admin/constants">constants</a>
</div>


';
}

$sHtml.=  '

<script>


// avoid console.log errors in ie
if(typeof console == "undefined")
    this.console = {log: function(){}};

oNs.fPhpTime = '.$fPhpTime.';
set_js_time(); // this should always be the last js statement on the page

</script>
</body>
</html>';

return $sHtml;
