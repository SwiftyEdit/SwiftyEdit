<?php

// switch the template for editing posts
if(isset($_POST['set_post_type'])) {
    $_SESSION['post_type_form'] = sanitizeUserInputs($_POST['set_post_type']);
    header( "HX-Refresh: true");
}

// pagination
if(isset($_POST['pagination'])) {
    $_SESSION['pagination_posts_page'] = (int) $_POST['pagination'];
    header( "HX-Trigger: update_posts_list");
    exit;
}

// search
if(isset($_POST['blog_text_filter'])) {
    $_SESSION['posts_text_filter'] = $_SESSION['posts_text_filter'] . ' ' . sanitizeUserInputs($_POST['blog_text_filter']);
    header( "HX-Trigger: update_posts_list");
}

// remove keyword from filter list
if(isset($_POST['rmkey'])) {
    print_r($_POST);
    $all_filter = explode(" ", $_SESSION['posts_text_filter']);
    $_SESSION['posts_text_filter'] = '';
    foreach($all_filter as $f) {
        if($_POST['rmkey'] == "$f") { continue; }
        if($f == "") { continue; }
        $_SESSION['posts_text_filter'] .= "$f ";
    }
    header( "HX-Trigger: update_posts_list");
}

// filter by category
if(isset($_POST['set_filter_cat'])) {

    $set_filter_cat = sanitizeUserInputs($_POST['set_filter_cat']);

    $filter_posts_categories = explode(" ", $_SESSION['filter_posts_categories']);
    $filter_posts_categories = array_unique($filter_posts_categories);

    if(in_array($set_filter_cat, $filter_posts_categories)) {
        // remove this category
        $filter_posts_categories = array_diff($filter_posts_categories, array($set_filter_cat));
    } else {
        // add this category
        $filter_posts_categories[] = $set_filter_cat;
    }

    $_SESSION['filter_posts_categories'] = implode(" ", $filter_posts_categories);

    header( "HX-Trigger: update_posts_list");
}

// filter by type
if(isset($_POST['set_filter_post_types'])) {
    $set_filter_type = sanitizeUserInputs($_POST['set_filter_post_types']);
    $filter_posts_types = explode(",", $_SESSION['filter_posts_types']);
    $filter_posts_types = array_unique($filter_posts_types);
    if(in_array($set_filter_type, $filter_posts_types)) {
        // remove this type
        $filter_posts_types = array_diff($filter_posts_types, array($set_filter_type));
    } else {
        // add this type
        $filter_posts_types[] = $set_filter_type;
    }
    $_SESSION['filter_posts_types'] = implode(",", $filter_posts_types);
    header( "HX-Trigger: update_posts_list");
}

// change priority
if(isset($_POST['post_priority'])) {

    $change_id = (int) $_POST['prio_id'];
    $db_posts->update("se_posts", [
        "post_priority" => $_POST['post_priority']
    ],[
        "post_id" => $change_id
    ]);

    header( "HX-Trigger: update_posts_list");
}

// delete post
if(isset($_POST['delete_post'])) {

    $del_id = (int) $_POST['delete_post'];

    /* first get the post it's data and check the type */
    $this_post_data = se_get_post_data($del_id);

    if($this_post_data['post_type'] == 'g') {
        /* it's a gallery, we have to delete the images too */
        $year = date('Y',$this_post_data['post_date']);
        se_remove_gallery($del_id,$year);
    }

    $delete = $db_posts->delete("se_posts", [
        "post_id" => $del_id
    ]);

    if($delete->rowCount() > 0) {
        show_toast($lang['msg_post_deleted'],'success');
        record_log($_SESSION['user_nick'],"delete post id: $del_id","8");
        header( "HX-Trigger: update_posts_list");
    }
}

// save or update post

if(isset($_POST['save_post'])) {

    print_r($_POST);

    foreach($_POST as $key => $val) {
        if(is_string($val)) {
            $$key = @htmlspecialchars($val, ENT_QUOTES);
        }
    }

    $post_releasedate = time();
    $post_lastedit = time();
    $post_lastedit_from = $_SESSION['user_nick'];
    $post_priority = (int) $_POST['post_priority'];

    if($_POST['post_date'] == "") {
        $post_date = time();
    }

    if($_POST['post_releasedate'] != "") {
        $post_releasedate = strtotime($_POST['post_releasedate']);
    }

    $clean_title = clean_filename($_POST['post_title']);
    $post_date_year = date("Y",$post_releasedate);
    $post_date_month = date("m",$post_releasedate);
    $post_date_day = date("d",$post_releasedate);

    if($_POST['post_slug'] == "") {
        $post_slug = "$post_date_year/$post_date_month/$post_date_day/$clean_title/";
    }

    $post_categories = '';
    if(is_array($_POST['post_categories'])) {
        $post_categories = implode("<->", $_POST['post_categories']);
    }

    $post_images = '';
    if(is_array($_POST['picker_0'])) {
        $post_images_string = implode("<->", $_POST['picker_0']);
        $post_images_string = "<->$post_images_string<->";
        $post_images = $post_images_string;
    }

    /* labels */
    $post_labels = '';
    if(is_array($_POST['post_labels'])) {
        $post_labels = implode(",", $_POST['post_labels']);
    }

    /* fix on top */

    if($_POST['post_fixed'] == 'fixed') {
        $post_fixed = 1;
    } else {
        $post_fixed = 2;
    }


    /* gallery thumbnails */
    if($_POST['del_tmb'] != '') {
        $del_tmb = se_filter_filepath($_POST['del_tmb']);
        $del_img = str_replace('_tmb','_img',$del_tmb);

        if(str_starts_with($del_tmb, '../content/galleries/')) {
            unlink($del_tmb);
            unlink($del_img);
        }
    }

    if($_POST['sort_tmb'] != '') {
        se_rename_gallery_image($_POST['sort_tmb']);
    }

    /* metas */
    if($_POST['post_meta_title'] == '') {
        $post_meta_title = $_POST['post_title'];
    } else {
        $post_meta_title = $_POST['post_meta_title'];
    }
    if($_POST['post_meta_description'] == '') {
        $post_meta_description = strip_tags($_POST['post_teaser']);
    } else {
        $post_meta_description = $_POST['post_meta_description'];
    }

    $post_meta_title = se_return_clean_value($post_meta_title);
    $post_meta_description = se_return_clean_value($post_meta_description);

    // get all $cols
    require SE_ROOT.'install/contents/se_posts.php';
    foreach($cols as $k => $v) {
        if($k == 'post_id') {continue;}
        $value = $$k;
        $inputs[$k] = "$value";
    }


    if($_POST['save_post'] == 'save') {
        $db_posts->insert("se_posts", $inputs);
        $post_id = $db_posts->id();
        record_log($_SESSION['user_nick'],"new post id: $post_id","6");
        show_toast($lang['msg_success_new_record'],'success');
    }
    if($_POST['save_post'] == 'update') {
        $post_id = (int) $_POST['post_id'];
        $db_posts->update("se_posts", $inputs, [
            "post_id" => $post_id
        ]);
        record_log($_SESSION['user_nick'],"updated post id: $post_id","3");
        show_toast($lang['msg_success_db_changed'],'success');
    }

    // update rss feed
    $target_page = $db_content->select("se_pages", "page_permalink", [
        "AND" => [
            "page_type_of_use" => "display_post",
            "page_language" => $post_lang
        ]
    ]);

    if($target_page[0] != '') {
        $rss_url = $se_base_url.$target_page[0].$clean_title.'-'.$post_id.'.html';
        $db_posts->update("se_posts", [
            "post_rss_url" => $rss_url
        ], [
            "post_id" => $post_id
        ]);

        /* send to rss feed */
        if($_POST['post_rss'] == 'on') {
            add_feed("$post_title",$_POST['post_teaser'],"$rss_url","post_$id","",$post_releasedate);
        }
    }


    echo '<pre>';
    //print_r($_POST);
    echo '</pre>';
}
