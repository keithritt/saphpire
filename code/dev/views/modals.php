<?

$aHtml = array();

//line();
if(!isset($this->bModalsAlreadyIncluded) || !$this->bModalsAlreadyIncluded) // intentionally long var name to avoid ambiguity
{
	//line();
	if(isset($this->aModals))
	{
		//line();
	  foreach($this->aModals as $sModalId)
	    $aHtml[] = $this->get_template('views/modal.php', $sModalId);
	}
	$this->bModalsAlreadyIncluded = true;
}

return implode($aHtml, "\n");