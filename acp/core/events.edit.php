<?php

//prohibit unauthorized access
require 'core/access.php';

/* set modus */

if((isset($_POST['id'])) && is_numeric($_POST['id'])) {

    $id = (int) $_POST['id'];
    $modus = 'update';
    $event_data = se_get_event_data($id);
    $submit_btn = '<input type="submit" class="btn btn-success w-100" name="save_event" value="'.$lang['update'].'">';

} else {
    $id = '';
    $modus = 'new';
    $submit_btn = '<input type="submit" class="btn btn-success w-100" name="save_event" value="'.$lang['save'].'">';

}

/* save or update post data */

if(isset($_POST['save_event']) OR isset($_POST['del_tmb']) OR isset($_POST['sort_tmb'])) {

    foreach($_POST as $key => $val) {
        if(is_string($val)) {
            $$key = @htmlspecialchars($val, ENT_QUOTES);
        }
    }

    $releasedate = time();
    $lastedit = time();
    $lastedit_from = $_SESSION['user_nick'];
    $priority = (int) $_POST['priority'];

    if($_POST['date'] == "") {
        $date = time();
    }

    if($_POST['releasedate'] != "") {
        $releasedate = strtotime($_POST['releasedate']);
    }

    if($_POST['event_start'] != "") {
        $event_start = strtotime($_POST['event_start']);
    }

    if($_POST['event_end'] != "") {
        $event_end = strtotime($_POST['event_end']);
        if($event_end < $event_start) {
            $event_end = $event_start;
        }
    }

    $event_startdate = $event_start;
    $event_enddate = $event_end;

    $clean_title = clean_filename($_POST['title']);
    $date_year = date("Y",$releasedate);
    $date_month = date("m",$releasedate);
    $date_day = date("d",$releasedate);


    if($_POST['slug'] == "") {
        $slug = "$date_year/$date_month/$date_day/$clean_title/";
    }

    $categories = '';
    if(is_array($_POST['categories'])) {
        $categories = implode("<->", $_POST['categories']);
    }

    $images = '';
    if(is_array($_POST['picker1_images'])) {
        $event_images_string = implode("<->", $_POST['picker1_images']);
        $event_images_string = "<->$event_images_string<->";
        $images = $event_images_string;
    }

    /* labels */
    $labels = '';
    if(is_array($_POST['labels'])) {
        $labels = implode(",", $_POST['labels']);
    }

    /* fix on top */

    if($_POST['fixed'] == 'fixed') {
        $fixed = 1;
    } else {
        $fixed = 2;
    }

    /* metas */
    if($_POST['meta_title'] == '') {
        $meta_title = $_POST['title'];
    } else {
        $meta_title = $_POST['meta_title'];
    }
    if($_POST['meta_description'] == '') {
        $meta_description = strip_tags($_POST['teaser']);
    } else {
        $meta_description = $_POST['meta_description'];
    }

    $meta_title = se_return_clean_value($meta_title);
    $meta_description = se_return_clean_value($meta_description);


    /* save or update data */

    /* get all $cols */
    require '../install/contents/se_events.php';
    // build sql string -> f.e. "releasedate" => $releasedate,
    foreach($cols as $k => $v) {
        if($k == 'id') {continue;}
        $value = $$k;
        $inputs[$k] = "$value";
    }

    if($modus == "update")	{
        $db_posts->update("se_events", $inputs, [
            "id" => $id
        ]);
    } else {
        $db_posts->insert("se_events", $inputs);
        $id = $db_posts->id();
        $modus = 'update';
        $submit_btn = '<input type="submit" class="btn btn-success w-100" name="save_post" value="'.$lang['update'].'">';
    }

    /* update the rss url */

    // get the posting-page by 'type_of_use' and $languagePack
    $target_page = $db_content->select("se_pages", "page_permalink", [
        "AND" => [
            "page_type_of_use" => "display_event",
            "page_language" => $event_lang
        ]
    ]);

    // if we have no target page - find a blog page
    if($target_page[0] == '') {
        $target_page = $db_content->select("se_pages", "page_permalink", [
            "AND" => [
                "page_posts_types[~]" => "e",
                "page_language" => $event_lang
            ]
        ]);
    }

    if($target_page[0] != '') {
        $rss_url = $se_base_url.$target_page[0].$clean_title.'-'.$id.'.html';
        $db_posts->update("se_events", [
            "rss_url" => $rss_url
        ], [
            "id" => $id
        ]);

        /* send to rss feed */
        if($_POST['rss'] == 'on') {
            add_feed("$title",$_POST['teaser'],"$rss_url","event_$id","",$releasedate);
        }
    }


    /* re load the posts data */
    $event_data = se_get_event_data($id);
}

/* language */

$event_lang = $event_data['event_lang'];

if($event_lang == '' && $default_lang_code != '') {
    $event_lang = $default_lang_code;
}

$select_lang  = '<select name="event_lang" class="custom-select form-control">';
foreach($lang_codes as $lang_code) {
    $select_lang .= "<option value='$lang_code'".($event_lang == "$lang_code" ? 'selected="selected"' :'').">$lang_code</option>";
}
$select_lang .= '</select>';



/* categories */

$cats = se_get_categories();
for($i=0;$i<count($cats);$i++) {
    $category = $cats[$i]['cat_name'];
    $array_categories = explode("<->", $event_data['categories']);
    $checked = "";
    if(in_array($cats[$i]['cat_hash'], $array_categories)) {
        $checked = "checked";
    }
    $checkboxes_cat .= '<div class="form-check">';
    $checkboxes_cat .= '<input class="form-check-input" id="cat'.$i.'" type="checkbox" name="categories[]" value="'.$cats[$i]['cat_hash'].'" '.$checked.'>';
    $checkboxes_cat .= '<label class="form-check-label" for="cat'.$i.'">'.$category.' <small>('.$cats[$i]['cat_lang'].')</small></label>';
    $checkboxes_cat .= '</div>';
}


/* release date */
if($event_data['releasedate'] > 0) {
    $releasedate = date('Y-m-d H:i:s', $event_data['releasedate']);
} else {
    $releasedate = date('Y-m-d H:i:s', time());
}


/* event dates */
if($event_data['event_startdate'] > 0) {
    $event_startdate = date('Y-m-d H:i:s', $event_data['event_startdate']);
} else {
    $event_startdate = date('Y-m-d H:i:s', time());
}

if($event_data['event_enddate'] > 0) {
    $event_enddate = date('Y-m-d H:i:s', $event_data['event_enddate']);
} else {
    $event_enddate = date('Y-m-d H:i:s', time());
}


/* priority */
$select_priority = "<select name='priority' class='form-control custom-select'>";
for($i=1;$i<11;$i++) {
    $option_add = '';
    $sel_prio = '';
    if($i == 1) {
        $option_add = ' ('.$lang['low'].')';
    }
    if($i == 10) {
        $option_add = ' ('.$lang['high'].')';
    }
    if($event_data['priority'] == $i) {
        $sel_prio = 'selected';
    }
    $select_priority .= '<option value="'.$i.'" '.$sel_prio.'>'.$i.' '.$option_add.'</option>';
}
$select_priority .= '</select>';


/* fix post on top */
if($event_data['fixed'] == '1') {
    $checked_fixed = 'checked';
}
$checkbox_fixed  = '<div class="form-check">';
$checkbox_fixed .= '<input class="form-check-input" id="fix" type="checkbox" name="fixed" value="fixed" '.$checked_fixed.'>';
$checkbox_fixed .= '<label class="form-check-label" for="fix">'.$lang['fixed'].'</label>';
$checkbox_fixed .= '</div>';


/* image widget */
$images = se_get_all_media_data('image');
$images = se_unique_multi_array($images,'media_file');
$array_images = explode("<->", $event_data['images']);
$choose_images = se_select_img_widget($images,$array_images,$prefs_posts_images_prefix,1);

/* status | draft or published */
if($event_data['status'] == "draft") {
    $sel_status_draft = "selected";
} else {
    $sel_status_published = "selected";
}
$select_status = "<select name='status' class='form-control custom-select'>";
if($_SESSION['drm_can_publish'] == "true") {
    $select_status .= '<option value="2" '.$sel_status_draft.'>'.$lang['status_draft'].'</option>';
    $select_status .= '<option value="1" '.$sel_status_published.'>'.$lang['status_public'].'</option>';
} else {
    /* user can not publish */
    $select_status .= '<option value="draft" selected>'.$lang['status_draft'].'</option>';
}
$select_status .= '</select>';

/* comments yes/no */

if($event_data['comments'] == 1) {
    $sel_comments_yes = 'selected';
    $sel_comments_no = '';
} else {
    $sel_comments_no = 'selected';
    $sel_comments_yes = '';
}

$select_comments  = '<select id="select_comments" name="comments"  class="custom-select form-control">';
$select_comments .= '<option value="1" '.$sel_comments_yes.'>'.$lang['yes'].'</option>';
$select_comments .= '<option value="2" '.$sel_comments_no.'>'.$lang['no'].'</option>';
$select_comments .= '</select>';

/* votings/reactions no, yes for registered users, yes for all */

if($event_data['votings'] == '') {
    $event_data['votings'] = $prefs_posts_default_votings;
}

if($event_data['votings'] == 1 OR $event_data['votings'] == '') {
    $sel_votings_1 = 'selected';
    $sel_votings_2 = '';
    $sel_votings_3 = '';
} else if($event_data['votings'] == 2) {
    $sel_votings_1 = '';
    $sel_votings_2 = 'selected';
    $sel_votings_3 = '';
} else {
    $sel_votings_1 = '';
    $sel_votings_2 = '';
    $sel_votings_3 = 'selected';
}

$select_votings  = '<select id="select_votings" name="votings"  class="custom-select form-control">';
$select_votings .= '<option value="1" '.$sel_votings_1.'>'.$lang['label_votings_status_off'].'</option>';
$select_votings .= '<option value="2" '.$sel_votings_2.'>'.$lang['label_votings_status_registered'].'</option>';
$select_votings .= '<option value="3" '.$sel_votings_3.'>'.$lang['label_votings_status_global'].'</option>';
$select_votings .= '</select>';


/* autor */

if($event_data['author'] == '') {
    $event_data['author'] = $_SESSION['user_firstname'] .' '. $_SESSION['user_lastname'];
}

if($event_data['author'] == "" && $prefs_default_publisher != '') {
    $event_data['author'] = $prefs_default_publisher;
}

if($prefs_publisher_mode == 'overwrite') {
    $event_data['author'] = $prefs_default_publisher;
}


/* RSS */
if($event_data['rss'] == "on") {
    $sel1 = "selected";
} else {
    $sel2 = "selected";
}
$select_rss = "<select name='rss' class='form-control custom-select'>";
$select_rss .= '<option value="on" '.$sel1.'>'.$lang['yes'].'</option>';
$select_rss .= '<option value="off" '.$sel2.'>'.$lang['no'].'</option>';
$select_rss .=	'</select>';


$form_tpl = file_get_contents('templates/post_event.tpl');
$event_data['type'] = 'e';

/* replace all entries from $lang */
foreach($lang as $k => $v) {
    $form_tpl = str_replace('{'.$k.'}', $lang[$k], $form_tpl);
}

/* labels */

$arr_checked_labels = explode(",", $event_data['labels']);

for($i=0;$i<$cnt_labels;$i++) {
    $label_title = $se_labels[$i]['label_title'];
    $label_id = $se_labels[$i]['label_id'];
    $label_color = $se_labels[$i]['label_color'];

    if(in_array("$label_id", $arr_checked_labels)) {
        $checked_label = "checked";
    } else {
        $checked_label = "";
    }

    $checkbox_set_labels .= '<div class="form-check form-check-inline" style="border-bottom: 1px solid '.$label_color.'">';
    $checkbox_set_labels .= '<input class="form-check-input" id="label'.$label_id.'" type="checkbox" '.$checked_label.' name="labels[]" value="'.$label_id.'">';
    $checkbox_set_labels .= '<label class="form-check-label" for="label'.$label_id.'">'.$label_title.'</label>';
    $checkbox_set_labels .= '</div>';
}

$form_tpl = str_replace('{event_labels}', $checkbox_set_labels, $form_tpl);


/* user inputs */

$form_tpl = str_replace('{title}', $event_data['title'], $form_tpl);
$form_tpl = str_replace('{teaser}', $event_data['teaser'], $form_tpl);
$form_tpl = str_replace('{text}', $event_data['text'], $form_tpl);
$form_tpl = str_replace('{author}', $event_data['author'], $form_tpl);
$form_tpl = str_replace('{source}', $event_data['source'], $form_tpl);
$form_tpl = str_replace('{slug}', $event_data['slug'], $form_tpl);
$form_tpl = str_replace('{tags}', $event_data['tags'], $form_tpl);
$form_tpl = str_replace('{rss_url}', $event_data['rss_url'], $form_tpl);
$form_tpl = str_replace('{select_rss}', $select_rss, $form_tpl);
$form_tpl = str_replace('{select_status}', $select_status, $form_tpl);

$form_tpl = str_replace('{meta_title}', $event_data['meta_title'], $form_tpl);
$form_tpl = str_replace('{meta_description}', $event_data['meta_description'], $form_tpl);

$form_tpl = str_replace('{checkboxes_lang}', $select_lang, $form_tpl);
$form_tpl = str_replace('{checkbox_categories}', $checkboxes_cat, $form_tpl);
$form_tpl = str_replace('{releasedate}', $releasedate, $form_tpl);
$form_tpl = str_replace('{widget_images}', $choose_images, $form_tpl);


$form_tpl = str_replace('{select_priority}', $select_priority, $form_tpl);
$form_tpl = str_replace('{checkbox_fixed}', $checkbox_fixed, $form_tpl);
$form_tpl = str_replace('{select_status}', $select_status, $form_tpl);
$form_tpl = str_replace('{select_comments}', $select_comments, $form_tpl);
$form_tpl = str_replace('{select_votings}', $select_votings, $form_tpl);

/* video */
$form_tpl = str_replace('{video_url}', $event_data['video_url'], $form_tpl);

/* links */
$form_tpl = str_replace('{link}', $event_data['link'], $form_tpl);

/* files */
$form_tpl = str_replace('{file_attachment_external}', $event_data['file_attachment_external'], $form_tpl);
$form_tpl = str_replace('{file_license}', $event_data['file_license'], $form_tpl);
$form_tpl = str_replace('{file_version}', $event_data['file_version'], $form_tpl);
$form_tpl = str_replace('{select_file}', $select_file, $form_tpl);

/* events */
$form_tpl = str_replace('{event_start}', $event_startdate, $form_tpl);
$form_tpl = str_replace('{event_end}', $event_enddate, $form_tpl);
$form_tpl = str_replace('{event_street}', $event_data['event_street'], $form_tpl);
$form_tpl = str_replace('{event_street}', $event_data['event_street'], $form_tpl);
$form_tpl = str_replace('{event_street_nbr}', $event_data['event_street_nbr'], $form_tpl);
$form_tpl = str_replace('{event_zip}', $event_data['event_zip'], $form_tpl);
$form_tpl = str_replace('{event_city}', $event_data['event_city'], $form_tpl);
$form_tpl = str_replace('{event_street}', $event_data['event_street'], $form_tpl);
$form_tpl = str_replace('{event_price_note}', $event_data['event_price_note'], $form_tpl);
$form_tpl = str_replace('{event_guestlist_limit}', $event_data['event_guestlist_limit'], $form_tpl);

/* guest list */

$sel_gl_type1 = '';
$sel_gl_type2 = '';
$sel_gl_type3 = '';

if($event_data['event_guestlist'] == '') {
    $event_data['event_guestlist'] = $prefs_posts_default_guestlist;
}

if($event_data['event_guestlist'] == '1') {
    $sel_gl_type1 = 'selected';
} else if($event_data['event_guestlist'] == '2') {
    $sel_gl_type2 = 'selected';
} else if($event_data['event_guestlist'] == '3') {
    $sel_gl_type3 = 'selected';
}

$select_guestlist = '<select class="form-control custom-select" name="event_guestlist">';

$select_guestlist .= '<option value="1" '.$sel_gl_type1.'>'.$lang['label_guestlist_status_deactivate'].'</option>';
$select_guestlist .= '<option value="2" '.$sel_gl_type2.'>'.$lang['label_guestlist_status_registered'].'</option>';
$select_guestlist .= '<option value="3" '.$sel_gl_type3.'>'.$lang['label_guestlist_status_global'].'</option>';

$select_guestlist .= '</select>';
$form_tpl = str_replace('{select_guestlist}', $select_guestlist, $form_tpl);

if($event_data['event_guestlist_public_nbr'] == '1') {
    $form_tpl = str_replace('{checked_gl_public_nbr_1}', 'checked', $form_tpl);
    $form_tpl = str_replace('{checked_gl_public_nbr_2}', '', $form_tpl);
} else {
    $form_tpl = str_replace('{checked_gl_public_nbr_1}', '', $form_tpl);
    $form_tpl = str_replace('{checked_gl_public_nbr_2}', 'checked', $form_tpl);
}

$form_tpl = str_replace('{checked_guestlist}', $checked_guestlist, $form_tpl);



/* form modes */

$form_tpl = str_replace('{type}', $event_data['type'], $form_tpl);
$form_tpl = str_replace('{id}', $event_data['id'], $form_tpl);
$form_tpl = str_replace('{date}', $event_data['date'], $form_tpl);
$form_tpl = str_replace('{year}', date('Y',$event_data['date']), $form_tpl);
$form_tpl = str_replace('{modus}', $modus, $form_tpl);
$form_tpl = str_replace('{token}', $_SESSION['token'], $form_tpl);
$form_tpl = str_replace('{formaction}', '?tn=events&sub=edit', $form_tpl);
$form_tpl = str_replace('{submit_button}', $submit_btn, $form_tpl);


echo $form_tpl;
