<?
//if(in_array(ENV, array('dev', 'beta') && $)
// this should arguable be in a different location


$sHtml = '';
if(!Session::get('bAllowView') && false)
{
  $sHtml.= '<form method="post"><input type="password" name="bAllowViewPw" autocomplete="off" autofocus><input type="submit" value="go"></form>';
  print $sHtml;
	stop();
}

// this is in parent_controller->__destruct() - but is more visable here
if(!Perm::$bChecked)
  Log::error('no permission check on: '.Request::get_full_url());

if(isset($sDocType))
  $sHtml.= $sDocType;

$sHtml.= '<html lang="en">
<head>';


// checking the 'Trident' is the only way to detect the browser vesion regardless of the compatability mode setting
// 4.0 = IE8;  5.0 = IE9; 6.0 = IE10; 7.0 = IE11; IE7 has no Trident value
// also note that the 'X-UA-Compatible' <meta> tag must be the first tag after the <head> tag in order to work
// @TODO - this is only necessary for ie
if(strpos($_SERVER['HTTP_USER_AGENT'], 'Trident/4.0'))
    $sHtml.= '<meta http-equiv="X-UA-Compatible" content="IE=8"/>';
elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'Trident/5.0'))
    $sHtml.= '<meta http-equiv="X-UA-Compatible" content="IE=9"/>';
elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'Trident/6.0'))
    $sHtml.= '<meta http-equiv="X-UA-Compatible" content="IE=10"/>';
elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'Trident/7.0'))
    $sHtml.= '<meta http-equiv="X-UA-Compatible" content="IE=11"/>';

    //<meta http-equiv="X-UA-Compatible" content="IE=11; IE=10; IE=9; IE=8; IE=7; IE=EDGE" />


$sHtml.= '<meta charset="utf-8" />';
$sHtml.= '<meta name="viewport" content="width=device-width, initial-scale=1" />';


  // twitter boostrap recommends <!DOCTYPE html> - but that breaks oldtowndrafthouse.com
  // this should disable zooming on mobile phones
  //<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
	if(isset($this->sFavIcon))
	 $sHtml.= '<link rel="shortcut icon" href="'.$this->sFavIcon.'">';

	if(isset($this->sTitle))
		$sHtml.= '<title>'.$this->sTitle.'</title>';

  //expose($aCss);

	if(isset(Controller::$aCss['head_start']))
  {
    foreach((array)Controller::$aCss['head_start'] as $sCss)
  		$sHtml.= '<link href="'.$sCss.'" type="text/css" rel="stylesheet" media="all" />'."\n";
  }


	$sHtml.= "<script>
  window.onerror=function(sMsg, sUrl, iLine)
  {
    record_js_error(sMsg, sUrl, iLine);
  }
  var oNs = {};
  oNs.fJsStartTs = new Date().getTime();
  oNs.BASE_URL = '".BASE_URL."';
  oNs.REQUEST_URL = '".REQUEST_URL."';
  oNs.DEBUG_MODE = ".Util::boolean_to_string(Request::$bDebugMode).";
	oNs.iPageViewId = ".Request::$iPageViewId.";
	</script>\n";
  if(isset(Controller::$aJs['head_start']))
  {
  	foreach((array)Controller::$aJs['head_start'] as $sJs)
  		$sHtml.= '<script type="text/javascript" src="'.$sJs.'" ></script>'."\n";
  }

$sHtml.= '

</head>
<body>';



return $sHtml;
