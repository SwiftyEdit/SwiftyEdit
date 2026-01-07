<?php

/**
 * global variables
 * @var string $languagePack
 * @var string $languagePackFallback
 * @var array $lang
 */

require_once __DIR__.'/functions.php';

if(isset($_GET['file'])) {

    $file = se_filter_filepath($_GET['file']);
    $section = se_filter_filepath($_GET['section']);

    if($_GET['file'] == 'start') {
        $file = '01-00-introduction.md';
    }

    echo '<div class="modal-dialog modal-xl modal-dialog-centered">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title">'.$lang['btn_docs'].'</h5>
    </div>
    <div class="modal-body" style="height: 70vh">
    <div class="h-100" id="showModalContent" hx-get="/admin-xhr/docs/read/?show_file='.$file.'&section='.$section.'" hx-trigger="load">
    </div> 
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'.$lang['close'].'</button>
    </div>
  </div>
</div>';
}

if(isset($_GET['show_file'])) {

    // possible path
    // ../docs/{version}/{lang}/filename.md
    // ../public/assets/themes/{theme}/docs/{lang}/filename.md
    // ../public/assets/themes/{theme}/readme.md
    // ../plugins/{plugin}/docs/{lang}/filename.md
    // ../plugins/{plugin}/readme.md

    $Parsedown = new ParsedownExtra();

    $show_file = se_filter_filepath($_GET['show_file']);
    $section = se_filter_filepath($_GET['section']);
    $docs_version = 'v2';

    // swiftyedit docs
    if(str_contains($show_file, '../docs/')) {
        $doc_filepath = '../docs/'.$docs_version.'/'.$languagePack.'/'.basename($show_file);
    }
    // single file - swiftyedit docs
    if(!str_contains($show_file, '/')) {
        if(str_starts_with($show_file, 'tip-')) {
            $doc_filepath = '../docs/'.$docs_version.'/'.$languagePack.'/tooltips/'.basename($show_file);
        } else {
            $doc_filepath = '../docs/'.$docs_version.'/'.$languagePack.'/'. basename($show_file);
        }
    }

    // plugins readme
    if(str_contains($show_file, '/plugins/')) {
        $plugin_dir = basename(dirname($show_file));
        $doc_filepath = SE_ROOT.'plugins/'.$plugin_dir.'/'.basename($show_file);
    }
    // theme readme
    if(str_contains($show_file, '/themes/')) {
        $theme_dir = basename(dirname($show_file));
        $doc_filepath = SE_PUBLIC.'/assets/themes/'.$theme_dir.'/'.basename($show_file);
    }

    echo '<div class="row h-100">';
    echo '<div class="w-25 h-100">';

    echo '<div class="h-100 overflow-auto">';

    echo show_sysdocs_index();
    echo show_plugins_index();
    echo show_themedocs_index();

    echo '</div>';

    echo '</div>';
    echo '<div class="d-flex h-100 w-75">';

    echo '<div class="h-100 overflow-auto">';
    if(str_ends_with($show_file, '.md')) {
        $show_file = se_parse_docs_file($doc_filepath);
        echo $show_file['content'];
    }
    echo '</div>';
    echo '</div>';
    echo '</div>';
}