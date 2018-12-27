oNs.oCalendar = {};



function oCalendar()
{
  this.init = function(sCalendarId)
  {
    //console.log('oCalendar->init('+sCalendarId+')');
    this.sId = sCalendarId;
  };



  this.set_month = function(iMonth)
  {
    //console.log('set_month()');
    this.iMonth = iMonth;
  };

  this.set_year = function(iYear)
  {
    //console.log('set_year()');
    this.iYear = iYear;
  };

  this.prev_month = function()
  {
    //console.log('prev_month');
    if(this.iMonth == 1)
    {
      this.iYear--;
      this.iMonth = 12;
    }
    else
      this.iMonth--;

    this.refresh();
  };

  this.next_month = function()
  {
    //console.log('next_month');
    if(this.iMonth == 12)
    {
      this.iYear++;
      this.iMonth = 1;
    }
    else
      this.iMonth++;

    this.refresh();
  };

  this.sRefreshUrl = '/calendar/get_data';

  this.refresh = function()
  {
    //console.log('refresh)');
    //console.log(this.iMonth);
    //console.log(this.iYear);
    //return;

    ajax({
      type: 'POST',
      url: oNs.BASE_URL + this.sRefreshUrl,
      data: {
        sCalendar: this.sId,
        iMonth: this.iMonth,
        iYear: this.iYear,
      },
      success: function(sHtml)
      {
        var oCalendar = $('#calendar_container'); // limits us to 1 calendar per page
        oCalendar.html(sHtml);
      }
    });
  }

}