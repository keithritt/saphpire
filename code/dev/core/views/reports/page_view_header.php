<?

$aHtml = array();

$aHtml[] = '<div class="row">';

$sDropdown = Form::get_sql_dropdown(
              'domain_id',
              "SELECT id, domain FROM domains ORDER BY domain",
              'master',
              null,
              array
              (
                'multiple' => 1,
                'title' => "",
                'filter' => 1,
                'class' => 'selectpicker form-control show-tick col-md-1',
                'report_id' => 'page_views'
              )
            );

$aHtml[] = Form::get_labeled_select('domain_id', 'Domain', $sDropdown, 2);

$sTextbox = Form::get_textbox('page_view_id', null, 'form-control', null, array('filter' => 1, 'report_id' => 'page_views', 'class' => 'col-md-1'));
$aHtml[] = Form::get_labeled_item('page_view_id', 'Page View ID', $sTextbox, 1);

$sTextbox = Form::get_textbox('url', null, 'form-control', null, array('filter' => 1, 'report_id' => 'page_views', 'class' => 'col-md-1'));
$aHtml[] = Form::get_labeled_item('url', 'URL', $sTextbox, 2);

$sTextbox = Form::get_textbox('request', null, 'form-control', null, array('filter' => 1, 'report_id' => 'page_views', 'class' => 'col-md-1'));
$aHtml[] = Form::get_labeled_item('request', 'Request', $sTextbox, 2);


$aHtml[] = '<span class="btn btn-primary clickable bold" onclick="run_report(\'page_views\');">Run Report</span>';
$aHtml[] = '<span class="btn btn-primary clickable bold" onclick="clear_filters(\'page_views\');">Clear Filters</span>';
$aHtml[] = '<br/><br/><br/>';
//get_labeled_texbox



$aHtml[] = '</div>';


return implode($aHtml, "\n");