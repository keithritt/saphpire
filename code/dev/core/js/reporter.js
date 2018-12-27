oNs.oReportData = {};

function run_report(sReport, oConfig)
{
  console.log('run_report('+sReport+')');
  //console.log(oNs.oReportData.sReport.sExtra);
  //console.log(oConfig);
  if(oConfig == undefined)
    oConfig = {};
  console.log(oConfig);
  //return;
  oNs.oReportData.sReport = jQuery.extend(oNs.oReportData.sReport, oConfig);


  console.log(oNs.oReportData.sReport);

  // get all the filters

  //oNs.aFilters = $("[filter=1]");

  //console.log(oNs.aFilters);

  var oData = {};

  $("[filter=1]").each(function()
  {

    oData[this.id] = $('#' + this.id).val();
  });

  oData.report = sReport;
  oData.page = oNs.oReportData.sReport.iPage;
  oData.limit = oNs.oReportData.sReport.iLimit;
  oData.sort_col = oNs.oReportData.sReport.sort_col;
  oData.sort_dir = oNs.oReportData.sReport.sort_dir;
  console.log(oNs.oReportData.sReport.extra_filters);
  $.each(oNs.oReportData.sReport.extra_filters, function(sIndex, sVal)
  {
    //console.log(sIndex);
    //console.log(sVal);
    oData[sIndex] = sVal;
  });

  //oData.extra_filters = oNs.oReportData.sReport.extra_filters;
  //oData.filter_vals = jQuery.getJSON(aFilterVals);

  //console.log(oData);
  //console.log(JSON.stringify(aFilterVals));
  //console.log('asdf');

  //if(oNs.oReportData.sReport.aExtra == undefined)
  //  oNs.oReportData.sReport.aExtra = {};
  //console.log(oNs.oReportData.sReport);
  oNs.ajax({
    type: 'POST',
    url: oNs.BASE_URL + oNs.oReportData.sReport.sUrl,
    data: oData,
    success: function(sHtml)
    {
      var oReport = $('#report_container_' + sReport);
      oReport.html(sHtml);

    $("#report_data_" + sReport).scroll(function ()
    {
        $("#report_titles_" + sReport).scrollLeft(this.scrollLeft);
    });

    }
  });
}

function init_report(sReport, oConfig)
{
  console.log('init_report('+sReport+')');
  //console.log(oConfig);
  oNs.oReportData.sReport = oConfig;
  //oNs.oReportData.sReport.aExtra = {};
  console.log(oConfig);

  console.log(oNs.oReportData.sReport.extra_filters);

  if(oNs.oReportData.sReport.extra_filters != undefined)
    oNs.oReportData.sReport.extra_filters = jQuery.parseJSON(oNs.oReportData.sReport.extra_filters);
}

function clear_filters(sReportId)
{
  $("[filter=1][report_id="+sReportId+"]").each(function()
  {
    //console.log(this.id);
    try
    {
      $('#' + this.id).val(null);
    }
    catch(oE){}

    try
    {
      $('#' + this.id).selectpicker('deselectAll');
    }
    catch(oE){}

  });
}

