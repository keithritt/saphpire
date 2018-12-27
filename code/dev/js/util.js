oNs.iAjaxCount = 0;

function ajax_complete(fAjaxStartTs)
{
  if(!oNs.bAjaxComplete)
  {
      oNs.iAjaxCount--;
      if(oNs.iAjaxCount === 0)
        mark_ajax_complete();
  }

    if(oNs.DEBUG_MODE)
    {
      var fAjaxEndTs = new Date().getTime();

      $('#debug_last_ajax_time').html(((fAjaxEndTs - fAjaxStartTs) / 1000).toFixed(2));
    }
}

function ajax(oConfig)
{
  // add parent page view id

  if(oConfig.data == undefined)
    oConfig.data = {};

  oConfig.data.iParentId = oNs.iPageViewId;

  var fAjaxStartTs = new Date().getTime();
  if(oNs.fAjaxStartTs === undefined)
    oNs.fAjaxStartTs = fAjaxStartTs;

  if(!oNs.bAjaxComplete)
  {
      oNs.iAjaxCount++;
  }

  if(oConfig.complete)
  {
    var oTmp = oConfig.complete;
    oConfig.complete = function(){oTmp(); ajax_complete(fAjaxStartTs);};
  }
  else
    oConfig.complete = function(){ajax_complete(fAjaxStartTs);};

  jQuery.ajax(oConfig);
}

function mark_ajax_complete()
{
  oNs.bAjaxComplete = true;

  oNs.fAjaxTime = 0;
    if(oNs.fAjaxStartTs !== undefined)
    {
        oNs.fAjaxEndTs = new Date().getTime();
        oNs.fAjaxTime = ((oNs.fAjaxEndTs - oNs.fAjaxStartTs) / 1000).toFixed(2) * 1;
    }

    oNs.fTotalTime = (oNs.fPhpTime + oNs.fJsTime + oNs.fAjaxTime).toFixed(2) * 1;

    if(oNs.DEBUG_MODE)
    {
        $('#debug_js_time').html(oNs.fJsTime);
        $('#debug_ajax_time').html(oNs.fAjaxTime);
        $('#debug_total_time').html(oNs.fTotalTime);
    }

    record_page_request();
}

function set_js_time()
{
  $(document).ready(function()
  {
    oNs.fJsEndTs = new Date().getTime();
    oNs.fJsTime = ((oNs.fJsEndTs - oNs.fJsStartTs) / 1000).toFixed(2) * 1;

    if(oNs.iAjaxCount == 0)
      mark_ajax_complete();
  });
}

function record_page_request()
{
  try
  {

    jQuery.ajax({
      type: 'POST',
      url: oNs.BASE_URL + '/util/record_page_request',
      data: {
        iPageViewId: oNs.iPageViewId,
        sJsTime: oNs.fJsTime,
        sPhpTime: oNs.fPhpTime,
        sAjaxTime: oNs.fAjaxTime,
        sTotalTime: oNs.fTotalTime
      }
    });
  }
  catch(err){}

}

function record_js_error(sMsg, sUrl, iLine)
{
  try
  {
    ajax({
      type: 'POST',
      url: oNs.BASE_URL + '/util/record_js_error',
      data: {
        sUrl: sUrl, //oNs.REQUEST_URL,
        sError: sMsg,
        iLine: iLine
      }
    });
  }
  catch(err){}
}