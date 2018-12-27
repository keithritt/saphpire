<?

$this->bIncludedEndView = true;

//pr('start of end.php');
//expose_backtrace();
//expose($aJs);

$sHtml = '';

//expose($this->aJs);

if(isset(Controller::$aJs['body_end']))
{
	foreach((array)Controller::$aJs['body_end'] as $sJs)
		$sHtml.= '<script type="text/javascript" src="'.$sJs.'" ></script>'."\n";
}

if(isset(Controller::$aCss['body_end']))
{
  foreach((array)Controller::$aCss['body_end'] as $sCss)
    $sHtml.= '<link href="'.$sCss.'" type="text/css" rel="stylesheet" media="all" />'."\n";
}

if(isset(Controller::$sFooterJs))
	$sHtml.= "<script>".Controller::$sFooterJs."</script>";

$this->fPhpTime = number_format((microtime(true) - START_TIME), 2);

//var_dump(Request::$bDebugMode);

if(Request::$bDebugMode)
  $sHtml.= $this->get_view('core/views/debug_bar.php');

$sHtml.=  '

<script>


// avoid console.log errors in ie
if(typeof console == "undefined")
    this.console = {log: function(){}};

oNs.fPhpTime = '.$this->fPhpTime.';
oNs.set_js_time(); // this should always be the last js statement on the page

</script>
</body>
</html>';

unset(Controller::$aRegions['body_end']);


return $sHtml;