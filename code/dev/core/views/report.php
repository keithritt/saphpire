<?

/*
view variables
$this->sReportId
$this->sReportTitle
$this->aReportExtra
$this->sReportUrl
*/

if(Request::$bDebugMode)
{
  if(!isset($this->sReportId))
    stop('report missing $this->sReportId param');
}

//line();

if(!isset($this->aReportExtraFilters))
  $this->aReportExtraFilters = array();

//if(isset($this->aReportExtraFilters))
$sExtraFilters = "extra_filters: '".json_encode($this->aReportExtraFilters)."', ";


//expose($this->aReportExtraFilters);

if(isset($this->sReportUrl))
  $sReportUrl = $this->sReportUrl;
else
  $sReportUrl = '/util/get_report_data';

$this->add_js('/code/'.CODE_ENV.'/core/js/reporter.js');
$this->add_css('/code/'.CODE_ENV.'/core/css/reporter.css', 'body_end');

$this->add_footer_js("init_report('".$this->sReportId."', {".$sExtraFilters."iPage: 1, sUrl: '".$sReportUrl."' })");
//Controller::add_footer_js("init_report('".$this->sReport."', {iPage: ".$this->iPage.", iLimit: ".$this->iLimit."})");
$this->add_footer_js("run_report('".$this->sReportId."')");

$aHtml = array();

$this->oReporter = new Reporter($this->sReportId);
$this->oReporter->set_header_html();

//expose($this->oReporter->sHeader);
//stop();

if(isset($this->aModals))
  $this->aModals = array_merge($this->aModals, $this->oReporter->aModals);
else
  $this->aModals = $this->oReporter->aModals;

//line();

//expose($this->aModals);

//foreach($this->aModals as $sModal)/
//{/
//  require(CODE_PATH.'/'.$sModal);//
//}
//if(isset($this->sReportTitle))
//  $aHtml[] = '<div id="report_title" class="center">'.$this->sReportTitle.'</div>';

//if(isset($sTable))
//  print $sTable;

$sReportContainerStyle = 'width: 58%';

$aHtml[] = '<div id="report_header_'.$this->oReporter->sId.'">';
$aHtml[] =   $this->oReporter->sHeaderHtml;
$aHtml[] = '</div>';
$aHtml[] = '<div id="report_container_'.$this->oReporter->sId.'" stylexx="'.$sReportContainerStyle.'">';
$aHtml[] = '</div>';
//line();

$aHtml[] = $this->get_view('core/views/modals.php');

//expose_html($aHtml);
//stop();

return implode($aHtml, "\n");

/*

<container>
  <header>

  <body>
    <titles>
    <report data>
    <pagination>
  </body>
</container>

*/