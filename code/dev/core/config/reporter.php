<?

switch(Config::$sPrimaryLookup)
{

    case 'domain_admin_albums:front':
      Config::$vRet = array
      (
        'header' => '<span class="btn btn-primary clickable bold" onclick="create_album();">Create Album</span>',
      );
      break;
    case 'domain_admin_albums:back':
      Config::$vRet = array
      (
        'schema' => 'master',
        //'order_by' => 'types.id',



        'columns' => array
        (

          '_edit_delete' => array
          (
            'display' => '',
            'width'=> 20,
            'sortable' => false,
            'edit_onclick' => 'edit_album('.DELIMITER.'album_id'.DELIMITER.')',
            'delete_onclick' => 'delete_album('.DELIMITER.'album_id'.DELIMITER.')',
          ),
          'album_id' => array
          (
            'display' => false,
            //'row_id' => true,
          ),
          'title' => array
          (
            'display' => 'Title',
            'callback' => 'view_album_link',
            'width' => 350,
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
        ),
      );

      $aDefaultWhere = array();

      $sWhere = Reporter::build_where($aDefaultWhere, array());
      $sOrderBy = Reporter::build_order_by(Config::$vRet);

      Config::$vRet['sql'] = "
        SELECT
          albums.id album_id,
          albums.title,
          albums.album_date,
          statuses.id status_id,
          statuses.display status
        FROM
          albums
        LEFT JOIN
          types statuses ON (statuses.id = albums.status_id)
        ".$sWhere."
        ".$sOrderBy;
      break;
    case 'domain_admin_album_view:back':
      Config::$vRet = array
      (
        'schema' => 'master',
        'filters' => array
        (
          'album_id' => array
          (
            'column' => 'images.album_id',
            'data_type' => 'int',
          ),
        ),



        'columns' => array
        (

          '_edit_delete' => array
          (
            'display' => '',
            //'width'=> 20,
            'sortable' => false,
            'edit_onclick' => 'edit_image('.DELIMITER.'image_id'.DELIMITER.')',
            'delete_onclick' => 'delete_image('.DELIMITER.'image_id'.DELIMITER.')',
          ),
          'ord' => array
          (
            'display' => 'Order',
            'sortable' => false,
          ),
          'image_id' => array
          (
            'display' => 'Photo',
            'row_id' => true,
            'left_html' => '<img src="'.BASE_URL.'/image/view/',
            'right_html' => '/mdt/" style="border: 2px solid white; height: 80px; width: 80px;">',
            'sortable' => false,
          ),


          'title' => array
          (
            'display' => 'Title',
            'sortable' => false,
          ),
        ),
      );

      $aDefaultWhere = array();

      $sWhere = Reporter::build_where($aDefaultWhere, Config::$vRet['filters']);
      $sOrderBy = Reporter::build_order_by(Config::$vRet);

      Config::$vRet['sql'] = "
          SELECT
            images.id image_id,
            images.ord,
            images.title,
            images.description
          FROM
            images
        ".$sWhere."
        ".$sOrderBy;
      break;
    case 'page_views:front':
      Config::$vRet = array
      (
        'header' => 'core/views/reports/page_view_header.php',
      );
      break;
    case 'page_views:back':
      Config::$vRet = array
      (
        'schema' => 'master',
        'filters' => array
        (
          'domain_id' => array
          (
            'column' => 'domains.id',
            'data_type' => 'int',
          ),
          'page_view_id' => array
          (
            'column' => 'page_views.id',
            'data_type' => 'int',
          ),
          'url' => array
          (
            'column' => 'page_views.url',
            'data_type' => 'string',
            'comparison' => 'in_string',
          ),
          'request' => array
          (
            'columns' => array
            (
              'page_views.get_params',
              'page_views.post_params',
            ),
            'data_type' => 'string',
            'comparison' => 'in_string',
          ),
        ),
        'order_by' => 'page_views.ts DESC',
        'columns' => array
        (
          'page_view_id' => array
          (
            'display' => 'Page View ID',
            'width' => 135,
          ),
          'session_id' => array
          (
            'display' => 'Session',
          ),
          'domain' => array
          (
            'display' => 'Domain',
            'width' => 200,
          ),
          'url' => array
          (
            'display' => 'URL',
            'width' => 250,
          ),
          'date' => array
          (
            'display' => 'Date',
            'callback' => 'date',
            'width' => 80,
          ),
          'time' => array
          (
            'display' => 'Time',
            'callback' => 'time',
            'width' => 80,
          ),
          'php_time' => array
          (
            'display' => 'PHP Time',
            'width' => 110,
          ),
          'js_time' => array
          (
            'display' => 'JS Time',
            'width' => 100,
          ),
          'ajax_time' => array
          (
            'display' => 'AJAX Time',
            'width' => 120,
          ),
          'total_time' => array
          (
            'display' => 'Total Time',
            'width' => 120,
          ),
          'parent_id' => array
          (
            'display' => 'Parent ID',
            'width' => 110,
          ),
          'peak_mem_usage' => array
          (
            'display' => 'Peak Memory',
            'width' => 140,
            'sort_col' => 'page_views.peak_mem_usage',
          ),
          'code_level' => array
          (
            'display' => 'Code Level',
            'width' => 120,
          ),
          'get_params' => array
          (
            'display' => 'Get',
            'width' => 500,
          ),
          'post_params' => array
          (
            'display' => 'Post',
            'width' => 500,
          ),
        ),
      );

      $sWhere = Reporter::build_where(null, Config::$vRet['filters']);
      $sOrderBy = Reporter::build_order_by(Config::$vRet);

      Config::$vRet['sql'] = "
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
          domains ON (domains.id = sessions.domain_id)
        ".$sWhere."
        ".$sOrderBy;
      break;
    case 'errors:front':
      Config::$vRet = array
      (
        'header' => 'core/views/reports/errors_header.php',
      );
      break;
    case 'errors:back':

      Config::$vRet = array
      (
        'schema' => 'master',
        'filters' => array
        (
          'domain_id' => array
          (
            'column' => 'domains.id',
            'data_type' => 'int',
          ),
          'error_id' => array
          (
            'column' => 'logs.id',
            'data_type' => 'int',
          ),
          'page_view_id' => array
          (
            'column' => 'page_views.id',
            'data_type' => 'int',
          ),
          'error' => array
          (
            'column' => 'logs.msg',
            'data_type' => 'string',
            'comparison' => 'in_string',
          ),
          'file' => array
          (
            'column' => 'logs.file',
            'data_type' => 'string',
            'comparison' => 'in_string',
          ),
        ),
        'order_by' => 'logs.create_ts DESC',
        'columns' => array
        (
          'id' => array
          (
            'display' => 'ID',
          ),
          'page_view_id' => array
          (
            'display' => 'Page View',
          ),
          'domain' => array
          (
            'display' => 'Domain',
            'width' => 250,
          ),
          'date' => array
          (
            'display' => 'Date',
            'callback' => 'date',
          ),
          'time' => array
          (
            'display' => 'Time',
            'callback' => 'time',
          ),
          'type_name' => array
          (
            'display' => 'Type',
          ),
          'msg' => array
          (
            'display' => 'Error',
            'width' => 450
          ),
          'extra' => array
          (
            'display' => 'Extra',
            'width' => 300,
          ),
          'file' => array
          (
            'display' => 'File',
            'width' => 550,
          ),
          'line' => array
          (
            'display' => 'Line',
          ),
        ),
      );

      $aDefaultWhere = array(
        "logs.type_id IN(
            ".Log::TYPE_PHP_ERROR.",
            ".Log::TYPE_JS_ERROR.",
            ".Log::TYPE_SQL_ERROR.",
            ".Log::TYPE_SQL_SLOW.",
            ".Log::TYPE_DEPRECATED.")",
        );


      $sWhere = Reporter::build_where($aDefaultWhere, Config::$vRet['filters']);
      $sOrderBy = Reporter::build_order_by(Config::$vRet);

      Config::$vRet['sql'] = "
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
          page_views ON (page_views.id = logs.page_view_id)
        LEFT JOIN
          sessions ON (sessions.id = page_views.session_id)
        LEFT JOIN
          domains ON (domains.id = logs.domain_id)
        LEFT JOIN
          types ON (types.id = logs.type_id)
        ".$sWhere."
        ".$sOrderBy;
      break;
    case 'logs:front':
      Config::$vRet = array
      (
        'header' => 'core/views/reports/logs_header.php',
      );
      break;
    case 'logs:back':
      Config::$vRet = array
      (
        'schema' => 'master',
        'filters' => array
        (
          'domain_id' => array
          (
            'column' => 'domains.id',
            'data_type' => 'int',
          ),
          'log_id' => array
          (
            'column' => 'logs.id',
            'data_type' => 'int',
          ),
          'page_view_id' => array
          (
            'column' => 'page_views.id',
            'data_type' => 'int',
          ),
          'log' => array
          (
            'column' => 'logs.msg',
            'data_type' => 'string',
            'comparison' => 'in_string',
          ),
          'file' => array
          (
            'column' => 'logs.file',
            'data_type' => 'string',
            'comparison' => 'in_string',
          ),
        ),
        'order_by' => 'logs.create_ts DESC',
        'columns' => array
        (
          'id' => array
          (
            'display' => 'ID',
          ),
          'page_view_id' => array
          (
            'display' => 'Page View',
          ),
          'domain' => array
          (
            'display' => 'Domain',
            'width' => 200,
          ),
          'date' => array
          (
            'display' => 'Date',
            'callback' => 'date',
          ),
          'time' => array
          (
            'display' => 'Time',
            'callback' => 'time',
          ),
          'type_name' => array
          (
            'display' => 'Type',
          ),
          'msg' => array
          (
            'display' => 'Error',
            'width' => 450,
          ),
          'file' => array
          (
            'display' => 'File',
            'width' => 550,
          ),
          'line' => array
          (
            'display' => 'Line',
          ),
        ),
      );

      $aDefaultWhere = array(
        "logs.type_id = ".Log::TYPE_MSG
      );


      $sWhere = Reporter::build_where(null/*$aDefaultWhere*/, Config::$vRet['filters']);
      $sOrderBy = Reporter::build_order_by(Config::$vRet);

      //Log::error('asdf');

      Config::$vRet['sql'] = "
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
          page_views ON (page_views.id = logs.page_view_id)
        LEFT JOIN
          sessions ON (sessions.id = page_views.session_id)
        LEFT JOIN
          domains ON (domains.id = sessions.domain_id)
        LEFT JOIN
          types ON (types.id = logs.type_id)
        ".$sWhere."
        ".$sOrderBy;
      break;
    case 'types:front':
      Config::$vRet = array
      (
      );
      break;
    case 'types:back':
      Config::$vRet = array
      (
        'schema' => 'master',
        'filters' => array
        (
          'domain_id' => array
          (
            'column' => 'domains.id',
            'data_type' => 'int',
          ),
          'error_id' => array
          (
            'column' => 'logs.id',
            'data_type' => 'int',
          ),
          'page_view_id' => array
          (
            'column' => 'page_views.id',
            'data_type' => 'int',
          ),
          'error' => array
          (
            'column' => 'logs.msg',
            'data_type' => 'string',
            'comparison' => 'in_string',
          ),
          'file' => array
          (
            'column' => 'logs.file',
            'data_type' => 'string',
            'comparison' => 'in_string',
          ),
        ),
        'order_by' => 'types.id',
        'columns' => array
        (
          'id' => array
          (
            'display' => 'ID',
          ),
          'parent_type_id' => array
          (
            'display' => 'Parent ID',
          ),
          'type' => array
          (
            'display' => 'Type',
            'width' => 200,
          ),
          'display' => array
          (
            'display' => 'Display',
            'width' => 300,
          ),
          'ord' => array
          (
            'display' => 'Order',
          ),
          'domain' => array
          (
            'display' => 'Domain',
            'width' => 200,
          ),
        ),
      );

      $aDefaultWhere = array();

      $sWhere = Reporter::build_where($aDefaultWhere, Config::$vRet['filters']);
      $sOrderBy = Reporter::build_order_by(Config::$vRet);

      Config::$vRet['sql'] = "
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
           domains ON (domains.id = types.domain_id)
        ".$sWhere."
        ".$sOrderBy;
      break;
    case 'emails:front':
      Config::$vRet = array
      (
      );
      break;
    case 'emails:back':
      Config::$vRet = array
      (
        'schema' => 'master',
        'filters' => array
        (
          'domain_id' => array
          (
            'column' => 'domains.id',
            'data_type' => 'int',
          ),
          'error_id' => array
          (
            'column' => 'logs.id',
            'data_type' => 'int',
          ),
          'page_view_id' => array
          (
            'column' => 'page_views.id',
            'data_type' => 'int',
          ),
          'error' => array
          (
            'column' => 'logs.msg',
            'data_type' => 'string',
            'comparison' => 'in_string',
          ),
          'file' => array
          (
            'column' => 'logs.file',
            'data_type' => 'string',
            'comparison' => 'in_string',
          ),
        ),
        'order_by' => 'emails.wait_ts DESC',
        'columns' => array
        (
          'subject' => array
          (
            'display' => 'Subject',
          ),
          'to_adr' => array
          (
            'display' => 'To',
          ),
          'from_adr' => array
          (
            'display' => 'From',
          ),
          'wait_ts' => array
          (
            'display' => 'Wait Time',
            'callback' => 'date_time',
          ),
          'sent_ts' => array
          (
            'display' => 'Send Time',
            'callback' => 'date_time',
          ),
          'status_name' => array
          (
            'display' => 'Status',
          ),
          'priority_name' => array
          (
            'display' => 'Priority',
          ),
        ),
      );

      $aDefaultWhere = array();

      $sWhere = Reporter::build_where($aDefaultWhere, Config::$vRet['filters']);
      $sOrderBy = Reporter::build_order_by(Config::$vRet);

      Config::$vRet['sql'] = "
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
       -- LEFT JOIN
       --   types email_status ON (
       --     email_status.id = emails.status_id)
        LEFT JOIN
          types priority ON (
            priority.id = emails.priority_id)
        LEFT JOIN
          types status ON (
            status.id = email_recipients.status_id)
        ".$sWhere."
        ".$sOrderBy;
      break;
    case 'scheduler:front':
      Config::$vRet = array
      (
          'modals' => array(
            'core/views/modals/scheduler.php',
          ),
      );
      // @TODO - this isnt really a 'config' - not sure how i feel about this method
      //expose('/code/'.CODE_ENV.'/core/js/scheduler.js');
      Controller::add_js('/code/'.CODE_ENV.'/core/js/scheduler.js', 'body_end');
      break;
    case 'scheduler:back':
      Config::$vRet = array
      (
        'schema' => 'master',
        'filters' => array
        (
          'domain_id' => array
          (
            'column' => 'domains.id',
            'data_type' => 'int',
          ),
          'error_id' => array
          (
            'column' => 'logs.id',
            'data_type' => 'int',
          ),
          'page_view_id' => array
          (
            'column' => 'page_views.id',
            'data_type' => 'int',
          ),
          'error' => array
          (
            'column' => 'logs.msg',
            'data_type' => 'string',
            'comparison' => 'in_string',
          ),
          'file' => array
          (
            'column' => 'logs.file',
            'data_type' => 'string',
            'comparison' => 'in_string',
          ),
        ),
        'order_by' => 'scheduler.id',
        'columns' => array
        (
          '_edit_delete' => array
          (
            // this is such a hack
            'display' => '<div class="clickable" onclick="add_cron();">Add Cron</div>',
            //'width'=> 20,
            'sortable' => false,
            'edit_onclick' => 'edit_cron('.DELIMITER.'id'.DELIMITER.')',
            'delete_onclick' => 'delete_cron('.DELIMITER.'id'.DELIMITER.')',
          ),
          'run_now' => array
          (
            'display' => 'Run Now',
            'left_html' => '<div class="clickable" onclick="run_now(',
            'right_html' => ');"</div>run now</div>',
            'sortable' => false,
          ),
          'id' => array
          (
            'display' => 'ID',
            //'width' => 20,
          ),
          'file' => array
          (
            'display' => 'File',
            'left_html' => '<div id="file_'.DELIMITER.'id'.DELIMITER.'">',
            'right_html' => '</div>',
            'width' => 200,
          ),
          'cron' => array
          (
            'display' => 'Cron',
            'left_html' => '<div id="cron_'.DELIMITER.'id'.DELIMITER.'">',
            'right_html' => '</div>',
            'width' => 200,
          ),
          'next_run_ts' => array
          (
            'display' => 'Next Run',
            'callback' => 'date_time',
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
          ),
          'group_id' => array
          (
            'display' => 'Group ID',
            'left_html' => '<div id="group_'.DELIMITER.'id'.DELIMITER.'">',
            'right_html' => '</div>',
            //'width' => 20,
          ),
        ),
      );

      $aDefaultWhere = array();

      $sWhere = Reporter::build_where($aDefaultWhere, Config::$vRet['filters']);
      $sOrderBy = Reporter::build_order_by(Config::$vRet);

      Config::$vRet['sql'] = "
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
          types statuses ON (statuses.id = scheduler.status_id)
        ".$sWhere."
        ".$sOrderBy;
      break;
}

/*
      case 'training_stats':
        $this->sTitle = 'Training Stats';
        $this->sSchema = 'iamjacksjourney';
        $this->sSql = "
        SELECT
          CONCAT('<a href=\"/training/edit_stat?id=', id ,'\">edit</a>') edit_link,
          name,
          date,
          miles_run,
          bike_mileage,
          push_ups,
          pull_ups,
          sit_ups,
          sprints,
          mat_time,
          weight,
          extra
        FROM
          training
         ";

        $this->sDefaultSortCol = "date";
        $this->sDefaultSortDir = "DESC";
        $this->aColumns = array
        (
          'edit_link' => array
          (
            'display' => '',
            'width'=> 20,
          'sortable' => false,
          ),
          'name' => array
          (
            'display' => 'Name',
          ),
          'date' => array
          (
            'display' => 'Date',
            'callback' => 'date',
          ),
          'miles_run' => array
          (
            'display' => 'Mileage',
          ),
          'bike_mileage' => array
          (
            'display' => 'Bike Mileage',

          ),
          'push_ups' => array
          (
            'display' => 'Push Ups',
          ),
          'pull_ups' => array
          (
            'display' => 'Pull Ups',
          ),
          'sit_ups' => array
          (
            'display' => 'Sit Ups',
          ),
          'sprints' => array
          (
            'display' => 'Sprints',
          ),
          'mat_time' => array
          (
            'display' => 'Mat Time',
          ),
          'weight' => array
          (
            'display' => 'Weight',
          ),
          'extra' => array
          (
            'display' => 'Extra',
          ),
        );

        break;


      case 'bar_albums':
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
          albums.xref_type_id = ".(int)TYPE_XREF_BAR." AND
          albums.xref_id = ".(int)Session::get('login||bar_data||id');

        //expose($this->sSql);

        //$this->sDefaultSortCol = "date";
        //$this->sDefaultSortDir = "DESC";
        $this->aColumns = array
        (
          //<span class="glyphicon glyphicon-search" aria-hidden="true"></span>
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
      case 'bar_events':
        $this->sTitle = 'specials';
        $this->sSchema = 'barmend';
        $this->sSql = "
        SELECT
          specials.id special_id,
          specials.amount,
          specials.display,
          specials.interval_id,
          specials.interval_desc,
          intervals.display interval_name,
          weekdays.display weekday_name
        FROM
          specials
         JOIN
           types intervals on (intervals.id = specials.interval_id)
        LEFT JOIN
          types weekdays on (weekdays.id = specials.interval_desc)";

        //expose($this->sSql);

        //$this->sDefaultSortCol = "date";
        //$this->sDefaultSortDir = "DESC";
        $this->aColumns = array
        (
          //<span class="glyphicon glyphicon-search" aria-hidden="true"></span>
          '_edit_delete' => array
          (
            'display' => '',
            //'width'=> 20,
            'class' => 'col-md-1 center',
            'sortable' => false,
            'edit_onclick' => 'edit_special('.DELIMITER.'special_id'.DELIMITER.')',
            'delete_onclick' => 'delete_special('.DELIMITER.'special_id'.DELIMITER.')',
          ),
          'special_id' => array
          (
            'display' => false,
            'row_id' => true,
          ),
          'amount' => array
          (
            'display' => 'Price',
            'callback' => 'money',
          ),
          'display' => array
          (
            'display' => 'Drink',
          ),
          'interval_name' => array
          (
            'display' => 'Occurrence',
          ),
          'interval_desc' => array
          (
            'display' => 'Description',
            'callback' => 'translate_interval_desc',
          ),

        );

        break;
      case 'bar_album_view':

        //expose($this->oController->aExtra['album_id']);

        //if(isset($this->oController->iAlbumId))
        //  $this->aExtra['album_id'] = $this->oController->iAlbumId;
        $this->sTitle = 'album';
        $this->sSchema = 'barmend';
        $this->sNoRowsMsg = 'This album has no photos.';
        $this->sSql = "
        SELECT
          images.id image_id,
          images.ord,
          images.title,
          images.description
        FROM
          images
        WHERE
          images.album_id = ".(int)$this->aExtra['album_id']."
        ORDER BY
          COALESCE(images.ord, 9999),
          images.ts";

        //expose($this->sSql);

        //$this->sDefaultSortCol = "date";
        //$this->sDefaultSortDir = "DESC";
        $this->aColumns = array
        (

          //<span class="glyphicon glyphicon-search" aria-hidden="true"></span>
          '_edit_delete' => array
          (
            'display' => '',
            //'width'=> 20,
            'class' => 'col-md-1 center',
            'sortable' => false,
            'edit_onclick' => 'edit_image('.DELIMITER.'image_id'.DELIMITER.')',
            'delete_onclick' => 'delete_image('.DELIMITER.'image_id'.DELIMITER.')',
          ),
          'ord' => array
          (
            'display' => 'Order',
            'sortable' => false,
            'class' => 'col-md-1 center',
          ),
          'image_id' => array
          (
            'display' => 'Photo',
            'row_id' => true,
            'left_html' => '<img src="'.BASE_URL.'/image/view/',
            'right_html' => '/mdt/" style="border: 2px solid white; height: 80px; width: 80px;">',
            'sortable' => false,
            'class' => 'col-md-1 center',
          ),
          //'image' => array
         // (
         //   'display' => 'Photo',
         //   //'callback' => 'money',
         // ),

          'title' => array
          (
            'display' => 'Title',
            'sortable' => false,
          ),
        );

        break;
    }
*/