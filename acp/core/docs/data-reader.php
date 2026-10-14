<?php

/**
 * global variables
 * @var string $languagePack
 * @var string $languagePackFallback
 * @var array $lang
 */

require_once __DIR__.'/functions.php';

if(isset($_GET['file'])) {

    // strip a trailing #anchor before filtering - se_filter_filepath()'s
    // whitelist doesn't include '#', so left in place it would get dropped
    // and glued onto the filename (e.g. "basics.md#dashboard" -> "basics.mddashboard").
    // The anchor itself is only needed client-side (see backend.js pendingAnchor).
    $file = se_filter_filepath(explode('#', $_GET['file'], 2)[0]);

    if($_GET['file'] == 'start') {
        $file = '01-00-introduction.md';
    }

    $modal_title = $lang['btn_docs'];
    $docs_context = se_docs_resolve_context($file);
    if($docs_context !== null) {
        $modal_title .= ' · '.htmlspecialchars($docs_context['name']);
    }

    echo '<div class="modal-dialog modal-xl modal-dialog-centered">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title">'.$modal_title.'</h5>
    </div>
    <div class="modal-body" style="height: 70vh">
    <div class="h-100" id="showModalContent" hx-get="/admin-xhr/docs/read/?show_file='.$file.'" hx-trigger="load">
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
    $docs_version = 'v2';
    $docs_root = __DIR__.'/../../../docs/'.$docs_version;

    // swiftyedit docs
    $doc_filepath = $docs_root.'/'.$languagePack.'/'.basename($show_file);
    if(!is_file($doc_filepath)) {
        $doc_filepath = $docs_root.'/'.$languagePackFallback.'/'.basename($show_file);
    }

    // theme or plugin docs (readme.md or docs/{lang}/*.md)
    $docs_context = se_docs_resolve_context($show_file);
    if($docs_context !== null) {
        $doc_filepath = $docs_context['file'];
    }

    echo '<div class="row h-100">';
    echo '<div class="w-25 h-100">';

    echo '<div class="h-100 overflow-auto">';

    if($docs_context !== null) {
        // opened from a theme/plugin: show only its own docs
        echo show_context_docs_index($docs_context);
    } else {
        echo show_sysdocs_index();
        echo show_context_overview_index('plugin');
        echo show_context_overview_index('theme');
    }

    echo '</div>';

    echo '</div>';
    echo '<div class="d-flex h-100 w-75">';

    echo '<div class="h-100 overflow-auto" id="docsScrollContainer">';
    if(str_ends_with($show_file, '.md')) {
        $show_file = se_parse_docs_file($doc_filepath);
        $docs_content = $show_file['content'];
        if($docs_context !== null) {
            // relative markdown links ([text](other.md)) point to the same theme/plugin
            $docs_content = preg_replace(
                '#href="([A-Za-z0-9_-][A-Za-z0-9_.-]*\.md(?:\#[^"]*)?)"#',
                'href="'.$docs_context['base'].'$1"',
                $docs_content
            );
        }
        echo $docs_content;
    }
    echo '</div>';
    echo '</div>';
    echo '</div>';
}