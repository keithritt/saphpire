<?

class Form
{
	public static function open($sAction, $sTarget = null, $sMethod = 'post')
  {
    //hit();
    //stop();
    $aOptions = array(
      'action' => $sAction,
      'target' => $sTarget,
      'method' => $sMethod);
    return self::_open($aOptions);
  }

  public static function _open($aOptions)
  {
    //hit();
    //stop();
    if(isset($aOptions['target']))
      $sTarget = 'target="'.$aOptions['target'].'"';
    else
      $sTarget = '';
    return '<form method="'.$aOptions['method'].'" '.$sTarget.' action="'.$aOptions['action'].'">';
  }

  public static function close()
  {
    return '</form>';
  }

  public static function get_submit_button($sValue = 'Submit')
  {
    return '<input type="submit" value="'.$sValue.'" />';
  }

  public static function get_dropdown($sName, $aOptions, $vDefault = null, $aAttributes = null)
	{
    //hit();
    return self::_get_dropdown(array('name' => $sName, 'options' => $aOptions, 'default' => $vDefault, 'attributes'=> $aAttributes));
	}

  public static function get_sql_dropdown($sName, $sSql, $sSchema = null, $vDefault = null, $aAttributes = null)
  {
    //hit();
    $sSchema = Util::Coalesce($sSchema, 'master');
    $oDb = Db::static_init($sSchema);
    //expose($oDb->select_array($sSql));
    return self::_get_dropdown(array('name' => $sName, 'options' => $oDb->select_array($sSql), 'default' => $vDefault, 'attributes'=> $aAttributes));
  }


	public static function _get_dropdown($aParams)
	{
		//pr('_get_dropdown()');
		//expose($aParams);
		$sName = $aParams['name'];
    //pr($sName);
		$sId = @Util::coalesce($aParams['id'], $sName);
		$vDefault = @Util::coalesce($aParams['default'], null);
		//expose($vDefault);
		$aOptions = $aParams['options'];


    if(!isset($aParams['attributes']['class']))
    {
      //pr('hit if');
      $aParams['attributes']['class'] = 'selectpicker show-tick';
    }
    //else
    //  pr('hit else');

    $sAttributes = '';
    foreach($aParams['attributes'] as $sKey => $sVal)
      $sAttributes.= $sKey.'="'.$sVal.'" ';

    //$sAttributes = 'multiple="multiple"';
    //$sAttributes.= ' multiple="" title="Select One"';


		$sRet = '<select id="'.$sId.'" name="'.$sName.'" '.$sAttributes.'>';
		foreach($aOptions as $sVal => $vDisplay)
		{
			if(is_array($vDisplay))
			{
				$sRet.= '<optgroup label="'.$sVal.'">';
				foreach($vDisplay as $sSubVal => $sSubDisplay)
				{
					if($sSubVal == $vDefault)
						$sSelected = 'selected="selected"';
					else
						$sSelected = '';
					$sRet.= '<option value="'.$sSubVal.'" '.$sSelected.' >'.$sSubDisplay.'</option>';
				}

				$sRet.= '</optgroup>';
			}
			else
			{
					if($sVal == $vDefault)
						$sSelected = 'selected="selected"';
					else
						$sSelected = '';
				$sRet.= '<option value="'.$sVal.'" '.$sSelected.'>'.$vDisplay.'</option>';
			}
		}
		$sRet.= '</select>';

		return $sRet;

	}

	public static function get_hidden($sName, $sValue = null)
	{

    return self::_get_hidden(array('name' => $sName, 'value' => $sValue));
	}

	public static function _get_hidden($aOptions)
	{
		$sId = @Util::coalesce($aOptions['id'], $aOptions['name']);
		$sClass = @Util::coalesce($aOptions['class'], 'form-control');
		return '<input id="'.$sId.'" type="hidden" name="'.$aOptions['name'].'" value="'.$aOptions['value'].'" class="'.$sClass.'" />';
	}


  //@TODO - add logic to make this an integer only field
  public static function get_integer_textbox($sName, $sClass = null, $sStyle = null)
  {
    return self::_get_textbox(array('name' => $sName, 'class' => $sClass, 'style' => $sStyle));
  }

	public static function get_textbox($sName, $sValue = '', $sClass = null, $sStyle = null, $aAttributes = null)
	{
		return self::_get_textbox(array('name' => $sName, 'value' => $sValue, 'class' => $sClass, 'style' => $sStyle, 'attributes' => $aAttributes));
	}

  public static function get_textbox2($sName, $sValue = '', $aAttributes = null)
  {
    return self::_get_textbox(array('name' => $sName, 'value' => $sValue, 'attributes' => $aAttributes));
  }

	public static function _get_textbox($aOptions)
	{
		$sId = @Util::coalesce($aOptions['id'], $aOptions['name']);
    $aAttributes = @Util::coalesce($aOptions['attributes'], array());
    //expose($aAttributes);
    $sValue = @Util::coalesce($aOptions['value'], '');
		$sClass = @Util::coalesce($aOptions['class'], $aAttributes['class'], 'form-control');
    $sStyle = @Util::coalesce($aOptions['style'], $aAttributes['style'], '');

    unset($aAttributes['class'], $aAttributes['style']);
    $sAttributes = '';

    foreach($aAttributes as $sKey => $sVal)
      $sAttributes.= $sKey.'="'.$sVal.'" ';

		return '
      <input id="'.$sId.'" name="'.$aOptions['name'].'" type="text" value="'.$sValue.'" class="'.$sClass.'" style="'.$sStyle.'" '.$sAttributes.'/>';
	}

  // bootstrap specific
  public static function get_labeled_item($sName, $sLabel, $sHtml, $iSize = null)
  {
    $iSize = Util::coalesce($iSize, 1);
    return '<div class="col-xs-'.$iSize.'">
      <label for="'.$sName.'">'.$sLabel.'</label>
      '.$sHtml.'
    </div>';
  }

  //bootstrap select specific
  public static function get_labeled_select($sName, $sLabel, $sHtml, $iSize = null)
  {
    $iSize = Util::coalesce($iSize, 1);

    //expose_html($sHtml);

    return '
    <div class="rowx">
      <div class="col-xs-'.$iSize.'">
        <label for="'.$sName.'">'.$sLabel.'</label>
        <div class="form-group">
          '.$sHtml.'
        </div>
      </div>
    </div>';
  }

	public static function get_price_textbox($sName = 'price')
	{
		$sId = $sName; // fix later
		return '
		<div class="input-group">
			<span class="input-group-addon">$</span>
			<input id="'.$sId.'" type="text" name="'.$sName.'" class="form-control">
		</div>';
	}

  public static function get_textarea($sName)
  {
    return self::_get_textarea(array('name' => $sName));
  }

  public static function get_textarea2($sName, $sValue = '', $aAttributes = null)
  {
    return self::_get_textarea(array('name' => $sName, 'value' => $sValue, 'attributes' => $aAttributes));
  }

  public static function _get_textarea($aOptions)
  {
    $sId = @Util::coalesce($aOptions['id'], $aOptions['name']);
    $aAttributes = @Util::coalesce($aOptions['attributes'], array());

    //expose($aAttributes);

    $sClass = @Util::coalesce($aOptions['class'], $aAttributes['class'], 'form-control');
    unset($aAttributes['class']);
    $sAttributes = '';

    foreach($aAttributes as $sKey => $sVal)
      $sAttributes.= $sKey.'="'.$sVal.'" ';

    //expose($sAttributes);

    return '<textarea id="'.$sId.'" name="'.$aOptions['name'].'" class="'.$sClass.'" '.$sAttributes.'></textarea>';
  }

  public static function get_date_picker($sId)
  {
    return self::_get_date_picker(array('id' => $sId));
  }

  public static function _get_date_picker($aOptions = array())
  {
    //pr('get_date_picker()');
    //expose($aOptions);
    // not sure if i like this method yet
    //global $oThis;
    //pr('get_data_picker()');
    $sId = @Util::coalesce($aOptions['id'], 'picker_'.uniqid());
    //expose($sId);
    $sName = @Util::coalesce($aOptions['name'], $sId);
    $sDate = @Util::coalesce($aOptions['date'], date('n/j/Y'));

    //$oThis->sId = $sId;
    //$oThis->sDate = $sDate;
    //expose($sDate);
    //die();
    //$sMonthStart = str_replace('/', '/1/', $sMonth); // inject a 1 for strtotime() lookups
    //$aCalendar = get_calendar_array(array('month' => $sMonth));
    //glyphicon glyphicon-calendar

    /*
                            <span class="btn-group">
                                <a class="btn"><i class="icon-chevron-left"></i></a>
                              <a class="btn active">'.date('F Y', strtotime($sMonthStart)).'</a>
                              <a class="btn"><i class="icon-chevron-right"></i></a>
                            </span>
    */

    $aMonths = array();
    $aMonths[1] = array('name' => 'January', 'abbr' => 'Jan');
    $aMonths[2] = array('name' => 'February', 'abbr' => 'Feb');
    $aMonths[3] = array('name' => 'March', 'abbr' => 'Mar');
    $aMonths[4] = array('name' => 'April', 'abbr' => 'Apr');
    $aMonths[5] = array('name' => 'May', 'abbr' => 'May');
    $aMonths[6] = array('name' => 'June', 'abbr' => 'Jun');
    $aMonths[7] = array('name' => 'July', 'abbr' => 'Jul');
    $aMonths[8] = array('name' => 'August', 'abbr' => 'Aug');
    $aMonths[9] = array('name' => 'September', 'abbr' => 'Sep');
    $aMonths[10] = array('name' => 'October', 'abbr' => 'Oct');
    $aMonths[11] = array('name' => 'November', 'abbr' => 'Nov');
    $aMonths[12] = array('name' => 'December', 'abbr' => 'Dec');

    $iStartYear = date('Y');
    $iStopYear = $iStartYear + 5;
    $sRet = '
    <div class="containerx">
      <div class="row">
        <div class="col-sm-5">
          <input id="'.$sId.'" name="'.$sName.'" type="textbox" class="form-control" autocomplete="off"
          placeholder="mm/dd/yyyy" onchange="oNs.oDatePicker[\''.$sId.'\'].change_date();">
        </div>
        <div classx="col-sm-3>
          <button type="button" class="btn btn-default"
          onclick="oNs.oDatePicker[\''.$sId.'\'].toggle_calendar();"><span class="glyphicon glyphicon-calendar"></button>
        </div>
      </div>
      <div id="'.$sId.'_button_row" class="row" style="display:none;">
      <button onclick="oNs.oDatePicker[\''.$sId.'\'].prev_month(true);" type="button" class="btn btn-default"><span class="glyphicon glyphicon-triangle-left"></button>

      <select id="'.$sId.'_month" onchange="oNs.oDatePicker[\''.$sId.'\'].set_month(this.value, true);" class="selectpicker show-tick" data-width="65px">';
    foreach($aMonths as $iKey => $aMonth)
    {
      $sRet.= '<option value="'.$iKey.'" title="'.$aMonth['abbr'].'">'.$aMonth['name'].'</option>';
    }
    $sRet.= '
      </select>
      <select id="'.$sId.'_year" onchange="oNs.oDatePicker[\''.$sId.'\'].set_year(this.value, true);" class="selectpicker show-tick" data-width="75px">';
      for($iWalk = $iStartYear; $iWalk <= $iStopYear; $iWalk++)
        $sRet.= '<option value="'.$iWalk.'">'.$iWalk.'</option>';

      $sRet.= '
      </select>
       <button onclick="oNs.oDatePicker[\''.$sId.'\'].next_month(true);" type="button" class="btn btn-default"><span class="glyphicon glyphicon-triangle-right"></button>
      </div>
      <div id="'.$sId.'_calendar_row" class="row" style="display:none;">
            <div class="span12x">
            <table class="table-condensed table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Su</th>
                            <th>Mo</th>
                            <th>Tu</th>
                            <th>We</th>
                            <th>Th</th>
                            <th>Fr</th>
                            <th>Sa</th>
                        </tr>
                    </thead>
                    <tbody>';
    for($iWeekWalk = 1; $iWeekWalk <= 6; $iWeekWalk++)
      {
        $sRet.= '<tr id="'.$sId.'_week_'.$iWeekWalk.'">';
        for($iDayWalk = 1; $iDayWalk <= 7; $iDayWalk++)
        {
          $sRet.= '<td id="'.$sId.'_week_'.$iWeekWalk.'_day_'.$iDayWalk.'" class="center clickable"
          onclick="oNs.oDatePicker[\''.$sId.'\'].click_date(this.id);"></td>';
        }
        $sRet.= '</tr>';
      }
      $sRet.= '
                      </tbody>
                  </table>
              </div>
        </div>
      </div>';

      return $sRet;
    }

  public static function _get_date_picker_old($aOptions = array())
  {
    //pr('get_data_picker()');
    $sId = @Util::coalesce($aOptions['id'], 'picker_'.uniqid());
    $sName = @Util::coalesce($aOptions['name'], $sId);
    $sMonth = @Util::coalesce($aOptions['month'], date('n/Y'));
    $sMonthStart = str_replace('/', '/1/', $sMonth); // inject a 1 for strtotime() lookups
    $aCalendar = get_calendar_array(array('month' => $sMonth));
    //glyphicon glyphicon-calendar

    /*
                            <span class="btn-group">
                                <a class="btn"><i class="icon-chevron-left"></i></a>
                              <a class="btn active">'.date('F Y', strtotime($sMonthStart)).'</a>
                              <a class="btn"><i class="icon-chevron-right"></i></a>
                            </span>
    */

		$aMonths = array();
		$aMonths[1] = array('name' => 'January', 'abbr' => 'Jan');
		$aMonths[2] = array('name' => 'February', 'abbr' => 'Feb');
		$aMonths[3] = array('name' => 'March', 'abbr' => 'Mar');
		$aMonths[4] = array('name' => 'April', 'abbr' => 'Apr');
		$aMonths[5] = array('name' => 'May', 'abbr' => 'May');
		$aMonths[6] = array('name' => 'June', 'abbr' => 'Jun');
		$aMonths[7] = array('name' => 'July', 'abbr' => 'Jul');
		$aMonths[8] = array('name' => 'August', 'abbr' => 'Aug');
		$aMonths[9] = array('name' => 'September', 'abbr' => 'Sep');
		$aMonths[10] = array('name' => 'October', 'abbr' => 'Oct');
		$aMonths[11] = array('name' => 'November', 'abbr' => 'Nov');
		$aMonths[12] = array('name' => 'December', 'abbr' => 'Dec');

		$iStartYear = date('Y');
		$iStopYear = $iStartYear + 5;
    $sRet = '
    <div class="container">
			<div class="row">
				<input id="'.$sId.'" name="'.$sName.'" type="textbox" class-"form-control">
				<button type="button" class="btn btn-default"><span class="glyphicon glyphicon-calendar"></button>
			</div>
      <div class="row">
			<button type="button" class="btn btn-default"><span class="glyphicon glyphicon-triangle-left"></button>

			<select class="selectpicker show-tick" data-width="65px">';
		foreach($aMonths as $iKey => $aMonth)
		{
			$sRet.= '<option value="'.$iKey.'" title="'.$aMonth['abbr'].'">'.$aMonth['name'].'</option>';
		}
		$sRet.= '
			</select>
			<select class="selectpicker show-tick" data-width="70px">';
			for($iWalk = $iStartYear; $iWalk <= $iStopYear; $iWalk++)
				$sRet.= '<option value="'.$iWalk.'">'.$iWalk.'</option>';

			$sRet.= '
			</select>
       <button type="button" class="btn btn-default"><span class="glyphicon glyphicon-triangle-right"></button>
      </div>
      <div class="row">
            <div class="span12x">
            <table class="table-condensed table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Su</th>
                            <th>Mo</th>
                            <th>Tu</th>
                            <th>We</th>
                            <th>Th</th>
                            <th>Fr</th>
                            <th>Sa</th>
                        </tr>
                    </thead>
                    <tbody>';
      foreach($aCalendar as $aWeek)
      {
        $sRet.= '<tr>';
        foreach($aWeek as $aDay)
        {
          $sClass = 'center';
          if(isset($aDay['class']))
          {
            //line();
            //expose($aDay['class']);
            switch($aDay['class'])
            {
              case 'prev':
              case 'next':
                $sClass.= ' muted';
                break;
            }
          }
          $sRet.= '<td class="'.$sClass.'">'.$aDay['date'].'</td>';
        }
        $sRet.= '</tr>';
      }
      $sRet.= '
                    </tbody>
                </table>
            </div>
      </div>
    </div>';

    return $sRet;
  }

}