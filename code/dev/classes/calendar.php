<?

class Calendar
{
  public function __construct($sId, /*$sTheme = 'bootstrap_big'*/ $iMonth = null, $iYear = null)
  {
    $this->sId = $sId; //'calendar'; // can override with set_id()
    $this->iMonth = Util::coalesce($iMonth, date('n'));
    $this->iYear = Util::coalesce($iYear, date('Y'));
    $this->sMonthStart = $this->iMonth.'/1/'.$this->iYear;
    $this->iMonthStart = strtotime($this->sMonthStart);
    $this->iMonthStartDay = date('w', $this->iMonthStart) + 1;
    $this->iDaysInMonth = date('t', $this->iMonthStart);
    $this->sRefreshUrl = '/calendar/get_data';
    $this->sTheme = 'bootstrap_big'; //$sTheme;

    $this->set_configs();
  }
  
  // with the hard coding of the js var name - this might not be the best place for this logic
  public function get_js()
  {
    $aHtml = array();

    $aHtml[] = "
    if(oNs.calendars == undefined)
      oNs.calendars = {};
    oNs.calendars.".$this->sId." = new oCalendar();
    oNs.calendars.".$this->sId.".init('".$this->sId."');
    oNs.calendars.".$this->sId.".set_month(".$this->iMonth.");
    oNs.calendars.".$this->sId.".set_year(".$this->iYear.");
    oNs.calendars.".$this->sId.".sRefreshUrl = '".$this->sRefreshUrl."';
    ";

    return implode($aHtml, "\n");
  }

  public function get_html()
  {
    $this->custom_content();
    $aHtml = array();

    $aHtml[] = '<div id="calendar_container" class="hidden-xs">';
    $aHtml[] = $this->get_header();
    $aHtml[] = $this->get_calendar();
    $aHtml[] = $this->get_footer();

    $aHtml[] = '</div>';


    return implode($aHtml, "\n");
  }

  // @TODO calendar doesnt render well on small screens
  public function get_upcoming_html()
  {
    $aHtml = array();
    if(count($this->aUpcomingEvents))
    {
      $aHtml[] = '<div id="upcoming_container" class="hidden-sm hidden-md hidden-lg">';
      $aHtml[] = '
      <ul class="list-group h5">
        <li class="list-group-item active h4">Upcoming Events</li>';

      foreach($this->aUpcomingEvents as $iTs => $sDesc)
        $aHtml[] = '<li class="list-group-item">'.date('D n/j', $iTs).': '.$sDesc.'</li>';

      $aHtml[] = '<li class="list-group-item active">Mobile devices are limited to 30 days of events. For full calendar please visit the
      site on a larger screen.</li>';


      $aHtml[] = '</ul>';
      $aHtml[] = '</div">';
    }
    return implode($aHtml, "\n");
  }


  private function get_header()
  {
    $aHtml = array();

    $aHtml[] = '<div class="center bold" style="'.$this->sHeaderStyle.'">';
    $aHtml[] = '<span class="clickable glyphicon glyphicon-menu-left" onclick="oNs.calendars.'.$this->sId.'.prev_month();"></span>';

    //TODO - make this a dropdown with an onchange listener
    $aHtml[] = date('F', $this->iMonthStart);

    // add the year if not in the current year
    if($this->iYear != date('Y'))
      $aHtml[] = $this->iYear;

    $aHtml[] = '<span class="clickable glyphicon glyphicon-menu-right" onclick="oNs.calendars.'.$this->sId.'.next_month();"></span>';

    $aHtml[] = '</div>';
    $aHtml[] = '<br/>';

    return implode($aHtml, "\n");
  }

  private function get_footer()
  {
    $aHtml = array();
    $aHtml[] = '<div class="center bold" style="'.$this->sFooterStyle.'">';
    $aHtml[] = '<span class="clickable glyphicon glyphicon-refresh" onclick="oNs.calendars.'.$this->sId.'.refresh();" ></span>';
    $aHtml[] = '</div>';
    $aHtml[] = '<br/>';

    return implode($aHtml, "\n");
  }

  private function get_calendar()
  {
    $aHtml = array();
    $aHtml[] = '
    <table class="table-condensedx table-borderedx table-stripedx">
            <thead>
                <tr>
                    <th class="'.$this->sThClass.'" style="'.$this->sThStyle.'">Sunday</th>
                    <th class="'.$this->sThClass.'" style="'.$this->sThStyle.'">Monday</th>
                    <th class="'.$this->sThClass.'" style="'.$this->sThStyle.'">Tuesday</th>
                    <th class="'.$this->sThClass.'" style="'.$this->sThStyle.'">Wednesday</th>
                    <th class="'.$this->sThClass.'" style="'.$this->sThStyle.'">Thursday</th>
                    <th class="'.$this->sThClass.'" style="'.$this->sThStyle.'">Friday</th>
                    <th class="'.$this->sThClass.'" style="'.$this->sThStyle.'">Saturday</th>
                </tr>
            </thead>
            <tbody>';

    $iDateWalk = 1;

    for($iWeekWalk = 1; $iWeekWalk <= 6; $iWeekWalk++)
    {
      if($iDateWalk > $this->iDaysInMonth)
        continue;
      $aHtml[] = '<tr>';
      for($iDayWalk = 1; $iDayWalk <= 7; $iDayWalk++)
      {
        $aHtml[] =  '<td style="'.$this->sTdStyle.'" class="'.$this->sTdClass.'">';
        if($iWeekWalk == 1 && $iDayWalk  < $this->iMonthStartDay)
        {
          // do nothing
        }
        elseif($iDateWalk > $this->iDaysInMonth)
        {
          // do nothing
        }
        else
        {
          $aHtml[] = '<div style="text-align:right;">';
          $aHtml[] = $iDateWalk;
          $aHtml[] = '</div>';

          if(isset($this->aContent['one_time'][$this->iMonth.'/'.$iDateWalk.'/'.$this->iYear]))
            $aHtml[] = $this->aContent['one_time'][$this->iMonth.'/'.$iDateWalk.'/'.$this->iYear];
          elseif(isset($this->aContent['yearly'][$this->iMonth.'/'.$iDateWalk]))
            $aHtml[] = $this->aContent['yearly'][$this->iMonth.'/'.$iDateWalk];
          elseif(isset($this->aContent['monthly'][$iDateWalk]))
            $aHtml[] = $this->aContent['monthly'][$iDateWalk];
          elseif(isset($this->aContent['weekly'][$iDayWalk]))
            $aHtml[] = $this->aContent['weekly'][$iDayWalk];

          $iDateWalk++;
        }
        $aHtml[] = '</td>';
      }
      $aHtml[] =  '</tr>';
    }

      $aHtml[] = '</tbody>';
      $aHtml[] = '</table>';
      return implode($aHtml, "\n");
  }

  private function set_configs()
  {
    $this->iCellWidth = 100;
    $this->iCalendarWidth = $this->iCellWidth * 7;
    $this->sThStyle = 'width: 12.5%;';
    $this->sThClass = 'center bold';

    $this->sHeaderStyle = '
    width: 87.5%;
    font-size: 24px;';

    $this->sFooterStyle = '
    width: 81.25%;
    text-align: right;';

    $this->sTdStyle = '
    height: '.$this->iCellWidth.'px;
    border: 1px solid black;
    vertical-align: top;
    padding: 4px;
    background: white;';
    $this->sTdClass = 'center';

  }

  public function add_one_time_content($sDate, $sContent)
  {
    $this->aContent['one_time'][$sDate] = $sContent;
  }

  public function add_yearly_content($sDate, $sContent)
  {
    $this->aContent['yearly'][$sDate] = $sContent;
  }

  public function add_monthly_content($sDate, $sContent)
  {
    $this->aContent['monthly'][$sDate] = $sContent;
  }

  public function add_weekly_content($vDay, $sContent)
  {
    switch($vDay)
    {
      case TYPE_DAY_SUN:
        $iDayId = 1;
        break;
      case TYPE_DAY_MON:
        $iDayId = 2;
        break;
      case TYPE_DAY_TUE:
        $iDayId = 3;
        break;
      case TYPE_DAY_WED:
        $iDayId = 4;
        break;
      case TYPE_DAY_THU:
        $iDayId = 5;
        break;
      case TYPE_DAY_FRI:
        $iDayId = 6;
        break;
      case TYPE_DAY_SAT:
        $iDayId = 7;
        break;
      default:
        Log::error('unknown $vDay: '.$vDay);
        break;
    }

    $this->aContent['weekly'][$iDayId] = $sContent;
  }

  private function add_upcoming_content($aEventData)
  {
    $aCalcData = array();

    $iStart = strtotime('today');
    $iEnd = strtotime('+ 30 days');

    foreach($aEventData as $aEvent)
    {
      switch($aEvent['interval_id'])
      {
        case TYPE_INTERVAL_ONE_TIME:
          $sDate = $aEvent['interval_desc'];
          $iTs = Util::strtotime($sDate);
          if($iTs >= $iStart && $iTs <= $iEnd)
            $aCalcData[TYPE_INTERVAL_ONE_TIME][$iTs] = '<div class="clickable underlined" onclick="view_event('.$aEvent['id'].')">'.$aEvent['title'].'</div>';
          break;
        case TYPE_INTERVAL_WEEKLY:
          $iTs = Util::strtotime(self::get_weekday_from_id($aEvent['interval_desc']));
          $sOrigDate = date(DATE_FORMAT_SHORT2, $iTs);

          if($iTs >= $iStart && $iTs <= $iEnd)
            $aCalcData[TYPE_INTERVAL_WEEKLY][$iTs] = '<div class="clickable underlined" onclick="view_event('.$aEvent['id'].')">'.$aEvent['title'].'</div>';

          for($iWalk = 1; $iWalk <= 5; $iWalk++)
          {
            $iTs = Util::strtotime($sOrigDate.'+ '.(7 * $iWalk).' days');

            if($iTs >= $iStart && $iTs <= $iEnd)
              $aCalcData[TYPE_INTERVAL_WEEKLY][$iTs] = '<div class="clickable underlined" onclick="view_event('.$aEvent['id'].')">'.$aEvent['title'].'</div>';
          }
          break;
        case TYPE_INTERVAL_MONTHLY:
          for($iWalk = 0; $iWalk <= 1; $iWalk++)
          {
            if($iWalk == 0)
            {
              $iMonth = date('n');
              $iYear = date('Y');

            }
            else
            {
              if(date('n') == 12) // if december - do january of next year
              {
                $iMonth = 1;
                $iYear++;
              }
              else
                $iMonth++;
            }
            $sDate = $iMonth.'/'.$aEvent['interval_desc'].'/'.$iYear;
            $iTs = Util::strtotime($sDate);
            if($iTs >= $iStart && $iTs <= $iEnd)
              $aCalcData[TYPE_INTERVAL_MONTHLY][$iTs] = '<div class="clickable underlined" onclick="view_event('.$aEvent['id'].')">'.$aEvent['title'].'</div>';

          }
          break;
        case TYPE_INTERVAL_YEARLY:
          $sDate = $aEvent['interval_desc'].date('/Y');
          $iTs = Util::strtotime($sDate);
          if($iTs >= $iStart && $iTs <= $iEnd)
            $aCalcData[TYPE_INTERVAL_YEARLY][$iTs] = '<div class="clickable underlined" onclick="view_event('.$aEvent['id'].')">'.$aEvent['title'].'</div>';
          break;
      }
    }

    // combine $aCalcData into 1 array - having more rare occurrences override
    $aTmp = array();
    
    if(isset($aCalcData[TYPE_INTERVAL_WEEKLY]))
    {
      foreach($aCalcData[TYPE_INTERVAL_WEEKLY] as $iTs => $sDesc)
        $aTmp[$iTs] = $sDesc;
    }

    if(isset($aCalcData[TYPE_INTERVAL_MONTHLY]))
    {
      foreach($aCalcData[TYPE_INTERVAL_MONTHLY] as $iTs => $sDesc)
        $aTmp[$iTs] = $sDesc;
    }

    if(isset($aCalcData[TYPE_INTERVAL_YEARLY]))
    {
      foreach($aCalcData[TYPE_INTERVAL_YEARLY] as $iTs => $sDesc)
        $aTmp[$iTs] = $sDesc;
    }

    if(isset($aCalcData[TYPE_INTERVAL_ONE_TIME]))
    {
      foreach($aCalcData[TYPE_INTERVAL_ONE_TIME] as $iTs => $sDesc)
        $aTmp[$iTs] = $sDesc;
    }

    ksort($aTmp);

    $this->aUpcomingEvents = $aTmp;
  }

  public static function get_weekday_from_id($iDayId)
  {
    switch($iDayId)
    {
      case TYPE_DAY_MON:
        return 'Monday';
      case TYPE_DAY_TUE:
        return 'Tuesday';
      case TYPE_DAY_WED:
        return 'Wednesday';
      case TYPE_DAY_THU:
        return 'Thursday';
      case TYPE_DAY_FRI:
        return 'Friday';
      case TYPE_DAY_SAT:
        return 'Saturday';
      case TYPE_DAY_SUN:
        return 'Sunday';
      default:
        Log::error('unknown $iDaysId: '.$iDayId.' with Calendar::get_weekday_from_id()');
        return '';
    }
  }


}
