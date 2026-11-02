<?php

/**
 * SwiftyEdit /admin-xhr/uploads/read/
 * media library: active search keywords, stats, the folder select and the list of files
 *
 * global variables
 * @var array $lang
 * @var object $db_content
 * @var object $twig
 * @var string $languagePack
 * @var array $lang_codes
 * @var array $se_labels
 * @var string $img_path
 * @var string $files_path
 */

use Medoo\Medoo;

require_once __DIR__.'/functions.php';

$writer_uri = '/admin-xhr/uploads/write/';

$action = $_REQUEST['action'] ?? '';

// list active keywords from search input
if($action == 'list_active_searches') {
    $keywords = array_filter(explode(" ", $_SESSION['uploads_text_filter'] ?? ''));
    echo $twig->render('components/list/active-searches.twig', [
        'keywords' => array_map(static fn($keyword) => html_entity_decode($keyword, ENT_QUOTES, 'UTF-8'), array_values($keywords)),
        'writer_uri' => $writer_uri
    ]);
    exit;
}

// number of images and files in the database, one entry per file
if($action == 'show_stats') {
    $count_files = static fn(string $prefix): int => count($db_content->select("se_media", "media_file", [
        "media_file[~]" => "$prefix%",
        "GROUP" => "media_file"
    ]));
    echo $twig->render('uploads/stats.twig', [
        'images' => $count_files('../images/'),
        'files' => $count_files('../files/')
    ]);
    exit;
}

// select of the folders of the media library
if($action == 'select_directory') {

    $current_folder = se_media_current_folder();
    $groups = [];
    foreach([$img_path => $lang['images'], $files_path => $lang['files']] as $root => $label) {
        $folders = [];
        foreach(array_merge([$root], se_get_dirs_rec($root)) as $dir) {
            $folders[] = [
                'value' => $dir,
                // e.g. "images/gallery"
                'label' => basename($root).substr($dir, strlen($root)),
                'selected' => ($dir == $current_folder)
            ];
        }
        $groups[] = ['label' => $label, 'folders' => $folders];
    }

    echo $twig->render('uploads/select-directory.twig', [
        'groups' => $groups,
        'writer_uri' => $writer_uri
    ]);
    exit;
}

// list files
if($action == 'list') {

    $nbr_show_items = 50;
    $active_page = (int) ($_SESSION['pagination_uploads'] ?? 0);

    $current_folder = se_media_current_folder();
    // media DB paths are stored as "../images/..." / "../files/..."
    $file_query = str_replace('assets/', '../', $current_folder);

    // images are shown as thumbnail cards, other files as rows
    $list_view = str_starts_with($file_query, '../images') ? 'thumbs' : 'grid';

    // whitelist sort keys, they are used inside a raw SQL expression below
    $order_key = $_SESSION['sorting_media_list'] ?? 'media_lastedit';
    if(!in_array($order_key, ['media_lastedit', 'media_file', 'media_filesize'])) {
        $order_key = 'media_lastedit';
    }
    $order_direction = (($_SESSION['sorting_media_direction'] ?? 'DESC') == 'ASC') ? 'ASC' : 'DESC';

    // files of the folder (and its subfolders), every search keyword has to be part of the path
    $like = ["$file_query/%"];
    foreach(array_filter(explode(" ", $_SESSION['uploads_text_filter'] ?? '')) as $keyword) {
        $like[] = "%$keyword%";
    }

    // se_media stores one row per file and language.
    // The list shows one entry per file, regardless of the selected language.
    $media_where = [
        "AND" => [
            "media_id[>]" => 0,
            "media_file[~]" => ["AND" => $like]
        ]];

    $media_data_cnt = count($db_content->select("se_media", "media_file",
        $media_where + ["GROUP" => "media_file"]
    ));

    $page_files = $db_content->select("se_media", [
            "media_file",
            "sort_value" => Medoo::raw("MAX(<$order_key>)")
        ],
        $media_where + [
            "GROUP" => "media_file",
            "ORDER" => ["sort_value" => $order_direction],
            "LIMIT" => [$active_page * $nbr_show_items, $nbr_show_items]
        ]
    );
    $page_files = array_column($page_files, 'media_file');

    // fetch all language rows of the files on this page and group them by file
    $media_rows_by_file = [];
    if(count($page_files) > 0) {
        $media_rows = $db_content->select("se_media", "*", [
            "media_file" => $page_files
        ]);
        foreach($media_rows as $row) {
            $media_rows_by_file[$row['media_file']][$row['media_lang']] = $row;
        }
    }

    // flags are base64 encoded images, build them only once per language
    $lang_flags = [];
    foreach($lang_codes as $lang_code) {
        $lang_flags[$lang_code] = return_language_flag_src($lang_code);
    }

    $files = [];
    foreach($page_files as $file) {

        $file_rows = $media_rows_by_file[$file] ?? [];
        if(count($file_rows) < 1) {
            continue;
        }

        // prefer the row of the current backend language, fall back to any existing row
        $media = $file_rows[$languagePack] ?? reset($file_rows);
        // public url, e.g. /images/example.jpg (see .htaccess)
        $src = str_replace('../', '/', $media['media_file']);

        // the thumbnail of an image, if there is one
        $thumb_src = '';
        $media_thumb = (string) $media['media_thumb'];
        if(str_starts_with($media_thumb, '../images_tmb/') && is_file(SE_PUBLIC.'/assets/'.substr($media_thumb, 3))) {
            $thumb_src = substr($media_thumb, 2);
        }

        // label titles are stored html-encoded (sanitizeUserInputs), Twig escapes on output
        $labels = [];
        $media_labels = explode(',', (string) $media['media_labels']);
        foreach($se_labels as $l) {
            if(in_array((string) $l['label_id'], $media_labels)) {
                $labels[] = [
                    'color' => $l['label_color'],
                    'title' => html_entity_decode((string) $l['label_title'], ENT_QUOTES, 'UTF-8')
                ];
            }
        }

        $files[] = [
            'id' => (int) $media['media_id'],
            'form_id' => 'media-form-'.$media['media_id'],
            'path' => $media['media_file'],
            'src' => $src,
            'thumb_src' => $thumb_src ?: $src,
            // path inside the media root, e.g. gallery/example.jpg
            'filename' => preg_replace('#^/(images|files)/#', '', $src),
            'lastedit' => se_format_datetime($media['media_lastedit']),
            'filesize' => readable_filesize($media['media_filesize']),
            'hits' => (int) $media['media_file_hits'],
            // languages with data for this file, the others get a greyed out flag
            'langs' => array_keys($file_rows),
            'labels' => $labels
        ];
    }

    echo $twig->render('uploads/list-files.twig', [
        'view' => $list_view,
        'files' => $files,
        'lang_flags' => $lang_flags,
        'pagination' => se_print_pagination($writer_uri, (int) ceil($media_data_cnt / $nbr_show_items), $active_page, 10, '', 'pagination_uploads'),
        // subfolders can be deleted, the media roots not
        'delete_folder' => (se_media_folder($current_folder, false) !== false) ? $current_folder : '',
        'writer_uri' => $writer_uri
    ]);
    exit;
}
