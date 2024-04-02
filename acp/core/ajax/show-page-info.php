<?php

/**
 * show page info in modal
 *
 * @var array $lang
 */

require '_include.php';

$set_lang = $_SESSION['lang'];
if(isset($_REQUEST['set_lang'])) {
    $set_lang = se_return_clean_value($_REQUEST['set_lang']);
}


if(isset($_POST['pageid'])){
    $page_id = (int) $_POST['pageid'];
}

$page_data = $db_content->get("se_pages", "*", [
    "page_id" => "$page_id"
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
    $thumbs = explode('<->', $page_data['page_thumbnail']);
    echo'<img src="'.$thumbs[0].'" class="img-fluid">';
} else {
    echo'<img src="images/swiftyedit-page-icon.png" class="img-fluid">';
}

echo '</div>';
echo '</div>';




echo '<hr>';
echo '<div class="btn-group d-flex justify-content-end">';
if($_SESSION['acp_editpages'] == "allowed"){
    echo '<form action="?tn=pages&sub=edit" method="POST">';
    echo '<button class="btn btn-sm btn-default ms-auto me-1" name="editpage" value="'.$page_data['page_id'].'" title="'.$lang['edit'].'">'.$lang['edit'].'</button>';
    echo '<button type="button" class="btn btn-sm btn-default" data-bs-dismiss="modal">Close</button>';
    echo $hidden_csrf_token;
    echo '</form>';
} else {
    echo '<button type="button" class="btn btn-sm btn-default" data-bs-dismiss="modal">Close</button>';
}
echo '</div>';
