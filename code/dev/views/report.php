<?

$aHtml = array();

if(isset($this->sTitle))
  $aHtml[] = $this->sTitle;


$aHtml[] =  '<div id="report_container" style="margin: auto;"></div>';

$aHtml[] = $this->get_template('views/modals.php');

return implode($aHtml, "\n");
