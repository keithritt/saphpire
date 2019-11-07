<?

class Reporter
{
  //@TODO - this does a lot of unnecessary stuff when only RunReport() is executed
  function __construct($sReport, $oController = null)
  {
    if(isset($oController))
      Log::deprecated_code('Reporter with controller passed in.');

    Controller::add_js('/code/'.CODE_ENV.'/js/reporter.js', 'body_end');
    Controller::add_css('/code/'.CODE_ENV.'/css/reporter.css');

    $this->sReport = $sReport;
    $this->iPage = 1;
    $this->iLimit = 25;
    $this->sSort = null;
    $this->iTotalRows = null;
    $this->iTotalPages= null;
    $this->sContainerId = 'report_container';
    $this->sHtml = '';
    $this->sHeaderHtml = '';
    $this->sBodyHtml = '';
    $this->sFooterHtml = '';
    $this->sNoRowsMsg = 'No data available.';
    $this->aExtra = (array)Controller::_get('extra');

    $this->aSpecialCols = array('_edit', '_delete', '_edit_delete');

    switch($this->sReport)
    {
      case 'page_views':
        $this->sTitle = 'Page Views';
        $this->sSchema = 'master';
        $this->sSql = "
        SELECT
         domains.id domain_id,
         domains.domain,
         sessions.id session_id,
         sessions.name session_name,
         page_views.id page_view_id,
         page_views.url,
         page_views.ts date,
         page_views.ts time,
         page_views.php_time,
         page_views.js_time,
         page_views.ajax_time,
         page_views.total_time,
         page_views.parent_id,
         CONCAT(ROUND((page_views.peak_mem_usage / 1000000), 1), ' MB') peak_mem_usage,
         page_views.code_level,
         page_views.get_params,
         page_views.post_params
        FROM
          page_views
        LEFT JOIN
          sessions ON (sessions.id = page_views.session_id)
        LEFT JOIN
          domains ON (domains.id = sessions.domain_id)";

        $this->sDefaultSortCol = 'ts';
        $this->sDefaultSortDir = 'DESC';
        $this->aColumns = array
        (
          'page_view_id' => array
          (
            'display' => 'Page View',
            'class' => 'col-md-1',
          ),
          'session_id' => array
          (
            'display' => 'Session',
            'class' => 'col-md-1',
          ),
          'domain' => array
          (
            'display' => 'Domain',
            'class' => 'col-md-1',
          ),
          'url' => array
          (
            'display' => 'URL',
            'class' => 'col-md-1',
          ),
          'date' => array
          (
            'display' => 'Date',
            'callback' => 'date',
            'class' => 'col-md-1',
          ),
          'time' => array
          (
            'display' => 'Time',
            'callback' => 'time',
            'class' => 'col-md-1',
          ),
          'php_time' => array
          (
            'display' => 'PHP Time',
            'class' => 'col-md-1',
          ),
          'js_time' => array
          (
            'display' => 'JS Time',
            'class' => 'col-md-1',
          ),
          'ajax_time' => array
          (
            'display' => 'AJAX Time',
            'class' => 'col-md-1',
          ),
          'total_time' => array
          (
            'display' => 'Total Time',
            'class' => 'col-md-1',
          ),
          'parent_id' => array
          (
            'display' => 'Parent ID',
            'class' => 'col-md-1',
          ),
          'peak_mem_usage' => array
          (
            'display' => 'Peak Memory',
            'class' => 'col-md-1',
          ),
          'code_level' => array
          (
            'display' => 'Code Level',
            'class' => 'col-md-1',
          ),
          'get_params' => array
          (
            'display' => 'Get',
            'class' => 'col-md-2',
          ),
          'post_params' => array
          (
            'display' => 'Post',
            'class' => 'col-md-2',
          ),
        );

        break;
      case 'emails':
        $this->sTitle = 'Emails';
        $this->sSchema = 'master';
        $this->sSql = "
        SELECT
         emails.id email_id,
         emails.subject,
         emails.from_adr,
         emails.wait_ts,
         email_recipients.id email_recipient_id,
         email_recipients.email_address to_adr,
         email_recipients.sent_ts,
         COALESCE(status.display, status.type) status_name,
         COALESCE(priority.display, priority.type) priority_name
        FROM
          emails
        LEFT JOIN
          email_recipients ON (
            email_recipients.email_id = emails.id)
        LEFT JOIN
          types priority ON (
            priority.id = emails.priority_id)
        LEFT JOIN
          types status ON (
            status.id = email_recipients.status_id)
         ";

        $this->sDefaultSortCol = 'emails.wait_ts';
        $this->sDefaultSortDir = 'DESC';
        $this->aColumns = array
        (
          'subject' => array
          (
            'display' => 'Subject',
            'class' => 'col-md-1',
          ),
          'to_adr' => array
          (
            'display' => 'To',
            'class' => 'col-md-1',
          ),
          'from_adr' => array
          (
            'display' => 'From',
            'class' => 'col-md-1',
          ),
          'wait_ts' => array
          (
            'display' => 'Wait Time',
            'class' => 'col-md-1',
            'callback' => 'date_time',
          ),
          'sent_ts' => array
          (
            'display' => 'Send Time',
            'class' => 'col-md-1',
            'callback' => 'date_time',
          ),
          'status_name' => array
          (
            'display' => 'Status',
            'class' => 'col-md-1',
          ),
          'priority_name' => array
          (
            'display' => 'Priority',
            'class' => 'col-md-1',
          ),
        );

        break;
      case 'errors':
        $this->sTitle = 'Errors';
        $this->sSchema = 'master';
        $this->sSql = "
        SELECT
          logs.id,
          logs.msg,
          logs.create_ts date,
          logs.create_ts time,
          logs.file,
          logs.extra,
          logs.line,
          logs.cat cat_id,
          page_views.id page_view_id,
          domains.domain,
          COALESCE(types.display, types.type) type_name
        FROM
          logs
        LEFT JOIN
          page_views ON (
            page_views.id = logs.page_view_id)
        LEFT JOIN
          sessions ON (sessions.id = page_views.session_id)
        LEFT JOIN
          domains ON (
            domains.id = sessions.domain_id)
        LEFT JOIN
          types ON (
            types.id = logs.type_id)
        WHERE
          logs.type_id IN(
            ".Log::TYPE_PHP_ERROR.",
            ".Log::TYPE_JS_ERROR.",
            ".Log::TYPE_SQL_ERROR.",
            ".Log::TYPE_SQL_SLOW.",
            ".Log::TYPE_DEPRECATED.")";

        $this->sDefaultSortCol = 'logs.create_ts';
        $this->sDefaultSortDir = 'DESC';
        $this->aColumns = array
        (
          'id' => array
          (
            'display' => 'ID',
            'class' => 'col-md-1',
          ),
          'page_view_id' => array
          (
            'display' => 'Page View',
            'class' => 'col-md-1',
          ),
          'domain' => array
          (
            'display' => 'Domain',
            'class' => 'col-md-1',
          ),
          'date' => array
          (
            'display' => 'Date',
            'callback' => 'date',
            'class' => 'col-md-1',
          ),
          'time' => array
          (
            'display' => 'Time',
            'callback' => 'time',
            'class' => 'col-md-1',
          ),
          'type_name' => array
          (
            'display' => 'Type',
            'class' => 'col-md-1',
          ),
          'msg' => array
          (
            'display' => 'Error',
            'class' => 'col-md-2',
          ),
          'extra' => array
          (
            'display' => 'Extra',
            'class' => 'col-md-2',
          ),
          'file' => array
          (
            'display' => 'File',
            'class' => 'col-md-1',
          ),
          'line' => array
          (
            'display' => 'Line',
            'class' => 'col-md-1',
          ),
        );

        break;
      case 'logs':
        $this->sTitle = 'Logs';
        $this->sSchema = 'master';
        $this->sSql = "
        SELECT
          logs.id,
          logs.msg,
          logs.create_ts date,
          logs.create_ts time,
          logs.file,
          logs.line,
          logs.cat cat_id,
          page_views.id page_view_id,
          domains.domain,
          COALESCE(types.display, types.type) type_name
        FROM
          logs
        LEFT JOIN
          page_views ON (
            page_views.id = logs.page_view_id)
        LEFT JOIN
          sessions ON (sessions.id = page_views.session_id)
        LEFT JOIN
          domains ON (
            domains.id = sessions.domain_id)
        LEFT JOIN
          types ON (
            types.id = logs.type_id)
        WHERE
          logs.type_id = ".Log::TYPE_MSG;

        $this->sDefaultSortCol = 'logs.create_ts';
        $this->sDefaultSortDir = 'DESC';
        $this->aColumns = array
        (
          'id' => array
          (
            'display' => 'ID',
            'class' => 'col-md-1',
          ),
          'page_view_id' => array
          (
            'display' => 'Page View',
            'class' => 'col-md-1',
          ),
          'domain' => array
          (
            'display' => 'Domain',
            'class' => 'col-md-1',
          ),
          'date' => array
          (
            'display' => 'Date',
            'callback' => 'date',
            'class' => 'col-md-1',
          ),
          'time' => array
          (
            'display' => 'Time',
            'callback' => 'time',
            'class' => 'col-md-1',
          ),
          'type_name' => array
          (
            'display' => 'Type',
            'class' => 'col-md-1',
          ),
          'msg' => array
          (
            'display' => 'Error',
            'class' => 'col-md-2',
          ),
          'file' => array
          (
            'display' => 'File',
            'class' => 'col-md-1',
          ),
          'line' => array
          (
            'display' => 'Line',
            'class' => 'col-md-1',
          ),
        );

        break;
      case 'types':
        $this->sTitle = 'Types';
        $this->sSchema = 'master';
        $this->sSql = "
        SELECT
          types.id,
          types.parent_type_id,
          types.type,
          types.display,
          types.ord,
          domains.domain
        FROM
          types
        LEFT JOIN
           domains ON (
              domains.id = types.domain_id)";
        $this->sDefaultSortCol = "types.id";
        $this->aColumns = array
        (
          'id' => array
          (
            'display' => 'ID',
            'class' => 'col-md-1',
          ),
          'parent_type_id' => array
          (
            'display' => 'Parent ID',
            'class' => 'col-md-1',
          ),
          'type' => array
          (
            'display' => 'Type',
            'class' => 'col-md-1',
          ),
          'display' => array
          (
            'display' => 'Display',
            'class' => 'col-md-1',
          ),
          'ord' => array
          (
            'display' => 'Order',
            'class' => 'col-md-1',
          ),
          'domain' => array
          (
            'display' => 'Domain',
            'class' => 'col-md-1',
          ),
        );

        break;
      case 'scheduler':
        $this->oController->add_js('/application/js/officialloop_com/scheduler.js', 'body_end');
        $this->sTitle = 'Scheduler';
        $this->sSchema = 'master';
        $this->sSql = "
        SELECT
          scheduler.id,
          scheduler.id run_now,
          scheduler.file,
          scheduler.status_id,
          scheduler.next_run_ts,
          scheduler.group_id,
          scheduler.cron,
          COALESCE(statuses.display, statuses.type) status_name
        FROM
          scheduler
        LEFT JOIN
          types statuses ON (
            statuses.id = scheduler.status_id)
         ";

        $this->sDefaultSortCol = 'id';
        $this->aColumns = array
        (
          '_edit_delete' => array
          (
            // this is such a hack
            'display' => '<div class="clickable" onclick="add_cron();">Add Cron</div>',
            //'width'=> 20,
            'class' => 'col-md-1 center',
            'sortable' => false,
            'edit_onclick' => 'edit_cron('.DELIMITER.'id'.DELIMITER.')',
            'delete_onclick' => 'delete_cron('.DELIMITER.'id'.DELIMITER.')',
          ),
          'run_now' => array
          (
            'display' => 'Run Now',
            'left_html' => '<div class="clickable" onclick="run_now(',
            'right_html' => ');"</div>run now</div>',
            'class' => 'col-md-1',
            'sortable' => false,
          ),
          'id' => array
          (
            'display' => 'ID',
            //'width' => 20,
            'class' => 'col-md-1',
          ),
          'file' => array
          (
            'display' => 'File',
            'left_html' => '<div id="file_'.DELIMITER.'id'.DELIMITER.'">',
            'right_html' => '</div>',
            //'width' => 200,
          ),
          'cron' => array
          (
            'display' => 'Cron',
            'class' => 'col-md-1',
            'left_html' => '<div id="cron_'.DELIMITER.'id'.DELIMITER.'">',
            'right_html' => '</div>',
            //'width' => 200,
          ),
          'next_run_ts' => array
          (
            'display' => 'Next Run',
            'callback' => 'date_time',
            'class' => 'col-md-1',
          ),

          'status_id' => array
          (
            'display' => '',
            'left_html' => '<div id="status_'.DELIMITER.'id'.DELIMITER.'">',
            'right_html' => '</div>',
            'class' => 'hidden',
          ),

          'status_name' => array
          (
            'display' => 'Status',
            //'left_html' => '<div id="status_'.DELIMITER.'id'.DELIMITER.'">',
            //'right_html' => '</div>',
            //'callback' => 'date',
            'class' => 'col-md-1',

          ),


          'group_id' => array
          (
            'display' => 'Group ID',
            'class' => 'col-md-1',
            'left_html' => '<div id="group_'.DELIMITER.'id'.DELIMITER.'">',
            'right_html' => '</div>',
            //'width' => 20,
          ),
        );

        //@TODO - this is unnessesary for ajax call
        $aTmp = array();
        $aTmp['id'] = 'edit_cron_modal';
        $aTmp['title'] = 'Add New Cron';
        $aTmp['body'] = '
        <form class="form-horizontal">';
        $aTmp['body'].= Form::_get_hidden('cron_id', 'new');
        $aTmp['body'].=
        '<label class="col-sm-2 control-label">File:</label>
              <div class="col-sm-10">';
        $aTmp['body'].= Form::_get_textbox('file');
        $aTmp['body'].= '</div>';

        $aTmp['body'].=
        '<label class="col-sm-2 control-label">Cron:</label>
              <div class="col-sm-10">';
        $aTmp['body'].= Form::_get_textbox('cron');
        $aTmp['body'].= '</div>';

        $aTmp['body'].=
        '<label class="col-sm-2 control-label">Status:</label>
              <div class="col-sm-10">';

        $aStatuses = array(
          TYPE_SCHEDULER_STATUS_DISABLED => 'Disabled',
          TYPE_SCHEDULER_STATUS_READY => 'Ready',
          TYPE_SCHEDULER_STATUS_RUNNING => 'Running',

          );

        $aTmp['body'].= Form::_get_dropdown('status', $aStatuses);
        $aTmp['body'].= '</div>';

        $aTmp['body'].=
        '<label class="col-sm-2 control-label">Group:</label>
              <div class="col-sm-10">';
        $aTmp['body'].= Form::_get_textbox('group');
        $aTmp['body'].= '</div>';

        $aTmp['footer'] = '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-default" onclick="save_cron();">Save Cron</button>';

        $this->oController->add_modal($aTmp);

        break;

      case 'albums':
        $this->sTitle = 'albums';
        $this->sSchema = 'barmend';
        $this->sSql = "
        SELECT
          albums.id album_id,
          albums.title,
          albums.album_date,
          statuses.id status_id,
          statuses.display status
        FROM
          albums
        LEFT JOIN
          ".MASTER_SCHEMA.".types statuses ON (statuses.id = albums.status_id)
        WHERE
          albums.xref_id = ".(int)Session::get('login||data||id');

        $this->aColumns = array
        (
          '_edit_delete' => array
          (
            'display' => '',
            //'width'=> 20,
            'class' => 'col-md-1 center',
            'sortable' => false,
            'edit_onclick' => 'edit_album('.DELIMITER.'album_id'.DELIMITER.')',
            'delete_onclick' => 'delete_album('.DELIMITER.'album_id'.DELIMITER.')',
          ),
          'album_id' => array
          (
            'display' => false,
            'row_id' => true,
          ),
          'title' => array
          (
            'display' => 'Title',
            'callback' => 'view_album_link',
          ),
          'album_date' => array
          (
            'display' => 'Date',
            'callback' => 'date',
          ),
          'status' => array
          (
            'display' => 'Status',
            //'callback' => 'date',
            'sortable' => false,
          ),
        );

        break;
    }

    $this->oDb = new Db($this->sSchema);
  }

  function set_html()
  {
    $this->set_body_html();
    $this->set_footer_html();
    $this->sHtml =  '
    <div id="'.$this->sContainerId.'">
      <div id="'.$this->sContainerId.'_header" class="header">'.$this->sHeaderHtml.'</div>
      <div id="'.$this->sContainerId.'_body" class="body">'.$this->sBodyHtml.'</div>
      <div id="'.$this->sContainerId.'_footer" class="footer">'.$this->sFooterHtml.'</div>
    </div>';
  }

  function set_header_html()
  {
    $this->sHeaderHtml = '';
  }

  function set_body_html()
  {
    $aRows = $this->oDb->select_rows_and_count($this->sSql);//, $this->sSchema, $sEnv);
    $this->set_totals($aRows['count']);

    if(!$aRows['count'])
    {
      $this->sBodyHtml = '
      <div class="alert alert-info" role="alert">'.$this->sNoRowsMsg.'
      <span onclick="run_report(\''.$this->sReport.'\');" class="glyphicon glyphicon-refresh clickable"></span></div>';
      return;
    }
    unset($aRows['count']);
    $sTable = '<table class="table table-bordered table-striped table-hover table-condensed table-responsive">';
    $sTable.= '<tr>';
    foreach($this->aColumns as $sKey => $aColumn)
    {
      if($aColumn['display'] !== false)
      {
        $sClass = @Util::coalesce($aColumn['class'], 'col-md-3');
        if($sKey == $this->sSortCol && $this->sSortDir == 'ASC')
          $sDir = 'DESC';
        else
          $sDir = 'ASC';

        $sTable.= '<td class="'.$sClass.'">';
        //

        if(@Util::coalesce($aColumn['sortable'], true))
        {
          $sTable.= '<span class="column_header clickable" onclick="run_report(\''.$this->sReport.'\', {sSortCol: \''.$sKey.'\', sSortDir: \''.$sDir.'\'});">'.$aColumn['display'].'</span>';
          $sTable.= '
              <span class="sort_buttons">
                <span class="sort_button clickable up" onclick="run_report(\''.$this->sReport.'\', {sSortCol: \''.$sKey.'\', sSortDir: \'ASC\'});">&nbsp;</span>
                <span class="sort_button clickable down" onclick="run_report(\''.$this->sReport.'\', {sSortCol: \''.$sKey.'\', sSortDir: \'DESC\'});">&nbsp;</span>

              </span>';
        }
        else
          $sTable.= '<span class="column_header">'.$aColumn['display'].'</span>';

        $sTable.= '</td>';
      }

    }
    $sTable.'</tr>';

    foreach($aRows as $aRow)
    {
      $sTable.= '<tr>';
      foreach($this->aColumns as $sKey => $aColumn)
      {
        if($aColumn['display'] !== false)
        {
          $sClass = @Util::coalesce($aColumn['class'], 'col-md-3');
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
            $sVal = $aRow[$sKey];

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

          $sTable.= '<td class="'.$sClass.'">'.$sVal.'</td>';
        }
      }
      $sTable.= '</tr>';
    }
    $sTable.= '</table>';

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
      <li class="clickable"><span onclick="run_report(\''.$this->sReport.'\');" class="glyphicon glyphicon-refresh"></span></li>
      <li class="'.$sPrevClass.'"><span onclick="run_report(\''.$this->sReport.'\', {iPage: '.($this->iPage - 1).'})">&laquo;</span></li>
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
      <li class="'.$sNextClass.'"><span onclick="run_report(\''.$this->sReport.'\', {iPage: '.($this->iPage + 1).'})">&raquo;</span></li>
    </ul>';

    //$this->sHtml = $sTable;
    $this->sBodyHtml = $sTable.$sNav;
  }

 

  function set_footer_html()
  {
    $this->sFooterHtml = '';
  }

  private function _date($sVal, $aRow)
  {
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

  private function _translate_interval_desc($sVal, $aRow)
  {
    switch($aRow['interval_id'])
    {
      case TYPE_INTERVAL_WEEKLY:
        return $aRow['weekday_name'].'s';
        break;
      case TYPE_INTERVAL_MONTHLY:
        return date('jS', Util::strtotime('1/'.$aRow['interval_desc'].'/2000')).' of each month';
        break;
      case TYPE_INTERVAL_ONE_TIME:
      case TYPE_INTERVAL_YEARLY:
        return $aRow['interval_desc'];
        break;
    }
  }

  public function set_limit($iLimit)
  {
    $this->iLimit = $iLimit;
  }

  public function set_sort($sSortCol = null, $sSortDir)
  {
    $sSortDir = Util::coalesce($sSortDir, 'ASC');
    if(isset($sSortCol))
    {
      $this->sSortCol = $sSortCol;
      $this->sSortDir = $sSortDir;
    }
    elseif(isset($this->sDefaultSortCol))
    {
      $this->sSortCol = $this->sDefaultSortCol;
      $this->sSortDir = @Util::coalesce($this->sDefaultSortDir, $sSortDir);
    }
    else
    {
      $this->sSortCol = $this->sSortDir = null;
    }
  }

  public function set_totals($iTotalRows)
  {
    $this->iTotalRows = $iTotalRows;
    $this->iTotalPages = ceil($iTotalRows / $this->iLimit);
  }

  public function set_sql()
  {
    $iOffset = ($this->iPage - 1) * $this->iLimit;
    if(isset($this->sSortCol, $this->sSortDir))
    {
      $this->sSql.= " ORDER BY ".$this->sSortCol." ".$this->sSortDir;
    }
    $this->sSql.=" LIMIT ".$iOffset.', '.$this->iLimit;
  }

  public function run_report()
  {
    // cant remember what sExtra wsa for
    Controller::add_footer_js("init_report('".$this->sReport."', {iPage: ".$this->iPage.", iLimit: ".$this->iLimit.", sExtra: '".json_encode($this->aExtra)."'})");
    Controller::add_footer_js("run_report('".$this->sReport."')");
  }
}
