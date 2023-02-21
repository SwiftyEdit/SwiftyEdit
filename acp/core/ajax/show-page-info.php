<?php

require '_include.php';

$set_lang = $_SESSION['lang'];
if(isset($_REQUEST['set_lang'])) {
    $set_lang = $_REQUEST['set_lang'];
}


if(isset($_POST['pageid'])){
    $page_id = (int) $_POST['pageid'];
}

$page_data = $db_content->get("se_pages", "*", [
    "page_id" => "$page_id"
]);


echo '<div class="row">';
echo '<div class="col-md-8">';

echo '<table class="table table-sm">';
echo '<tr><td class="text-end">ID</td><td><code>'.$page_data['page_id'].'</code></td></tr>';
echo '<tr><td class="text-end">Hash</td><td><code>'.se_return_clean_value($page_data['page_hash']).'</code></td></tr>';
echo '<tr><td class="text-end">'.$lang['f_page_classes'].'</td><td><code>'.se_return_clean_value($page_data['page_classes']).'</code></td></tr>';
echo '<tr><td class="text-end text-nowrap">Page impressions</td><td><code>'.$page_data['page_hits'].'</code></td></tr>';

echo '<tr><td class="text-end">'.$lang['f_page_title'].'</td><td><code>'.$page_data['page_title'].'</code></td></tr>';
echo '<tr><td class="text-end">'.$lang['f_meta_description'].'</td><td><code>'.$page_data['page_meta_description'].'</code></td></tr>';
echo '<tr><td class="text-end">'.$lang['f_meta_keywords'].'</td><td><code>'.$page_data['page_meta_keywords'].'</code></td></tr>';

echo '<tr><td class="text-end">'.$lang['f_meta_robots'].'</td><td><code>'.se_return_clean_value($page_data['page_meta_robots']).'</code></td></tr>';
echo '<tr><td class="text-end">'.$lang['f_page_status'].'</td><td><code>'.se_return_clean_value($page_data['page_status']).'</code></td></tr>';

echo '<tr><td class="text-end">'.$lang['f_page_linkname'].'</td><td><code>'.se_return_clean_value($page_data['page_linkname']).'</code></td></tr>';
echo '<tr><td class="text-end">'.$lang['f_page_permalink'].'</td><td><code>'.se_return_clean_value($page_data['page_permalink']).'</code></td></tr>';
echo '<tr><td class="text-end">'.$lang['f_page_permalink_short'].'</td><td><code>'.se_return_clean_value($page_data['page_permalink_short']).'</code></td></tr>';
echo '<tr><td class="text-end">'.$lang['h_page_hits'].'</td><td><code>'.se_return_clean_value($page_data['page_permalink_short_cnt']).'</code></td></tr>';

if($page_data['page_redirect'] != '') {
    echo '<tr><td class="text-end">'.$lang['f_page_redirect'].'</td><td><code>'.se_return_clean_value($page_data['page_redirect']).' ['.$page_data['page_redirect_code'].']</code></td></tr>';
}

if($page_data['page_funnel_uri'] != '') {
    echo '<tr><td class="text-end">'.$lang['f_page_funnel_uri'].'</td>';
    echo'<td>';
    $funnels = explode(',', $page_data['page_funnel_uri']);
    foreach($funnels as $funnel) {
        echo '<code>'.se_return_clean_value($funnel).'</code><br>';
    }
    echo '</td>';
}


echo '<tr><td class="text-end">'.$lang['tab_content'].'</td><td><code>'.se_return_clean_value(first_words($page_data['page_content'],50)).'</code></td></tr>';
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
