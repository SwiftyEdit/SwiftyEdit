<?php
session_start();
error_reporting(E_ALL ^E_NOTICE ^E_DEPRECATED ^E_WARNING);
session_start();

include '../../config.php';
include_once 'functions.php';
include_once '../../core/functions/functions.php';
include 'icons.php';
require '../../core/vendor/autoload.php';
require 'access.php';

$Parsedown = new Parsedown();

$docs_section = 1;
if(isset($_GET['section'])) {
    $_SESSION['docs_section'] = (int) $_GET['section'];
    unset($_SESSION['get_file']);
}

if(!isset($_SESSION['docs_section'])) {
    $_SESSION['docs_section'] = $docs_section;
}

$nav_active = array_fill(1,3, "");
$nav_active[$_SESSION['docs_section']] = 'active';

if(isset($_GET['get_file'])) {
    $_SESSION['get_file'] = se_filter_filepath($_GET['get_file']);
}

if(isset($_SESSION['get_file'])) {
    $show_file = se_parse_docs_file($_SESSION['get_file']);
    $docs_viewer_content = $show_file['content'];
}

if(!isset($show_file)) {
    // show index
    if($_SESSION['docs_section'] == 1) {
        $docs_viewer_content = show_sysdocs_index();
    } else if ($_SESSION['docs_section'] == 2){
        $docs_viewer_content = show_themedocs_index();
    } else {
        $docs_viewer_content = show_addondocs_index();
    }
}


?>


<!DOCTYPE html>
<html lang="<?php echo $languagePack; ?>" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="../theme/dist/backend.css" type="text/css" media="screen, projection">
    <script src="../theme/dist/backend.js"></script>
</head>
<body style="background: var(--bs-widget-bg);">
<div class="container py-2">

<?php

echo '<ul class="nav nav-pills nav-fill">';
echo '<li class="nav-item"><a class="nav-link '.$nav_active[1].'" href="?section=1" title="System">'.$icon['gear'].'</a></li>';
echo '<li class="nav-item"><a class="nav-link '.$nav_active[2].'" href="?section=2" title="Themes">'.$icon['palette2'].'</a></li>';
echo '<li class="nav-item"><a class="nav-link '.$nav_active[3].'" href="?section=3" title="Addons">'.$icon['plugin'].'</a></li>';
echo '</ul>';


echo '<p>'.$docs_viewer_content.'</p>';

?>
</div>
</body>
</html>


<?php

/**
 * list index of system docs
 * @return string
 */
function show_sysdocs_index() {

    $languagePack = $_SESSION['lang'];

    if(is_dir('../docs/'.$languagePack)) {
        $docsfiles = glob('../docs/'.$languagePack.'/*.md');
    } else {
        $docsfiles = glob('../docs/en/*.md');
    }

    foreach($docsfiles as $doc) {
        // skip tooltips
        if (str_starts_with(basename($doc), 'tip-')) {
            continue;
        }

        $parsed_file = se_parse_docs_file($doc);
        $parsed_files[] = [
            "title" => $parsed_file['header']['title'],
            "priority" => $parsed_file['header']['priority'],
            "btn" => $parsed_file['header']['btn'],
            "file" => $doc
        ];
    }

    $sorted_parsed_files = se_array_multisort($parsed_files, 'priority', SORT_ASC);

    $list = '<ul>';
    foreach($sorted_parsed_files as $k => $v) {

        $list .= '<li>';
        $list .= '<a href="?get_file='.$sorted_parsed_files[$k]['file'].'" title="'.$sorted_parsed_files[$k]['title'].'">'.$sorted_parsed_files[$k]['btn'].'</a>';
        $list .= '</li>';

    }
    $list .= '</ul>';

    return $list;
}

function show_themedocs_index() {

    $languagePack = $_SESSION['lang'];
    $list = '';
    $all_themes = get_all_templates();

    foreach($all_themes as $theme) {
        unset($docsfiles,$parsed_files);
        $docs_directory = SE_ROOT.'/styles/'.$theme.'/docs/'.$languagePack;
        if(is_dir($docs_directory)) {
            $docsfiles = glob($docs_directory.'/*.md');
        } else {
            $docs_directory = SE_CONTENT.'/modules/'.$theme.'/docs/en';
            $docsfiles = glob($docs_directory.'/*.md');
        }

        if((is_array($docsfiles)) && count($docsfiles) > 0) {
            $list .= '<div class="card">';
            $list .= '<div class="card-header">'.$theme.'</div>';

            foreach($docsfiles as $doc) {
                // skip tooltips
                if (str_starts_with(basename($doc), 'tip-')) {
                    continue;
                }

                $parsed_file = se_parse_docs_file($doc);
                $parsed_files[] = [
                    "title" => $parsed_file['header']['title'],
                    "priority" => $parsed_file['header']['priority'],
                    "btn" => $parsed_file['header']['btn'],
                    "file" => $doc
                ];
            }

            $sorted_parsed_files = se_array_multisort($parsed_files, 'priority', SORT_ASC);

            $list .= '<ul class="list-group list-group-flush">';
            foreach($sorted_parsed_files as $k => $v) {

                $list .= '<li class="list-group-item">';
                $list .= '<a href="?get_file='.$sorted_parsed_files[$k]['file'].'">'.$sorted_parsed_files[$k]['btn'].'</a>';
                $list .= '</li>';

            }
            $list .= '</ul>';
            $list .= '</div>';
        }
    }

    return $list;
}


function show_addondocs_index() {

    $languagePack = $_SESSION['lang'];
    $list = '';
    $all_addons = get_all_modules();

    foreach($all_addons as $addon) {
        unset($docsfiles,$parsed_files);
        $docs_directory = SE_CONTENT.'/modules/'.$addon['folder'].'/docs/'.$languagePack;
        if(is_dir($docs_directory)) {
            $docsfiles = glob($docs_directory.'/*.md');
        } else {
            $docs_directory = SE_CONTENT.'/modules/'.$addon['folder'].'/docs/en';
            $docsfiles = glob($docs_directory.'/*.md');
        }

        if((is_array($docsfiles)) && count($docsfiles) > 0) {
            $list .= '<div class="card">';
            $list .= '<div class="card-header">'.$addon['folder'].'</div>';

            foreach($docsfiles as $doc) {
                // skip tooltips
                if (str_starts_with(basename($doc), 'tip-')) {
                    continue;
                }

                $parsed_file = se_parse_docs_file($doc);
                $parsed_files[] = [
                    "title" => $parsed_file['header']['title'],
                    "priority" => $parsed_file['header']['priority'],
                    "btn" => $parsed_file['header']['btn'],
                    "file" => $doc
                ];
            }

            $sorted_parsed_files = se_array_multisort($parsed_files, 'priority', SORT_ASC);

            $list .= '<ul class="list-group list-group-flush">';
            foreach($sorted_parsed_files as $k => $v) {

                $list .= '<li class="list-group-item">';
                $list .= '<a href="?get_file='.$sorted_parsed_files[$k]['file'].'">'.$sorted_parsed_files[$k]['btn'].'</a>';
                $list .= '</li>';

            }
            $list .= '</ul>';
            $list .= '</div>';

        }
    }


    return $list;

}

?>