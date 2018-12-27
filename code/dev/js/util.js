/*
function encrypt(sWord) // requires including the md5 library
{
  //console.log(sWord);
  sWord = $.trim(sWord);
  //console.log(sWord);
  sWord = sWord.toLowerCase();
  //console.log(sWord);
  sWord = md5(sWord);
  //console.log(sWord);
  sWord = sWord.substr(0,10);
  //console.log(sWord);
  sWord = sWord.toLowerCase();
  //console.log(sWord);
  return sWord;
}

*/


oNs.iAjaxCount = 0;

function ajax_complete(fAjaxStartTs)
{
  //console.log('ajax_complete('+fAjaxStartTs+')');
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

//console.log('before ajax calls');
//ajax({url: 'http://dev.barmend.com', complete: function(){asdf();}});
//console.log('middle ajax calls');
//ajax({url: 'http://dev.barmend.com', completexx: function(){asdf();}});
//ajax({url: 'http://dev.barmend.com', completexx: function(){asdf();}});
//ajax({url: 'http://dev.barmend.com', completexx: function(){asdf();}});
//ajax({url: 'http://dev.barmend.com', completexx: function(){asdf();}});
//ajax({url: 'http://dev.barmend.com', completexx: function(){asdf();}});
//console.log('after ajax calls');

function ajax(oConfig)
{
  //console.log('start ajax('+oConfig.url+')');
  //console.log(oConfig.complete);

 //console.log('ajax()');

  //if(oConfig.fStartTs === undefined)
  //   oConfig.fStartTs = new Date().getTime();

  // add parent page view id

  if(oConfig.data == undefined)
    oConfig.data = {};

  oConfig.data.iParentId = oNs.iPageViewId;


  var fAjaxStartTs = new Date().getTime();
  if(oNs.fAjaxStartTs === undefined)
    oNs.fAjaxStartTs = fAjaxStartTs;


  //console.log('ajax start ts = '+oNs.fAjaxStartTs);



  if(!oNs.bAjaxComplete)
  {
      oNs.iAjaxCount++;
      //if(oNs.iAjaxCount == 1)
      //      oNs.iAjaxStartTs = oConfig.fStartTs;
  }

  //console.log('oNs.iAjaxCount = ' + oNs.iAjaxCount);

  if(oConfig.complete)
  {
    //console.log('complete is set');
    //oConfig.complete();
    var oTmp = oConfig.complete;
    oConfig.complete = function(){oTmp(); ajax_complete(fAjaxStartTs);};


  }
  else
    oConfig.complete = function(){ajax_complete(fAjaxStartTs);};

  //console.log(oConfig.complete);
  /*
        if(oParams.fStartTs == undefined)
            oParams.fStartTs = new Date().getTime();



        // pass in extra params for the WEB_REQUESTS table
        if(oParams.params == undefined)
            oParams.params = {};

        if(oParams.params.iParentRequestId == undefined) // make sure were not overridding any actual ajax params
            oParams.params.iParentRequestId = Dbs.iWebRequestId;

        if(oParams.params.bAjaxComplete == undefined)
            oParams.params.bAjaxComplete = Dbs.bAjaxComplete;


            */

  //console.log(oConfig);

  jQuery.ajax(oConfig);

  /*
      // ajax callback
        if(!Dbs.bAjaxComplete)
        {
            Dbs.iAjaxCount--;
            if(Dbs.iAjaxCount == 0)
            {
                if(typeof mark_ajax_complete === 'function')
                    mark_ajax_complete();
            }
        }
        */

  //console.log('end ajax()');
}

function mark_ajax_complete()
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

    //record_js_time();

    record_page_request();

    //if(Dbs.bIsDeveloper)
   // {
   //     set_ajax_debugs('ajax_time', fAjaxTime);
   //     set_ajax_debugs('total_time', Dbs.fTotalTime);
   // }

    //Ext.Ajax.request(
    //{
    //    params:
    //    {
    //        iWidth: document.body.clientWidth,
    //        iHeight: document.body.clientHeight,
    //        fAjaxTime: fAjaxTime,
    //        fJsTime: fJsTime
    //    },
    //    url: Dbs.BASE_URL + 'inventory/developer/record_page'
    //});
}

function set_js_time()
{
  //console.log('set_js_time()');
  //$('#js_debug_time').innerHTML = 'asdf';
  $(document).ready(function()
  {
    //console.log('set_js_time()');
    //console.log(oNs.iAjaxCount);
    //setTimeout(function()
  //  {
      //try
    //  {
        oNs.fJsEndTs = new Date().getTime();
        oNs.fJsTime = ((oNs.fJsEndTs - oNs.fJsStartTs) / 1000).toFixed(2) * 1;

        if(oNs.iAjaxCount == 0)
          mark_ajax_complete();

        //console.log('oNs.fJsTime = '+oNs.fJsTime);


        //console.log(oNs.END_TS);
        //console.log(((oNs.END_TS - oNs.START_TS) / 1000));
      //  ajax({
    //      type: 'POST',
  //        url: oNs.BASE_URL + '/util/record_js_time',
//          data: {//
            //sJsTime: sJsTime,
            //sUrl: oNs.REQUEST_URL
          //}
        //});
    //  }
  //    catch(err){}
    //}, 1);
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
    //console.log(sMsg);
    //console.log(sUrl);
    //console.log(iLine);
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