<?php

/**
 * SwiftyEdit /admin-xhr/blog/write/
 * list actions (pagination, search, filters, pin, priority, delete),
 * saving posts and the gallery thumbnails
 *
 * global variables
 * @var array $lang
 * @var object $db_posts
 * @var object $db_content
 * @var string $se_base_url
 * @var array $cols columns of se_posts, from install/contents/se_posts.php (save_post)
 */

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
    $add_text_filter = sanitizeUserInputs($_POST['blog_text_filter']);
    $text_filter = $_SESSION['posts_text_filter'] ?? '';
    if($add_text_filter != '' && !in_array($add_text_filter, explode(' ', $text_filter))) {
        $_SESSION['posts_text_filter'] = trim($text_filter.' '.$add_text_filter);
    }
    // the current page may not exist in the filtered list
    $_SESSION['pagination_posts_page'] = 0;
    header("HX-Trigger: update_posts_list");
    exit;
}

// remove a search keyword
if(isset($_POST['rmkey'])) {
    // the keywords are stored sanitized (see above), the button sends the plain keyword
    $remove_keyword = sanitizeUserInputs($_POST['rmkey']);
    $keywords = explode(' ', $_SESSION['posts_text_filter'] ?? '');
    $keywords = array_filter($keywords, static fn($keyword) => $keyword != '' && $keyword != $remove_keyword);
    $_SESSION['posts_text_filter'] = implode(' ', $keywords);
    $_SESSION['pagination_posts_page'] = 0;
    header("HX-Trigger: update_posts_list");
    exit;
}

// filter by category, a click adds or removes the category
if(isset($_POST['set_filter_cat'])) {
    $set_filter_cat = sanitizeUserInputs($_POST['set_filter_cat']);
    $filter_posts_categories = array_filter(explode(" ", $_SESSION['filter_posts_categories'] ?? ''));
    if(in_array($set_filter_cat, $filter_posts_categories)) {
        $filter_posts_categories = array_diff($filter_posts_categories, [$set_filter_cat]);
    } else {
        $filter_posts_categories[] = $set_filter_cat;
    }
    $_SESSION['filter_posts_categories'] = implode(" ", array_unique($filter_posts_categories));
    $_SESSION['pagination_posts_page'] = 0;
    header("HX-Trigger: update_posts_list");
    exit;
}

// filter by type, a click adds or removes the type
if(isset($_POST['set_filter_post_types'])) {
    $set_filter_type = sanitizeUserInputs($_POST['set_filter_post_types']);
    $filter_posts_types = array_filter(explode(",", $_SESSION['filter_posts_types'] ?? ''));
    if(in_array($set_filter_type, $filter_posts_types)) {
        $filter_posts_types = array_diff($filter_posts_types, [$set_filter_type]);
    } else {
        $filter_posts_types[] = $set_filter_type;
    }
    $_SESSION['filter_posts_types'] = implode(",", array_unique($filter_posts_types));
    $_SESSION['pagination_posts_page'] = 0;
    header("HX-Trigger: update_posts_list");
    exit;
}

// pin (sfixed) or unpin (rfixed) a post, the star in the list
if(isset($_POST['sfixed']) || isset($_POST['rfixed'])) {
    $db_posts->update("se_posts", [
        "post_fixed" => isset($_POST['sfixed']) ? 1 : 2
    ],[
        "post_id" => (int) ($_POST['sfixed'] ?? $_POST['rfixed'])
    ]);
    header("HX-Trigger: update_posts_list");
    exit;
}

// change priority, the input in the list
if(isset($_POST['priority'])) {
    $db_posts->update("se_posts", [
        "post_priority" => (int) $_POST['priority']
    ],[
        "post_id" => (int) $_POST['prio_id']
    ]);
    header("HX-Trigger: update_posts_list");
    exit;
}

// delete post
if(isset($_POST['delete_post'])) {

    $del_id = (int) $_POST['delete_post'];

    /* first get the post it's data and check the type */
    $this_post_data = se_get_post_data($del_id);

    if(($this_post_data['post_type'] ?? '') == 'g') {
        /* it's a gallery, we have to delete the images too */
        $year = date('Y',(int) $this_post_data['post_date']);
        se_remove_gallery($del_id,$year);
    }

    $delete = $db_posts->delete("se_posts", [
        "post_id" => $del_id
    ]);

    if($delete !== null && $delete->rowCount() > 0) {
        $db_content->delete("se_tags_relations", [
            "content_type" => "post",
            "content_id" => $del_id
        ]);
        $db_content->delete("se_feeds", [
            "feed_subid" => "post_$del_id"
        ]);
        se_generate_xml_sitemap('posts');
        show_toast($lang['msg_success_entry_delete'],'success');
        record_log($_SESSION['user_nick'],"delete post id: $del_id",8);
    }
    header("HX-Trigger: update_posts_list");
    exit;
}

// save or update post
if(isset($_POST['save_post'])) {

    // all columns of se_posts ($cols)
    require SE_ROOT.'install/contents/se_posts.php';

    /*
     * every column is written: by default from the same-named POST field
     * (html-encoded), a column without a POST field is stored empty -
     * the columns built below overwrite their value
     */
    $values = [];
    foreach(array_keys($cols) as $col) {
        $posted = $_POST[$col] ?? '';
        $values[$col] = is_string($posted) ? htmlspecialchars($posted, ENT_QUOTES) : '';
    }

    $values['post_lastedit'] = time();
    $values['post_lastedit_from'] = $_SESSION['user_nick'];
    $values['post_priority'] = (int) ($_POST['post_priority'] ?? 0);

    if(($_POST['post_date'] ?? '') == '') {
        $values['post_date'] = time();
    }

    // release date from the form, otherwise now (an invalid date is stored empty, as before)
    $values['post_releasedate'] = time();
    if(($_POST['post_releasedate'] ?? '') != '') {
        $values['post_releasedate'] = strtotime($_POST['post_releasedate']);
    }

    if(($_POST['post_slug'] ?? '') == '') {
        $values['post_slug'] = clean_filename($_POST['post_title'] ?? '').'/';
    } else {
        $values['post_slug'] = se_clean_permalink($_POST['post_slug']);
    }

    $post_lang = $_POST['post_lang'] ?? '';

    // the page that displays the posts (display_post), if there is none
    // a catalog page for posts - used as the "default" main category page
    $target_page = $db_content->select("se_pages", "page_permalink", [
        "AND" => [
            "page_type_of_use" => "display_post",
            "page_language" => $post_lang
        ]
    ]);
    if(($target_page[0] ?? '') == '') {
        $target_page = $db_content->select("se_pages", "page_permalink", [
            "AND" => [
                "page_posts_types[~]" => "m",
                "page_language" => $post_lang
            ]
        ]);
    }
    $target_page_permalink = (string) ($target_page[0] ?? '');

    if(($_POST['main_category_slug'] ?? '') == 'default') {
        $values['main_category_slug'] = $target_page_permalink;
    } else {
        $values['main_category_slug'] = se_clean_permalink($_POST['main_category_slug'] ?? '');
    }

    // the rss and canonical urls point to the main category page,
    // same as events (acp/core/events/data-writer.php)
    $post_page_permalink = $values['main_category_slug'] ?: $target_page_permalink;

    // existing posts: build the urls if the form has none
    if(is_numeric($values['post_id'])) {
        $filename = str_replace("/", "", $values['post_slug']) . '-' . $values['post_id'] . '.html';

        // rss url
        if(($_POST['post_rss_url'] ?? '') == '') {
            $values['post_rss_url'] = $se_base_url . $post_page_permalink . $filename;
        }

        // canonical url: keep an explicit value if one was set on the form,
        // otherwise build it from the main category page + slug, same
        // pattern as product canonical urls (see acp/core/functions_shop.php).
        if(($_POST['post_canonical_url'] ?? '') == '') {
            $values['post_canonical_url'] = $se_base_url . $post_page_permalink . $filename;
        }
    }

    $values['post_categories'] = is_array($_POST['post_categories'] ?? null) ? implode("<->", $_POST['post_categories']) : '';

    $values['post_images'] = '';
    if(is_array($_POST['picker_0'] ?? null)) {
        $values['post_images'] = '<->'.implode("<->", $_POST['picker_0']).'<->';
    }

    /* labels */
    $values['post_labels'] = is_array($_POST['post_labels'] ?? null) ? implode(",", $_POST['post_labels']) : '';

    $values['addon_string'] = se_encode_addon_values($_POST['addon_values'] ?? null);

    /* fix on top */
    $values['post_fixed'] = (($_POST['post_fixed'] ?? '') == 'fixed') ? 1 : 2;

    $values['post_hide_detail_page'] = 0;
    if(isset($_POST['posts_hide_detail_page'])) {
        $values['post_hide_detail_page'] = 1;
        $values['post_rss'] = 'off';
    }

    /* metas */
    $post_meta_title = $_POST['post_meta_title'] ?? '';
    if($post_meta_title == '') {
        $post_meta_title = $_POST['post_title'] ?? '';
    }
    $post_meta_description = $_POST['post_meta_description'] ?? '';
    if($post_meta_description == '') {
        $post_meta_description = strip_tags($_POST['post_teaser'] ?? '');
    }
    $values['post_meta_title'] = se_return_clean_value($post_meta_title);
    $values['post_meta_description'] = se_return_clean_value($post_meta_description);

    se_generate_xml_sitemap('posts');

    // post_uuid is not part of the form: keep the existing one on update
    // (or generate one if it is missing), generate a new one for new posts
    $values['post_uuid'] = '';
    if($_POST['save_post'] == 'update') {
        $values['post_uuid'] = (string) $db_posts->get("se_posts", "post_uuid", ["post_id" => (int) ($_POST['post_id'] ?? 0)]);
    }
    if($values['post_uuid'] === '') {
        $values['post_uuid'] = se_generate_uuid();
    }

    // counters are written by the frontend (views, link clicks, downloads) and
    // are not part of the form - on update they must keep their stored value
    $counter_cols = ['post_hits', 'post_link_hits', 'post_file_attachment_hits'];

    $inputs = [];
    foreach(array_keys($cols) as $col) {
        if($col == 'post_id') {continue;}
        if($_POST['save_post'] == 'update' && in_array($col, $counter_cols)) {continue;}
        $inputs[$col] = (string) $values[$col];
    }

    $post_id = 0;
    if($_POST['save_post'] == 'save') {
        $db_posts->insert("se_posts", $inputs);
        $post_id = (int) $db_posts->id();
        record_log($_SESSION['user_nick'],"new post id: $post_id",6);
        show_toast($lang['msg_success_new_record'],'success');
        header( "HX-Redirect: /admin/blog/edit/$post_id/");
    }
    if($_POST['save_post'] == 'update') {
        $post_id = (int) $_POST['post_id'];
        $db_posts->update("se_posts", $inputs, [
            "post_id" => $post_id
        ]);
        record_log($_SESSION['user_nick'],"updated post id: $post_id",3);
        show_toast($lang['msg_success_db_changed'],'success');
    }

    se_set_content_tags('post', $post_id, explode(',', $_POST['content_tags'] ?? ''));

    // send to rss feed
    if(($_POST['post_rss'] ?? '') == 'on' && $post_page_permalink != '') {
        $post_rss_url = (string) $values['post_rss_url'];
        if($post_rss_url == '') {
            $post_rss_url = $se_base_url . $post_page_permalink . str_replace("/", "", $values['post_slug']) . '-' . $post_id . '.html';
        }
        add_feed($values['post_title'], $_POST['post_teaser'] ?? '', $post_rss_url, "post_$post_id", "", $values['post_releasedate']);
    }
    exit;
}

if(isset($_POST['reorder_gallery_tmbs'])) {
    $order = json_decode($_POST['order'] ?? '', true);
    se_reorder_gallery_tmbs($_POST['reorder_gallery_tmbs'], $order);
    header( "HX-Trigger: update_gallery_thumbs");
}

if(isset($_POST['delete_gallery_tmb'])) {
    $del_tmb = se_filter_filepath($_POST['delete_gallery_tmb']);
    $del_img = str_replace('_tmb','_img',$del_tmb);

    if(str_starts_with($del_tmb, SE_PUBLIC.'/assets/galleries/')) {
        unlink($del_tmb);
        unlink($del_img);
        header( "HX-Trigger: update_gallery_thumbs");
    }
}
