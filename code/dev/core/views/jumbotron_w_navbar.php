<?

$aHtml = array();

//line();
$aHtml[] = $this->get_view('core/views/start.php');
//line();
$aHtml[] = $this->get_view('core/views/navbar.php');
//line();


if(isset($this->aSubTemplates))
{
  foreach((array)$this->aSubTemplates as $sTemplate)
    $aHtml[] = $this->get_template($sTemplate);
}

if(isset($this->sJumboTronContent))
{
	$aHtml[] = 	'
    <div class="jumbotron">
      <div class="container">';
	$aHtml[] = $this->sJumboTronContent;
	$aHtml[] = '</div></div>';
}

// TODO - this is hackish and only used for barmend.com homepage currently
if(isset($this->sSubJumboContent))
{
	$aHtml[] = 	'<div class="container">';
	$aHtml[] = $this->sSubJumboContent;
	$aHtml[] = '</div>';
}


$aHtml[] = $this->get_view('core/views/end.php');



return implode($aHtml, "\n");