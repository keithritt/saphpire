





oNs.iAjaxCount = 0;

oNs.ajax_complete = function(fAjaxStartTs)
{

  if(!oNs.bAjaxComplete)
  {
      oNs.iAjaxCount--;
      if(oNs.iAjaxCount === 0)
        oNs.mark_ajax_complete();
  }

    if(oNs.DEBUG_MODE)
    {
      var fAjaxEndTs = new Date().getTime();

      $('#debug_last_ajax_time').html(((fAjaxEndTs - fAjaxStartTs) / 1000).toFixed(2));
    }
}


var ajax = function(oConfig)
{
  //console.log(arguments.callee.caller.toString());
  var sMsg = 'deprecated call to ajax() - page view_id: ' + oNs.iPageViewId + ' - ' +  arguments.callee.caller.toString();
  oNs.record_js_error(sMsg, oNs.BASE_URL + oNs.REQUEST_URL);

  if(oNs.DEBUG_MODE)
    console.log('deprecated call to ajax()');
  else
    oNs.ajax(oConfig);
}

oNs.ajax = function(oConfig)
{


  if(oConfig.data == undefined)
    oConfig.data = {};

  //oConfig.data.iParentId = oNs.iPageViewId;
  oConfig.data.parent_id = oNs.iPageViewId;


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
    oConfig.complete = function(){oTmp(); oNs.ajax_complete(fAjaxStartTs);};


  }
  else
    oConfig.complete = function(){oNs.ajax_complete(fAjaxStartTs);};



  jQuery.ajax(oConfig);


}

oNs.mark_ajax_complete = function()
{
  //console.log('mark_ajax_complete()');
  oNs.bAjaxComplete = true;

  //oNs.fJsTime = (oNs.fJsEndTs - oNs.fJsStartTs) / 1000;
  oNs.fAjaxTime = 0;
    if(oNs.fAjaxStartTs !== undefined)
    {
        oNs.fAjaxEndTs = new Date().getTime();
      //console.log(' oNs.fAjaxStartTs = '+ oNs.fAjaxStartTs);
      //console.log(' oNs.fAjaxEndTs = '+ oNs.fAjaxEndTs);

        oNs.fAjaxTime = ((oNs.fAjaxEndTs - oNs.fAjaxStartTs) / 1000).toFixed(2) * 1;
      //console.log(' oNs.fAjaxTime = '+ oNs.fAjaxTime);
    }

    //oNs.fPhpTime = 0;
    oNs.fTotalTime = (oNs.fPhpTime + oNs.fJsTime + oNs.fAjaxTime).toFixed(2) * 1;

    if(oNs.DEBUG_MODE)
    {
        $('#debug_js_time').html(oNs.fJsTime);
        $('#debug_ajax_time').html(oNs.fAjaxTime);
        $('#debug_total_time').html(oNs.fTotalTime);
    }



    oNs.record_page_request();


}

oNs.set_js_time = function()
{
  $(document).ready(function()
  {
    oNs.fJsEndTs = new Date().getTime();
    oNs.fJsTime = ((oNs.fJsEndTs - oNs.fJsStartTs) / 1000).toFixed(2) * 1;

    if(oNs.iAjaxCount == 0)
      oNs.mark_ajax_complete();
  });
}

oNs.record_page_request = function()
{
  try
  {

    jQuery.ajax({ // should this be oNs.ajax() ?
      type: 'POST',
      url: oNs.BASE_URL + '/util/record_page_request',
      data: {
        iPageViewId: oNs.iPageViewId,
        sJsTime: oNs.fJsTime,
        sPhpTime: oNs.fPhpTime,
        sAjaxTime: oNs.fAjaxTime,
        sTotalTime: oNs.fTotalTime,
        // get rid of hungarian
        page_view_id: oNs.iPageViewId,
        js_time: oNs.fJsTime,
        php_time: oNs.fPhpTime,
        ajax_time: oNs.fAjaxTime,
        total_time: oNs.fTotalTime
      }
    });
  }
  catch(err){}

}

oNs.iErrorCount = 0;
oNs.record_js_error = function(sMsg, sUrl, iLine)
{
  oNs.iErrorCount++;
  if(oNs.iErrorCount > 100)
    return; // avoid infinite loops

  try
  {
    //console.log(sMsg);
    //console.log(sUrl);
    //console.log(iLine);
    oNs.ajax({
      type: 'POST',
      url: oNs.BASE_URL + '/util/record_js_error',
      data: {
        // remove hungarian notation
        url: sUrl, //oNs.REQUEST_URL,
        error: sMsg,
        line: iLine
      }
    });
  }
  catch(err){}

}