oNs.oReportData = {};

function run_report(sReport, oConfig)
{
  //console.log('run_report('+sReport+')');
  //console.log(oNs.oReportData.sReport.sExtra);
  //console.log(oConfig);
  if(oConfig == undefined)
    oConfig = {};
  //console.log(oConfig);
  //return;
  oNs.oReportData.sReport = jQuery.extend( oNs.oReportData.sReport, oConfig);

  //if(oNs.oReportData.sReport.aExtra == undefined)
  //  oNs.oReportData.sReport.aExtra = {};
  //console.log(oNs.oReportData.sReport);
  ajax({
    type: 'POST',
    url: oNs.BASE_URL + '/util/get_report_data',
    data: {
      sReport: sReport,
      iPage: oNs.oReportData.sReport.iPage,
      iLimit: oNs.oReportData.sReport.iLimit,
      sSortCol: oNs.oReportData.sReport.sSortCol,
      sSortDir: oNs.oReportData.sReport.sSortDir,
      sExtra: oNs.oReportData.sReport.sExtra
    },
    success: function(sHtml)
    {
      var oReport = $('#report_container');
      oReport.html(sHtml);
    }
  });
}

function init_report(sReport, oConfig)
{
  //sole.log('init_report('+sReport+')');
  //console.log(oConfig);
  oNs.oReportData.sReport = oConfig;
  //oNs.oReportData.sReport.aExtra = {};
}