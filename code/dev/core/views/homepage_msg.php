<?

$aHtml = array();

$aHtml[] = '<div class="col-sm-8">';

$oCustomContent = Model::init('barmend', 'custom_content', $this->oDb);
$oCustomContent->fetch('bar_id = '.$this->oBar->id.' AND type_id = '.(int)TYPE_CUSTOM_CONTENT_HOMEPAGE_MSG, '0 or 1');

//expose($oCustomContent->content);
//stop();

if(isset($oCustomContent->content) && trim($oCustomContent->content) != '')
{

  $aHtml[] = '
  <ul class="list-group h5">';
    $aHtml[] = '<li class="list-group-item">'.$oCustomContent->content.'</li>';

  $aHtml[] = '
  </ul>';//<li class=list-group-item>Last Updated: '.date('n/d/y').'</li>
}

$aHtml[] = '</div>';

return implode("\n", $aHtml);