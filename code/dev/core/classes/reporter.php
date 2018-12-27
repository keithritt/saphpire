<?

class Reporter
{

  private $sConfigFile;


  //@TODO - this does a lot of unnecessary stuff when only RunReport() is executed
  function __construct($sId, $oController = null)
  {
    //hit();
    if(isset($oController))
      Log::deprecated_code('Reporter with controller passed in.');

    $this->sId = $sId;
    $this->sConfigFile = CODE_PATH.'/core/config/reporter.php';
    $this->bConfigSet = false;





    //hit();

    //$this->sConfigFile = ;

    //expose($_REQUEST);
    //pr('Reporter->__construct('.$sReport.')');
    //$this->oController = $oController;
    //Controller::add_js('/code/'.CODE_ENV.'/js/reporter.js', 'body_end');
    //Controller::add_css('/code/'.CODE_ENV.'/css/reporter.css');

    //line();
  }

  function set_config_file($sFile)
  {
    //hit();

    $this->sConfigFile = $sFile;
  }

  //TODO - split this up between display and lookup logic  - ie ajax call doesnt need title & front end doenst need sql
  function set_config($sMode)
  {
    //hit();
    if($this->bConfigSet)
      return;

    $this->aCoreData = (array)Config::get(CODE_PATH.'/core/config/reporter.php', $this->sId.':'.$sMode);
    $this->aCustomData = (array)Config::get(CODE_PATH.'/custom/domains/'.DOMAIN.'/config/reporter.php', $this->sId.':'.$sMode);

    $this->aReportData = array_merge($this->aCoreData, $this->aCustomData);
    //expose($this->aReportData);

    if($sMode == 'back' && Request::$bDebugMode && !count($this->aReportData))
      stop('no backend config settings for report: '.$this->sId);
    //line();
    //expose($this->aReportData);

    //$this->sId = $sId;




    switch($sMode)
    {
      case 'front':
        //$this->sTitle = $this->aReportData['title'];
      //line();
      //expose($this->aModals);
        $this->aModals = @Util::coalesce($this->aReportData['modals'], array());
        $this->sHeader = @Util::coalesce($this->aReportData['header'], '');






        //$this->sContainerId = $this->sId.'_report_container';
        //$this->sContainerStyle = 'height: '.$this->iContainerHeight.'px; width: 98%; margin: auto;';
        //expose($this->sHeader);
        break;
      case 'back':
        expose($_REQUEST);
        $this->sSql = $this->aReportData['sql'];
        //expose($this->sSql);
        //$this->aWheres = @Util::coalesce($this->aReportData['wheres'], array());
        $this->oDb = new Db($this->aReportData['schema']);
        $this->iPage = @Util::coalesce($_REQUEST['page'], 1);
        $this->iLimit = @Util::coalesce($_REQUEST['limit'], $this->aReportData['page_size'], 50);

        $iOffset = ($this->iPage - 1) * $this->iLimit;

        $this->sSql.= " LIMIT ".$iOffset.', '.$this->iLimit;

        expose($this->sSql);

        $this->iTitleHeight = @Util::coalesce($this->aReportData['title_height'], 50);
        $this->iScrollBarWidth = 17;
        $this->iDataHeight = @Util::coalesce($this->aReportData['title_height'], 500);

        $this->iContainerHeight = $this->iTitleHeight + $this->iDataHeight;


      //$this->sHeaderStyle = 'xclear: right;';
      //$this->sBodyStyle = 'xoverflow: scroll;  height: 90%;';


        $this->sSortCol = @Util::coalesce($_REQUEST['sort_cal'], $this->aReportData['sort_col']);
        $this->sSortDir = @Util::coalesce($_REQUEST['sort_dir'], $this->aReportData['sort_dir']);
        $this->iTotalRows = null;
        $this->iTotalPages= null;

        break;
    }



    //$this->sContainerId = 'report_container_'.$this->sId;
    //$this->sBodyId = 'report_body_'.$this->sId;
    $this->sHtml = '';
    //$this->sHeader = '';
    $this->sBodyHtml = '';
    $this->sFooterHtml = '';
    $this->sNoRowsMsg = 'No data available.';
    $this->aExtra = (array)Controller::_get('extra');
    //$this->sTopBar = @Util::coalesce($this->aReportData['top_bar']);


    //expose($this->aModals);

    //expose($this->aExtra);

    //line();


    //if(isset())
    //  $this->aExtra = array();

    $this->aSpecialCols = array('_edit', '_delete', '_edit_delete');

    $this->bConfigSet = true;

    //expose($this->sSql);
    return;
    line();


    stop();

    //line();

    $this->oDb = new Db($this->sSchema);


  }

  function set_header_html()
  {
    //hit();
    $this->set_config('front');
    //expose($this->aReportData['js']);
    //stop();

    //$this->sHeaderHtml = '<div id="report_title_'.$this->sId.'" class=" report_title center">'.$this->sTitle.'</div>';
    //if(isset($this->aReportData['top_bar']))
    //  $this->sHeaderHtml.= '<div id="report_top_bar_'.$this->sId.'" class=" report_top_bar">'.$this->aReportData['top_bar'].'</div>';

    //expose($this->sHeader);
    if(trim($this->sHeader) != '' && file_exists(CODE_PATH.'/'.$this->sHeader))
    {
      //line();
      $this->sHeaderHtml = Controller::get_static_view($this->sHeader);
      //expose($this->sHeaderHtml);
      //stop();
    }
    else
    {
      //line();
      $this->sHeaderHtml = $this->sHeader;
    }

    //line();


  }

  function set_body_html()
  {
    //hit();
    $this->set_config('back');


    $aRows = $this->oDb->select_rows_and_count($this->sSql);//, $this->sSchema, $sEnv);
    //line();
    $this->set_totals($aRows['count']);

    //expose($aRows['count']);

    if(!$aRows['count'])
    {
      $this->sBodyHtml = '
      <div class="alert alert-info" role="alert">'.$this->sNoRowsMsg.'
      <span onclick="run_report(\''.$this->sId.'\');" class="glyphicon glyphicon-refresh clickable"></span></div>';
      return;
    }

    $iTableWidth = 0;
    foreach($this->aReportData['columns'] as $sKey => $aColumn)
      $iTableWidth+= @Util::coalesce($aColumn['width'], 100);

    //expose($iTableWidth);
    unset($aRows['count']);
    $sTable = '';

    //$sTable = '<table class="tablex table-borderedx table-stripedx table-hoverx table-condensedx table-responsivex" style="table-layout: fixed; width: '.$iTableWidth.'px;">';
    $sTable.= '<div id="report_titles_wrapper_'.$this->sId.'" style="height: '.$this->iTitleHeight.'px; overflow: hidden; margin: 0; padding: 0;">';
    $sTable.= '<div id="report_titles_'.$this->sId.'"  style="height: '.($this->iTitleHeight + $this->iScrollBarWidth) .'px; width: calc(100% - '.$this->iScrollBarWidth.'px); overflow-y: hidden;">';
    //$sTable.= $sWideText;

    $sTable.= '<table xid="report_titles_'.$this->sId.'" class="table" style="table-layout: fixed; width: '.$iTableWidth.'px;">';
    $sTable.= '<tr>';
    //expose($this->aConfig     foreach($this->aReportData['columns'] as $sKey => $aColumn)
    foreach($this->aReportData['columns'] as $sKey => $aColumn)
    {
      //line();
      //pr($sKey);
      //expose($aColumn);
      if($aColumn['display'] !== false)
      {
        //$sClass = @Util::coalesce($aColumn['class'], '');
        $iWidth = @Util::coalesce($aColumn['width'], 100);
        if($sKey == $this->sSortCol && $this->sSortDir == 'ASC')
          $sDir = 'DESC';
        else
          $sDir = 'ASC';

        //$sTable.= '<th classx="'.$sClass.'" style="width:'.$iWidth.'px; overflow: hidden; white-space: nowrap;">';
        $sTable.= '<td style="width:'.$iWidth.'px; overflow: hidden; white-space: nowrap; borderx:1px solid red;">';
        //

        if(@Util::coalesce($aColumn['sortable'], true))
        {
         // line();
          $sTable.= '
            <span class="column_header">
              <span class="column_title clickable" onclick="run_report(\''.$this->sId.'\', {sort_col: \''.$sKey.'\', sort_dir: \''.$sDir.'\'});">'.$aColumn['display'].'</span>
              <span class="sort_buttons">
                <span class="sort_button clickable up" onclick="run_report(\''.$this->sId.'\', {sort_col: \''.$sKey.'\', sort_dir: \'ASC\'});"></span>
                <span class="sort_button clickable down" onclick="run_report(\''.$this->sId.'\', {sort_col: \''.$sKey.'\', sort_dir: \'DESC\'});"></span>
              </span>
            </span>';
        }
        else
        {
          $sTable.= '<span class="column_title">'.$aColumn['display'].'</span>';
          //$sTable.= $aColumn['display'];
        }

        $sTable.= '</td>';
      }

    }
    $sTable.= '</tr>';
    $sTable.= '</table>';

    $sTable.= '</div>';
    $sTable.= '</div>';
    //$sTable.= '<table id="report_tbody_'.$this->sId.'" style="display: block; overflow: auto; height: 600px;">';
    $sTable.= '<div id="report_data_'.$this->sId.'" style="overflow: auto; height: '.$this->iDataHeight.'px; margin: 0; padding: 0;">';
    //$sTable.= $sWideText;
    //$sTable.= $sTallText;

    $sTable.= '<table class="table table-striped"  style=" table-layout: fixed; width: '.$iTableWidth.'px; ">';

    //expose($aRows);
    //stop();

    //pr($this->sId);

    foreach($aRows as $aRow)
    {
      //continue;
      $sTable.= '<tr>';
      foreach($this->aReportData['columns'] as $sKey => $aColumn)
      {
        //pr($sKey);
        //expose($aColumn);
        if($aColumn['display'] !== false)
        {
          $sClass = @Util::coalesce($aColumn['class'], '');
          if(isset($aColumn['callback']) && method_exists($this, '_'.$aColumn['callback']))
          {
            $sMethod = '_'.$aColumn['callback'];
            $sVal = $this->$sMethod($aRow[$sKey], $aRow);
          }
          elseif(in_array($sKey, $this->aSpecialCols))
          {
            switch($sKey)
            {
              case '_edit':
                $sVal = $this->get_special_col_val('edit', $aColumn, $aRow);
                break;
              case '_delete':
                $sVal = $this->get_special_col_val('delete', $aColumn, $aRow);
                break;
              case '_edit_delete':
              $sVal = $this->get_special_col_val('edit', $aColumn, $aRow);
              $sVal.= Util::sp(2);
              $sVal.= $this->get_special_col_val('delete', $aColumn, $aRow);
            }
          }
          else
            $sVal = Util::array_isset($aRow, $sKey);

          if(isset($aColumn['left_html']))
          {
            $sLeftHtml = $aColumn['left_html'];
            // copying logic from onclick
            if(strpos($sLeftHtml, DELIMITER))
            {
              $iFirstOccurrence = strpos($sLeftHtml, DELIMITER);
              $iLastOccurrence = strrpos($sLeftHtml, DELIMITER);

              $sReplace = substr($sLeftHtml, $iFirstOccurrence + DELIMITER_LENGTH, ($iLastOccurrence - $iFirstOccurrence - DELIMITER_LENGTH));
              $sLeftHtml = str_replace(DELIMITER.$sReplace.DELIMITER, $aRow[$sReplace], $sLeftHtml);
            }
            $sVal = $sLeftHtml.$sVal;
          }

          //@TODO - add DELIMTER replace logic for right_html if needed
          if(isset($aColumn['right_html']))
            $sVal.=$aColumn['right_html'];

          //$sVal = 'x';

          $iWidth = @Util::coalesce($aColumn['width'], 100);

          //$sTable.= '<td style="overflow: hidden; white-space: nowrap; width: '.$iWidth.'px; classx="'.$sClass.'">'.$sVal.'</td>';

          //$sVal = 'x';
          $sTable.= '<td style="width:'.$iWidth.'px; overflow: hidden; white-space: nowrap;">'.$sVal.'</td>';
        }
      }
      $sTable.= '</tr>';
      //break;
    }
   // $sTable.= '</tbody>';
    $sTable.= '</table>';

    $sTable.= '</div>';

      $sNav = '

    <div class="clearfix"></div>
    <ul class="pagination pull-right">
      <li><span class="glyphicon glyphicon-refresh"></span></li>
      <li class="disabled"><span class="glyphicon glyphicon-chevron-left"></span></li>
      <li class="active"><span>Viewing page # of #</span></li>
      <li><a href="#"><span class="glyphicon glyphicon-chevron-right"></span></a></li>
    </ul>';

    $sPrevClass = $sNextClass = 'clickable';
    if($this->iPage == 1)
      $sPrevClass = 'disabled';

    if($this->iPage == $this->iTotalPages)
      $sNextClass = 'disabled';

    $sNav = '
        <ul class="pagination pull-right">
      <li class="clickable"><span onclick="run_report(\''.$this->sId.'\');" class="glyphicon glyphicon-refresh"></span></li>
      <li class="'.$sPrevClass.'"><span onclick="run_report(\''.$this->sId.'\', {iPage: '.($this->iPage - 1).'})">&laquo;</span></li>
      <li class="disabled"><span>Page
      <input type="text" style="
        width: 30px;
        height: 20px;
        display: inline;
        border: 1px solid #c0c0c0;
        color: #999999;
        text-align: center;"
        class="form-controlx" value="'.$this->iPage.'"></input>
       of '.$this->iTotalPages.'</span></li>
      <li class="'.$sNextClass.'"><span onclick="run_report(\''.$this->sId.'\', {iPage: '.($this->iPage + 1).'})">&raquo;</span></li>
    </ul>';

    //$this->iTotalPages = $iTotalRows;

    //expose($sPrevClass);



    //$this->sHtml = $sTable;
    //expose($sNav);

    //print $sTable;
    //die();
    $this->sBodyHtml = $sTable.$sNav;
  }

  private function get_special_col_val($sType, $aColumn, $aRow, $sGlyph = null)
  {
    //pr('get_special_col_val('.$sType.')');
    //line();
    switch($sType)
    {
      case 'edit':
        $sGlyph = @Util::coalesce($sGlyph,'pencil');
        $aColumn['onclick'] = @Util::coalesce($aColumn['edit_onclick'], $aColumn['onclick']);
        break;
      case 'delete':
        $sGlyph = @Util::coalesce($sGlyph,'remove');
        $aColumn['onclick'] = @Util::coalesce($aColumn['delete_onclick'], $aColumn['onclick']);
        break;
    }
    //line();
    $sOnclick = '';
    if(isset($aColumn['onclick']))
    {
      if(strpos($aColumn['onclick'], DELIMITER))
      {
        ///pr('onlick has a delimiter');
        //$aColumn['onclick'] = 'edit_special(MMMspecial_idMMM)';
        //expose($aColumn['onclick']);
        //expose(DELIMITER);
        $iFirstOccurrence = strpos($aColumn['onclick'], DELIMITER);
        $iLastOccurrence = strrpos($aColumn['onclick'], DELIMITER);

        $sReplace = substr($aColumn['onclick'], $iFirstOccurrence + DELIMITER_LENGTH, ($iLastOccurrence - $iFirstOccurrence - DELIMITER_LENGTH));

        //expose($sReplace);
        $aColumn['onclick'] = str_replace(DELIMITER.$sReplace.DELIMITER, $aRow[$sReplace], $aColumn['onclick']);
        //expose($aColumn['onclick']);
        //stop();
      }
      //line();
      $sOnclick = 'onclick="'.$aColumn['onclick'].'"';
    }

    //line();

    return '<span class="glyphicon glyphicon-'.$sGlyph.' clickable" aria-hidden="true" '.$sOnclick.'></span>';
  }

  function set_footer_html()
  {
    //line();


    $this->sFooterHtml = '';
  }

  private function _date($sVal, $aRow)
  {
    //pr('_date()');
    if(!empty($sVal))
      return date(DATE_FORMAT_SHORT, Util::strtotime($sVal));
  }

  private function _time($sVal, $aRow)
  {
    if(!empty($sVal))
      return date(TIME_FORMAT_SHORT, Util::strtotime($sVal));
  }

  private function _date_time($sVal, $aRow)
  {
    //pr('_date()');
    if(!empty($sVal))
      return date(DATE_FORMAT_SHORT.' '.TIME_FORMAT_SHORT, Util::strtotime($sVal));
  }

  private function _money($sVal, $aRow)
  {

    return '$'.number_format($sVal, 2);
  }

  // TODO - use a link class
  private function _view_album_link($sVal, $aRow)
  {

    return '<a href="/admin/albums/view/'.$aRow['album_id'].'">'.$sVal.'</a>';
  }

  public function set_page($iPage)
  {

    $this->iPage = $iPage;
  }

  public function set_limit($iLimit)
  {

    $this->iLimit = $iLimit;
  }

  public function set_offset()
  {
    $this->iOffset = ($this->iPage - 1) * $this->iLimit;
  }

  private function _translate_interval_desc($sVal, $aRow)
  {
    //pr('_translate_interval_desc()');
    switch($aRow['interval_id'])
    {
      case TYPE::_INTERVAL_WEEKLY:
        return $aRow['weekday_name'].'s';
        break;
      case TYPE::_INTERVAL_MONTHLY:
        return date('jS', Util::strtotime('1/'.$aRow['interval_desc'].'/2000')).' of each month';
        break;
      case TYPE::_INTERVAL_ONE_TIME:
      case TYPE::_INTERVAL_YEARLY:
        return $aRow['interval_desc'];
        break;
    }


    //return //date(DATE_FORMAT_SHORT, strtotime($sVal));
  }



  public function set_sort($sSortCol = null, $sSortDir)
  {
    //pr('set_sort('.$sSortCol.','.$sSortDir.')');
    $sSortDir = Util::coalesce($sSortDir, 'ASC');
    //expose($sSortDir);
    if(isset($sSortCol))
    {
      //line();
      $this->sSortCol = $sSortCol;
      $this->sSortDir = $sSortDir;

      //expose($this->sSortCol);
      //expose($this->sSortDir);
    }
    elseif(isset($this->sDefaultSortCol))
    {
      //line();
      $this->sSortCol = $this->sDefaultSortCol;
      //line();
      $this->sSortDir = @Util::coalesce($this->sDefaultSortDir, $sSortDir);
      //line();
    }
    else
    {
      $this->sSortCol = $this->sSortDir = null;
    }
    //line();
  }

  public function set_totals($iTotalRows)
  {
    $this->iTotalRows = $iTotalRows;
    $this->iTotalPages = ceil($iTotalRows / $this->iLimit);
    //expose($this->iTotalPages);
  }

  public function set_sql()
  {
    expose($_REQUEST);
    $iOffset = ($this->iPage - 1) * $this->iLimit;
    //$this->sSql = str_replace("SELECT", "SELECT SQL_CALC_FOUND_ROWS", $this->sSql);
    if(isset($this->sSortCol, $this->sSortDir))
    {
      //line();
      $this->sSql.= " ORDER BY ".$this->sSortCol." ".$this->sSortDir;
    }
    //else
      //line();
    $this->sSql.=" LIMIT ".$iOffset.', '.$this->iLimit;
    expose($this->sSql);
  }

  public function run_report()
  {
    hit();
    //pr('run_report()');
    $this->set_config('front');
    // cant remember what sExtra was for
    Controller::add_footer_js("init_report('".$this->sId."', {iPage: ".$this->iPage.", iLimit: ".$this->iLimit.", sExtra: '".json_encode($this->aExtra)."'})");
    //Controller::add_footer_js("init_report('".$this->sId."', {iPage: ".$this->iPage.", iLimit: ".$this->iLimit."})");
    Controller::add_footer_js("run_report('".$this->sId."')");
  }

  public static function build_order_by($aConfig)
  {
    //hit();
    //expose($aConfig);
    if(isset($_REQUEST['sort_col']))
    {
      $sSortCol = @Util::Coalesce($aConfig['columns'][$_REQUEST['sort_col']]['sort_col'], $_REQUEST['sort_col']);
      $sOrderBy = 'ORDER BY '.$sSortCol;
      if(isset($_REQUEST['sort_dir']))
        $sOrderBy.= ' '.$_REQUEST['sort_dir'];
    }
    elseif(isset($aConfig['order_by']))
      $sOrderBy = 'ORDER BY '.$aConfig['order_by'];
    else
      $sOrderBy = '';

    return $sOrderBy;

  }

  public static function build_limit($aConfig)
  {
    $iPage = @Util::Coalesce($_REQUEST['page'], 1);
    $iLimit = @Util::Coalesce($aConfig['page_size'], 100);

    $iOffset = ($iPage - 1) * $iLimit;

    return " LIMIT ".$iOffset.', '.$iLimit;
  }



  public static function build_where($aDefault, $aFilters = null, $aRequest = null)
  {
    $aRequest = Util::coalesce($aRequest, $_REQUEST);

    $aWheres = (array)$aDefault;

    if(count($aFilters))
    {
      foreach($aFilters as $sId => $aData)
      {
        //expose($aFilters);
        if(isset($aRequest[$sId]) && !empty($aRequest[$sId]))
        {
          $sComp = @Util::coalesce($aData['comparison'], 'in');

          switch($sComp)
          {
            case 'in':
              if(isset($aData['column']))
                $aWheres[] = $aData['column'].' IN '.Db::get_in_sql($aRequest[$sId], $aData['data_type']);
              elseif(isset($aData['columns']))
              {
                $aTmp = array();
                foreach($aData['columns'] as $sColumn)
                  $aTmp[] = $sColumn.' IN '.Db::get_in_sql($aRequest[$sId], $aData['data_type']);

                $aWheres[] = implode($aTmp, " OR \n");
              }
              break;
            case 'in_string':
              if(isset($aData['column']))
                $aWheres[] = $aData['column'].' LIKE '.Db::esc('%'.$aRequest[$sId].'%');
              elseif(isset($aData['columns']))
              {
                $aTmp = array();
                foreach($aData['columns'] as $sColumn)
                  $aTmp[] = $sColumn.' LIKE '.Db::esc('%'.$aRequest[$sId].'%');

                $aWheres[] = implode($aTmp, " OR \n");
              }
              break;
          }
        }
      }
    }

    if(count($aWheres))
      $sRet = "WHERE ".implode($aWheres, " AND \n");
    else
      $sRet = '';

    //expose($aWheres);

    return $sRet;

  }

  public static function build_wherexx($aLookups)
  {
    //pr('build_where');
    //expose($aLookups);
    $aTmp = array();
    foreach($aLookups as $sCol => $vVal)
    {
      if(is_int($vVal))
        $aTmp[] = $sCol.' = '.(int)$vVal;
      elseif(is_numeric($vVal))
        $aTmp[] = $sCol.' = '.$vVal;
      else
        $aTmp[] = $sCol.' = '.self::esc($vVal);


    }

       // expose($aTmp);

      return implode($aTmp, ' AND ');
  }

}




