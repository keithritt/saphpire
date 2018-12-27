//console.log('date_picker.js');

oNs.oDatePicker = {};



function oDatePicker()
{

  this.init = function(sPickerId, sDate)
  {
    //console.log('oDatePicker.init('+sPickerId+', '+sDate+')');
    oNs.oDatePicker[sPickerId]= this;
    this.sId = sPickerId;
    this.oDateInput = $('#'+this.sId);
    this.oButtonRow = $('#'+this.sId+'_button_row');
    this.oCalendarRow = $('#'+this.sId+'_calendar_row');
    this.iPickedYear = null;
    this.iPickedMonth = null;
    this.iPickedDay = null;
    console.log(sDate);
    if(sDate == undefined)
    {
      console.log('null date');
      this.oDate = new Date();
      this.set_year(this.oDate.getFullYear());
      this.set_month(this.oDate.getMonth() + 1);
      //this.set_day(this.oDate.getDate());

      this.fill_in_dates();
    }
    else
    {
      this.pick_date(sDate);
    }
    //console.log(this);

    //console.log(oDp.oDate.toString());
    //console.log(this.oDate.getMonth() + 1);
    //this.set_date((this.oDate.getMonth() + 1 )+'/'+this.oDate.getDate()+'/'+this.oDate.getFullYear());
  };


  this.set_year = function(iYear, bRefill)
  {
    //console.log('this.set_year('+iYear+')');
    if(iYear == this.iYear) // if they match stop
      return true;

    if(bRefill == undefined)
      bRefill = false;

    this.iYear = iYear;

    this.bIsLeapYear = (this.iYear % 4) == 0;
    $('#'+this.sId+'_year').val(this.iYear);
    $('#'+this.sId+'_year').selectpicker('render');

    this.set_month(this.iMonth, bRefill);

    //if(bRefill)
    //  this.fill_in_dates();
  };
  this.set_month = function(iMonth, bRefill)
  {
    //console.log('this.set_month('+iMonth+')');
    //console.log(bRefill);
    if(iMonth == this.iMonth) // if they match stop
    {
      //console.log();
      //return true; TODO -temporarily disabling because changing the year doesnt force a refresh of the calendar
    }

    if(bRefill == undefined)
      bRefill = false;

    //console.log();

    this.iMonth = iMonth;

    // this had errors
    //$('#'+this.sId+'_month').selectpicker('val', this.iMonth);

    $('#'+this.sId+'_month').val(this.iMonth);
    $('#'+this.sId+'_month').selectpicker('render');

    //console.log();

    if(this.bIsLeapYear && this.iMonth == 2)
    {
      this.iDaysInMonth = 29;
      this.iDaysInPrevMonth = 31;
    }
    else if(this.bIsLeapYear && this.iMonth == 3)
    {
      this.iDaysInMonth = 31;
      this.iDaysInPrevMonth = 29;
    }
    else
    {
      this.iDaysInMonth = this.oDaysInMonth[this.iMonth];
      this.iDaysInPrevMonth = this.oDaysInMonth[this.iMonth - 1];
    }

    //console.log();

    this.oTmp = new Date(this.iMonth + '/1/' + this.iYear);
    this.iMonthStart = this.oTmp.getDay();

    //console.log();
    if(this.iMonthStart != 0 ) // if the month starts on any day but sunday - need the prev months days
    {
      //console.log();
      this.iPrevMonthSun = this.iDaysInPrevMonth - this.iMonthStart + 1;
    }
    else
    {
      //console.log();
      this.iPrevMonthSun = false;
    }

    if(bRefill)
      this.fill_in_dates();

  };
  this.set_day = function(iDay)
  {
    //console.log('this.set_day('+iDay+')');
    if(iDay == this.iDay) // if they match stop
      return true;

    this.iDay = iDay;

  }
  this.fill_in_dates = function()
  {
    //console.log('fill_in_dates()');
    //console.log(this.iPrevMonthSun);
    var sMonthType;
    var iDateWalk;
    if(this.iPrevMonthSun)
    {
      //console.log();
      iDateWalk = this.iPrevMonthSun;
      sMonthType = 'prev';
    }
    else
    {
      //console.log();
      iDateWalk = 1;
      sMonthType = 'curr';
    }

    for(iWeek = 1; iWeek <= 6; iWeek++)
    {
      if(sMonthType == 'next')
      {
        $('#'+this.sId+'_week_'+iWeek).hide();
        continue;
      }
      else
        $('#'+this.sId+'_week_'+iWeek).show();

      for(iDay = 1; iDay <= 7; iDay++)
      {
        //console.log();
        //console.log(this.sId);

        //console.log($('#'+this.sId+'_week_'+iWeek+'_day_'+iDay).html);
        //console.log('#'+this.sId+'_week_'+iWeek+'_day_'+iDay);

        var oDateBox = $('#'+this.sId+'_week_'+iWeek+'_day_'+iDay);
        oDateBox.html(iDateWalk);
        if(sMonthType == 'curr')
          oDateBox.addClass('bold');
        else
          oDateBox.removeClass('bold');

        if(sMonthType == 'curr' && iDateWalk == this.iDay && this.iMonth == this.iPickedMonth && this.iYear == this.iPickedYear)
          oDateBox.addClass('btn-primary');
        else
          oDateBox.removeClass('btn-primary');

        oDateBox.attr('month_type', sMonthType);



        if(sMonthType == 'prev' && iDateWalk == this.iDaysInPrevMonth)
        {
          sMonthType = 'curr';
          iDateWalk = 1;
        }
        else if(sMonthType == 'curr' && iDateWalk == this.iDaysInMonth)
        {
            sMonthType  = 'next';
            iDateWalk = 1;
        }
        else
          iDateWalk++;
      }
    }
  };
  this.next_month = function(bRefill)
  {
    //console.log('next_month()');
    if(bRefill == undefined)
      bRefill = false;

    if(this.iMonth == 12)
    {
      this.set_year(this.iYear + 1);
      this.set_month(1);

    }
    else
      this.set_month(this.iMonth + 1);

    if(bRefill)
      this.fill_in_dates();
  };
  this.prev_month = function(bRefill)
  {

    if(bRefill == undefined)
      bRefill = false;
    //console.log('prev_month()');
    if(this.iMonth == 1)
    {
      this.set_year(this.iYear - 1);
      this.set_month(12);
    }
    else
      this.set_month(this.iMonth - 1);

    if(bRefill)
      this.fill_in_dates();
  };
  // when a user clicks on a calendar box
  this.click_date = function(sBoxId)
  {
    //console.log('pick_date('+sBoxId+')');
    var oDateBox = $('#'+sBoxId);
    var sMonthType = oDateBox.attr('month_type');
    //console.log(sMonthType);
    var iDay = oDateBox.html();
    //console.log(iDay);

    switch(sMonthType)
    {
      case 'curr':
        break;
      case 'prev':
        this.prev_month();
        break;
      case 'next':
        this.next_month();
        break;
    }
    this.set_day(iDay);

    this.pick_date(this.iMonth+'/'+this.iDay+'/'+this.iYear);
    //this.oDateInput.val(this.sFullDate);
    //this.fill_in_dates();
  };
  // when a user edits the input box
  this.change_date = function()
  {
    //console.log('change_date()');
    this.pick_date(this.oDateInput.val());
    // copying this from in init() function - perhaps need to move this to its own function

  }
  this.pick_date = function(sDate)
  {
    //console.log('pick_date('+sDate+')');
    this.oDate = new Date(sDate);
    this.set_year(this.oDate.getFullYear());
    this.set_month(this.oDate.getMonth() + 1);
    this.set_day(this.oDate.getDate());

    this.iPickedYear = this.iYear;
    this.iPickedMonth =this.iMonth;
    this.iPickedDay = this.iDay;
    this.sPickedDate = this.iMonth+'/'+this.iDay+'/'+this.iYear;
    this.oDateInput.val(this.sPickedDate);


    this.fill_in_dates();


  };
  this.toggle_calendar = function()
  {
    this.oButtonRow.toggle();
    this.oCalendarRow.toggle();
  }

  this.oDaysInMonth = {
    0:31, // cheap hack for jan - 1
    1:31,
    2:28,
    3:31,
    4:30,
    5:31,
    6:30,
    7:31,
    8:31,
    9:30,
    10:31,
    11:30,
    12:31
  };


}
