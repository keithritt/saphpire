<?

$sModalId = @Util::coalesce($sTemplateId, $this->sModalId, 'modal');

$sTitle = @Util::coalesce($this->aTemplateData[$sTemplateId]['title'], $this->sModalTitle, '');
$sTitleId = $sModalId.'_title';
$sUpdateMsgId = $sModalId.'_updt_msg';

$sBody = @Util::coalesce($this->aTemplateData[$sTemplateId]['body'], $this->sModalBody, '');
$sFooter = @Util::coalesce($this->aTemplateData[$sTemplateId]['footer'], $this->sModalBody, '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>');
$sModalClass = @Util::coalesce($this->aTemplateData[$sTemplateId]['modal_class'], '');

$aHtml = array();

$aHtml[] = '
<div
  class="modal fade"
  id="'.$sModalId.'"
  tabindex="-1"
  role="dialog"
  aria-labelledby="'.$sTitleId.'"
  aria-hidden="true"
  >
  <div class="modal-dialog '.$sModalClass.'">
    <div class="modal-content containerx">';

$aHtml[] = '
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="'.$sTitleId.'">'.$sTitle.'</h4>
        <div id="'.$sUpdateMsgId.'"></div>
      </div>';

$aHtml[] = '
      <div class="modal-body">
        '.$sBody.'
      </div>
      <br>
      <div class="modal-footer">
				'.$sFooter.'

      </div>
    </div>
  </div>
</div>';



return implode($aHtml, "\n");
