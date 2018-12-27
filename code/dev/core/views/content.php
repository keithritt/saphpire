<?
// literally just prints out whatever was in $this->sContent - nice for very basic pages

$aHtml = array();

$aHtml[] = $this->get_view('core/views/start.php');

$aHtml[] = $this->sContent;

$aHtml[] = $this->get_view('core/views/end.php');


return implode($aHtml, "\n");