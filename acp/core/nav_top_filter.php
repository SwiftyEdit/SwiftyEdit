<?php

if(!isset($_SESSION['global_filter_label'])) {
    $_SESSION['global_filter_label'] = '';
}

if(!isset($_SESSION['global_filter_languages'])) {
    $_SESSION['global_filter_languages'] = '';
}

if(!isset($_SESSION['global_filter_status'])) {
    $_SESSION['global_filter_status'] = '';
}

$cnt_global_filters = 0;
$global_filter_label = array();
$global_filter_languages = array();
$global_filter_status = array();

if($_SESSION['global_filter_label'] != '') {
    $cnt_global_filters++;
    $global_filter_label = json_decode($_SESSION['global_filter_label']);
}

if($_SESSION['global_filter_languages'] != '') {
    $cnt_global_filters++;
    $global_filter_languages = json_decode($_SESSION['global_filter_languages']);
}

if($_SESSION['global_filter_status'] != '') {
    $cnt_global_filters++;
    $global_filter_status = json_decode($_SESSION['global_filter_status']);
}


echo '<div class="offcanvas offcanvas-end" tabindex="-1" id="globalFilter" aria-labelledby="offcanvasTopLabel">';
echo '<div class="offcanvas-header">';
echo '<h5 class="offcanvas-title" id="offcanvasTopLabel">Filter</h5>';
echo '<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>';
echo '</div>';
echo '<div class="offcanvas-body">';

echo '<div id="globalFilterResponse"></div>';

echo '<form hx-post="/admin-xhr/widgets/write/" hx-target="#globalFilterResponse" hx-swap="innerHTML" id="globalFilterForm">';


/* status */
echo '<div class="card mt-1">';
echo '<div class="card-header p-1 px-2">'.$lang['label_status'].'</div>';
echo '<div class="card-body">';

for($i=1;$i<6;$i++) {
    $var = 'checked_status_'.$i;
    $$var = '';
    if(in_array("$i",$global_filter_status)) {
        $$var = 'checked';
    }
}

echo '<input type="checkbox" name="set_status[]" value="1" class="btn-check" id="status-1" '.$checked_status_1.' autocomplete="off">';
echo '<label class="btn btn-sm btn-default m-1" for="status-1">'.$lang['status_public'].'</label>';

echo '<input type="checkbox" name="set_status[]" value="2" class="btn-check" id="status-2" '.$checked_status_2.' autocomplete="off">';
echo '<label class="btn btn-sm btn-default m-1" for="status-2">'.$lang['status_draft'].'</label>';

echo '<input type="checkbox" name="set_status[]" value="3" class="btn-check" id="status-3" '.$checked_status_3.' autocomplete="off">';
echo '<label class="btn btn-sm btn-default m-1" for="status-3">'.$lang['status_private'].'</label>';

echo '<input type="checkbox" name="set_status[]" value="4" class="btn-check" id="status-4" '.$checked_status_4.' autocomplete="off">';
echo '<label class="btn btn-sm btn-default m-1" for="status-4">'.$lang['status_ghost'].'</label>';

echo '<input type="checkbox" name="set_status[]" value="5" class="btn-check" id="status-5" '.$checked_status_5.' autocomplete="off">';
echo '<label class="btn btn-sm btn-default m-1" for="status-5">'.$lang['status_redirect'].'</label>';

echo '</div>';
echo '</div>';

/* labels */
echo '<div class="card mt-1">';
echo '<div class="card-header">'.$lang['labels'].'</div>';
echo '<div class="card-body">';
for($i=0;$i<$cnt_labels;$i++) {

    $checked = '';
    if(in_array($se_labels[$i]['label_id'],$global_filter_label)) {
        $checked = 'checked';
    }

    echo '<input type="checkbox" name="set_label[]" value="'.$se_labels[$i]['label_id'].'" class="btn-check" id="btn-check-'.$i.'" '.$checked.' autocomplete="off">';
    echo '<label class="btn btn-sm btn-default m-1" for="btn-check-'.$i.'">';
    echo '<span class="label-dot" style="background-color: '.$se_labels[$i]['label_color'].'"></span> ';
    echo $se_labels[$i]['label_title'].'</label>';

}

echo '</div>';
echo '</div>';

/* languages */
echo '<div class="card mt-1">';
echo '<div class="card-header p-1 px-2">'.$lang['label_language'].'</div>';
echo '<div class="card-body">';

foreach($lang_codes as $lang_code) {
    $checked = '';

    if(in_array($lang_code,$global_filter_languages)) {
        $checked = 'checked';
    }

    echo '<input type="checkbox" name="set_lang[]" value="'.$lang_code.'" class="btn-check" id="lang-check-'.$lang_code.'" '.$checked.' autocomplete="off">';
    echo '<label class="btn btn-sm btn-default m-1" for="lang-check-'.$lang_code.'">';
    echo $lang_code.'</label>';
}

echo '</div>';
echo '</div>';


echo '<div class="mt-3">';
echo '<button type="submit" class="btn btn-default" name="set_global_filter">'.$lang['save'].'</button>';
echo '</div>';

echo $hidden_csrf_token;


echo '</form>';

echo '</div>';
echo '</div>';