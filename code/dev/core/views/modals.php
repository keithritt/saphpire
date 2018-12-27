<?

$aHtml = array();

//line();
if(!isset($this->bModalsAlreadyIncluded) || !$this->bModalsAlreadyIncluded) // intentionally long var name to avoid ambiguity
{
	//line();
	if(isset($this->aModals))
	{
		//line();
    //expose($this->aModals);
	  foreach($this->aModals as $sModalId)
    {
      //if(file_exists(CODE_PATH.'/'.$sModalId))
      //  $aHtml[] = $this->get_view($sModalId);
      //else
	     $aHtml[] = $this->get_view('core/views/modal.php', $sModalId);
    }
	}
  $this->aModals = array(); // clear modal array out in case it is called twice
	//$this->bModalsAlreadyIncluded = true;
}

return implode($aHtml, "\n");