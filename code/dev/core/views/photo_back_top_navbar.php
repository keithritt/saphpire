<?

$aHtml = array();
$aHtml[] = $this->get_view('core/views/start.php');
$aHtml[] = $this->get_view('core/views/navbar.php');

$aHtml[] = '
<div class="container" style="margin-top: 60px;">
  <div class="jumbotron" style="background: rgb(54, 25, 25); background: rgba(255, 255, 255, .5);">
    <div class="row">';

if(isset($this->sMainContent))
  $aHtml[] = $this->sMainContent;



foreach((array)$this->aSubTemplates as $sTemplate)
{
  //pr($sTemplate);
  $aHtml[] =  $this->get_view($sTemplate);
}

$aHtml[] = '
    </div>
  </div>
</div>';

//line();
//line();
$aHtml[] = $this->get_view('core/views/modals.php');



$aHtml[] = $this->get_view('core/views/end.php');
//$sHtml.= $this->get_view('generics/start.php');



if(isset($this->sBackgroundImage))
{
  // todo move to css file
  $aHtml[] = '
  <img class="bg" src="'.$this->sBackgroundImage.'">';
}
//TODO - add default image

$aHtml[] = '

<style>
img.bg {
  /* Set rules to fill background */
  min-height: 100%;
  min-width: 1024px;

  /* Set up proportionate scaling */
  width: 100%;
  height: auto;

  /* Set up positioning */
  position: fixed;
  top: 0;
  left: 0;

  z-index: -1;
  x-webkit-filter: grayscale(1);
  fxilter: grayscale(1);
}

@media screen and (max-width: 1024px) { /* Specific to this particular image */
  img.bg {
    left: 50%;
    margin-left: -512px;   /* 50% */
  }
}</style>';

return implode("\n", $aHtml);