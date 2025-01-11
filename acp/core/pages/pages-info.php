<?php

$get_page_id = (int) $_GET['page_info'];
$page_data = $db_content->get("se_pages", "*", [
    "page_id" => "$get_page_id"
]);

$page_id = (int) $page_data['page_id'];
$page_hash = se_return_clean_value($page_data['page_hash']);
$page_classes = se_return_clean_value($page_data['page_classes']);
$page_hits = (int) $page_data['page_hits'];
$page_title = se_return_clean_value($page_data['page_title']);
$page_description = se_return_clean_value($page_data['page_meta_description']);
$page_keywords = se_return_clean_value($page_data['page_meta_keywords']);
$page_robots = se_return_clean_value($page_data['page_meta_robots']);
$page_status = se_return_clean_value($page_data['page_status']);
$page_linkname = se_return_clean_value($page_data['page_linkname']);
$page_permalink = se_return_clean_value($page_data['page_permalink']);
$page_permalink_short = se_return_clean_value($page_data['page_permalink_short']);
$page_short_link_hits = (int) $page_data['page_permalink_short_cnt'];
$page_redirect = se_return_clean_value($page_data['page_redirect']);
$page_redirect_code = (int) $page_data['page_redirect_code'];
$page_funnel_url = se_return_clean_value($page_data['page_funnel_uri']);


echo '<div class="modal-dialog modal-xl modal-dialog-centered">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title">'.$get_page['page_title'].'</h5>
    </div>
    <div class="modal-body">';

echo '<div class="row">';
echo '<div class="col-md-8">';

echo '<table class="table table-sm">';
echo '<tr><td class="text-end">ID</td><td><code>'.$page_id.'</code></td></tr>';
echo '<tr><td class="text-end">Hash</td><td><code>'.$page_hash.'</code></td></tr>';
echo '<tr><td class="text-end">'.$lang['label_classes'].'</td><td><code>'.$page_classes.'</code></td></tr>';
echo '<tr><td class="text-end text-nowrap">'.$lang['label_pages_pageviews'].'</td><td><code>'.$page_hits.'</code></td></tr>';

echo '<tr><td class="text-end">'.$lang['label_title'].'</td><td><code>'.$page_title.'</code></td></tr>';
echo '<tr><td class="text-end">'.$lang['label_description'].'</td><td><code>'.$page_description.'</code></td></tr>';
echo '<tr><td class="text-end">'.$lang['label_keywords'].'</td><td><code>'.$page_keywords.'</code></td></tr>';

echo '<tr><td class="text-end">'.$lang['label_pages_meta_robots'].'</td><td><code>'.$page_robots.'</code></td></tr>';
echo '<tr><td class="text-end">'.$lang['label_status'].'</td><td><code>'.$page_status.'</code></td></tr>';

echo '<tr><td class="text-end">'.$lang['label_pages_link_name'].'</td><td><code>'.$page_linkname.'</code></td></tr>';
echo '<tr><td class="text-end">'.$lang['label_pages_permalink'].'</td><td><code>'.$page_permalink.'</code></td></tr>';
echo '<tr><td class="text-end">'.$lang['label_pages_permalink_short'].'</td><td><code>'.$page_permalink_short.'</code></td></tr>';
echo '<tr><td class="text-end">'.$lang['label_pages_clicks'].'</td><td><code>'.$page_short_link_hits.'</code></td></tr>';

if($page_data['page_redirect'] != '') {
    echo '<tr><td class="text-end">'.$lang['label_pages_redirect'].'</td><td><code>'.$page_redirect.' ['.$page_redirect_code.']</code></td></tr>';
}

if($page_data['page_funnel_uri'] != '') {
    echo '<tr><td class="text-end">'.$lang['label_pages_funnel_uri'].'</td>';
    echo'<td>';
    $funnels = explode(',', $page_funnel_url);
    foreach($funnels as $funnel) {
        echo '<code>'.se_return_clean_value($funnel).'</code><br>';
    }
    echo '</td>';
}


echo '<tr><td class="text-end">'.$lang['label_content'].'</td><td><code>'.se_return_clean_value(first_words($page_data['page_content'],50)).'</code></td></tr>';
echo '</table>';

echo '</div>';
echo '<div class="col-md-4">';
if($page_data['page_thumbnail'] != '') {
    $thumbs = explode('<->', html_entity_decode($page_data['page_thumbnail']));
    $thumb_src = str_replace('../content/images/', '/images/', $thumbs[0]);
    echo'<img src="'.$thumb_src.'" class="img-fluid">';
} else {
    echo'<img src="images/swiftyedit-page-icon.png" class="img-fluid">';
}

echo '</div>';
echo '</div>';




echo '<hr>';
echo '<div class="btn-group d-flex justify-content-end">';
if($_SESSION['acp_editpages'] == "allowed"){
    echo '<form action="/admin/pages/edit/" method="post" class="d-inline">';
    echo '<button class="btn btn-default" name="page_id" value="'.$page_data['page_id'].'">'.$icon['edit'].' '.$lang['edit'].'</button>';
    echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
    echo '</form>';
}
echo '</div>';

echo '
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'.$lang['close'].'</button>
    </div>
  </div>
</div>';
